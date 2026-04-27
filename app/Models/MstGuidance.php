<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstGuidance extends Model
{
    use HasFactory;

    protected $table = 'mst_guidance';

    protected $primaryKey = 'guidance_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'guidance_id',
        'guidance',
        'reference',
        // 'objective_purpose',
    ];
}
