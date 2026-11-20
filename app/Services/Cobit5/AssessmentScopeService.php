<?php

namespace App\Services\Cobit5;

use App\Models\Cobit5\Cobit5AssessmentScope;
use Illuminate\Support\Facades\File;

class AssessmentScopeService
{
    /**
     * Get the assessment scope and self-diagnostic data for an organization or user.
     * If saved data exists, return it; otherwise return default baseline.
     */
    public function getScopeData(?int $organizationId, ?int $userId): array
    {
        $saved = Cobit5AssessmentScope::where(function ($query) use ($organizationId, $userId) {
            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            } elseif ($userId) {
                $query->where('user_id', $userId);
            }
        })->latest()->first();

        $defaultDimensions = $this->getDefaultScopeData();
        $defaultSelfDiagnostic = $this->getDefaultSelfDiagnostic();

        if ($saved && ! empty($saved->scope_data)) {
            $raw = $saved->scope_data;

            // Check if saved format is nested { dimensions: ..., self_diagnostic: ... }
            if (isset($raw['dimensions']) && is_array($raw['dimensions'])) {
                $dimensions = $this->recalculate($raw['dimensions']);
                $selfDiagnostic = $this->mergeSelfDiagnostic($defaultSelfDiagnostic, $raw['self_diagnostic'] ?? []);
            } else {
                // Legacy root dimensions format
                $dimensions = $this->recalculate($raw);
                $selfDiagnostic = $defaultSelfDiagnostic;
            }

            return [
                'dimensions' => $dimensions,
                'self_diagnostic' => $selfDiagnostic,
            ];
        }

        return [
            'dimensions' => $this->recalculate($defaultDimensions),
            'self_diagnostic' => $defaultSelfDiagnostic,
        ];
    }

    /**
     * Save customized scope data and self-diagnostic.
     */
    public function saveScopeData(?int $organizationId, ?int $userId, array $data): Cobit5AssessmentScope
    {
        $dimensions = isset($data['dimensions']) && is_array($data['dimensions'])
            ? $this->recalculate($data['dimensions'])
            : $this->recalculate($data);

        $selfDiagnostic = isset($data['self_diagnostic']) && is_array($data['self_diagnostic'])
            ? $data['self_diagnostic']
            : [];

        $payload = [
            'dimensions' => $dimensions,
            'self_diagnostic' => $selfDiagnostic,
        ];

        return Cobit5AssessmentScope::updateOrCreate(
            [
                'organization_id' => $organizationId,
            ],
            [
                'user_id' => $userId,
                'scope_data' => $payload,
            ]
        );
    }

    /**
     * Reset scope data to default.
     */
    public function resetScopeData(?int $organizationId): bool
    {
        if ($organizationId) {
            Cobit5AssessmentScope::where('organization_id', $organizationId)->delete();
        }

        return true;
    }

    /**
     * Recalculate all subtotals and grand totals for enterprise goals hierarchy.
     */
    public function recalculate(array $dimensions): array
    {
        foreach ($dimensions as &$dim) {
            $dimEgWeightSum = 0;
            $dimItrgScoreSum = 0;

            if (isset($dim['enterprise_goals']) && is_array($dim['enterprise_goals'])) {
                foreach ($dim['enterprise_goals'] as &$eg) {
                    $egWeight = isset($eg['weight']) ? (int) $eg['weight'] : 1;
                    $dimEgWeightSum += $egWeight;

                    $egItrgSum = 0;
                    if (isset($eg['itrgs']) && is_array($eg['itrgs'])) {
                        foreach ($eg['itrgs'] as &$itrg) {
                            $score = isset($itrg['score']) ? (int) $itrg['score'] : 0;
                            $egItrgSum += $score;
                        }
                    }
                    $eg['subtotal'] = $egItrgSum;
                    $dimItrgScoreSum += $egItrgSum;
                }
            }

            $dim['eg_count'] = $dimEgWeightSum;
            $dim['total_score'] = $dimItrgScoreSum;
        }

        return $dimensions;
    }

    /**
     * Merge saved self-diagnostic answers into template domains and processes.
     */
    public function mergeSelfDiagnostic(array $templateDomains, array $savedDiagnostic): array
    {
        if (empty($savedDiagnostic)) {
            return $templateDomains;
        }

        // Flatten saved values by process code for easy lookup
        $savedByCode = [];
        if (isset($savedDiagnostic[0]['processes'])) {
            // It's structured as domains
            foreach ($savedDiagnostic as $d) {
                foreach ($d['processes'] ?? [] as $p) {
                    if (! empty($p['code'])) {
                        $savedByCode[$p['code']] = $p;
                    }
                }
            }
        } else {
            // It's a key-value map e.g. "EDM01" => [...]
            $savedByCode = $savedDiagnostic;
        }

        foreach ($templateDomains as &$domain) {
            foreach ($domain['processes'] as &$proc) {
                $code = $proc['code'] ?? '';
                if (isset($savedByCode[$code])) {
                    $saved = $savedByCode[$code];
                    $proc['importance'] = $saved['importance'] ?? $proc['importance'] ?? '';
                    $proc['performance'] = $saved['performance'] ?? $proc['performance'] ?? '';
                    $proc['formality'] = $saved['formality'] ?? $proc['formality'] ?? '';
                    $proc['audited'] = $saved['audited'] ?? $proc['audited'] ?? '';
                    $proc['accountable'] = $saved['accountable'] ?? $proc['accountable'] ?? '';
                }
            }
        }

        return $templateDomains;
    }

    /**
     * Default baseline exact hierarchy matching the COBIT 5 assessment scope table.
     */
    public function getDefaultScopeData(): array
    {
        $jsonPath = __DIR__ . '/enterprise_goals_hierarchy.json';
        if (File::exists($jsonPath)) {
            $data = json_decode(File::get($jsonPath), true);
            if (is_array($data) && ! empty($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Get full IT-related goals hierarchy for Tab 4.
     */
    public function getItrgHierarchy(): array
    {
        $jsonPath = __DIR__ . '/itrg_hierarchy.json';
        if (File::exists($jsonPath)) {
            $data = json_decode(File::get($jsonPath), true);
            if (is_array($data) && ! empty($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Get default Self-Diagnostic template for Tab 5 (37 COBIT 5 processes in 5 domains).
     */
    public function getDefaultSelfDiagnostic(): array
    {
        $jsonPath = __DIR__ . '/self_diagnostic_template.json';
        if (File::exists($jsonPath)) {
            $data = json_decode(File::get($jsonPath), true);
            if (is_array($data) && ! empty($data)) {
                return $data;
            }
        }

        return [];
    }
}
