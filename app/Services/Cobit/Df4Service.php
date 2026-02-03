<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df4Data;
use App\Models\DesignFactor4;
use App\Models\DesignFactor4RelativeImportance;
use App\Models\DesignFactor4Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF4 Service - IT-Related Issues
 * 
 * Special: 20 inputs, uses average-based relative importance
 */
final class Df4Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        // Extract 20 inputs
        $normalizedInputs = [];
        for ($i = 1; $i <= Df4Data::INPUT_COUNT; $i++) {
            $normalizedInputs[] = (int) ($inputs["input{$i}df4"] ?? $inputs[$i - 1] ?? 0);
        }

        DB::beginTransaction();

        try {
            $data = [
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
            ];
            for ($i = 1; $i <= Df4Data::INPUT_COUNT; $i++) {
                $data["input{$i}df4"] = $normalizedInputs[$i - 1];
            }
            DesignFactor4::create($data);

            // Calculate scores (DF4 uses raw values)
            $scores = $this->calculateDf4Scores($normalizedInputs);
            $ri = $this->calculateDf4RelativeImportance($normalizedInputs, $scores);

            $this->saveScores($dfId, $assessmentId, $scores);
            $this->saveRelativeImportance($dfId, $assessmentId, $ri);

            DB::commit();

            return ['success' => true, 'inputs' => $normalizedInputs, 'scores' => $scores, 'relativeImportance' => $ri];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function calculateDf4Scores(array $inputs): array
    {
        $scores = [];
        foreach (Df4Data::MAP as $i => $row) {
            $score = 0.0;
            foreach ($inputs as $j => $input) {
                $score += $row[$j] * $input;
            }
            $scores[$i] = $score;
        }
        return $scores;
    }

    private function calculateDf4RelativeImportance(array $inputs, array $scores): array
    {
        $inputAvg = array_sum($inputs) / count($inputs);
        $baselineAvg = Df4Data::BASELINE_INPUT_VALUE; // All baselines are 2
        $ratio = ($inputAvg != 0) ? $baselineAvg / $inputAvg : 0;

        $ri = [];
        foreach ($scores as $i => $score) {
            $baseline = Df4Data::BASELINE_SCORES[$i] ?? 0;
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
        $data = ['id' => Auth::id(), 'df4_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df4_' . ($i + 1)] = $v;
        }
        DesignFactor4Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df4_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df4_' . ($i + 1)] = $v;
        }
        DesignFactor4RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId, int $dfId): array
    {
        $history = ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $inputRecord = DesignFactor4::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())->orderByDesc('created_at')->first();

        if ($inputRecord) {
            $inputs = [];
            for ($i = 1; $i <= Df4Data::INPUT_COUNT; $i++) {
                $inputs["input{$i}df4"] = $inputRecord->{"input{$i}df4"};
            }
            $history['inputs'] = $inputs;
        }

        $scoreRecord = DesignFactor4Score::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df4Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = $scoreRecord->{'s_df4_' . $i};
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor4RelativeImportance::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df4Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = $riRecord->{'r_df4_' . $i};
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
