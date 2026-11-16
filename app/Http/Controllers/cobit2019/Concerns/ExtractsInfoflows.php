<?php

namespace App\Http\Controllers\cobit2019\Concerns;

use App\Models\MstObjective;
use Illuminate\Support\Arr;

trait ExtractsInfoflows
{
    /**
     * Extract infoflows from an objective's practices in a consistent, null-safe way.
     */
    protected function extractInfoflows(MstObjective $objective)
    {
        $practices = $objective->practices ?? collect([]);

        return $practices->flatMap(function ($p) {
            $inputs = Arr::get($p, 'infoflowinput', $p->infoflowinput ?? []);

            return collect($inputs)->map(function ($inp) use ($p) {
                return [
                    'practice_id' => $p->practice_id ?? null,
                    'input' => $inp,
                    'connectedoutputs' => Arr::get($inp, 'connectedoutputs', $inp['connectedoutputs'] ?? []),
                ];
            });
        })->values();
    }
}
