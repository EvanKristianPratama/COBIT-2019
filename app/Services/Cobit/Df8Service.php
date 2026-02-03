<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df8Data;
use App\Models\DesignFactor8;
use App\Models\DesignFactor8RelativeImportance;
use App\Models\DesignFactor8Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF8 Service - IT Implementation Methods
 */
final class Df8Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        $normalizedInputs = [
            (int) ($inputs['input1df8'] ?? $inputs[0] ?? 0),
            (int) ($inputs['input2df8'] ?? $inputs[1] ?? 0),
            (int) ($inputs['input3df8'] ?? $inputs[2] ?? 0),
        ];

        DB::beginTransaction();

        try {
            DesignFactor8::create([
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
                'input1df8' => $normalizedInputs[0],
                'input2df8' => $normalizedInputs[1],
                'input3df8' => $normalizedInputs[2],
            ]);

            $scores = $this->calculator->calculateScores($normalizedInputs, Df8Data::MAP);
            $ri = $this->calculator->calculateRelativeImportance($scores, Df8Data::BASELINE_SCORES);

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
        $data = ['id' => Auth::id(), 'df8_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($scores as $i => $v) {
            $data['s_df8_' . ($i + 1)] = $v;
        }
        DesignFactor8Score::create($data);
    }

    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = ['id' => Auth::id(), 'df8_id' => $dfId, 'assessment_id' => $assessmentId];
        foreach ($values as $i => $v) {
            $data['r_df8_' . ($i + 1)] = $v;
        }
        DesignFactor8RelativeImportance::create($data);
    }

    public function loadHistory(int $assessmentId, int $dfId): array
    {
        $history = ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $inputRecord = DesignFactor8::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($inputRecord) {
            $history['inputs'] = [$inputRecord->input1df8, $inputRecord->input2df8, $inputRecord->input3df8];
        }

        $scoreRecord = DesignFactor8Score::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df8Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = $scoreRecord->{'s_df8_' . $i};
            }
            $history['scores'] = $scores;
        }

        $riRecord = DesignFactor8RelativeImportance::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())->orderByDesc('created_at')->orderByDesc('id')->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df8Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = $riRecord->{'r_df8_' . $i};
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
