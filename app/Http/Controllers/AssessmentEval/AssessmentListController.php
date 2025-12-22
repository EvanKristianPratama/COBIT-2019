<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Models\MstEval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentListController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $org = $user->organisasi ?? null;

            $myQuery = MstEval::with(['user', 'maturityScore'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            $otherQuery = null;
            if ($org) {
                $otherQuery = MstEval::with(['user', 'maturityScore'])
                    ->where('user_id', '!=', $user->id)
                    ->whereHas('user', function ($q) use ($org) {
                        $q->where('organisasi', $org);
                    })
                    ->orderBy('created_at', 'desc');
            }

            $myAssessments = $myQuery->paginate(10, ['*'], 'my_page');
            $otherAssessments = $otherQuery 
                ? $otherQuery->paginate(10, ['*'], 'other_page') 
                : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

            $allEvals = collect($myAssessments->items());
            if ($otherQuery) {
                $allEvals = $allEvals->merge($otherAssessments->items());
            }

            if ($allEvals->isNotEmpty()) {
                $evalIds = $allEvals->pluck('eval_id')->unique()->values()->all();

                $scopeCounts = \App\Models\TrsScoping::whereIn('eval_id', $evalIds)
                    ->select('eval_id', DB::raw('count(*) as scope_count'))
                    ->groupBy('eval_id')
                    ->pluck('scope_count', 'eval_id')
                    ->toArray();

                $lastActivityDates = \App\Models\TrsActivityeval::whereIn('eval_id', $evalIds)
                    ->select('eval_id', DB::raw('MAX(updated_at) as last_activity_at'))
                    ->groupBy('eval_id')
                    ->pluck('last_activity_at', 'eval_id');

                $years = $allEvals->pluck('tahun')->filter()->unique()->values()->all();
                $targetAverages = [];
                $targetMaturityMap = [];
                
                if (!empty($years)) {
                    // Fetch Target Capability (existing logic)
                    $targetCaps = \App\Models\TargetCapability::whereIn('tahun', $years)
                        ->when($org, function($q) use ($org) {
                            $q->where('organisasi', $org);
                        }, function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })
                        ->get();

                    $domainCols = [
                        'EDM01','EDM02','EDM03','EDM04','EDM05',
                        'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
                        'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
                        'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
                        'MEA01','MEA02','MEA03','MEA04',
                    ];

                    foreach ($targetCaps as $tc) {
                        $sum = 0;
                        $count = 0;
                        foreach ($domainCols as $col) {
                            $val = $tc->$col;
                            if (!is_null($val) && is_numeric($val)) {
                                $sum += $val;
                                $count++;
                            }
                        }
                        $targetAverages[$tc->tahun] = $count > 0 ? round($sum / $count, 2) : 0;
                    }
                    
                    // Fetch Target Maturity per year (NEW)
                    $targetMaturities = \App\Models\TargetMaturity::whereIn('tahun', $years)
                        ->when($org, function($q) use ($org) {
                            $q->where('organisasi', $org);
                        }, function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })
                        ->get();
                    
                    foreach ($targetMaturities as $tm) {
                        $targetMaturityMap[$tm->tahun] = $tm->target_maturity;
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
                $statsQuery->whereHas('user', function ($q) use ($org) {
                    $q->where('organisasi', $org);
                });
            } else {
                $statsQuery->where('user_id', $user->id);
            }
            $finishedAssessments = (clone $statsQuery)->where('status', 'finished')->count();
            $draftAssessments = max(0, $totalAssessments - $finishedAssessments);

            return view('assessment-eval.list', compact('myAssessments', 'otherAssessments', 'totalAssessments', 'finishedAssessments', 'draftAssessments'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load assessments: ' . $e->getMessage()]);
        }
    }
}
