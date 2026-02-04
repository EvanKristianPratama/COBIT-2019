<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstObjective extends Model
{
    use HasFactory;
    
    protected $table = 'mst_objective';

    protected $primaryKey = 'objective_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'objective_id',
        'objective',
        'objective_description',
        'objective_purpose',
    ];

    public function domains()
    {
        return $this->belongsToMany(
            MstArea::class,
            'trs_domain',
            'objective_id',
            'area'
        )->withPivot('domain');
    }

    public function practices()
    {
        return $this->hasMany(MstPractice::class, 'objective_id', 'objective_id');
    }

    public function policies()
    {
        return $this->hasMany(MstPolicy::class, 'objective_id', 'objective_id');
    }

    // public function SIA()
    // {
    //     return $this->hasMany(MstSIA::class, 'objective_id', 'objective_id');
    // }

    public function s_i_a()
    {
        return $this->hasMany(
            MstSIA::class,        // your SIA model
            'objective_id',       // foreign key on the MstSia table
            'objective_id'        // local key on this model
        );
    }

    public function entergoals()
    {
        return $this->belongsToMany(
            MstEntergoals::class,
            'trs_entergoals',
            'objective_id',
            'entergoals_id'
        );
    }

    public function aligngoals()
    {
        return $this->belongsToMany(
            MstAligngoals::class,
            'trs_aligngoals',
            'objective_id',
            'aligngoals_id'
        );
    }

    public function guidance()
    {
        return $this->belongsToMany(
            MstGuidance::class,
            'trs_objectiveguidance',
            'objective_id',
            'guidance_id'
        )->withPivot('component');
    }

    public function keyculture()
    {
        return $this->hasMany(MstKeyCulture::class, 'objective_id', 'objective_id');
    }

    public function skill()
    {
        return $this->hasMany(MstSkill::class, 'objective_id', 'objective_id');
    }

    /**
     * Get all evaluations for this objective
     */
    public function evaluations()
    {
        return $this->hasMany(MstEval::class, 'objective_id', 'objective_id');
    }

    public function roadmaps()
    {
        return $this->hasMany(TrsRoadmap::class, 'objective_id', 'objective_id');
    }
}
