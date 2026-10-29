<?php

namespace App\Services\Assessment\Objective;

class AssessmentObjectiveService
{
    /**
     * Helper: Get and sort objectives
     */
    public function getSortedObjectives(?int $focusAreaId = 1)
    {
        $objectives = \App\Models\MstObjective::where('focus_area_id', $focusAreaId ?: 1)
            ->with(['practices.activities'])->get();
        $domainOrder = ['EDM' => 1, 'APO' => 2, 'BAI' => 3, 'DSS' => 4, 'MEA' => 5];

        return $objectives->sortBy(function ($obj) use ($domainOrder) {
            $prefix = preg_replace('/[0-9]+/', '', $obj->objective_id);
            if (empty($prefix)) {
                $prefix = substr($obj->objective_id, 0, 3);
            }
            $rank = $domainOrder[$prefix] ?? 99;

            return sprintf('%02d_%s', $rank, $obj->objective_id);
        })->values();
    }
}
