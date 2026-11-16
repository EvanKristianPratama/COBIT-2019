<?php

namespace App\Services\Cobit;

use App\Models\MstCobitEvolution;
use App\Models\MstFocusArea;
use App\Models\MstObjective;
use App\Models\MstPractice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the "Evolusi GAMO 4.1 -> 5 -> 2019" page at practice level:
 *   COBIT 2019 practice -> COBIT 5 practice(s) -> COBIT 4.1 control objective(s).
 *
 * - GAMO and practice names come from mst_objective / mst_practice (COBIT 2019 core model).
 * - COBIT 2019 -> COBIT 5 comes from trs_cobit2019_cobit5_mappings when someone saved it,
 *   otherwise from an automatic name match inside the GAMO that mst_cobit_evolution links to.
 * - COBIT 5 -> COBIT 4.1 reuses the existing 4.1 -> 5 mapping, reversed (same data as the 5 -> 4.1 page).
 */
class CobitEvolutionService
{
    public const MANUAL_TABLE = 'trs_cobit2019_cobit5_mappings';

    private const CONTROL_SOURCES = 'mst_cobit4_cobit5_mapping_sources';

    private const CONTROL_MAPPINGS = 'trs_cobit4_cobit5_mappings';

    public function __construct(private Cobit41To5MappingSource $source)
    {
    }

    public function manualTableReady(): bool
    {
        return Schema::hasTable(self::MANUAL_TABLE);
    }

    /** COBIT 4.1 -> COBIT 5 mapping lives in the database, so it can be edited from this page. */
    public function control4Ready(): bool
    {
        return Schema::hasTable(self::CONTROL_SOURCES) && Schema::hasTable(self::CONTROL_MAPPINGS);
    }

    private ?array $reference = null;

    /**
     * COBIT 5 catalog plus the COBIT 5 -> COBIT 4.1 control objectives, loaded once per request.
     *
     * @return array{links: array, controls: array, cobit5Gamos: array<string,string>, cobit5Practices: array<string,array>}
     */
    private function reference(): array
    {
        if ($this->reference !== null) {
            return $this->reference;
        }

        $catalog = $this->source->practiceCatalog();
        $forwardRows = $this->source->forwardRows();
        $links = $this->source->componentLinks(
            $catalog,
            $forwardRows->pluck('source_code')->unique()->values()->all(),
            $forwardRows->pluck('target_code')->filter()->unique()->values()->all()
        );
        $controls = $this->source->control4ObjectivesByCobit5Practice($catalog, $forwardRows, $links['cobit41']);

        $cobit5Gamos = [];
        $cobit5Practices = [];
        foreach ($catalog['domains'] as $domain) {
            foreach ($domain['processes'] as $gamoCode => $process) {
                $cobit5Gamos[$gamoCode] = $process['title'];
                foreach ($process['practices'] as $practice) {
                    $cobit5Practices[$practice['code']] = $practice + ['gamo' => $gamoCode];
                }
            }
        }

        return $this->reference = compact('links', 'controls', 'cobit5Gamos', 'cobit5Practices');
    }

