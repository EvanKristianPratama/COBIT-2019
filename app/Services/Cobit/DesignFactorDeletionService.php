<?php

namespace App\Services\Cobit;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DesignFactorDeletionService
{
    public function __construct(private readonly CobitAssessmentAccessService $access) {}

    public function delete(Assessment $assessment, User $user): void
    {
        DB::transaction(function () use ($assessment, $user): void {
            $assessment = Assessment::whereKey($assessment->assessment_id)->lockForUpdate()->firstOrFail();
            abort_unless($this->access->canInput($user, $assessment)
                && $this->access->isInActiveOrganizations($user, $assessment), 403);

            foreach ($this->dependentTables() as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('assessment_id', $assessment->assessment_id)->delete();
                }
            }

            $assessment->accessAssignments()->delete();
            $assessment->delete();
        });
    }

    private function dependentTables(): array
    {
        $tables = ['trs_step4', 'trs_step3', 'trs_step2', 'df_step3', 'df_step2'];

        // Delete calculated children before their DF input rows (foreign keys).
        for ($number = 1; $number <= 10; $number++) {
            $tables[] = 'design_factor_'.$number.'_score';
            $tables[] = 'design_factor_'.$number.'_relative_importance';
        }
        for ($number = 1; $number <= 10; $number++) {
            if ($number === 3) {
                array_push($tables, 'design_factor_3_c', 'design_factor_3_b', 'design_factor_3_a');
            } else {
                $tables[] = 'design_factor_'.$number;
            }
        }

        return $tables;
    }
}
