<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrsDomain extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'trs_domain';

    protected $primaryKey = ['area', 'objective_id'];

    protected $keyType = 'string';

    protected $fillable = [
        'focus_area_id',
        'area',
        'objective_id',
        'domain',
    ];


    public function area()
    {
        return $this->belongsTo(MstArea::class, 'area', 'area');
    }

    public function objective()
    {
        return $this->belongsTo(MstObjective::class, 'objective_id', 'objective_id');
    }
}
