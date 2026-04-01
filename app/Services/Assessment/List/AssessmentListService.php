<?php

namespace App\Services\Assessment\List;

use App\Models\MstEval;
use App\Models\TargetCapability;
use App\Models\TargetMaturity;
use App\Models\TrsActivityeval;
use App\Models\TrsScoping;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AssessmentListService
{
    /**
     * @return array<string, mixed>
     */
    public function getIndexData(User $user): array
    {
        $org = $user->organisasi ?? null;

        $myQuery = MstEval::with(['user', 'maturityScore'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        $otherQuery = null;
        if ($org) {
            $otherQuery = MstEval::with(['user', 'maturityScore'])
                ->where('user_id', '!=', $user->id)
                ->whereHas('user', function ($query) use ($org) {
                    $query->where('organisasi', $org);
                })
                ->orderBy('created_at', 'desc');
        }

        $myAssessments = $myQuery->paginate(10, ['*'], 'my_page');
        $otherAssessments = $otherQuery
            ? $otherQuery->paginate(10, ['*'], 'other_page')
            : new LengthAwarePaginator([], 0, 10);

        $allEvals = collect($myAssessments->items());
        if ($otherQuery) {
            $allEvals = $allEvals->merge($otherAssessments->items());
        }

        if ($allEvals->isNotEmpty()) {
            $evalIds = $allEvals->pluck('eval_id')->unique()->values()->all();

            $scopeCounts = TrsScoping::whereIn('eval_id', $evalIds)
                ->select('eval_id', DB::raw('count(*) as scope_count'))
                ->groupBy('eval_id')
                ->pluck('scope_count', 'eval_id')
                ->toArray();

            $lastActivityDates = TrsActivityeval::whereIn('eval_id', $evalIds)
                ->select('eval_id', DB::raw('MAX(updated_at) as last_activity_at'))
                ->groupBy('eval_id')
                ->pluck('last_activity_at', 'eval_id');

            $years = $allEvals->pluck('tahun')->filter()->unique()->values()->all();
            $targetAverages = [];
            $targetMaturityMap = [];

            if ($years !== []) {
                $targetCaps = TargetCapability::whereIn('tahun', $years)
                    ->when($org, function ($query) use ($org) {
                        $query->where('organisasi', $org);
                    }, function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->get();

                $domainCols = [
                    'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
                    'APO01', 'APO02', 'APO03', 'APO04', 'APO05', 'APO06', 'APO07', 'APO08', 'APO09', 'APO10', 'APO11', 'APO12', 'APO13', 'APO14',
                    'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
                    'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
                    'MEA01', 'MEA02', 'MEA03', 'MEA04',
                ];

                foreach ($targetCaps as $targetCap) {
                    $sum = 0;
                    $count = 0;
                    foreach ($domainCols as $col) {
                        $value = $targetCap->{$col};
                        if ($value !== null && is_numeric($value)) {
                            $sum += $value;
                            $count++;
                        }
                    }

                    $targetAverages[$targetCap->tahun] = $count > 0 ? round($sum / $count, 2) : 0;
                }

                $targetMaturities = TargetMaturity::whereIn('tahun', $years)
                    ->when($org, function ($query) use ($org) {
                        $query->where('organisasi', $org);
                    }, function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->get();

                foreach ($targetMaturities as $targetMaturity) {
                    $targetMaturityMap[$targetMaturity->tahun] = $targetMaturity->target_maturity;
                }
            }

            foreach ($allEvals as $evaluation) {
                $evaluation->scope_count = $scopeCounts[$evaluation->eval_id] ?? 0;
                $evaluation->last_saved_at = $lastActivityDates[$evaluation->eval_id] ?? $evaluation->created_at;
                $evaluation->avg_target_capability = $targetAverages[$evaluation->tahun ?? ''] ?? 0;
                $evaluation->target_maturity = $targetMaturityMap[$evaluation->tahun ?? ''] ?? null;
            }
        }

        $totalAssessments = $myAssessments->total() + ($otherQuery ? $otherAssessments->total() : 0);

        $statsQuery = MstEval::query();
        if ($org) {
            $statsQuery->whereHas('user', function ($query) use ($org) {
                $query->where('organisasi', $org);
            });
        } else {
            $statsQuery->where('user_id', $user->id);
        }

        $finishedAssessments = (clone $statsQuery)->where('status', 'finished')->count();

        return [
            'myAssessments' => $myAssessments,
            'otherAssessments' => $otherAssessments,
            'totalAssessments' => $totalAssessments,
            'finishedAssessments' => $finishedAssessments,
            'draftAssessments' => max(0, $totalAssessments - $finishedAssessments),
        ];
    }
}
