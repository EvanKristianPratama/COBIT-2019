<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Services\Cobit\Cobit41To5MappingSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class Cobit4Cobit5MappingController extends Controller
{
    public function __construct(private Cobit41To5MappingSource $source)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'domain' => 'nullable|string|max:10',
            'process' => 'nullable|string|max:10',
        ]);

        $catalog = $this->source->practiceCatalog();
        $forwardRows = $this->source->forwardRows();
        $processTitles = $this->cobit41ProcessTitles();
        $componentLinks = $this->source->componentLinks(
            $catalog,
            $forwardRows->pluck('source_code')->unique()->values()->all(),
            $forwardRows->pluck('target_code')->filter()->unique()->values()->all()
        );
        $gamoNames = [];
        foreach ($catalog['domains'] ?? [] as $domain) {
            foreach ($domain['processes'] ?? [] as $gamoCode => $process) {
                $gamoNames[$gamoCode] = $process['title'];
                foreach ($process['practices'] ?? [] as $practice) {
                    $gamoNames[$practice['code']] = $process['title'];
                }
            }
        }
        foreach ($catalog['unverified_practices'] ?? [] as $practice) {
            $gamoNames[$practice['code']] = $practice['gamo_title'];
        }

        $rows = $forwardRows
            ->groupBy('source_code')
            ->map(function ($sourceRows) use ($catalog, $componentLinks, $gamoNames, $processTitles) {
                $source = $sourceRows->first();
                $processCode = $this->cobit41ProcessCode($source->source_code);
                $targets = $sourceRows
                    ->filter(fn (object $target) => filled($target->target_code))
                    ->pluck('target_code')
                    ->unique()
                    ->values();

                return (object) [
                    'source_code' => $source->source_code,
                    'process_code' => $processCode,
                    'process_title' => $processTitles[$processCode] ?? null,
                    'domain' => $source->domain,
                    'source_title' => $source->source_title,
                    'source_component_url' => $componentLinks['cobit41'][$source->source_code] ?? null,
                    'mapping_status' => $source->mapping_status,
                    'mapping_note' => $source->mapping_note,
                    'review_note' => $source->review_note,
                    'target_components' => $targets
                        ->map(fn (string $code) => (object) [
                            'code' => $code,
                            'gamo_title' => $gamoNames[$code] ?? 'GAMO belum terverifikasi',
                            'component_url' => $this->targetComponentUrl($code, $catalog, $componentLinks['cobit5']),
                        ])
                        ->values(),
                    'target_code' => $targets->implode(' '),
                    'source_order' => $source->source_order,
                ];
            })
            ->sortBy(fn (object $row) => [$this->cobit41DomainRank($row->domain), $row->source_order])
            ->filter(fn (object $row) => blank($filters['domain'] ?? null) || $row->domain === $filters['domain'])
            ->filter(fn (object $row) => blank($filters['process'] ?? null) || $row->process_code === $filters['process'])
            ->filter(fn (object $row) => $this->matchesSearch($row, $filters['q'] ?? null))
            ->values();

        $domains = $forwardRows
            ->pluck('domain')
            ->unique()
            ->sortBy(fn (string $domain) => $this->cobit41DomainRank($domain))
            ->values();

        // Process navigator: every COBIT 4.1 process (PO1, AI2, DS5, ...) with its name, grouped by domain.
        $processes = $forwardRows
            ->unique('source_code')
            ->map(fn (object $row) => (object) [
                'code' => $this->cobit41ProcessCode($row->source_code),
                'domain' => $row->domain,
            ])
            ->unique('code')
            ->map(fn (object $process) => (object) [
                'code' => $process->code,
                'domain' => $process->domain,
                'title' => $processTitles[$process->code] ?? null,
            ])
            ->sortBy(fn (object $process) => [$this->cobit41DomainRank($process->domain), (int) preg_replace('/\D/', '', $process->code)])
            ->groupBy('domain');

        return view('cobit_component.shared.cobit4-cobit5-mapping', [
            'rows' => $rows,
            'domains' => $domains,
            'processes' => $processes,
            'reference' => config('cobit4-cobit5-mapping.reference'),
            'referenceUrl' => config('cobit4-cobit5-mapping.reference_url'),
            'filters' => $filters,
        ]);
    }

    public function reverse(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'gamo' => 'nullable|string|max:5',
        ]);

        $catalog = $this->source->practiceCatalog();
        $forwardRows = $this->source->forwardRows();
        $componentLinks = $this->source->componentLinks(
            $catalog,
            $forwardRows->pluck('source_code')->unique()->values()->all(),
            $forwardRows->pluck('target_code')->filter()->unique()->values()->all()
        );
        $practiceCodesByProcess = [];
        $allGamos = collect();

        foreach ($catalog['domain_order'] as $domainCode) {
            $domain = $catalog['domains'][$domainCode];
            foreach ($domain['processes'] as $gamoCode => $process) {
                $practiceCodesByProcess[$gamoCode] = collect($process['practices'])->pluck('code')->all();
                $allGamos->push((object) [
                    'gamo_code' => $gamoCode,
                    'gamo_title' => $process['title'],
                ]);
            }
        }

        foreach ($catalog['unverified_practices'] as $practice) {
            $allGamos->push((object) [
                'gamo_code' => $practice['gamo_code'],
                'gamo_title' => $practice['gamo_title'],
            ]);
        }

        $mappingsByPractice = [];
        $unmappedControls = collect();
        foreach ($forwardRows as $row) {
            if (blank($row->target_code)) {
                if ($row->mapping_status === 'deleted') {
                    $unmappedControls->push((object) [
                        'code' => $row->source_code,
                        'title' => $row->source_title,
                        'note' => $row->mapping_note,
                        'component_url' => $componentLinks['cobit41'][$row->source_code] ?? null,
                        'order' => $row->source_order,
                    ]);
                }

                continue;
            }

            $targetPracticeCodes = $row->target_level === 'process'
                ? ($practiceCodesByProcess[$row->target_code] ?? [$row->target_code])
                : [$row->target_code];

            foreach ($targetPracticeCodes as $practiceCode) {
                $mappingsByPractice[$practiceCode][] = (object) [
                    'cobit41_code' => $row->source_code,
                    'cobit41_title' => $row->source_title,
                    'component_url' => $componentLinks['cobit41'][$row->source_code] ?? null,
                    'mapping_note' => $row->mapping_note,
                    'review_note' => $row->review_note,
                    'source_order' => $row->source_order,
                ];
            }
        }

        $groups = collect();
        foreach ($catalog['domain_order'] as $domainCode) {
            $domain = $catalog['domains'][$domainCode];

            foreach ($domain['processes'] as $gamoCode => $process) {
                if (filled($filters['gamo'] ?? null) && $filters['gamo'] !== $gamoCode) {
                    continue;
                }

                $practices = collect($process['practices'])
                    ->map(function (array $practice) use ($mappingsByPractice, $componentLinks, $domainCode, $domain, $gamoCode, $process, $filters) {
                        $mappings = collect($mappingsByPractice[$practice['code']] ?? [])
                            ->sortBy('source_order')
                            ->unique('cobit41_code')
                            ->values();
                        $practiceRow = (object) [
                            'code' => $practice['code'],
                            'title' => $practice['title'],
                            'component_url' => $componentLinks['cobit5'][$practice['code']] ?? null,
                            'review_note' => $practice['review_note'] ?? null,
                            'domain_code' => $domainCode,
                            'domain_title' => $domain['title'],
                            'gamo_code' => $gamoCode,
                            'gamo_title' => $process['title'],
                            'mappings' => $mappings,
                        ];

                        $term = trim($filters['q'] ?? '');
                        if ($term === '' || $this->matchesSearch($practiceRow, $term)) {
                            return $practiceRow;
                        }

                        $matchingMappings = $mappings
                            ->filter(fn (object $mapping) => $this->matchesSearch($mapping, $term))
                            ->values();

                        if ($matchingMappings->isEmpty()) {
                            return null;
                        }

                        $practiceRow->mappings = $matchingMappings;

                        return $practiceRow;
                    })
                    ->filter()
                    ->values();

                if ($practices->isNotEmpty()) {
                    $groups->push((object) [
                        'code' => $gamoCode,
                        'title' => $process['title'],
                        'domain_code' => $domainCode,
                        'domain_title' => $domain['title'],
                        'practices' => $practices,
                    ]);
                }
            }

            foreach ($catalog['unverified_practices'] as $practice) {
                if ($practice['domain_code'] !== $domainCode
                    || (filled($filters['gamo'] ?? null) && $filters['gamo'] !== $practice['gamo_code'])) {
                    continue;
                }

                $mappings = collect($mappingsByPractice[$practice['code']] ?? [])
                    ->sortBy('source_order')
                    ->unique('cobit41_code')
                    ->values();
                $practiceRow = (object) [
                    'code' => $practice['code'],
                    'title' => $practice['title'],
                    'component_url' => $componentLinks['cobit5'][$practice['code']] ?? null,
                    'review_note' => $practice['review_note'] ?? null,
                    'domain_code' => $domainCode,
                    'domain_title' => $domain['title'],
                    'gamo_code' => $practice['gamo_code'],
                    'gamo_title' => $practice['gamo_title'],
                    'mappings' => $mappings,
                ];

                $term = trim($filters['q'] ?? '');
                if ($term !== '' && ! $this->matchesSearch($practiceRow, $term)) {
                    $practiceRow->mappings = $mappings
                        ->filter(fn (object $mapping) => $this->matchesSearch($mapping, $term))
                        ->values();

                    if ($practiceRow->mappings->isEmpty()) {
                        continue;
                    }
                }

                $groups->push((object) [
                    'code' => $practice['gamo_code'],
                    'title' => $practice['gamo_title'],
                    'domain_code' => $domainCode,
                    'domain_title' => $domain['title'],
                    'practices' => collect([$practiceRow]),
                ]);
            }
        }

        $unmappedControls = $unmappedControls
            ->filter(fn (object $control) => blank($filters['gamo'] ?? null))
            ->filter(fn (object $control) => $this->matchesSearch($control, $filters['q'] ?? null))
            ->sortBy('order')
            ->values();

        $allGamos = $allGamos
            ->unique('gamo_code')
            ->values();

        return view('cobit_component.shared.cobit5-cobit41-mapping', [
            'groups' => $groups,
            'gamos' => $allGamos,
            'unmappedControls' => $unmappedControls,
            'filters' => $filters,
            'reference' => config('cobit4-cobit5-mapping.reference'),
            'referenceUrl' => config('cobit4-cobit5-mapping.reference_url'),
        ]);
    }

    /** Framework order of COBIT 4.1 groups: PO, AI, DS, ME, then Application Controls. */
    private function cobit41DomainRank(string $domain): int
    {
        return ['PO' => 1, 'AI' => 2, 'DS' => 3, 'ME' => 4, 'AC' => 5][$domain] ?? 9;
    }

    /** PO1.4 => PO1, AI2.10 => AI2, AC3 => AC (Application Controls are one group). */
    private function cobit41ProcessCode(string $sourceCode): string
    {
        return preg_match('/^AC\d/i', $sourceCode) ? 'AC' : strtoupper(explode('.', $sourceCode)[0]);
    }

    /** COBIT 4.1 process code => process name, e.g. PO1 => Define a Strategic IT Plan. */
    private function cobit41ProcessTitles(): array
    {
        return $this->source->cobit41ProcessTitles();
    }

    private function targetComponentUrl(string $code, array $catalog, array $practiceLinks): ?string
    {
        if (str_contains($code, '.')) {
            $firstPracticeCode = preg_replace('/-\d{2}$/', '', $code);

            return $practiceLinks[$firstPracticeCode] ?? null;
        }

        foreach ($catalog['domains'] ?? [] as $domain) {
            $practiceCode = $domain['processes'][$code]['practices'][0]['code'] ?? null;
            if ($practiceCode && isset($practiceLinks[$practiceCode])) {
                return $practiceLinks[$practiceCode];
            }
        }

        return null;
    }

    private function matchesSearch(object $row, ?string $term): bool
    {
        $term = trim($term ?? '');
        if ($term === '') {
            return true;
        }

        $text = implode(' ', array_filter([
            $row->source_code ?? null,
            $row->source_title ?? null,
            $row->process_code ?? null,
            $row->process_title ?? null,
            $row->target_code ?? null,
            $row->mapping_note ?? null,
            $row->review_note ?? null,
            $row->gamo_code ?? null,
            $row->gamo_title ?? null,
            $row->domain_code ?? null,
            $row->cobit5_code ?? null,
            $row->cobit5_title ?? null,
            $row->cobit41_code ?? null,
            $row->cobit41_title ?? null,
            $row->code ?? null,
            $row->title ?? null,
            $row->note ?? null,
        ], fn ($value) => is_string($value) && $value !== ''));

        return str_contains(mb_strtolower($text), mb_strtolower($term));
    }
}
