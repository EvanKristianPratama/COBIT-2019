<?php

namespace App\Services\Assessment\Evidence;

use App\Models\MstEvidenceRef;
use App\Models\MstObjective;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ReferenceInfoflowMapping
{
    public function available(): bool
    {
        return count(array_intersect(['trs_evidence_ref_infoflow', 'mst_evidence_ref', 'mst_infoflowinput', 'mst_infoflowoutput'], Schema::getTableListing())) === 4;
    }

    public function choices(MstObjective $objective): Collection
    {
        if (! $this->available()) {
            return collect();
        }
        $choices = collect();
        foreach (['input' => ['mst_infoflowinput', 'input_id', 'from'], 'output' => ['mst_infoflowoutput', 'output_id', 'to']] as $direction => [$table, $key, $endpoint]) {
            $rows = DB::table($table.' as flow')->join('mst_practice as p', 'p.practice_id', '=', 'flow.practice_id')
                ->join('mst_objective as o', 'o.objective_id', '=', 'p.objective_id')
                ->where('o.objective_id', $objective->objective_id)->where('o.focus_area_id', $objective->focus_area_id)
                ->get(['flow.'.$key.' as id', 'flow.description', 'flow.'.$endpoint.' as endpoint', 'p.practice_id', 'p.practice_name']);
            foreach ($rows as $row) {
                if (trim($row->description ?? '') === '') {
                    continue;
                }
                $choices->push(['direction' => $direction, 'id' => (int) $row->id,
                    'practice_id' => $row->practice_id, 'practice_code' => $this->displayCode($row->practice_id),
                    'practice_name' => $this->displayText($row->practice_name), 'description' => $this->displayText($row->description),
                    'endpoint' => $this->displayText($row->endpoint)]);
            }
        }

        return $choices->sortBy(fn ($row) => [$row['practice_code'], $row['direction'], $row['id']])->values();
    }

    public function synchronize(MstEvidenceRef $reference, array $attributes): void
    {
        if (! array_key_exists('flow_mappings', $attributes)) {
            return;
        }
        abort_unless($this->available(), 409, 'Pemetaan Information Flow belum diaktifkan.');
        $objective = MstObjective::where('objective_id', $reference->objective_id)->where('focus_area_id', $reference->focus_area_id)->firstOrFail();
        $choices = $this->choices($objective)->keyBy(fn ($row) => $row['direction'].':'.$row['id']);
        $selected = [];
        foreach ($attributes['flow_mappings'] as $index => $mapping) {
            $key = $mapping['direction'].':'.$mapping['flow_id'];
            if (! $choices->has($key)) {
                throw ValidationException::withMessages(['flow_mappings.'.$index.'.flow_id' => 'Pilih alur dari GAMO dan model referensi ini.']);
            }
            if (isset($selected[$key])) {
                throw ValidationException::withMessages(['flow_mappings.'.$index.'.flow_id' => 'Alur yang sama sudah dipilih.']);
            }
            $selected[$key] = $mapping;
        }
        $retained = [];
        foreach ($selected as $mapping) {
            $input = $mapping['direction'] === 'input' ? (int) $mapping['flow_id'] : null;
            $output = $mapping['direction'] === 'output' ? (int) $mapping['flow_id'] : null;
            $row = $reference->infoflowMappings()->firstOrCreate(['input_id' => $input, 'output_id' => $output]);
            $retained[] = $row->id;
        }
        $reference->infoflowMappings()->whereNotIn('id', $retained)->delete();
    }

    public function displayCode(?string $value): string
    {
        return preg_replace('/\.(?:M|FA)\d+(?:\.\d+)?$/i', '', str_replace('"', '', trim($value ?? '')));
    }

    public function displayText(?string $value): string
    {
        return preg_replace('/^[\s"“”]+|[\s"“”]+$/u', '', $value ?? '');
    }
}
