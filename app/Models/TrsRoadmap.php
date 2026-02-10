<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsRoadmap extends Model
{
    use HasFactory;

    protected $table = 'trs_roadmap';

    protected $fillable = [
        'objective_id',
        'year',
        'level',
        'rating',
    ];

    protected $casts = [
        'year' => 'integer',
        'level' => 'integer',
    ];
}
