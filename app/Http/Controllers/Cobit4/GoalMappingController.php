<?php

namespace App\Http\Controllers\Cobit4;

use App\Http\Controllers\Controller;
use App\Models\Cobit4\Cobit4BusinessGoal;
use App\Models\Cobit4\Cobit4ItGoal;
use App\Models\Cobit4\Cobit4Process;
use App\Models\MstFocusArea;
use Illuminate\Support\Facades\Schema;

class GoalMappingController extends Controller
{
    /**
     * COBIT 4.1: Business Goals -> IT Goals and IT Goals -> IT Processes.
     */
    public function index()
    {
        $ready = Schema::hasTable('mst_cobit4_business_goals')
            && Schema::hasTable('mst_cobit4_it_goals')
            && Schema::hasTable('trs_cobit4_business_it_goal_maps')
            && Schema::hasTable('trs_cobit4_it_goal_process_maps');

        $businessGoals = collect();
        $itGoals = collect();
        $processLinks = [];

        if ($ready) {
            $businessGoals = Cobit4BusinessGoal::with('itGoals:id,code')
                ->orderBy('order_no')
                ->get();
            $itGoals = Cobit4ItGoal::with('processMaps')
                ->orderBy('order_no')
                ->get();
            $processLinks = $this->processLinks();
        }

        return view('cobit_component.cobit4_1.goal-mapping.index', [
            'ready' => $ready,
            'businessGoals' => $businessGoals->groupBy('perspective'),
            'itGoals' => $itGoals,
            'criteria' => Cobit4ItGoal::CRITERIA,
            'processLinks' => $processLinks,
        ]);
    }

    /** process code (PO1) => COBIT 4.1 component page url, when that process exists. */
    private function processLinks(): array
    {
        if (! Schema::hasTable('mst_cobit4_processes')) {
            return [];
        }

        $focusAreaIds = MstFocusArea::where('version', '4.1')->orderBy('id')->pluck('id');

        return Cobit4Process::whereIn('focus_area_id', $focusAreaIds)
            ->orderBy('focus_area_id')
            ->get(['code', 'objective_id', 'focus_area_id'])
            ->unique(fn ($p) => strtoupper($p->code))
            ->mapWithKeys(fn ($p) => [strtoupper($p->code) => route('cobit_component.show', [
                'id' => $p->objective_id ?: $p->code,
                'focus_area' => $p->focus_area_id,
            ])])
            ->all();
    }
}
