<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df3Data;
use App\Models\DesignFactor3a;
use App\Models\DesignFactor3b;
use App\Models\DesignFactor3c;
use App\Models\DesignFactor3RelativeImportance;
use App\Models\DesignFactor3Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF3 Service - Risk Profile
 * 
 * Special: Uses 3 separate input models:
 * - DesignFactor3a: 19 category inputs
 * - DesignFactor3b: 19 impact values
 * - DesignFactor3c: 19 likelihood values
 */
final class Df3Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        // Extract 19 category inputs
        $categoryInputs = [];
        for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
            $categoryInputs[] = (int) ($inputs["input{$i}df3"] ?? 0);
        }

        // Extract 19 impact values
        $impactInputs = [];
        for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
            $impactInputs[] = (float) ($inputs["impact{$i}"] ?? 0);
        }

        // Extract 19 likelihood values
        $likelihoodInputs = [];
        for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
            $likelihoodInputs[] = (float) ($inputs["likelihood{$i}"] ?? 0);
        }

        DB::beginTransaction();

        try {
            // Save DesignFactor3a
            $data3a = ['id' => Auth::id(), 'df_id' => $dfId, 'assessment_id' => $assessmentId];
            for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
                $data3a["input{$i}df3"] = $categoryInputs[$i - 1];
            }
            DesignFactor3a::create($data3a);

            // Save DesignFactor3b
            $data3b = ['id' => Auth::id(), 'df_id' => $dfId, 'assessment_id' => $assessmentId];
            for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
                $data3b["impact{$i}"] = $impactInputs[$i - 1];
            }
            DesignFactor3b::create($data3b);

            // Save DesignFactor3c
            $data3c = ['id' => Auth::id(), 'df_id' => $dfId, 'assessment_id' => $assessmentId];
            for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
                $data3c["likelihood{$i}"] = $likelihoodInputs[$i - 1];
            }
            DesignFactor3c::create($data3c);

            // Calculate using category inputs
            $scores = $this->calculateScores($categoryInputs);
            $ri = $this->calculateRelativeImportance($categoryInputs, $scores);

            $this->saveScores($dfId, $assessmentId, $scores);
            $this->saveRelativeImportance($dfId, $assessmentId, $ri);

            DB::commit();

            return [
                'success' => true,
                'inputs' => $categoryInputs,
                'scores' => $scores,
                'relativeImportance' => $ri,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate scores: Input × MAP
     */
    private function calculateScores(array $inputs): array
    {
        $scores = [];
        foreach (Df3Data::MAP as $i => $row) {
            $score = 0.0;
            foreach ($inputs as $j => $input) {
                $score += $row[$j] * $input;
            }
            $scores[$i] = $score;
        }
        return $scores;
    }

    /**
     * Calculate relative importance with average adjustment
     */
    private function calculateRelativeImportance(array $inputs, array $scores): array
    {
        $inputAvg = array_sum($inputs) / count($inputs);
        $baselineAvg = Df3Data::BASELINE_INPUT_VALUE;
        $ratio = ($inputAvg != 0) ? $baselineAvg / $inputAvg : 0;

        $ri = [];
        foreach ($scores as $i => $score) {
            $baseline = Df3Data::BASELINE_SCORES[$i] ?? 0;
            if ($baseline == 0) {
                $ri[$i] = 0;
                continue;
            }
            $value = $ratio * 100 * $score / $baseline;
            $ri[$i] = (int) ($this->calculator->mround($value, 5) - 100);
        }
        return $ri;
    }

    private function saveScores(int $dfId, int $assessmentId, array $scores): void
    {
        $data = ['id' => Auth::id(), 'df3_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df3_' . ($i + 1)] = $v;
        }
        DesignFactor3Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df3_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df3_' . ($i + 1)] = $v;
        }
        DesignFactor3RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId, int $dfId): array
    {
        $history = [
            'inputs' => null,
            'scores' => null,
            'relativeImportance' => null,
        ];

        // Load category inputs (3a)
        $record3a = DesignFactor3a::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        // Load impact (3b)
        $record3b = DesignFactor3b::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        // Load likelihood (3c)
        $record3c = DesignFactor3c::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        if ($record3a || $record3b || $record3c) {
            $inputs = [];
            if ($record3a) {
                for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
                    $inputs["input{$i}df3"] = (int) ($record3a->{"input{$i}df3"} ?? 0);
                }
            }
            if ($record3b) {
                for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
                    $inputs["impact{$i}"] = (float) ($record3b->{"impact{$i}"} ?? 0);
                }
            }
            if ($record3c) {
                for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
                    $inputs["likelihood{$i}"] = (float) ($record3c->{"likelihood{$i}"} ?? 0);
                }
            }
            $history['inputs'] = $inputs;
        }

        $scoreRecord = DesignFactor3Score::where('assessment_id', $assessmentId)
            ->where('df3_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df3Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = (float) ($scoreRecord->{'s_df3_' . $i} ?? 0);
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor3RelativeImportance::where('assessment_id', $assessmentId)
            ->where('df3_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df3Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = (float) ($riRecord->{'r_df3_' . $i} ?? 0);
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
