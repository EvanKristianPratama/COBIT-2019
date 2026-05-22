<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsPractRoles extends Model
{
    use HasFactory;

    protected $table = 'trs_practroles';

    protected $primaryKey = ['practice_id', 'role_id'];

    public $incrementing = false;

    // protected $keyType = 'int';

    protected $casts = [
        'practice_id' => 'string',
        'role_id' => 'integer',
        'r_a' => 'string',
    ];

    public $timestamps = false;

    protected $fillable = [
        'practice_id',
        'role_id',
        'r_a',
    ];
}
