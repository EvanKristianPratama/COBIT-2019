<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstPractice extends Model
{
    use HasFactory;

    protected $table = 'mst_practice';

    protected $primaryKey = 'practice_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'focus_area_id',
        'practice_id',
        'objective_id',
        'practice_name',
        'practice_description',
    ];

    public function objective()
    {
        return $this->belongsTo(MstObjective::class, 'objective_id', 'objective_id');
    }

    public function guidances()
    {
        return $this->belongsToMany(
            MstGuidance::class,
            'trs_practiceguidance',  // pivot table
            'practice_id',           // this model’s FK on pivot
            'guidance_id'            // other model’s FK on pivot
        );
    }

    public function activities()
    {
        return $this->hasMany(MstActivities::class, 'practice_id', 'practice_id');
    }

    public function practicemetr()
    {
        return $this->hasMany(MstPracticeMetr::class, 'practice_id', 'practice_id');
    }

    public function roles()
    {
        return $this->belongsToMany(
            MstRoles::class,
            'trs_practroles',  // pivot table
            'practice_id',           // this model’s FK on pivot
            'role_id'            // other model’s FK on pivot
        )->withPivot('r_a');;
    }

    public function infoflowinput()
    {
        return $this->hasMany(MstInfoflowInput::class, 'practice_id', 'practice_id');
    }

    public function infoflowoutput()
    {
        return $this->hasMany(MstInfoflowOutput::class, 'practice_id', 'practice_id');
    }
}
