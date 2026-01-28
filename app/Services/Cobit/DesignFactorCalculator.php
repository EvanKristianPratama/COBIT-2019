<?php

declare(strict_types=1);

namespace App\Services\Cobit;

/**
 * Design Factor Calculator
 *
 * A pure, stateless service for calculating Design Factor scores.
 * This class follows SOLID principles:
 * - Single Responsibility: Only handles mathematical calculations
 * - Open/Closed: New DF types can add data, not modify this code
 * - Dependency Inversion: Takes arrays as input, no model dependencies
 *
 * KISS: The formulas are simple and well-documented.
 * DRY: This one class handles ALL DF calculations.
 */
final class DesignFactorCalculator
{
    /**
     * Calculate scores using matrix multiplication
     *
     * Formula: Score[i] = Σ (Map[i][j] × Input[j])
     *
     * @param  array<int, float>   $inputs  User input values (as percentages 0-100)
     * @param  array<int, array<int, float>> $map Mapping matrix (objectives × inputs)
     * @return array<int, float> Calculated scores per objective
     */
    public function calculateScores(array $inputs, array $map): array
    {
        // Convert inputs from percentage (0-100) to decimal (0-1)
        $normalizedInputs = array_map(
            fn(float $val): float => $val / 100.0,
            $inputs
        );

        $scores = [];

        foreach ($map as $objectiveIndex => $coefficients) {
            $score = 0.0;

            foreach ($coefficients as $inputIndex => $coefficient) {
                $inputValue = $normalizedInputs[$inputIndex] ?? 0.0;
                $score += $coefficient * $inputValue;
            }

            // Round to 2 decimal places for consistency with Excel
            $scores[$objectiveIndex] = round($score, 2);
        }

        return $scores;
    }

    /**
     * Calculate relative importance values
     *
     * Formula: RelImp[i] = MROUND((Score[i] / Baseline[i]) × 100, 5) - 100
     *
     * @param  array<int, float> $scores   Calculated scores
     * @param  array<int, float> $baselines Baseline scores per objective
     * @return array<int, float> Relative importance values (can be negative)
     */
    public function calculateRelativeImportance(array $scores, array $baselines): array
    {
        $relativeImportance = [];

        foreach ($scores as $index => $score) {
            $baseline = $baselines[$index] ?? 0.0;

            if ($baseline == 0) {
                // Avoid division by zero
                $relativeImportance[$index] = 0;
                continue;
            }

            // Calculate percentage relative to baseline
            $percentage = ($score / $baseline) * 100;

            // Round to nearest 5 (MROUND equivalent)
            $rounded = $this->mround($percentage, 5);

            // Subtract 100 to get relative difference
            $relativeImportance[$index] = (int) ($rounded - 100);
        }

        return $relativeImportance;
    }

    /**
     * Round value to nearest multiple (Excel MROUND equivalent)
     *
     * @param float $value    Value to round
     * @param float $multiple Multiple to round to (e.g., 5)
     * @return float Rounded value
     */
    public function mround(float $value, float $multiple): float
    {
        if ($multiple == 0) {
            return 0.0;
        }

        return round($value / $multiple) * $multiple;
    }

    /**
     * Validate that inputs sum to approximately 100%
     *
     * @param  array<int, float> $inputs Input percentages
     * @param  float $tolerance Allowed deviation from 100 (default 1%)
     * @return bool True if valid
     */
    public function validateInputSum(array $inputs, float $tolerance = 1.0): bool
    {
        $sum = array_sum($inputs);
        return abs($sum - 100) <= $tolerance;
    }
}
