<?php

namespace App\Services\Cobit;

use App\Models\MstFocusArea;
use Illuminate\Support\Collection;

class CobitCoreModelService
{
    public function objectives(): Collection
    {
        $core = MstFocusArea::query()
            ->where(fn ($query) => $query->where('version', '2019')->orWhereNull('version')->orWhere('version', ''))
            ->where(fn ($query) => $query->whereRaw("UPPER(TRIM(code)) IN ('CORE MODEL', 'COREMOD', 'CORE')")
                ->orWhereRaw("LOWER(TRIM(name)) = 'core model'"))
            ->orderBy('id')->first();

        if (! $core) {
            return collect();
        }

        $order = array_flip($this->codes());

        return $core->objectives()->whereIn('objective_id', $this->codes())->get()
            ->sortBy(fn ($objective) => $order[$objective->objective_id])->values();
    }

    public function codes(): array
    {
        $codes = [];
        foreach (['EDM' => 5, 'APO' => 14, 'BAI' => 11, 'DSS' => 6, 'MEA' => 4] as $domain => $count) {
            for ($number = 1; $number <= $count; $number++) {
                $codes[] = $domain.str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            }
        }

        return $codes;
    }
}
