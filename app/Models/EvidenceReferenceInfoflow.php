<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceReferenceInfoflow extends Model
{
    protected $table = 'trs_evidence_ref_infoflow';

    protected $fillable = ['evidence_ref_id', 'input_id', 'output_id'];

    public function input(): BelongsTo
    {
        return $this->belongsTo(MstInfoflowInput::class, 'input_id', 'input_id');
    }

    public function output(): BelongsTo
    {
        return $this->belongsTo(MstInfoflowOutput::class, 'output_id', 'output_id');
    }
}
