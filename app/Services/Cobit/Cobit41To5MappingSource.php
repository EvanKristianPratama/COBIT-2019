<?php

namespace App\Services\Cobit;

use App\Models\Cobit4\Cobit4Process;
use App\Models\MstFocusArea;
use App\Models\MstObjective;
use App\Models\MstPractice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Shared read access to the COBIT 4.1 -> COBIT 5 mapping data and the COBIT 5 practice catalog.
 * Used by the 4.1 <-> 5 mapping pages and by the GAMO evolution page.
 */
class Cobit41To5MappingSource
{
    /**
     * COBIT 5 GAMOs and practices, in the shape of config/cobit5-practice-catalog.php.
     * Read from the database; the config file is only used while the catalog tables are missing or empty.
     */
    public function practiceCatalog(): array
    {
        if (Schema::hasTable('mst_cobit5_catalog_gamos') && Schema::hasTable('mst_cobit5_catalog_practices')) {
            $gamos = DB::table('mst_cobit5_catalog_gamos')->orderBy('order_no')->get();
            if ($gamos->isNotEmpty()) {
                return $this->catalogFromDatabase($gamos);
            }
        }

        $catalog = config('cobit5-practice-catalog');
        if (! is_array($catalog) || $catalog === []) {
            $catalogPath = config_path('cobit5-practice-catalog.php');
            if (is_file($catalogPath)) {
                $catalog = require $catalogPath;
            }
        }

        return array_replace([
            'domain_order' => [],
            'domains' => [],
            'unverified_practices' => [],
        ], is_array($catalog) ? $catalog : []);
    }

    private function catalogFromDatabase($gamos): array
    {
        $practices = DB::table('mst_cobit5_catalog_practices')->orderBy('order_no')->get()->groupBy('gamo_code');
        $catalog = ['domain_order' => [], 'domains' => [], 'unverified_practices' => []];

        foreach ($gamos as $gamo) {
            $rows = $practices[$gamo->code] ?? collect();

            if (! $gamo->is_verified) {
                foreach ($rows as $row) {
                    $catalog['unverified_practices'][] = array_filter([
                        'code' => $row->code,
                        'title' => $row->title,
                        'domain_code' => $gamo->domain_code,
                        'domain_title' => $gamo->domain_title,
                        'gamo_code' => $gamo->code,
                        'gamo_title' => $gamo->title,
                        'review_note' => $row->review_note,
                    ], fn ($value) => $value !== null);
                }

                continue;
            }

            if (! in_array($gamo->domain_code, $catalog['domain_order'], true)) {
                $catalog['domain_order'][] = $gamo->domain_code;
            }
            $catalog['domains'][$gamo->domain_code] ??= ['title' => $gamo->domain_title, 'processes' => []];
            $catalog['domains'][$gamo->domain_code]['processes'][$gamo->code] = [
                'title' => $gamo->title,
                'practices' => $rows->map(fn (object $row) => array_filter([
                    'code' => $row->code,
                    'title' => $row->title,
                    'review_note' => $row->review_note,
                ], fn ($value) => $value !== null))->values()->all(),
            ];
        }

        return $catalog;
    }

    /** One row per COBIT 4.1 control objective / COBIT 5 target pair (target columns are null when unmapped). */
    public function forwardRows()
    {
        if (Schema::hasTable('mst_cobit4_cobit5_mapping_sources')
            && Schema::hasTable('trs_cobit4_cobit5_mappings')) {
            return DB::table('mst_cobit4_cobit5_mapping_sources as source')
                ->leftJoin('trs_cobit4_cobit5_mappings as mapping', 'mapping.source_id', '=', 'source.id')
                ->select([
                    'source.id as source_id',
                    'source.code as source_code',
                    'source.domain',
                    'source.title as source_title',
                    'source.mapping_status',
                    'source.mapping_note',
                    'source.review_note',
                    'source.order_no as source_order',
                    'mapping.target_code',
                    'mapping.target_level',
                    'mapping.order_no as target_order',
                ])
                ->orderBy('source.order_no')
                ->orderBy('mapping.order_no')
                ->get();
        }

        return collect(config('cobit4-cobit5-mapping.sources', []))
            ->flatMap(function (array $source, int $sourceOrder) {
                $targets = $source['targets'] ?? [];
                if ($targets === []) {
                    $targets = [null];
                }

                return collect($targets)->map(
                    fn (?string $targetCode, int $targetOrder) => (object) [
                        'source_code' => $source['code'],
                        'domain' => $source['domain'],
                        'source_title' => $source['title'],
                        'mapping_status' => $source['mapping_status'] ?? 'mapped',
                        'mapping_note' => $source['mapping_note'] ?? null,
                        'review_note' => $source['review_note'] ?? null,
                        'target_code' => $targetCode,
                        'target_level' => $targetCode === null ? null : (str_contains($targetCode, '.') ? 'practice' : 'process'),
                        'source_order' => $sourceOrder + 1,
                        'target_order' => $targetOrder + 1,
                    ]
                );
            })
            ->values();
    }

