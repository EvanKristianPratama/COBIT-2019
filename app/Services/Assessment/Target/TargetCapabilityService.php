<?php

namespace App\Services\Assessment\Target;

class TargetCapabilityService
{
    /**
     * Helper: Fetch Target Capabilities as map
     */
    public function fetchTargetCapabilities($evaluation)
    {
        $targetCapabilityMap = [];
        $assessmentYear = $evaluation->tahun ?? $evaluation->year ?? $evaluation->assessment_year ?? null;

        if ($assessmentYear) {
            $targetCapability = \App\Models\TargetCapability::where('user_id', $evaluation->user_id)
                ->where('tahun', (int) $assessmentYear)
                ->when(
                    filled($evaluation->organization_id),
                    fn ($query) => $query->where('organization_id', $evaluation->organization_id)
                )
                ->latest('updated_at')
                ->first();

            if ($targetCapability) {
                // List of all 40 GAMOs
                $fields = [
                    'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
                    'APO01', 'APO02', 'APO03', 'APO04', 'APO05', 'APO06', 'APO07', 'APO08', 'APO09', 'APO10', 'APO11', 'APO12', 'APO13', 'APO14',
                    'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
                    'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
                    'MEA01', 'MEA02', 'MEA03', 'MEA04',
                ];
                foreach ($fields as $field) {
                    $targetCapabilityMap[$field] = $targetCapability->$field !== null ? (int) $targetCapability->$field : null;
                }
            }
        }

        return $targetCapabilityMap;
    }
}
