<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MstCobitEvolution extends Model
{
    protected $table = 'mst_cobit_evolution';

    protected $fillable = [
        'cobit2019_id',
        'domain',
        'title_2019',
        'desc_2019',
        'cobit5_id',
        'cobit5_title',
        'cobit4_id',
        'cobit4_title',
        'status',
        'notes',
    ];
}
