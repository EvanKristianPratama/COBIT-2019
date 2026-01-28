<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Cobit;

use App\Data\Cobit\Df10Data;
use App\Services\Cobit\DesignFactorCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for DesignFactorCalculator
 *
 * These tests verify the mathematical calculations match the original logic.
 * Running: php artisan test --filter DesignFactorCalculatorTest
 */
class DesignFactorCalculatorTest extends TestCase
{
    private DesignFactorCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DesignFactorCalculator();
    }

    /**
     * Test MROUND function works correctly
     */
    public function test_mround_rounds_to_nearest_multiple(): void
    {
        // Test rounding to nearest 5
        $this->assertEquals(100.0, $this->calculator->mround(102, 5));
        $this->assertEquals(105.0, $this->calculator->mround(103, 5));
        $this->assertEquals(105.0, $this->calculator->mround(104, 5));
        $this->assertEquals(110.0, $this->calculator->mround(107.5, 5));

        // Edge case: zero multiple
        $this->assertEquals(0.0, $this->calculator->mround(100, 0));
    }

    /**
     * Test score calculation with baseline inputs (15%, 70%, 15%)
     * This should produce scores matching the BASELINE_SCORES
     */
    public function test_calculate_scores_with_baseline_inputs(): void
    {
        $inputs = Df10Data::BASELINE_INPUTS; // [15, 70, 15]
        $scores = $this->calculator->calculateScores($inputs, Df10Data::MAP);

        // The first objective (EDM01) should calculate as:
        // 3.5 * 0.15 + 2.5 * 0.70 + 1.5 * 0.15 = 0.525 + 1.75 + 0.225 = 2.50
        $this->assertEquals(2.50, $scores[0], 'EDM01 score should be 2.50');

        // Verify we get 40 scores
        $this->assertCount(40, $scores);

        // Compare first 5 scores to expected baselines
        $this->assertEquals(2.50, $scores[0]); // EDM01
        $this->assertEquals(2.58, $scores[1]); // EDM02
        $this->assertEquals(1.08, $scores[2]); // EDM03
        $this->assertEquals(2.00, $scores[3]); // EDM04
        $this->assertEquals(1.08, $scores[4]); // EDM05
    }

    /**
     * Test relative importance with baseline inputs should be 0
     */
    public function test_relative_importance_with_baseline_is_zero(): void
    {
        $inputs = Df10Data::BASELINE_INPUTS;
        $scores = $this->calculator->calculateScores($inputs, Df10Data::MAP);
        $ri = $this->calculator->calculateRelativeImportance($scores, Df10Data::BASELINE_SCORES);

        // With baseline inputs, relative importance should be 0 for all objectives
        foreach ($ri as $index => $value) {
            $this->assertEquals(
                0,
                $value,
                "Relative importance for objective {$index} should be 0 with baseline inputs"
            );
        }
    }

    /**
     * Test with extreme inputs (100%, 0%, 0%)
     */
    public function test_calculate_scores_with_high_threat_inputs(): void
    {
        $inputs = [100, 0, 0]; // 100% high threat
        $scores = $this->calculator->calculateScores($inputs, Df10Data::MAP);

        // EDM01: 3.5 * 1.0 + 2.5 * 0 + 1.5 * 0 = 3.50
        $this->assertEquals(3.50, $scores[0]);

        // Relative importance should be positive (above baseline)
        $ri = $this->calculator->calculateRelativeImportance($scores, Df10Data::BASELINE_SCORES);
        $this->assertGreaterThan(0, $ri[0], 'High threat should increase relative importance');
    }

    /**
     * Test with low threat inputs (0%, 0%, 100%)
     */
    public function test_calculate_scores_with_low_threat_inputs(): void
    {
        $inputs = [0, 0, 100]; // 100% low threat
        $scores = $this->calculator->calculateScores($inputs, Df10Data::MAP);

        // EDM01: 3.5 * 0 + 2.5 * 0 + 1.5 * 1.0 = 1.50
        $this->assertEquals(1.50, $scores[0]);

        // Relative importance should be negative (below baseline)
        $ri = $this->calculator->calculateRelativeImportance($scores, Df10Data::BASELINE_SCORES);
        $this->assertLessThan(0, $ri[0], 'Low threat should decrease relative importance');
    }

    /**
     * Test input validation
     */
    public function test_validate_input_sum(): void
    {
        // Valid: sums to 100
        $this->assertTrue($this->calculator->validateInputSum([15, 70, 15]));
        $this->assertTrue($this->calculator->validateInputSum([33, 33, 34]));

        // Valid with tolerance
        $this->assertTrue($this->calculator->validateInputSum([33, 33, 33], 1.0));

        // Invalid: doesn't sum to 100
        $this->assertFalse($this->calculator->validateInputSum([50, 50, 50]));
        $this->assertFalse($this->calculator->validateInputSum([10, 10, 10]));
    }

    /**
     * Test division by zero is handled
     */
    public function test_relative_importance_handles_zero_baseline(): void
    {
        $scores = [1.0, 2.0];
        $baselines = [0.0, 2.0]; // First baseline is zero

        $ri = $this->calculator->calculateRelativeImportance($scores, $baselines);

        // Should return 0 for division by zero, not throw error
        $this->assertEquals(0, $ri[0]);
        $this->assertEquals(0, $ri[1]); // (1.0/2.0)*100 = 50, mround(50,5)=50, 50-100=-50... wait
        // Actually: (2.0/2.0)*100 = 100, mround(100,5)=100, 100-100=0
        $this->assertEquals(0, $ri[1]);
    }
}
