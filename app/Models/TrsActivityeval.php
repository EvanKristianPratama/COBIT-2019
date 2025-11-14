<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrsActivityeval extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trs_activityeval';
    
    protected $fillable = [
        'eval_id',
        'activity_id',
        'level_achieved',
        'evidence',
        'notes'
    ];

    /**
     * Get the evaluation this activity belongs to
     */
    public function evaluation()
    {
        return $this->belongsTo(MstEval::class, 'eval_id', 'eval_id');
    }

    /**
     * Get the activity being evaluated
     */
    public function activity()
    {
        return $this->belongsTo(MstActivities::class, 'activity_id', 'activity_id');
    }

    /**
     * Scope for filtering by achievement level
     */
    public function scopeByAchievement($query, $achievement)
    {
        return $query->where('level_achieved', $achievement);
    }

    /**
     * Scope for filtering by evaluation
     */
    public function scopeByEvaluation($query, $evalId)
    {
        return $query->where('eval_id', $evalId);
    }
}
