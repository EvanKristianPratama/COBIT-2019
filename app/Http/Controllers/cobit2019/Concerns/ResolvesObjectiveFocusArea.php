<?php

namespace App\Http\Controllers\cobit2019\Concerns;

use App\Models\MstObjective;

trait ResolvesObjectiveFocusArea
{
    /**
     * Resolve the focus area for a given objective id.
     */
    protected function focusAreaIdForObjective(string $objectiveId): int
    {
        return (int) (MstObjective::where('objective_id', $objectiveId)->value('focus_area_id') ?? 1);
    }
}
