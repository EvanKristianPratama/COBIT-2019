<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstFocusArea;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GoalCascadeController extends Controller
{
    private const TABLES = [
        'mst_entergoals', 'mst_entergoalsmetr', 'mst_aligngoals', 'mst_aligngoalsmetr', 'mst_objective',
        'mst_cobit2019_goal_dimensions', 'trs_cobit2019_eg_ag_maps', 'trs_cobit2019_ag_objective_maps',
    ];

    private const OBJECTIVE_DOMAIN_ORDER = ['EDM' => 1, 'APO' => 2, 'BAI' => 3, 'DSS' => 4, 'MEA' => 5];

    /**
     * COBIT 2019 goals cascade: Enterprise Goals (+metrics), Alignment Goals (+metrics),
     * Enterprise Goals -> Alignment Goals, Alignment Goals -> Governance/Management Objectives.
     */
    public function index()
    {
        $ready = collect(self::TABLES)->every(fn (string $table) => Schema::hasTable($table));

        $data = [
            'ready' => $ready,
            'enterpriseGoals' => collect(), 'alignmentGoals' => collect(), 'objectives' => collect(),
            'egAgMatrix' => [], 'agObjectiveMatrix' => [],
        ];

        if ($ready) {
            $dimensions = DB::table('mst_cobit2019_goal_dimensions')->pluck('bsc_dimension', 'goal_id');

            $enterpriseGoals = $this->goals('mst_entergoals', 'entergoals_id', 'mst_entergoalsmetr', 'entergoalsmetr_id', $dimensions);
            $alignmentGoals = $this->goals('mst_aligngoals', 'aligngoals_id', 'mst_aligngoalsmetr', 'aligngoalsmetr_id', $dimensions);

            $egAgMatrix = [];
            foreach (DB::table('trs_cobit2019_eg_ag_maps')->get() as $row) {
                $egAgMatrix[$row->aligngoals_id][$row->entergoals_id] = $row->level;
            }

            $agObjectiveMatrix = [];
            foreach (DB::table('trs_cobit2019_ag_objective_maps')->get() as $row) {
                $agObjectiveMatrix[$row->objective_id][$row->aligngoals_id] = $row->level;
            }

            $data = [
                'ready' => true,
                'enterpriseGoals' => $enterpriseGoals,
                'alignmentGoals' => $alignmentGoals,
                'objectives' => $this->objectives(),
                'egAgMatrix' => $egAgMatrix,
                'agObjectiveMatrix' => $agObjectiveMatrix,
            ];
        }

        return view('cobit_component.cobit2019.goal-cascade.index', $data);
    }

    /** Base goals only (EG01..13 / AG01..13), not the per-model clones (EG01.M25 ...). */
    private function goals(string $table, string $idColumn, string $metricTable, string $metricPk, Collection $dimensions): Collection
    {
        $metrics = DB::table($metricTable)
            ->where($idColumn, 'not like', '%.%')
            ->orderBy($metricPk)
            ->get()
            ->groupBy($idColumn);

        return DB::table($table)
            ->where($idColumn, 'not like', '%.%')
            ->orderBy($idColumn)
            ->get()
            ->map(fn ($goal) => (object) [
                'code' => $goal->{$idColumn},
                'dimension' => $dimensions[$goal->{$idColumn}] ?? '-',
                'title' => $this->clean($goal->description),
                'metrics' => ($metrics[$goal->{$idColumn}] ?? collect())
                    ->map(fn ($m) => $this->clean($m->description))
                    ->values(),
            ]);
    }

    /** COBIT 2019 objectives (EDM01, APO01, ...) in framework order, with link to the component page. */
    private function objectives(): Collection
    {
        $focusArea = MstFocusArea::where('version', '2019')->orderBy('id')->first();
        if (! $focusArea) {
            return collect();
        }

        return DB::table('mst_objective')
            ->where('focus_area_id', $focusArea->id)
            ->where('objective_id', 'not like', '%.%')
            ->get(['objective_id', 'objective'])
            ->sortBy(fn ($o) => sprintf('%d-%s', self::OBJECTIVE_DOMAIN_ORDER[substr($o->objective_id, 0, 3)] ?? 9, $o->objective_id))
            ->map(fn ($o) => (object) [
                'code' => $o->objective_id,
                'title' => $o->objective,
                'url' => route('cobit_component.show', ['id' => $o->objective_id, 'focus_area' => $focusArea->id]),
            ])
            ->values();
    }

    private function clean(?string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', trim((string) $text, " \t\n\r\"")));
    }
}
