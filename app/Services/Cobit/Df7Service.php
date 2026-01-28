<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df7Data;
use App\Models\DesignFactor7;
use App\Models\DesignFactor7RelativeImportance;
use App\Models\DesignFactor7Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF7 Service - IT Sourcing Model
 * 
 * Note: DF7 uses average-based relative importance calculation
 */
final class Df7Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        $normalizedInputs = [
            (int) ($inputs['input1df7'] ?? $inputs[0] ?? 0),
            (int) ($inputs['input2df7'] ?? $inputs[1] ?? 0),
            (int) ($inputs['input3df7'] ?? $inputs[2] ?? 0),
            (int) ($inputs['input4df7'] ?? $inputs[3] ?? 0),
        ];

        DB::beginTransaction();

        try {
            DesignFactor7::create([
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
                'input1df7' => $normalizedInputs[0],
                'input2df7' => $normalizedInputs[1],
                'input3df7' => $normalizedInputs[2],
                'input4df7' => $normalizedInputs[3],
            ]);

            // DF7 uses raw values (not percentages) for calculation
            $scores = $this->calculateDf7Scores($normalizedInputs);
            $ri = $this->calculateDf7RelativeImportance($normalizedInputs, $scores);

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
     * DF7 uses raw input values (not percentages)
     */
    private function calculateDf7Scores(array $inputs): array
    {
        $scores = [];
        foreach (Df7Data::MAP as $i => $row) {
            $score = 0.0;
            foreach ($inputs as $j => $input) {
                $score += $row[$j] * $input;
            }
            $scores[$i] = round($score, 2);
        }
        return $scores;
    }

    /**
     * DF7 uses average-based relative importance
     * Formula: RelImp = MROUND((BaselineAvg/InputAvg) * Score / Baseline * 100, 5) - 100
     */
    private function calculateDf7RelativeImportance(array $inputs, array $scores): array
    {
        $inputAvg = array_sum($inputs) / count($inputs);
        $baselineAvg = array_sum(Df7Data::BASELINE_INPUTS) / count(Df7Data::BASELINE_INPUTS);
        $ratio = $baselineAvg / $inputAvg;

        $ri = [];
        foreach ($scores as $i => $score) {
            $baseline = Df7Data::BASELINE_SCORES[$i] ?? 0;
            if ($baseline == 0) {
                $ri[$i] = 0;
                continue;
            }
            $value = ($ratio * 100 * $score) / $baseline;
            $ri[$i] = (int) ($this->calculator->mround($value, 5) - 100);
        }
        return $ri;
    }

    private function saveScores(int $dfId, int $assessmentId, array $scores): void
    {
        $data = ['id' => Auth::id(), 'df7_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df7_' . ($i + 1)] = $v;
        }
        DesignFactor7Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df7_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df7_' . ($i + 1)] = $v;
        }
        DesignFactor7RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId): array
    {
        $history = ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $inputRecord = DesignFactor7::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($inputRecord) {
            $history['inputs'] = [
                $inputRecord->input1df7, $inputRecord->input2df7,
                $inputRecord->input3df7, $inputRecord->input4df7,
            ];
        }

        $scoreRecord = DesignFactor7Score::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df7Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = $scoreRecord->{'s_df7_' . $i};
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor7RelativeImportance::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df7Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = $riRecord->{'r_df7_' . $i};
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
