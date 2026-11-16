<?php

namespace App\Services\Cobit;

use App\Models\MstEvidenceRef;
use App\Models\MstFocusArea;
use App\Models\MstObjective;
use App\Services\Assessment\Evidence\EvidenceReferenceCatalog;
use App\Services\Assessment\Evidence\ReferenceInfoflowMapping;

class GamoOverviewService
{
    public function __construct(private readonly EvidenceReferenceCatalog $catalog, private readonly ReferenceInfoflowMapping $flowMapping)
    {
    }

    public function data(int $focusAreaId, ?string $objectiveId = null): array
    {
        $data = $this->referenceData($focusAreaId, $objectiveId);
        $data['objective']->load([
            'practices.practicemetr', 'practices.infoflowoutput', 'policies', 's_i_a',
        ]);
        $data['workProducts'] = $data['objective']->practices->flatMap->infoflowoutput
            ->unique('description')->values();

        return $data;
    }

    public function referenceData(int $focusAreaId, ?string $objectiveId = null): array
    {
        $focusArea = MstFocusArea::findOrFail($focusAreaId);
        abort_unless((string) $focusArea->version === '2019', 404);

        $objectives = MstObjective::where('focus_area_id', $focusAreaId)
            ->orderByRaw("CASE WHEN objective_id LIKE 'EDM%' THEN 0 WHEN objective_id LIKE 'APO%' THEN 1 WHEN objective_id LIKE 'BAI%' THEN 2 WHEN objective_id LIKE 'DSS%' THEN 3 ELSE 4 END")
            ->orderBy('objective_id')->get();
        $objectiveId ??= $objectives->first()?->objective_id;
        $objective = $objectives->firstWhere('objective_id', $objectiveId);
        abort_unless($objective, 404);

        $catalogReady = $this->catalog->available();
        $references = $catalogReady
            ? MstEvidenceRef::where('focus_area_id', $focusAreaId)->where('objective_id', $objectiveId)
                ->where('is_active', true)->orderBy('id')->get()
            : collect();
        $flowMappingReady = $this->flowMapping->available();
        $flowMappingOptions = $this->flowMapping->choices($objective);
        if ($flowMappingReady && $references->isNotEmpty()) {
            $references->load(['infoflowMappings.input.practice', 'infoflowMappings.output.practice']);
        }

        return compact('focusArea', 'focusAreaId', 'objectives', 'objective', 'references', 'catalogReady', 'flowMappingReady', 'flowMappingOptions');
    }
}
