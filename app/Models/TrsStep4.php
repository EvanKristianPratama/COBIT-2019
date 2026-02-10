<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsStep4 extends Model
{
    use HasFactory;

    protected $table = 'trs_step4';

    protected $fillable = [
        'assessment_id',
        'user_id',
        'objective_code',
        'objective_id',
        'adjustment',
        'reason_adjust',
        'concluded_priority',
        'suggested_level',
        'agreed_level',
        'reason_target',
        'is_selected',
    ];

    protected $casts = [
        'objective_code' => 'integer',
        'adjustment' => 'integer',
        'concluded_priority' => 'decimal:2',
        'suggested_level' => 'integer',
        'agreed_level' => 'integer',
        'is_selected' => 'boolean',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'assessment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
