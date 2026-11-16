<?php

namespace App\Http\Controllers\Cobit5;

use App\Http\Controllers\Controller;
use App\Models\Cobit5\Cobit5EnterpriseGoal;
use App\Models\Cobit5\Cobit5ItGoal;
use App\Models\Cobit5\Cobit5Process;
use App\Models\MstFocusArea;
use App\Models\MstObjective;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GoalMappingController extends Controller
{
    private const TABLES = [
        'mst_cobit5_enterprise_goals',
        'mst_cobit5_it_goals',
        'mst_cobit5_processes',
        'trs_cobit5_enterprise_it_goal_maps',
        'trs_cobit5_it_goal_process_maps',
    ];

    /**
     * COBIT 5: Enterprise Goals, IT-related Goals,
     * Enterprise Goals -> IT-related Goals (Fig 17) and IT-related Goals -> Processes (Fig 18).
     */
    public function index()
    {
        $ready = collect(self::TABLES)->every(fn (string $table) => Schema::hasTable($table));

        $data = [
            'ready' => $ready,
            'enterpriseGoals' => collect(),
            'itGoals' => collect(),
            'processes' => collect(),
            'egMatrix' => [],
            'processMatrix' => [],
            'processLinks' => [],
        ];

        if ($ready) {
            $enterpriseGoals = Cobit5EnterpriseGoal::orderBy('order_no')->get();
            $itGoals = Cobit5ItGoal::orderBy('order_no')->get();
            $processes = Cobit5Process::orderBy('order_no')->get();

            $egCodes = $enterpriseGoals->pluck('code', 'id');
            $itCodes = $itGoals->pluck('code', 'id');

            $egMatrix = [];
            foreach (DB::table('trs_cobit5_enterprise_it_goal_maps')->get() as $row) {
                $egMatrix[$itCodes[$row->it_goal_id]][$egCodes[$row->enterprise_goal_id]] = $row->level;
            }

            $processMatrix = [];
            foreach (DB::table('trs_cobit5_it_goal_process_maps')->get() as $row) {
                $processMatrix[$row->process_id][$itCodes[$row->it_goal_id]] = $row->level;
            }

            $data = [
                'ready' => true,
                'enterpriseGoals' => $enterpriseGoals,
                'itGoals' => $itGoals,
                'processes' => $processes->groupBy('domain_code'),
                'egMatrix' => $egMatrix,
                'processMatrix' => $processMatrix,
                'processLinks' => $this->processLinks(),
            ];
        }

        return view('cobit_component.cobit5.goal-mapping.index', $data);
    }

    /** process code (APO01) => COBIT 5 component page url. */
    private function processLinks(): array
    {
        $focusAreaIds = MstFocusArea::where('version', '5')->orderBy('id')->pluck('id');

        return MstObjective::whereIn('focus_area_id', $focusAreaIds)
            ->orderBy('focus_area_id')
            ->get(['objective_id', 'focus_area_id'])
            ->unique(fn ($o) => strtoupper(explode('.', $o->objective_id)[0]))
            ->mapWithKeys(fn ($o) => [strtoupper(explode('.', $o->objective_id)[0]) => route('cobit_component.show', [
                'id' => $o->objective_id,
                'focus_area' => $o->focus_area_id,
            ])])
            ->all();
    }
}
