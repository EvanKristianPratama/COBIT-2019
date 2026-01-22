<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsStep3 extends Model
{
    use HasFactory;

    protected $table = 'trs_step3';

    protected $fillable = [
        'assessment_id',
        'user_id',
        'objective_code',
        'rel_imp_df5',
        'rel_imp_df6',
        'rel_imp_df7',
        'rel_imp_df8',
        'rel_imp_df9',
        'rel_imp_df10',
        'total_step3_objective',
        'total_combined',
        'refined_scope_score',
    ];

    protected $casts = [
        'objective_code' => 'integer',
        'rel_imp_df5' => 'decimal:2',
        'rel_imp_df6' => 'decimal:2',
        'rel_imp_df7' => 'decimal:2',
        'rel_imp_df8' => 'decimal:2',
        'rel_imp_df9' => 'decimal:2',
        'rel_imp_df10' => 'decimal:2',
        'total_step3_objective' => 'decimal:2',
        'total_combined' => 'decimal:2',
        'refined_scope_score' => 'decimal:2',
    ];

    /**
     * Get the assessment that owns this step3 record
     */
    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'assessment_id');
    }

    /**
     * Get the user that owns this step3 record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the corresponding step2 record for this objective
     */
    public function step2()
    {
        return $this->hasOne(TrsStep2::class, 'objective_code', 'objective_code')
            ->where('assessment_id', $this->assessment_id)
            ->where('user_id', $this->user_id);
    }
}
