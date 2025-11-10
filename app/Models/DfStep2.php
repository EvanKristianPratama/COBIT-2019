<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DfStep2 extends Model
{
    use HasFactory;

    protected $table = 'df_step2';

    protected $fillable = [
        'assessment_id',
        'user_id',
        'weights',
    ];

    protected $casts = [
        'weights' => 'array',
        'relative_importances' => 'array',
    ];
}