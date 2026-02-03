<?php

declare(strict_types=1);

namespace App\Data\Cobit;

/**
 * DF10 (Threat Landscape) Data Configuration
 *
 * This class contains all the static mapping data for Design Factor 10.
 * Separating data from logic makes the code:
 * - Easier to understand (KISS)
 * - Easier to update (just change numbers here)
 * - Easier to test (inject different data)
 *
 * @see https://www.isaca.org/resources/cobit (COBIT 2019 Framework)
 */
final class Df10Data
{
    /**
     * Number of inputs for DF10
     */
    public const INPUT_COUNT = 3;

    /**
     * Number of objectives (output rows)
     */
    public const OBJECTIVE_COUNT = 40;

    /**
     * Baseline input percentages (used for relative importance calculation)
     * Format: [input1, input2, input3]
     */
    public const BASELINE_INPUTS = [15, 70, 15];

    /**
     * Baseline scores per objective (40 values)
     * Used as denominators in relative importance calculation
     */
    public const BASELINE_SCORES = [
        2.50, 2.58, 1.08, 2.00, 1.08, 1.58, 2.93, 1.15, 2.85, 2.50,
        1.35, 1.23, 1.65, 1.43, 1.58, 1.43, 1.50, 1.00, 1.93, 2.93,
        2.43, 2.50, 1.43, 2.00, 1.93, 2.43, 1.08, 1.00, 1.08, 2.43,
        1.00, 1.00, 1.08, 1.08, 1.08, 1.00, 2.00, 1.00, 1.00, 1.00,
    ];

    /**
     * Mapping matrix: 40 objectives x 3 inputs
     * Each row represents coefficients for one objective
     * Formula: Score[i] = sum(Map[i][j] * Input[j]) for j in 0..2
     */
    public const MAP = [
        [3.5, 2.5, 1.5], // EDM01
        [4.0, 2.5, 1.5], // EDM02
        [1.5, 1.0, 1.0], // EDM03
        [2.5, 2.0, 1.5], // EDM04
        [1.5, 1.0, 1.0], // EDM05
        [2.5, 1.5, 1.0], // APO01
        [4.0, 3.0, 1.5], // APO02
        [2.0, 1.0, 1.0], // APO03
        [4.0, 3.0, 1.0], // APO04
        [4.0, 2.5, 1.0], // APO05
        [1.0, 1.5, 1.0], // APO06
        [2.5, 1.0, 1.0], // APO07
        [3.0, 1.5, 1.0], // APO08
        [1.5, 1.5, 1.0], // APO09
        [2.5, 1.5, 1.0], // APO10
        [1.5, 1.5, 1.0], // APO11
        [2.0, 1.5, 1.0], // APO12
        [1.0, 1.0, 1.0], // APO13
        [2.5, 2.0, 1.0], // APO14
        [4.0, 3.0, 1.5], // BAI01
        [3.5, 2.5, 1.0], // BAI02
        [4.0, 2.5, 1.0], // BAI03
        [1.5, 1.5, 1.0], // BAI04
        [3.0, 2.0, 1.0], // BAI05
        [2.5, 2.0, 1.0], // BAI06
        [3.5, 2.5, 1.0], // BAI07
        [1.5, 1.0, 1.0], // BAI08
        [1.0, 1.0, 1.0], // BAI09
        [1.5, 1.0, 1.0], // BAI10
        [3.5, 2.5, 1.0], // BAI11
        [1.0, 1.0, 1.0], // DSS01
        [1.0, 1.0, 1.0], // DSS02
        [1.5, 1.0, 1.0], // DSS03
        [1.5, 1.0, 1.0], // DSS04
        [1.5, 1.0, 1.0], // DSS05
        [1.0, 1.0, 1.0], // DSS06
        [3.0, 2.0, 1.0], // MEA01
        [1.0, 1.0, 1.0], // MEA02
        [1.0, 1.0, 1.0], // MEA03
        [1.0, 1.0, 1.0], // MEA04
    ];

    /**
     * Get the objective labels (EDM01, APO01, etc.)
     *
     * @return array<int, string>
     */
    public static function getObjectiveLabels(): array
    {
        return [
            'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
            'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
            'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
            'APO11', 'APO12', 'APO13', 'APO14',
            'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05',
            'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
            'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
            'MEA01', 'MEA02', 'MEA03', 'MEA04',
        ];
    }
}
