<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsStep2 extends Model
{
    use HasFactory;

    protected $table = 'trs_step2';

    protected $fillable = [
        'assessment_id',
        'user_id',
        'objective_code',
        'rel_imp_df1',
        'rel_imp_df2',
        'rel_imp_df3',
        'rel_imp_df4',
        'total_objective',
        'initial_scope_score',
    ];

    protected $casts = [
        'objective_code' => 'integer',
        'rel_imp_df1' => 'decimal:2',
        'rel_imp_df2' => 'decimal:2',
        'rel_imp_df3' => 'decimal:2',
        'rel_imp_df4' => 'decimal:2',
        'total_objective' => 'decimal:2',
        'initial_scope_score' => 'decimal:2',
    ];

    /**
     * Get the assessment that owns this step2 record
     */
    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'assessment_id');
    }

    /**
     * Get the user that owns this step2 record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
