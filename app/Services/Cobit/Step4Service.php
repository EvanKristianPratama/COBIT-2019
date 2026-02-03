<?php

namespace App\Services\Cobit;

use App\Models\Assessment;
use App\Models\TargetCapability; // Assumed model name
use Illuminate\Support\Facades\Session;

class Step4Service
{
    /**
     * Calculate and save the Target Capability levels based on the concluded scope.
     *
     * @param Assessment $assessment
     * @param array $step2Data Initial scope data
     * @param array $step3Data Refined scope (adjustments)
     * @param array $step4Data Concluded scope adjustments
     * @return TargetCapability
     */
    public function saveTargetCapability(Assessment $assessment, array $step2Data, array $step3Data, array $step4Data)
    {
        // 1. Calculate final scores for each domain/process (EDM01, APO01, etc.)
        // Logic: specific calculation based on COBIT 2019 rules.
        // For now, we will map the inputs to the TargetCapability model.

        // Placeholder for calculation logic
        $targetLevels = $this->calculateTargetLevels($step2Data, $step3Data, $step4Data);
        
        // 2. Save or Update to Database
        // We use 'organisasi' and 'tahun' as keys if assessment_id is not directly linked,
        // BUT ideally we should link via assessment_id if possible, or user_id.
        // The current TargetCapability model uses user_id, organisasi, tahun.
        
        $data = [
            'user_id' => $assessment->user_id,
            'organisasi' => $assessment->instansi,
            'tahun' => $assessment->tahun ?? date('Y'),
        ];

        // Merge calculated levels
        $data = array_merge($data, $targetLevels);

        // Update or Create
        // unique key: user_id + organisasi + tahun ?? Or just insert new?
        // Assuming we update if exists for this assessment context
        
        $targetCap = TargetCapability::updateOrCreate(
            [
                'user_id' => $assessment->user_id,
                'organisasi' => $assessment->instansi,
                'tahun' => $assessment->tahun ?? date('Y'),
            ],
            $data
        );

        return $targetCap;
    }

    private function calculateTargetLevels(array $step2Data, array $step3Data, array $step4Data)
    {
        $levels = [];
        // Example logic:
        // Iterate through all 40 objectives (EDM01...MEA04)
        // Level = Initial (Step2) + Adjustments (Step3) + Adjustments (Step4)
        // This needs to be fleshed out with actual domain logic.
        
        // Detailed implementation would go here.
        // For now, returning empty to be filled by implementation.
        return $levels;
    }
}
