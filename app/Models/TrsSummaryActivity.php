<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsSummaryActivity extends Model
{
    use HasFactory;

    protected $table = 'trs_summaryactivity';

    protected $fillable = [
        'summary_id',
        'activityeval_id',
        'evidence_id',
        'miss_evidence'
    ];

    public function summaryReport()
    {
        return $this->belongsTo(TrsSummaryReport::class, 'summary_id');
    }

    public function activityEval()
    {
        return $this->belongsTo(TrsActivityeval::class, 'activityeval_id');
    }

    public function evidence()
    {
        return $this->belongsTo(MstEvidence::class, 'evidence_id');
    }

    /**
     * Get evidence name from relation or miss_evidence fallback
     */
    public function getEvidenceNameAttribute()
    {
        return $this->evidence?->judul_dokumen ?? $this->miss_evidence;
    }

    /**
     * Get evidence type from relation or default to 'Execution'
     */
    public function getEvidenceTypeAttribute()
    {
        return $this->evidence?->tipe ?? 'Execution';
    }
}
