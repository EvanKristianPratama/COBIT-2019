<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrsEntergoals extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'trs_entergoals';

    protected $primaryKey = ['objective_id', 'entergoals_id'];

    protected $keyType = 'string';

    protected $fillable = [
        'focus_area_id',
        'objective_id',
        'entergoals_id',
    ];

    public function objective()
    {
        return $this->belongsTo(MstObjective::class, 'objective_id', 'objective_id');
    }

    public function entergoals()
    {
        return $this->belongsTo(MstEntergoals::class, 'entergoals_id', 'entergoals_id');
    }
}
