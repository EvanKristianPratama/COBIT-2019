<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df1Data;
use App\Models\DesignFactor1;
use App\Models\DesignFactor1RelativeImportance;
use App\Models\DesignFactor1Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF1 Service - Enterprise Strategy
 * 
 * Uses average-based relative importance calculation
 */
final class Df1Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        $normalizedInputs = [
            (int) ($inputs['strategy_archetype'] ?? $inputs['input1df1'] ?? $inputs[0] ?? 0),
            (int) ($inputs['current_performance'] ?? $inputs['input2df1'] ?? $inputs[1] ?? 0),
            (int) ($inputs['future_goals'] ?? $inputs['input3df1'] ?? $inputs[2] ?? 0),
            (int) ($inputs['alignment_with_it'] ?? $inputs['input4df1'] ?? $inputs[3] ?? 0),
        ];

        DB::beginTransaction();

        try {
            DesignFactor1::create([
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
                'input1df1' => $normalizedInputs[0],
                'input2df1' => $normalizedInputs[1],
                'input3df1' => $normalizedInputs[2],
                'input4df1' => $normalizedInputs[3],
            ]);

            // Calculate scores (DF1 uses raw values, not percentages)
            $scores = $this->calculateDf1Scores($normalizedInputs);
            $ri = $this->calculateDf1RelativeImportance($normalizedInputs, $scores);

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
     * DF1 uses raw input values (not percentages)
     */
    private function calculateDf1Scores(array $inputs): array
    {
        $scores = [];
        foreach (Df1Data::MAP as $i => $row) {
            $score = 0.0;
            foreach ($inputs as $j => $input) {
                $score += $row[$j] * $input;
            }
            $scores[$i] = $score;
        }
        return $scores;
    }

    /**
     * DF1 uses average-based relative importance
     * Formula: RelImp = MROUND((BaselineAvg/InputAvg) * Score / Baseline * 100, 5) - 100
     */
    private function calculateDf1RelativeImportance(array $inputs, array $scores): array
    {
        $inputAvg = array_sum($inputs) / count($inputs);
        $baselineAvg = array_sum(Df1Data::BASELINE_INPUTS) / count(Df1Data::BASELINE_INPUTS);
        $ratio = ($inputAvg != 0) ? $baselineAvg / $inputAvg : 0;

        $ri = [];
        foreach ($scores as $i => $score) {
            $baseline = Df1Data::BASELINE_SCORES[$i] ?? 0;
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
        $data = ['id' => Auth::id(), 'df1_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df1_' . ($i + 1)] = $v;
        }
        DesignFactor1Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df1_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df1_' . ($i + 1)] = $v;
        }
        DesignFactor1RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId): array
    {
        $history = ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $inputRecord = DesignFactor1::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->first();

        if ($inputRecord) {
            $history['inputs'] = [
                $inputRecord->input1df1, $inputRecord->input2df1,
                $inputRecord->input3df1, $inputRecord->input4df1,
            ];
        }

        $scoreRecord = DesignFactor1Score::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df1Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = $scoreRecord->{'s_df1_' . $i};
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor1RelativeImportance::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df1Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = $riRecord->{'r_df1_' . $i};
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
