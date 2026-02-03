<?php

declare(strict_types=1);

namespace App\Data\Cobit;

/**
 * DF5 (IT Investment Portfolio) Data Configuration
 */
final class Df5Data
{
    public const INPUT_COUNT = 2;
    public const OBJECTIVE_COUNT = 40;

    /** Baseline inputs: [Growth/Transform, Run/Maintain] */
    public const BASELINE_INPUTS = [33, 67];

    /** Baseline scores per objective */
    public const BASELINE_SCORES = [
        1.66, 1.00, 1.99, 1.00, 1.33, 1.66, 1.00, 1.66, 1.00, 1.00,
        1.00, 1.33, 1.00, 1.33, 1.66, 1.33, 1.99, 1.99, 1.66, 1.00,
        1.00, 1.00, 1.33, 1.00, 1.66, 1.00, 1.00, 1.00, 1.66, 1.00,
        1.00, 1.66, 1.33, 1.99, 1.66, 1.66, 1.66, 1.33, 1.66, 1.66,
    ];

    /** Mapping matrix: 40 objectives × 2 inputs */
    public const MAP = [
        [3.0, 1.0], [1.0, 1.0], [4.0, 1.0], [1.0, 1.0], [2.0, 1.0],
        [3.0, 1.0], [1.0, 1.0], [3.0, 1.0], [1.0, 1.0], [1.0, 1.0],
        [1.0, 1.0], [2.0, 1.0], [1.0, 1.0], [2.0, 1.0], [3.0, 1.0],
        [2.0, 1.0], [4.0, 1.0], [4.0, 1.0], [3.0, 1.0], [1.0, 1.0],
        [1.0, 1.0], [1.0, 1.0], [2.0, 1.0], [1.0, 1.0], [3.0, 1.0],
        [1.0, 1.0], [1.0, 1.0], [1.0, 1.0], [3.0, 1.0], [1.0, 1.0],
        [1.0, 1.0], [3.0, 1.0], [2.0, 1.0], [4.0, 1.0], [3.0, 1.0],
        [3.0, 1.0], [3.0, 1.0], [2.0, 1.0], [3.0, 1.0], [3.0, 1.0],
    ];
}
