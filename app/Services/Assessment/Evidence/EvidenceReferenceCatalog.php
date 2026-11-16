<?php

namespace App\Services\Assessment\Evidence;

use App\Models\MstEvidence;
use App\Models\MstEvidenceRef;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class EvidenceReferenceCatalog
{
    public function available(): bool
    {
        return Schema::hasTable('mst_evidence_ref') && Schema::hasTable('trs_ref_evidence');
    }

    public function options(): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        return MstEvidenceRef::with(['objective', 'focusArea'])
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN objective_id LIKE 'EDM%' THEN 0 WHEN objective_id LIKE 'APO%' THEN 1 WHEN objective_id LIKE 'BAI%' THEN 2 WHEN objective_id LIKE 'DSS%' THEN 3 ELSE 4 END")
            ->orderBy('objective_id')
            ->orderBy('nama_dokumen')
            ->get();
    }

    public function rules(): array
    {
        if (! $this->available()) {
            return ['reference_ids' => ['sometimes', 'array', 'max:0']];
        }

        return [
            'reference_ids' => ['sometimes', 'array', 'max:100'],
            'reference_ids.*' => ['integer', 'distinct', Rule::exists('mst_evidence_ref', 'id')->where('is_active', true)],
        ];
    }

    public function synchronize(MstEvidence $evidence, array $attributes): void
    {
        if ($this->available() && array_key_exists('reference_ids', $attributes)) {
            $evidence->references()->sync($attributes['reference_ids']);
        }
    }
}
