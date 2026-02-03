<?php

namespace App\Services\Api;

use App\Models\Assessment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssessmentApiService
{
    /**
     * Get assessment details by ID.
     *
     * @param int|string $id
     * @return Assessment
     * @throws ModelNotFoundException
     */
    public function getAssessmentDetails($id): Assessment
    {
        // Try to find by primary key or kode_assessment
        $assessment = Assessment::with(['df1', 'df2', 'df3']) // Eager load necessary relations
            ->where('assessment_id', $id)
            ->orWhere('kode_assessment', $id)
            ->firstOrFail();

        return $assessment;
    }

    /**
     * Calculate maturity or other business logic can go here.
     */
    public function calculateMaturity(Assessment $assessment)
    {
        // ... calculation logic ...
    }
}
