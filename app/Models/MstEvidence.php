<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MstEvidence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mst_evidence';

    protected $fillable = [
        'eval_id',
        'organization_id',
        'judul_dokumen',
        'no_dokumen',
        'tahun_terbit',
        'tahun_kadaluarsa',
        'tipe',
        'pengesahan',
        'pemilik_dokumen',
        'klasifikasi',
        'grup',
        'link',
        'ket_tipe',
        'summary',
    ];

    protected $casts = [
        'tahun_terbit' => 'integer',
        'tahun_kadaluarsa' => 'integer',
    ];

    /**
     * Get the evaluation that owns the evidence.
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(MstEval::class, 'eval_id', 'eval_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(MstOrganization::class, 'organization_id', 'organization_id');
    }

    public function references(): BelongsToMany
    {
        return $this->belongsToMany(MstEvidenceRef::class, 'trs_ref_evidence', 'evidence_id', 'evidence_ref_id')
            ->withTimestamps();
    }
}