    public function componentLinks(array $catalog, array $sourceCodes = [], array $targetCodes = []): array
    {
        $cobit5Links = [];
        $cobit41Links = [];
        $practiceCodes = collect($catalog['domains'] ?? [])
            ->flatMap(fn (array $domain) => collect($domain['processes'] ?? [])
                ->flatMap(fn (array $process) => collect($process['practices'] ?? [])->pluck('code')))
            ->merge(collect($catalog['unverified_practices'] ?? [])->pluck('code'))
            ->merge($targetCodes)
            ->filter()
            ->unique()
            ->values();

        if (Schema::hasTable('mst_focusarea')) {
            $cobit5FocusAreaIds = MstFocusArea::where('version', '5')->orderBy('id')->pluck('id');
            if (Schema::hasTable('mst_practice')) {
                $cobit5Links = MstPractice::query()
                    ->whereIn('focus_area_id', $cobit5FocusAreaIds)
                    ->whereIn('practice_id', $practiceCodes)
                    ->whereNotNull('objective_id')
                    ->orderBy('focus_area_id')
                    ->get(['practice_id', 'objective_id', 'focus_area_id'])
                    ->unique('practice_id')
                    ->mapWithKeys(fn (MstPractice $practice) => [$practice->practice_id => route('cobit_component.show', [
                        'id' => $practice->objective_id,
                        'focus_area' => $practice->focus_area_id,
                    ])])
                    ->all();
            }

            if (Schema::hasTable('mst_objective')) {
                $cobit5Objectives = MstObjective::query()
                    ->whereIn('focus_area_id', $cobit5FocusAreaIds)
                    ->orderBy('focus_area_id')
                    ->get(['objective_id', 'focus_area_id'])
                    ->unique(fn (MstObjective $objective) => $this->normalizeProcessCode($objective->objective_id));

                foreach ($practiceCodes as $practiceCode) {
                    if (isset($cobit5Links[$practiceCode])) {
                        continue;
                    }

                    $processCode = explode('.', $practiceCode, 2)[0];
                    $objective = $cobit5Objectives->first(
                        fn (MstObjective $candidate) => $this->normalizeProcessCode($candidate->objective_id)
                            === $this->normalizeProcessCode($processCode)
                    );

                    if ($objective) {
                        $cobit5Links[$practiceCode] = route('cobit_component.show', [
                            'id' => $objective->objective_id,
                            'focus_area' => $objective->focus_area_id,
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('mst_focusarea')) {
            $sourceCodes = collect($sourceCodes);
            if ($sourceCodes->isEmpty()) {
                $sourceCodes = collect(config('cobit4-cobit5-mapping.sources', []))->pluck('code');
            }
            $sourceCodes = $sourceCodes->filter()->unique();
            $cobit4FocusAreaIds = MstFocusArea::where('version', '4.1')->orderBy('id')->pluck('id');
            $cobit4Objectives = Schema::hasTable('mst_objective')
                ? MstObjective::query()
                    ->whereIn('focus_area_id', $cobit4FocusAreaIds)
                    ->orderBy('focus_area_id')
                    ->get(['objective_id', 'focus_area_id'])
                    ->unique(fn (MstObjective $objective) => $this->normalizeProcessCode($objective->objective_id))
                : collect();
            $cobit4Processes = Schema::hasTable('mst_cobit4_processes')
                ? Cobit4Process::query()
                    ->whereIn('focus_area_id', $cobit4FocusAreaIds)
                    ->orderBy('focus_area_id')
                    ->get(['code', 'objective_id', 'focus_area_id'])
                    ->unique(fn (Cobit4Process $process) => $this->normalizeProcessCode($process->code))
                : collect();

            foreach ($sourceCodes as $sourceCode) {
                // Application controls AC1-AC6 are represented with their COBIT 4.1 parent process DS11.
                $processCode = preg_match('/^AC[1-6]$/i', $sourceCode)
                    ? 'DS11'
                    : $this->normalizeProcessCode($sourceCode);
                $objective = $cobit4Objectives->first(
                    fn (MstObjective $candidate) => $this->normalizeProcessCode($candidate->objective_id) === $processCode
                );
                $process = $objective ? null : $cobit4Processes->first(
                    fn (Cobit4Process $candidate) => $this->normalizeProcessCode($candidate->code) === $processCode
                );

                if ($objective) {
                    $cobit41Links[$sourceCode] = route('cobit_component.show', [
                        'id' => $objective->objective_id,
                        'focus_area' => $objective->focus_area_id,
                    ]);
                } elseif ($process) {
                    $cobit41Links[$sourceCode] = route('cobit_component.show', [
                        'id' => $process->objective_id ?: $process->code,
                        'focus_area' => $process->focus_area_id,
                    ]);
                }
            }
        }

        return ['cobit5' => $cobit5Links, 'cobit41' => $cobit41Links];
    }

    /**
     * COBIT 5 practice code => COBIT 4.1 control objectives that map to it, ordered as in COBIT 4.1.
     * Process-level targets (e.g. EDM01) are expanded to every practice of that process.
     *
     * @return array<string, list<object{code:string,title:string,note:?string,url:?string}>>
     */
    public function control4ObjectivesByCobit5Practice(array $catalog, $forwardRows, array $cobit41Links): array
    {
        $practiceCodesByProcess = [];
        foreach ($catalog['domains'] ?? [] as $domain) {
            foreach ($domain['processes'] ?? [] as $gamoCode => $process) {
                $practiceCodesByProcess[$gamoCode] = collect($process['practices'])->pluck('code')->all();
            }
        }

        $byPractice = [];
        foreach ($forwardRows as $row) {
            if (blank($row->target_code)) {
                continue;
            }

            $practiceCodes = $row->target_level === 'process'
                ? ($practiceCodesByProcess[$row->target_code] ?? [$row->target_code])
                : [$row->target_code];

            foreach ($practiceCodes as $practiceCode) {
                $byPractice[$practiceCode][$row->source_code] ??= (object) [
                    'code' => $row->source_code,
                    'title' => $row->source_title,
                    'note' => $row->review_note,
                    'url' => $cobit41Links[$row->source_code] ?? null,
                    'order' => $row->source_order,
                ];
            }
        }

        return array_map(
            fn (array $controls) => collect($controls)->sortBy('order')->values()->all(),
            $byPractice
        );
    }

    /** COBIT 4.1 process code => process name, e.g. PO1 => Define a Strategic IT Plan. */
    public function cobit41ProcessTitles(): array
    {
        $titles = ['AC' => 'Application Controls'];
        if (! Schema::hasTable('mst_cobit4_processes') || ! Schema::hasTable('mst_focusarea')) {
            return $titles;
        }

        $focusAreaIds = MstFocusArea::where('version', '4.1')->orderBy('id')->pluck('id');

        return $titles + Cobit4Process::whereIn('focus_area_id', $focusAreaIds)
            ->orderBy('focus_area_id')
            ->get(['code', 'title'])
            ->unique(fn (Cobit4Process $process) => strtoupper($process->code))
            ->mapWithKeys(fn (Cobit4Process $process) => [strtoupper($process->code) => $process->title])
            ->all();
    }

    private function normalizeProcessCode(string $code): ?string
    {
        if (! preg_match('/^([A-Z]+)0*(\d+)/i', $code, $matches)) {
            return null;
        }

        return strtoupper($matches[1]).(int) $matches[2];
    }
}
