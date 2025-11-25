<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsEvalDetail extends Model
{
    use HasFactory;

    protected $table = 'trs_evaldetail';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'eval_id',
        'domain_id',
    ];

    /**
     * Belongs to evaluation (mst_eval)
     */
    public function evaluation()
    {
        return $this->belongsTo(MstEval::class, 'eval_id', 'eval_id');
    }

    /**
     * Scope to filter by evaluation id
     */
    public function scopeForEval($query, $evalId)
    {
        return $query->where('eval_id', $evalId);
    }
}