<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df2Data;
use App\Models\DesignFactor2;
use App\Models\DesignFactor2RelativeImportance;
use App\Models\DesignFactor2Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF2 Service - IT Management Goals
 * 
 * Special: Uses 2-step matrix multiplication:
 * Input (13) -> MAP_1 -> Intermediate (13) -> MAP_2 -> Score (40)
 */
final class Df2Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        // Extract 13 inputs
        $normalizedInputs = [];
        for ($i = 1; $i <= Df2Data::INPUT_COUNT; $i++) {
            $normalizedInputs[] = (int) ($inputs["input{$i}df2"] ?? $inputs[$i - 1] ?? 0);
        }

        DB::beginTransaction();

        try {
            $data = [
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
            ];
            for ($i = 1; $i <= Df2Data::INPUT_COUNT; $i++) {
                $data["input{$i}df2"] = $normalizedInputs[$i - 1];
            }
            DesignFactor2::create($data);

            // 2-step calculation
            $intermediate = $this->calculateIntermediate($normalizedInputs);
            $scores = $this->calculateScores($intermediate);
            $ri = $this->calculateRelativeImportance($normalizedInputs, $scores);

            $this->saveScores($dfId, $assessmentId, $scores);
            $this->saveRelativeImportance($dfId, $assessmentId, $ri);

            DB::commit();

            return ['success' => true, 'inputs' => $normalizedInputs, 'scores' => $scores, 'relativeImportance' => $ri];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Step 1: Input × MAP_1 -> Intermediate values
     */
    private function calculateIntermediate(array $inputs): array
    {
        $intermediate = array_fill(0, Df2Data::INTERMEDIATE_COUNT, 0);
        
        foreach ($inputs as $i => $inputValue) {
            foreach (Df2Data::MAP_1[$i] as $j => $coefficient) {
                $intermediate[$j] += $inputValue * $coefficient;
            }
        }
        
        return $intermediate;
    }

    /**
     * Step 2: Intermediate × MAP_2 -> Scores
     */
    private function calculateScores(array $intermediate): array
    {
        $scores = array_fill(0, Df2Data::OBJECTIVE_COUNT, 0);
        
        foreach ($intermediate as $i => $value) {
            foreach (Df2Data::MAP_2[$i] as $j => $coefficient) {
                $scores[$j] += $value * $coefficient;
            }
        }
        
        return $scores;
    }

    /**
     * Calculate relative importance with average adjustment
     */
    private function calculateRelativeImportance(array $inputs, array $scores): array
    {
        $inputAvg = array_sum($inputs) / count($inputs);
        $baselineAvg = array_sum(Df2Data::BASELINE_INPUTS) / count(Df2Data::BASELINE_INPUTS);
        $ratio = ($inputAvg != 0) ? $baselineAvg / $inputAvg : 0;

        $ri = [];
        foreach ($scores as $i => $score) {
            $baseline = Df2Data::BASELINE_SCORES[$i] ?? 0;
            if ($baseline == 0) {
                $ri[$i] = 0;
                continue;
            }
            // Floor calculation as per original logic
            $ri[$i] = (int) (floor(100 * $score / $baseline) - 100);
        }
        return $ri;
    }

    private function saveScores(int $dfId, int $assessmentId, array $scores): void
    {
        $data = ['id' => Auth::id(), 'df2_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df2_' . ($i + 1)] = $v;
        }
        DesignFactor2Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df2_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df2_' . ($i + 1)] = $v;
        }
        DesignFactor2RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId, int $dfId): array
    {
        $history = ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $inputRecord = DesignFactor2::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        if ($inputRecord) {
            $inputs = [];
            for ($i = 1; $i <= Df2Data::INPUT_COUNT; $i++) {
                $inputs[] = (int) ($inputRecord->{"input{$i}df2"} ?? 0);
            }
            $history['inputs'] = $inputs;
        }

        $scoreRecord = DesignFactor2Score::where('assessment_id', $assessmentId)
            ->where('df2_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df2Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = (float) ($scoreRecord->{'s_df2_' . $i} ?? 0);
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor2RelativeImportance::where('assessment_id', $assessmentId)
            ->where('df2_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df2Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = (float) ($riRecord->{'r_df2_' . $i} ?? 0);
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
