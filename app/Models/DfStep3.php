<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DfStep3 extends Model
{
    use HasFactory;

    protected $table = 'df_step3';

    protected $fillable = [
        'assessment_id',
        'user_id',
        'weights',
    ];

    protected $casts = [
        'weights' => 'array',
        'refined_scopes' => 'array',
    ];
}