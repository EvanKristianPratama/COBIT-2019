<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstSkill extends Model
{
    use HasFactory;

    protected $table = 'mst_skill';

    protected $primaryKey = 'skill_id';

    // public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'skill_id',
        'objective_id',
        'skill',
        // 'objective_purpose',
    ];

    public function objective()
    {
        return $this->belongsTo(MstObjective::class, 'objective_id');
    }

    public function guidances()
    {
        return $this->belongsToMany(
            MstGuidance::class,
            'trs_skillguidance',  // pivot table
            'skill_id',           // this model’s FK on pivot
            'guidance_id'            // other model’s FK on pivot
        );
    }
}