    /**
     * One entry per COBIT 2019 GAMO, ordered EDM, APO, BAI, DSS, MEA.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function gamos(): Collection
    {
        if (! Schema::hasTable('mst_cobit_evolution')) {
            return collect();
        }

        ['links' => $links, 'controls' => $controls, 'cobit5Gamos' => $cobit5Gamos, 'cobit5Practices' => $cobit5Practices] = $this->reference();

        $coreFocusAreaId = $this->coreFocusAreaId();
        $evolutions = MstCobitEvolution::query()
            ->orderByRaw("CASE domain WHEN 'EDM' THEN 1 WHEN 'APO' THEN 2 WHEN 'BAI' THEN 3 WHEN 'DSS' THEN 4 WHEN 'MEA' THEN 5 ELSE 6 END")
            ->orderBy('cobit2019_id')
            ->get();
        $gamoCodes = $evolutions->pluck('cobit2019_id')->all();

        $names = MstObjective::query()
            ->where('focus_area_id', $coreFocusAreaId)
            ->whereIn('objective_id', $gamoCodes)
            ->pluck('objective', 'objective_id');
        $practices = MstPractice::query()
            ->where('focus_area_id', $coreFocusAreaId)
            ->whereIn('objective_id', $gamoCodes)
            ->orderBy('practice_id')
            ->get(['practice_id', 'objective_id', 'practice_name'])
            ->groupBy('objective_id');
        $manual = $this->manualMappings();

        return $evolutions->map(function (MstCobitEvolution $evolution) use (
            $names, $practices, $manual, $cobit5Gamos, $cobit5Practices, $controls, $links, $coreFocusAreaId
        ) {
            $gamoCode = $evolution->cobit2019_id;
            $gamoName = $this->clean($names[$gamoCode] ?? $evolution->title_2019);
            $gamoUrl = route('cobit_component.show', array_filter(['id' => $gamoCode, 'focus_area' => $coreFocusAreaId]));

            // COBIT 5 GAMOs this GAMO evolved from, e.g. "BAI01 (parsial)" => BAI01.
            preg_match_all('/(?:EDM|APO|BAI|DSS|MEA)\d{2}/', (string) $evolution->cobit5_id, $matches);
            $cobit5GamoCodes = array_values(array_unique($matches[0]));
            $candidates = collect($cobit5Practices)
                ->filter(fn (array $practice) => in_array($practice['gamo'], $cobit5GamoCodes, true))
                ->all();

            $practiceRows = collect($practices[$gamoCode] ?? [])
                ->map(function (MstPractice $practice) use ($manual, $candidates, $cobit5Practices, $controls, $links, $gamoUrl) {
                    $code = $this->clean($practice->practice_id);
                    $title = $this->clean($practice->practice_name);

                    $autoMatch = $this->bestMatch($code, $title, $candidates);
                    if (array_key_exists($code, $manual)) {
                        $source = $manual[$code] === [] ? 'none' : 'manual';
                        $targetCodes = $manual[$code];
                    } else {
                        $source = $autoMatch ? 'auto' : 'none';
                        $targetCodes = $autoMatch ? [$autoMatch] : [];
                    }

                    $targets = collect($targetCodes)
                        ->filter(fn (string $targetCode) => isset($cobit5Practices[$targetCode]))
                        ->map(fn (string $targetCode) => [
                            'code' => $targetCode,
                            'title' => $cobit5Practices[$targetCode]['title'],
                            'url' => $links['cobit5'][$targetCode] ?? null,
                            'controls' => collect($controls[$targetCode] ?? [])
                                ->map(fn (object $control) => [
                                    'code' => $control->code,
                                    'title' => $control->title,
                                    'note' => $control->note,
                                    'url' => $control->url,
                                ])
                                ->all(),
                        ])
                        ->values()
                        ->all();

                    return [
                        'code' => $code,
                        'title' => $title,
                        'url' => $gamoUrl,
                        'source' => $source,
                        'saved' => array_key_exists($code, $manual),
                        'target_codes' => array_column($targets, 'code'),
                        'auto_target_codes' => $autoMatch ? [$autoMatch] : [],
                        'targets' => $targets,
                        'search' => $this->searchText($code, $title, $targets),
                    ];
                })
                ->values();

            $hasControls = $practiceRows->contains(
                fn (array $row) => collect($row['targets'])->contains(fn (array $target) => $target['controls'] !== [])
            );
            $storedCobit4 = trim((string) $evolution->cobit4_id);

            return [
                'mapping_id' => $evolution->id,
                'code' => $gamoCode,
                'domain' => $evolution->domain,
                'name' => $gamoName,
                'url' => $gamoUrl,
                'status' => $evolution->status,
                'notes' => (string) $evolution->notes,
                'cobit5_id' => (string) $evolution->cobit5_id,
                'cobit5_gamos' => array_map(
                    fn (string $code) => [
                        'code' => $code,
                        'title' => $cobit5Gamos[$code] ?? '',
                        'url' => $links['cobit5'][$code.'.01'] ?? null,
                    ],
                    $cobit5GamoCodes
                ),
                'cobit5_text' => $cobit5GamoCodes === [] ? (string) $evolution->cobit5_title : '',
                // Process-level hint, only for GAMOs where no practice reaches a COBIT 4.1 control objective.
                'cobit4_fallback' => (! $hasControls && $storedCobit4 !== '' && $storedCobit4 !== '-') ? $storedCobit4 : '',
                'practices' => $practiceRows->all(),
                'search' => mb_strtolower($gamoCode.' '.$gamoName.' '.$evolution->cobit5_id.' '.$evolution->cobit4_id),
            ];
        })->values();
    }

    /**
     * Reference data the inline editor needs in the browser: COBIT 5 practices (for the picker)
     * and COBIT 4.1 control objectives per COBIT 5 practice (so the 4.1 column updates live).
     */
    public function editorData(): array
    {
        $reference = $this->reference();
        $groups = [];
        foreach ($reference['cobit5Gamos'] as $gamoCode => $title) {
            $groups[] = [
                'gamo' => $gamoCode,
                'title' => $title,
                'practices' => array_keys(array_filter($reference['cobit5Practices'], fn (array $practice) => $practice['gamo'] === $gamoCode)),
            ];
        }

        $domainRank = ['PO' => 1, 'AI' => 2, 'DS' => 3, 'ME' => 4, 'AC' => 5];
        $sources = $this->source->forwardRows()
            ->unique('source_code')
            ->sortBy(fn (object $row) => [$domainRank[$row->domain] ?? 9, $row->source_order])
            ->values();
        $processTitles = $this->source->cobit41ProcessTitles();
        $controlGroups = [];
        $cobit4 = [];
        foreach ($sources as $row) {
            $process = preg_match('/^AC\d/i', $row->source_code) ? 'AC' : strtoupper(explode('.', $row->source_code)[0]);
            $controlGroups[$process] ??= ['process' => $process, 'title' => $processTitles[$process] ?? '', 'items' => []];
            $controlGroups[$process]['items'][] = $row->source_code;
            $cobit4[$row->source_code] = ['title' => $row->source_title, 'url' => $reference['links']['cobit41'][$row->source_code] ?? null];
        }

        return [
            'controlEditable' => $this->control4Ready(),
            'controlGroups' => array_values($controlGroups),
            'cobit4' => $cobit4,
            'groups' => $groups,
            'gamoTitles' => $reference['cobit5Gamos'],
            'cobit5' => collect($reference['cobit5Practices'])
                ->map(fn (array $practice, string $code) => [
                    'title' => $practice['title'],
                    'url' => $reference['links']['cobit5'][$code] ?? null,
                ])
                ->all(),
            'controls' => array_map(
                fn (array $list) => array_map(fn (object $control) => [
                    'code' => $control->code,
                    'title' => $control->title,
                    'note' => $control->note,
                    'url' => $control->url,
                ], $list),
                $reference['controls']
            ),
        ];
    }

