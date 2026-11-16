<?php

namespace App\Services\Assessment\Eval;

use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\TrsActivityeval;
use App\Models\TrsEvalDetail;
use App\Models\TrsMaturityScore;
use App\Models\TrsObjectiveScore;
use App\Models\TrsScoping;
use App\Models\TrsSummaryActivity;
use App\Models\TrsSummaryReport;
use App\Models\User;
use App\Services\Assessment\Access\AssessmentAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentDuplicationService
{
    public function __construct(private readonly AssessmentAccessService $access) {}

    public function duplicate(MstEval $source, User $user, int $year): MstEval
    {
        return DB::transaction(function () use ($source, $user, $year) {
            $copy = $source->replicate();
            $copy->user_id = $user->id;
            $copy->tahun = $year;
            $copy->status = 'in_progress';
            $copy->save();
            $this->access->assign($user, $copy, $user);

            foreach (TrsScoping::where('eval_id', $source->eval_id)->get() as $scope) {
                $newScope = $scope->replicate();
                $newScope->eval_id = $copy->eval_id;
                $newScope->save();
                foreach (TrsEvalDetail::where('eval_id', $source->eval_id)->where('scoping_id', $scope->id)->get() as $detail) {
                    $newDetail = $detail->replicate();
                    $newDetail->eval_id = $copy->eval_id;
                    $newDetail->scoping_id = $newScope->id;
                    $newDetail->save();
                }
            }

            $evidenceIds = [];
            $hasReferences = Schema::hasTable('trs_ref_evidence');
            foreach (MstEvidence::where('eval_id', $source->eval_id)->get() as $evidence) {
                $newEvidence = $evidence->replicate();
                $newEvidence->eval_id = $copy->eval_id;
                $newEvidence->save();
                $evidenceIds[$evidence->id] = $newEvidence->id;
                if ($hasReferences) {
                    $ids = DB::table('trs_ref_evidence')->where('evidence_id', $evidence->id)->pluck('evidence_ref_id');
                    $newEvidence->references()->attach($ids->all());
                }
            }

            $activityIds = [];
            foreach (TrsActivityeval::where('eval_id', $source->eval_id)->get() as $activity) {
                $newActivity = $activity->replicate();
                $newActivity->eval_id = $copy->eval_id;
                $newActivity->save();
                $activityIds[$activity->id] = $newActivity->id;
            }

            foreach (TrsSummaryReport::where('eval_id', $source->eval_id)->get() as $report) {
                $newReport = $report->replicate();
                $newReport->eval_id = $copy->eval_id;
                $newReport->save();
                foreach (TrsSummaryActivity::where('summary_id', $report->id)->get() as $link) {
                    if (! isset($activityIds[$link->activityeval_id])) {
                        continue;
                    }
                    $newLink = $link->replicate();
                    $newLink->summary_id = $newReport->id;
                    $newLink->activityeval_id = $activityIds[$link->activityeval_id];
                    if ($link->evidence_id !== null) {
                        $newLink->evidence_id = $evidenceIds[$link->evidence_id] ?? null;
                        if ($newLink->evidence_id === null) {
                            $original = MstEvidence::withTrashed()->find($link->evidence_id);
                            // Organization-owned evidence is shared; assessment-owned evidence must never point back to another assessment.
                            if ($original && ! $original->trashed() && $original->eval_id === null && $source->organization_id !== null && (int) $original->organization_id === (int) $source->organization_id) {
                                $newLink->evidence_id = $original->id;
                            } else {
                                $newLink->miss_evidence = $link->miss_evidence ?: $original?->judul_dokumen;
                            }
                        }
                    }
                    $newLink->save();
                }
            }

            foreach ([TrsObjectiveScore::class, TrsMaturityScore::class] as $model) {
                foreach ($model::where('eval_id', $source->eval_id)->get() as $score) {
                    $newScore = $score->replicate();
                    $newScore->eval_id = $copy->eval_id;
                    $newScore->save();
                }
            }

            return $copy;
        });
    }
}
