<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsScoping extends Model
{
    use HasFactory;

    protected $table = 'trs_scoping';
    
    protected $fillable = [
        'eval_id',
        'nama_scope'
    ];

    /**
     * Get the evaluation that owns the scoping.
     */
    public function evaluation()
    {
        return $this->belongsTo(MstEval::class, 'eval_id', 'eval_id');
    }

    /**
     * Get the details for the scoping.
     */
    public function details()
    {
        return $this->hasMany(TrsEvalDetail::class, 'scoping_id', 'id');
    }
}