    /** Valid COBIT 5 practice codes the editor may save. */
    public function cobit5PracticeCodes(): array
    {
        return array_keys($this->reference()['cobit5Practices']);
    }

    /** Valid COBIT 4.1 control objective codes from the master mapping. */
    public function cobit4ControlCodes(): array
    {
        return $this->source->forwardRows()->pluck('source_code')->unique()->values()->all();
    }

    public function practiceExists(string $code): bool
    {
        return MstPractice::query()
            ->where('focus_area_id', $this->coreFocusAreaId())
            ->whereRaw("REPLACE(practice_id, '\"', '') = ?", [$code])
            ->exists();
    }

    /**
     * Apply one editing session atomically: GAMO status/note/COBIT 5 code, practice -> COBIT 5 mappings,
     * and COBIT 4.1 control objectives added to / removed from COBIT 5 practices (the shared 4.1 -> 5 mapping).
     *
     * @param list<array{id:int,status:string,notes:?string,cobit5_id:?string}> $gamos
     * @param list<array{code:string,targets?:list<string>,reset?:bool}> $practices
     * @param list<array{target:string,add?:list<string>,remove?:list<string>}> $controls
     */
    public function saveChanges(array $gamos, array $practices, array $controls = []): void
    {
        DB::transaction(function () use ($gamos, $practices, $controls) {
            foreach ($gamos as $gamo) {
                MstCobitEvolution::whereKey($gamo['id'])->update([
                    'status' => $gamo['status'],
                    'notes' => ($gamo['notes'] ?? '') === '' ? null : $gamo['notes'],
                    'cobit5_id' => ($gamo['cobit5_id'] ?? '') === '' ? null : $gamo['cobit5_id'],
                ]);
            }

            foreach ($practices as $practice) {
                if (! empty($practice['reset'])) {
                    $this->resetPracticeMapping($practice['code']);
                } else {
                    $this->savePracticeMapping($practice['code'], $practice['targets'] ?? []);
                }
            }

            foreach ($controls as $change) {
                $this->saveControlChange($change['target'], $change['remove'] ?? [], $change['add'] ?? []);
            }
        });

        $this->reference = null;
    }

