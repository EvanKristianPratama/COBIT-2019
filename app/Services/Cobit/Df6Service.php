<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df6Data;
use App\Models\DesignFactor6;
use App\Models\DesignFactor6RelativeImportance;
use App\Models\DesignFactor6Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF6 Service - Adoption of Cloud Computing
 */
final class Df6Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        $normalizedInputs = [
            (int) ($inputs['input1df6'] ?? $inputs[0] ?? 0),
            (int) ($inputs['input2df6'] ?? $inputs[1] ?? 0),
            (int) ($inputs['input3df6'] ?? $inputs[2] ?? 0),
        ];

        DB::beginTransaction();

        try {
            DesignFactor6::create([
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
                'input1df6' => $normalizedInputs[0],
                'input2df6' => $normalizedInputs[1],
                'input3df6' => $normalizedInputs[2],
            ]);

            $scores = $this->calculator->calculateScores($normalizedInputs, Df6Data::MAP);
            $ri = $this->calculator->calculateRelativeImportance($scores, Df6Data::BASELINE_SCORES);

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
        $data = ['id' => Auth::id(), 'df6_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df6_' . ($i + 1)] = $v;
        }
        DesignFactor6Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df6_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df6_' . ($i + 1)] = $v;
        }
        DesignFactor6RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId): array
    {
        $history = ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $inputRecord = DesignFactor6::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($inputRecord) {
            $history['inputs'] = [$inputRecord->input1df6, $inputRecord->input2df6, $inputRecord->input3df6];
        }

        $scoreRecord = DesignFactor6Score::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df6Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = $scoreRecord->{'s_df6_' . $i};
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor6RelativeImportance::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df6Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = $riRecord->{'r_df6_' . $i};
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
