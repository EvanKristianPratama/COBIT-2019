<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df9Data;
use App\Models\DesignFactor9;
use App\Models\DesignFactor9RelativeImportance;
use App\Models\DesignFactor9Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF9 Service - Technology Adoption Strategy
 */
final class Df9Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        $normalizedInputs = [
            (int) ($inputs['input1df9'] ?? $inputs[0] ?? 0),
            (int) ($inputs['input2df9'] ?? $inputs[1] ?? 0),
            (int) ($inputs['input3df9'] ?? $inputs[2] ?? 0),
        ];

        DB::beginTransaction();

        try {
            DesignFactor9::create([
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
                'input1df9' => $normalizedInputs[0],
                'input2df9' => $normalizedInputs[1],
                'input3df9' => $normalizedInputs[2],
            ]);

            $scores = $this->calculator->calculateScores($normalizedInputs, Df9Data::MAP);
            $ri = $this->calculator->calculateRelativeImportance($scores, Df9Data::BASELINE_SCORES);

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
        $data = ['id' => Auth::id(), 'df9_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df9_' . ($i + 1)] = $v;
        }
        DesignFactor9Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df9_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df9_' . ($i + 1)] = $v;
        }
        DesignFactor9RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId, int $dfId): array
    {
        $history = ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $inputRecord = DesignFactor9::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($inputRecord) {
            $history['inputs'] = [$inputRecord->input1df9, $inputRecord->input2df9, $inputRecord->input3df9];
        }

        $scoreRecord = DesignFactor9Score::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df9Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = $scoreRecord->{'s_df9_' . $i};
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor9RelativeImportance::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df9Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = $riRecord->{'r_df9_' . $i};
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
