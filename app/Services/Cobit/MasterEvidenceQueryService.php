<?php

namespace App\Services\Cobit;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\Cobit5\WorkProductService;

class MasterEvidenceQueryService
{
    public const VERSIONS = ['2019', '5', '4.1'];

    public function filters(): array
    {
        $objectives = DB::table('mst_objective as o')
            ->join('mst_focusarea as f', 'f.id', '=', 'o.focus_area_id')
            ->whereIn('f.version', self::VERSIONS)->orderBy('o.objective_id')
            ->get(['o.objective_id', 'o.objective', 'o.focus_area_id', 'f.version'])
            ->map(function ($row) {
                $row->objective_code = $this->displayCode($row->objective_id, $row->version);

                return $row;
            });
        $preferred = Schema::hasTable('mst_cobit4_processes')
            ? DB::table('mst_cobit4_processes')->get(['objective_id', 'focus_area_id'])
                ->map(fn ($row) => $row->focus_area_id.'|'.$row->objective_id)->all()
            : [];
        $objectives = $objectives->groupBy(fn ($row) => $row->version.'|'.$row->focus_area_id.'|'.$row->objective_code)
            ->map(fn ($group) => $group->first(fn ($row) => in_array($row->focus_area_id.'|'.$row->objective_id, $preferred, true)) ?? $group->first())
            ->sortBy(function ($row) {
                preg_match('/^([A-Z]+)(\d+)/', $row->objective_code, $parts);
                $rank = ['EDM' => 0, 'APO' => 1, 'BAI' => 2, 'DSS' => 3, 'MEA' => 4, 'PO' => 5, 'AI' => 6, 'DS' => 7, 'ME' => 8];

                return [$rank[$parts[1] ?? ''] ?? 9, (int) ($parts[2] ?? 0), $row->focus_area_id, $row->objective_id];
            })->values();

        return [
            'models' => DB::table('mst_focusarea')->whereIn('version', self::VERSIONS)
                ->orderBy('id')->get(['id', 'name', 'version']),
            'objectives' => $objectives,
        ];
    }

    public function search(array $filters): LengthAwarePaginator
    {
        $query = $this->catalogQuery()->where('version', $filters['version'] ?? '2019');
        if (! empty($filters['focus_area'])) {
            $query->where('focus_area_id', (int) $filters['focus_area']);
        }
        if (! empty($filters['objective_id'])) {
            $query->where('objective_id', $filters['objective_id']);
        }
        if (trim($filters['q'] ?? '') !== '') {
            $keyword = '%'.trim($filters['q']).'%';
            $query->where(function ($query) use ($keyword) {
                foreach (['name', 'description', 'reference', 'objective_id'] as $field) {
                    $query->orWhere($field, 'like', $keyword);
                }
            });
        }

        return $this->ordered($query)->paginate((int) ($filters['per_page'] ?? 25), ['*'], 'page', (int) ($filters['page'] ?? 1))
            ->through(fn ($item) => $this->cleanReference($item));
    }

    public function forObjectives(array $objectiveIds): Collection
    {
        if ($objectiveIds === []) {
            return collect();
        }

        return $this->ordered($this->catalogQuery()->whereIn('objective_id', $objectiveIds))->get()
            ->map(fn ($item) => $this->cleanReference($item));
    }

    private function cleanReference(object $item): object
    {
        $item->reference = str_replace(['"', "'", '“', '”', '‘', '’'], '', $item->reference ?? '');
        $item->objective_code = $this->displayCode($item->objective_id ?? '', $item->version);

        return $item;
    }

    private function displayCode(string $id, string $version): string
    {
        $code = strtoupper(preg_replace('/\.(?:M|FA)\d+(?:\.\d+)?$/i', '', trim($id, " \t\n\r\0\x0B\"")));
        if ($version === '4.1' && preg_match('/^(PO|AI|DS|ME)0*(\d+)$/', $code, $parts)) {
            return $parts[1].(int) $parts[2];
        }

        return $code;
    }

