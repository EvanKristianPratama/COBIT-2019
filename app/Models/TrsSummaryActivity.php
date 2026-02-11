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
        // If we have evidence from mst_evidence, use judul_dokumen as is
        if ($this->evidence && $this->evidence->judul_dokumen) {
            return $this->evidence->judul_dokumen;
        }
        
        // If using miss_evidence (manually entered), format it nicely
        if ($this->miss_evidence) {
            // Replace underscores with spaces
            $formatted = str_replace('_', ' ', $this->miss_evidence);
            // Convert to title case for better readability
            $formatted = ucwords(strtolower($formatted));
            return $formatted;
        }
        
        return null;
    }

    /**
     * Get evidence type from relation or default to 'Execution'
     */
    public function getEvidenceTypeAttribute()
    {
        return $this->evidence?->tipe ?? 'Execution';
    }
}
