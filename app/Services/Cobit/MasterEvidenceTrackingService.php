<?php

namespace App\Services\Cobit;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MasterEvidenceTrackingService
{
    public function __construct(private readonly MasterEvidenceQueryService $catalog) {}

    public function track(array $filters): array
    {
        $models = DB::table('mst_focusarea')->whereIn('version', MasterEvidenceQueryService::VERSIONS)
            ->orderBy('id')->get();
        $modern = $models->first(fn ($model) => $model->version === '2019'
            && (empty($filters['focus_area']) || $model->id === (int) $filters['focus_area']));
        abort_unless($modern, 404, 'Model COBIT 2019 tidak ditemukan.');

        $selected = collect([$modern, $models->firstWhere('version', '5'), $models->firstWhere('version', '4.1')])->filter();
        $objectives = DB::table('mst_objective')->whereIn('focus_area_id', $selected->pluck('id'))
            ->orderByRaw("CASE WHEN objective_id LIKE 'EDM%' THEN 0 WHEN objective_id LIKE 'APO%' THEN 1 WHEN objective_id LIKE 'BAI%' THEN 2 WHEN objective_id LIKE 'DSS%' THEN 3 ELSE 4 END")
            ->orderBy('objective_id')->get();
        $anchors = $objectives->where('focus_area_id', $modern->id);
        if (! empty($filters['objective_id'])) {
            $anchors = $anchors->where('objective_id', $filters['objective_id']);
        }

        $mappings = Schema::hasTable('mst_cobit_evolution')
            ? DB::table('mst_cobit_evolution')->orderBy('id')->get()
            : collect(config('cobit-evolution.gamo', []))->map(fn ($row) => (object) ($row + ['cobit2019_id' => $row['id']]));
        $mappings = $mappings->groupBy(fn ($mapping) => $this->code($mapping->cobit2019_id));
        $evidence = $this->catalog->forObjectives($objectives->pluck('objective_id')->all())
            ->groupBy(fn ($item) => $item->focus_area_id.'|'.$item->objective_id);

        $rows = $anchors->map(function ($anchor) use ($mappings, $selected, $objectives, $evidence) {
            $code = $this->code($anchor->objective_id);
            $mappingRows = $mappings->get($code, collect());
            $columns = [];
            foreach (MasterEvidenceQueryService::VERSIONS as $version) {
                $field = $version === '5' ? 'cobit5_id' : 'cobit4_id';
                $label = $version === '2019' ? $code : $mappingRows->pluck($field)->filter()->unique()->implode('; ');
                $codes = $version === '2019' ? [$code] : $this->codes($label, $version);
                $model = $selected->firstWhere('version', $version);
                $processes = [];
                $missing = [];
                foreach ($codes as $mappedCode) {
                    $matches = $objectives->filter(fn ($objective) => $model
                        && $objective->focus_area_id === $model->id && $this->code($objective->objective_id) === $mappedCode);
                    if ($matches->isEmpty()) {
                        $missing[] = $mappedCode;
                    }
                    if ($matches->isNotEmpty()) {
                        // Legacy imports can keep PO2.M27 and PO02.M27 together.
                        // Present their canonical process once, retaining all evidence.
                        $objective = $matches->first(fn ($candidate) => $evidence->has($candidate->focus_area_id.'|'.$candidate->objective_id))
                            ?? $matches->first();
                        $items = $matches->flatMap(fn ($candidate) => $evidence->get($candidate->focus_area_id.'|'.$candidate->objective_id, collect()));
                        $processes[] = [
                            'code' => $mappedCode,
                            'objective_id' => $objective->objective_id,
                            'title' => $objective->objective,
                            'evidence' => $this->compactEvidence($items),
                        ];
                    }
                }
                $columns[] = ['version' => $version, 'mapping' => $label, 'processes' => $processes, 'missing_codes' => $missing];
            }

            return ['code' => $code, 'objective_id' => $anchor->objective_id, 'columns' => $columns,
                'notes' => $mappingRows->pluck('notes')->filter()->unique()->implode("\n")];
        })->values();

        if (trim($filters['q'] ?? '') !== '') {
            $needle = Str::lower(trim($filters['q']));
            $rows = $rows->filter(function ($row) use ($needle) {
                $text = $row['code'];
                foreach ($row['columns'] as $column) {
                    $text .= ' '.$column['mapping'];
                    foreach ($column['processes'] as $process) {
                        $text .= ' '.$process['title'];
                        foreach ($process['evidence'] as $item) {
                            $text .= ' '.$item['name'].' '.$item['description'].' '.$item['reference'];
                        }
                    }
                }

                return str_contains(Str::lower($text), $needle);
            })->values();
        }

        $page = (int) ($filters['page'] ?? 1);
        $paginator = new LengthAwarePaginator($rows->forPage($page, 5)->values(), $rows->count(), 5, $page);

        return ['models' => collect(MasterEvidenceQueryService::VERSIONS)->map(function ($version) use ($selected) {
            $model = $selected->firstWhere('version', $version);

            return ['version' => $version, 'id' => $model?->id, 'name' => $model?->name];
        })->all(), 'tracking' => $paginator];
    }

    private function compactEvidence(Collection $items): array
    {
        return $items->groupBy(fn ($item) => $item->source.'|'.Str::lower(preg_replace('/\s+/u', ' ', trim((string) $item->name))))
            ->map(fn ($group) => [
                'name' => $group->first()->name,
                'description' => $group->pluck('description')->filter()->first() ?? '',
                'reference' => $group->map(fn ($item) => $item->source === 'Referensi Dokumen'
                    ? 'REF-'.str_pad($item->source_id, 4, '0', STR_PAD_LEFT) : $item->reference)->filter()->unique()->implode(', '),
            ])->values()->all();
    }

    private function codes(string $label, string $version): array
    {
        $domains = $version === '4.1' ? 'PO|AI|DS|ME' : 'EDM|APO|BAI|DSS|MEA';
        preg_match_all('/\b('.$domains.')\s*0*(\d+)\b/i', $label, $matches, PREG_SET_ORDER);

        return array_values(array_unique(array_map(fn ($match) => $this->code($match[1].$match[2]), $matches)));
    }

    private function code(string $id): string
    {
        if (preg_match('/^(EDM|APO|BAI|DSS|MEA|PO|AI|DS|ME)\s*0*(\d+)/i', trim($id, " \t\n\r\0\x0B\""), $match)) {
            $domain = strtoupper($match[1]);

            return $domain.(in_array($domain, ['PO', 'AI', 'DS', 'ME'], true)
                ? (string) (int) $match[2] : str_pad((string) (int) $match[2], 2, '0', STR_PAD_LEFT));
        }

        return strtoupper($id);
    }
}
