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

    /**
     * Get the objective that owns the roadmap entry.
     */
    public function objective()
    {
        return $this->belongsTo(MstObjective::class, 'objective_id', 'objective_id');
    }
}