    private function ordered(Builder $query): Builder
    {
        return $query->orderByRaw("CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0
                WHEN UPPER(objective_id) LIKE 'APO%' THEN 1
                WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2
                WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3
                WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4
                WHEN UPPER(objective_id) LIKE 'PO%' THEN 5
                WHEN UPPER(objective_id) LIKE 'AI%' THEN 6
                WHEN UPPER(objective_id) LIKE 'DS%' THEN 7
                WHEN UPPER(objective_id) LIKE 'ME%' THEN 8 ELSE 9 END")
            ->orderBy('focus_area_id')
            ->orderByRaw("CASE WHEN version = '4.1' THEN CAST(SUBSTR(objective_id, 3) AS SIGNED) ELSE 0 END")
            ->orderBy('objective_id')->orderBy('sort_no')
            ->orderBy('source')->orderBy('name')->orderBy('source_id');
    }

    private function catalogQuery(): Builder
    {
        $tables = Schema::getTableListing();
        $sources = [];

        if (in_array('mst_evidence_ref', $tables, true)) {
            $wpSource = WorkProductService::SOURCE_PREFIX.'%';
            $sources[] = DB::table('mst_evidence_ref as r')
                ->join('mst_objective as o', function ($join) {
                    $join->on('o.objective_id', '=', 'r.objective_id')
                        ->on('o.focus_area_id', '=', 'r.focus_area_id');
                })
                ->join('mst_focusarea as f', 'f.id', '=', 'o.focus_area_id')
                ->where('r.is_active', true)->whereIn('f.version', self::VERSIONS)
                ->select(['f.version', 'f.id as focus_area_id', 'f.name as focus_area', 'o.objective_id',
                    'r.nama_dokumen as name', 'r.keterangan as description', 'r.id as source_id'])
                ->selectRaw("CASE WHEN f.version = '5' AND r.referensi_sumber LIKE ? THEN 'Work Product' ELSE 'Referensi Dokumen' END as source,
                    CASE WHEN f.version = '5' AND r.referensi_sumber LIKE ? THEN SUBSTR(r.referensi_sumber, ?) ELSE '' END as reference,
                    CASE WHEN f.version = '5' AND r.referensi_sumber LIKE ? THEN CAST(SUBSTR(r.referensi_sumber, INSTR(r.referensi_sumber, '-WP') + 3) AS SIGNED) ELSE 0 END as sort_no",
                    [$wpSource, $wpSource, strlen(WorkProductService::SOURCE_PREFIX) + 1, $wpSource]);
        }

        if (in_array('mst_infoflowoutput', $tables, true)) {
            $outputs = DB::table('mst_infoflowoutput as output')
                ->join('mst_practice as p', 'p.practice_id', '=', 'output.practice_id')
                ->join('mst_objective as o', 'o.objective_id', '=', 'p.objective_id')
                ->join('mst_focusarea as f', 'f.id', '=', 'o.focus_area_id')
                ->whereIn('f.version', ['2019', '5'])
                ->select(['f.version', 'f.id as focus_area_id', 'f.name as focus_area', 'o.objective_id',
                    'output.description as name', 'output.output_id as source_id', 'p.practice_id as reference'])
                ->selectRaw("'' as description, 'Output Work Product' as source, 0 as sort_no");
            // Once a process has its Figure 19 catalog, show that master rather than
            // repeating the older practice-output rows. Keep the original outputs intact.
            if (in_array('mst_evidence_ref', $tables, true)) {
                $outputs->where(function ($query) {
                    $query->where('f.version', '!=', '5')->orWhereNotExists(function ($catalog) {
                        $catalog->selectRaw('1')->from('mst_evidence_ref as wp')
                            ->whereColumn('wp.focus_area_id', 'o.focus_area_id')
                            ->whereColumn('wp.objective_id', 'o.objective_id')
                            ->where('wp.is_active', true)
                            ->where('wp.referensi_sumber', 'like', WorkProductService::SOURCE_PREFIX.'%');
                    });
                });
            }
            $sources[] = $outputs;
        }

        if (count(array_intersect(['mst_wp_4.1', 'trs_wp_to_obj_4.1', 'mst_cobit4_processes'], $tables)) === 3) {
            $sources[] = DB::table(DB::raw('`mst_wp_4.1` as wp'))
                ->join(DB::raw('`trs_wp_to_obj_4.1` as relation'), 'relation.wp_id', '=', 'wp.wp_id')
                ->join('mst_cobit4_processes as process', 'process.id', '=', 'relation.process_id')
                ->join('mst_focusarea as f', 'f.id', '=', 'process.focus_area_id')
                ->where('f.version', '4.1')
                ->select(['f.version', 'f.id as focus_area_id', 'f.name as focus_area', 'process.objective_id',
                    'wp.wp as name', 'wp.description', 'wp.wp_id as source_id'])
                ->selectRaw("'Work Product' as source, wp.wp_id as reference, wp.order_no as sort_no");
        }

        // All union branches must keep the same column order.
        $columns = ['version', 'focus_area_id', 'focus_area', 'objective_id', 'name', 'description',
            'source_id', 'source', 'reference', 'sort_no'];
        $union = null;
        foreach ($sources as $source) {
            $normalized = DB::query()->fromSub($source, 'source')->select($columns);
            $union = $union ? $union->unionAll($normalized) : $normalized;
        }

        if (! $union) {
            $union = DB::query()->selectRaw("'' as version, 0 as focus_area_id, '' as focus_area,
                '' as objective_id, '' as name, '' as description, '' as source_id,
                '' as source, '' as reference, 0 as sort_no")->whereRaw('1 = 0');
        }

        return DB::query()->fromSub($union, 'catalog');
    }
}
