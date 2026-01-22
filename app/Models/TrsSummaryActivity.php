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
}
