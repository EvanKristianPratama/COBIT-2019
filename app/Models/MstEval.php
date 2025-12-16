<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstEval extends Model
{
    use HasFactory;

    protected $table = 'mst_eval';
    protected $primaryKey = 'eval_id';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'user_id',
        'status',
        'tahun'
    ];

    /**
     * Get the user that owns the evaluation
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get all activity evaluations for this evaluation
     */
    public function activityEvaluations()
    {
        return $this->hasMany(TrsActivityeval::class, 'eval_id', 'eval_id');
    }

    /**
     * Get all evidences for this evaluation
     */
    public function evidences()
    {
        return $this->hasMany(MstEvidence::class, 'eval_id', 'eval_id');
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the maturity score associated with the evaluation.
     */
    public function maturityScore()
    {
        return $this->hasOne(TrsMaturityScore::class, 'eval_id', 'eval_id');
    }

    /**
     * Get the objective scores associated with the evaluation.
     */
    public function objectiveScores()
    {
        return $this->hasMany(TrsObjectiveScore::class, 'eval_id', 'eval_id');
    }
}
