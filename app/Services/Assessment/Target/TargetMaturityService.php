<?php

namespace App\Services\Assessment\Target;

use App\Models\TargetMaturity;
use App\Models\User;

class TargetMaturityService
{
    public function getTargetsForUser(int $userId)
    {
        return TargetMaturity::where('user_id', $userId)
            ->orderBy('tahun', 'desc')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(User $user, array $validated): TargetMaturity
    {
        return TargetMaturity::updateOrCreate(
            [
                'user_id' => $user->id,
                'tahun' => $validated['tahun'],
                'organisasi' => $user->organisasi ?? 'Unknown',
            ],
            [
                'target_maturity' => $validated['target_maturity'],
            ]
        );
    }

    public function deleteForUser(int $targetId, int $userId): void
    {
        $target = TargetMaturity::where('user_id', $userId)->findOrFail($targetId);
        $target->delete();
    }
}
