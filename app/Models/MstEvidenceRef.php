<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstEvidenceRef extends Model
{
    protected $table = 'mst_evidence_ref';

    protected $fillable = [
        'focus_area_id', 'objective_id', 'nama_dokumen', 'keterangan',
        'referensi_sumber', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(MstObjective::class, 'objective_id', 'objective_id');
    }

    public function focusArea(): BelongsTo
    {
        return $this->belongsTo(MstFocusArea::class, 'focus_area_id');
    }

    public function evidences(): BelongsToMany
    {
        return $this->belongsToMany(MstEvidence::class, 'trs_ref_evidence', 'evidence_ref_id', 'evidence_id')
            ->withTimestamps();
    }

    public function infoflowMappings(): HasMany
    {
        return $this->hasMany(EvidenceReferenceInfoflow::class, 'evidence_ref_id')->orderBy('id');
    }
}
