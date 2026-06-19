<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstEntergoals extends Model
{
    use HasFactory;

    protected $table = 'mst_entergoals';

    protected $primaryKey = 'entergoals_id';
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'entergoals_id',
        'description'
    ];

    public function entergoalsmetr()
    {
        return $this->hasMany(MstEntergoalsmetr::class, 'entergoals_id', 'entergoals_id');
    }

    public function objectives()
    {
        return $this->belongsToMany(
            MstObjective::class,
            'trs_entergoals',
            'entergoals_id',
            'objective_id',
            'entergoals_id',
            'objective_id'
        )->withPivot('focus_area_id');
    }
}
