<?php

declare(strict_types=1);

namespace App\Services\Cobit;

use App\Data\Cobit\Df10Data;
use App\Models\DesignFactor10;
use App\Models\DesignFactor10RelativeImportance;
use App\Models\DesignFactor10Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DF10 Service - Orchestrates DF10 operations
 *
 * This service:
 * - Handles database transactions
 * - Delegates calculations to DesignFactorCalculator
 * - Gets data from Df10Data
 *
 * SOLID:
 * - Single Responsibility: Only handles DF10 persistence
 * - Dependency Injection: Calculator is injected
 */
final class Df10Service
{
    public function __construct(
        private readonly DesignFactorCalculator $calculator
    ) {}

    /**
     * Process and store DF10 submission
     *
     * @param  int   $dfId       Design Factor ID
     * @param  int   $assessmentId Assessment ID
     * @param  array $inputs     User inputs [input1, input2, input3]
     * @return array{success: bool, scores: array, relativeImportance: array}
     * @throws \Exception If transaction fails
     */
    public function store(int $dfId, int $assessmentId, array $inputs): array
    {
        // Validate inputs
        $normalizedInputs = [
            (int) ($inputs['input1df10'] ?? $inputs[0] ?? 0),
            (int) ($inputs['input2df10'] ?? $inputs[1] ?? 0),
            (int) ($inputs['input3df10'] ?? $inputs[2] ?? 0),
        ];

        DB::beginTransaction();

        try {
            // 1. Save raw inputs
            $designFactor = DesignFactor10::create([
                'id' => Auth::id(),
                'df_id' => $dfId,
                'assessment_id' => $assessmentId,
                'input1df10' => $normalizedInputs[0],
                'input2df10' => $normalizedInputs[1],
                'input3df10' => $normalizedInputs[2],
            ]);

            // 2. Calculate scores using the calculator service
            $scores = $this->calculator->calculateScores(
                $normalizedInputs,
                Df10Data::MAP
            );

            // 3. Calculate relative importance
            $relativeImportance = $this->calculator->calculateRelativeImportance(
                $scores,
                Df10Data::BASELINE_SCORES
            );

            // 4. Save scores
            $this->saveScores($dfId, $assessmentId, $scores);

            // 5. Save relative importance
            $this->saveRelativeImportance($dfId, $assessmentId, $relativeImportance);

            DB::commit();

            return [
                'success' => true,
                'inputs' => $normalizedInputs,
                'scores' => $scores,
                'relativeImportance' => $relativeImportance,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Save calculated scores to database
     */
    private function saveScores(int $dfId, int $assessmentId, array $scores): void
    {
        $data = [
            'id' => Auth::id(),
            'df10_id' => $dfId,
            'assessment_id' => $assessmentId,
        ];

        // Map scores to column names (s_df10_1, s_df10_2, ...)
        foreach ($scores as $index => $value) {
            $data['s_df10_' . ($index + 1)] = $value;
        }

        DesignFactor10Score::create($data);
    }

    /**
     * Save relative importance values to database
     */
    private function saveRelativeImportance(int $dfId, int $assessmentId, array $values): void
    {
        $data = [
            'id' => Auth::id(),
            'df10_id' => $dfId,
            'assessment_id' => $assessmentId,
        ];

        // Map values to column names (r_df10_1, r_df10_2, ...)
        foreach ($values as $index => $value) {
            $data['r_df10_' . ($index + 1)] = $value;
        }

        DesignFactor10RelativeImportance::create($data);
    }

    /**
     * Load history data for a user
     *
     * @return array{inputs: array|null, scores: array|null, relativeImportance: array|null}
     */
    public function loadHistory(int $assessmentId, int $dfId): array
    {
        $history = [
            'inputs' => null,
            'scores' => null,
            'relativeImportance' => null,
        ];

        // Load inputs
        $inputRecord = DesignFactor10::where('assessment_id', $assessmentId)
            ->where('df_id', $dfId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($inputRecord) {
            $history['inputs'] = [
                $inputRecord->input1df10,
                $inputRecord->input2df10,
                $inputRecord->input3df10,
            ];
        }

        // Load scores
        $scoreRecord = DesignFactor10Score::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($scoreRecord) {
            $scores = [];
            for ($i = 1; $i <= Df10Data::OBJECTIVE_COUNT; $i++) {
                $scores[] = $scoreRecord->{'s_df10_' . $i};
            }
            $history['scores'] = $scores;
        }

        // Load relative importance
        $riRecord = DesignFactor10RelativeImportance::where('assessment_id', $assessmentId)
            ->where('id', Auth::id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($riRecord) {
            $ri = [];
            for ($i = 1; $i <= Df10Data::OBJECTIVE_COUNT; $i++) {
                $ri[] = $riRecord->{'r_df10_' . $i};
            }
            $history['relativeImportance'] = $ri;
        }

        return $history;
    }
}
