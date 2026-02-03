<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df5Data;
use App\Models\DesignFactor5;
use App\Models\DesignFactor5RelativeImportance;
use App\Models\DesignFactor5Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF5 Service - IT Investment Portfolio
 */
final class Df5Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        $normalizedInputs = [
            (int) ($inputs['input1df5'] ?? $inputs[0] ?? 0),
            (int) ($inputs['input2df5'] ?? $inputs[1] ?? 0),
        ];

        DB::beginTransaction();

        try {
            DesignFactor5::create([
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
                'input1df5' => $normalizedInputs[0],
                'input2df5' => $normalizedInputs[1],
            ]);

            $scores = $this->calculator->calculateScores($normalizedInputs, Df5Data::MAP);
            $ri = $this->calculator->calculateRelativeImportance($scores, Df5Data::BASELINE_SCORES);

            $this->saveScores($dfId, $assessmentId, $scores);
            $this->saveRelativeImportance($dfId, $assessmentId, $ri);

            DB::commit();

            return ['success' => true, 'inputs' => $normalizedInputs, 'scores' => $scores, 'relativeImportance' => $ri];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function saveScores(int $dfId, int $assessmentId, array $scores): void
    {
        $data = ['id' => Auth::id(), 'df5_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df5_' . ($i + 1)] = $v;
        }
        DesignFactor5Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df5_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df5_' . ($i + 1)] = $v;
        }
        DesignFactor5RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId, int $dfId): array
    {
        $history = ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $inputRecord = DesignFactor5::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($inputRecord) {
            $history['inputs'] = [$inputRecord->input1df5, $inputRecord->input2df5];
        }

        $scoreRecord = DesignFactor5Score::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df5Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = $scoreRecord->{'s_df5_' . $i};
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor5RelativeImportance::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df5Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = $riRecord->{'r_df5_' . $i};
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