    /**
     * Edit the COBIT 4.1 -> COBIT 5 mapping for one COBIT 5 practice.
     * A 4.1 control objective that maps to the whole COBIT 5 process is split into practice rows first,
     * so removing one practice leaves its siblings untouched.
     */
    private function saveControlChange(string $target, array $remove, array $add): void
    {
        $reference = $this->reference();
        $gamo = $reference['cobit5Practices'][$target]['gamo'];
        $siblings = array_values(array_filter(
            array_keys($reference['cobit5Practices']),
            fn (string $code) => $reference['cobit5Practices'][$code]['gamo'] === $gamo && $code !== $target
        ));
        $now = now();

        foreach ($remove as $controlCode) {
            $source = DB::table(self::CONTROL_SOURCES)->where('code', $controlCode)->first();
            if (! $source) {
                continue;
            }

            $rows = DB::table(self::CONTROL_MAPPINGS)->where('source_id', $source->id)->get();
            if ($direct = $rows->firstWhere('target_code', $target)) {
                DB::table(self::CONTROL_MAPPINGS)->where('id', $direct->id)->delete();
            } elseif ($wholeProcess = $rows->first(fn (object $row) => $row->target_code === $gamo && $row->target_level === 'process')) {
                DB::table(self::CONTROL_MAPPINGS)->where('id', $wholeProcess->id)->delete();
                $existing = $rows->pluck('target_code')->all();
                $split = array_values(array_diff($siblings, $existing));
                if ($split !== []) {
                    DB::table(self::CONTROL_MAPPINGS)->insert(array_map(fn (string $practiceCode) => [
                        'source_id' => $source->id,
                        'target_code' => $practiceCode,
                        'target_level' => 'practice',
                        'order_no' => $wholeProcess->order_no,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $split));
                }
            }

            if (! DB::table(self::CONTROL_MAPPINGS)->where('source_id', $source->id)->exists()) {
                DB::table(self::CONTROL_SOURCES)->where('id', $source->id)->update(['mapping_status' => 'deleted', 'updated_at' => $now]);
            }
        }

        foreach ($add as $controlCode) {
            $source = DB::table(self::CONTROL_SOURCES)->where('code', $controlCode)->first();
            if (! $source) {
                continue;
            }

            $rows = DB::table(self::CONTROL_MAPPINGS)->where('source_id', $source->id)->get();
            $covered = $rows->contains(fn (object $row) => $row->target_code === $target
                || ($row->target_code === $gamo && $row->target_level === 'process'));
            if (! $covered) {
                DB::table(self::CONTROL_MAPPINGS)->insert([
                    'source_id' => $source->id,
                    'target_code' => $target,
                    'target_level' => 'practice',
                    'order_no' => (int) $rows->max('order_no') + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            if ($source->mapping_status === 'deleted') {
                DB::table(self::CONTROL_SOURCES)->where('id', $source->id)->update(['mapping_status' => 'mapped', 'updated_at' => $now]);
            }
        }
    }

    /** Save the COBIT 5 targets of one COBIT 2019 practice. An empty list stores "reviewed, no equivalent". */
    public function savePracticeMapping(string $practiceCode, array $targetCodes): void
    {
        $targetCodes = array_values(array_unique($targetCodes));
        $now = now();

        DB::transaction(function () use ($practiceCode, $targetCodes, $now) {
            DB::table(self::MANUAL_TABLE)->where('source_code', $practiceCode)->delete();

            $rows = $targetCodes === []
                ? [['source_code' => $practiceCode, 'target_code' => null, 'order_no' => 1, 'created_at' => $now, 'updated_at' => $now]]
                : array_map(fn (string $targetCode, int $index) => [
                    'source_code' => $practiceCode,
                    'target_code' => $targetCode,
                    'order_no' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $targetCodes, array_keys($targetCodes));

            DB::table(self::MANUAL_TABLE)->insert($rows);
        });
    }

    /** Drop the saved mapping so the automatic match applies again. */
    public function resetPracticeMapping(string $practiceCode): void
    {
        DB::table(self::MANUAL_TABLE)->where('source_code', $practiceCode)->delete();
    }

    /** @return array<string, list<string>> 2019 practice code => saved COBIT 5 codes ([] = reviewed, none). */
    private function manualMappings(): array
    {
        if (! $this->manualTableReady()) {
            return [];
        }

        $mappings = [];
        foreach (DB::table(self::MANUAL_TABLE)->orderBy('source_code')->orderBy('order_no')->get(['source_code', 'target_code']) as $row) {
            $mappings[$row->source_code] ??= [];
            if ($row->target_code !== null) {
                $mappings[$row->source_code][] = $row->target_code;
            }
        }

        return $mappings;
    }

    private function coreFocusAreaId(): ?int
    {
        return MstFocusArea::where('version', '2019')->orderBy('id')->value('id');
    }

    /**
     * Best COBIT 5 practice for a COBIT 2019 practice by name similarity (word overlap).
     * Accepts a match at 0.5, or 0.3 when the practice keeps the same code.
     */
    private function bestMatch(string $code, string $title, array $candidates): ?string
    {
        $words = $this->words($title);
        $best = null;
        $bestScore = 0.0;

        foreach ($candidates as $candidateCode => $candidate) {
            $other = $this->words($candidate['title']);
            $union = count(array_unique(array_merge($words, $other)));
            $score = $union === 0 ? 0.0 : count(array_intersect($words, $other)) / $union;
            if ($candidateCode === $code) {
                $score += 0.001; // prefer the same code on a tie
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidateCode;
            }
        }

        $threshold = $best === $code ? 0.3 : 0.5;

        return $best !== null && $bestScore >= $threshold ? $best : null;
    }

    /** Lower-cased significant words with COBIT 5 British spellings folded (organisation -> organization). */
    private function words(string $text): array
    {
        $stop = ['the', 'a', 'an', 'of', 'to', 'and', 'for', 'in', 'on', 'with', 'that', 'is', 'are', 'as', 'by', 'or', 'enterprise', 'i', 't', 'it'];
        $words = [];

        foreach (preg_split('/[^a-z0-9]+/', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            if (in_array($word, $stop, true)) {
                continue;
            }
            $word = str_replace(['isation', 'ised', 'ising', 'ise'], ['ization', 'ize', 'ize', 'ize'], $word);
            $words[preg_replace('/(ing|ed|es|s)$/', '', $word)] = true;
        }

        return array_keys($words);
    }

    private function searchText(string $code, string $title, array $targets): string
    {
        $parts = [$code, $title];
        foreach ($targets as $target) {
            $parts[] = $target['code'];
            $parts[] = $target['title'];
            foreach ($target['controls'] as $control) {
                $parts[] = $control['code'];
                $parts[] = $control['title'];
            }
        }

        return mb_strtolower(implode(' ', $parts));
    }

    /** The core-model tables store some ids and names wrapped in literal double quotes. */
    private function clean(?string $value): string
    {
        return trim((string) $value, "\" \t\n\r");
    }
}
