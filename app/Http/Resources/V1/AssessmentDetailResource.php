<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'assessment_id' => $this->kode_assessment,
            'project_info' => [
                'id' => $this->id, // or assessment_id primary key if different
                'name' => $this->instansi ?? 'Unnamed Project',
                'year' => $this->tahun,
                'created_at' => $this->created_at->toIso8601String(),
            ],
            // Mocking these for now as per "Migrasi Cloud Server" example structure
            // In real implementatoin specific relations would be loaded
            'governance_objective' => 'APO05 - Managed Portfolio (Placeholder)', 
            'maturity_score' => [
                'current_level' => 2.5, // Logic to be calculated or fetched
                'target_level' => 4.0,  // Logic to be calculated or fetched
                'gap' => 1.5,
            ],
            'details' => $this->whenLoaded('df1', function () {
                // Example of creating details from loaded relations
                return $this->df1->map(function ($item) {
                     return [
                        'component' => 'Design Factor 1',
                        'value' => $item->toArray()
                     ];
                });
            }),
        ];
    }
}
