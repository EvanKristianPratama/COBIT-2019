<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstKeyCulture extends Model
{
    use HasFactory;

    protected $table = 'mst_keyculture';

    protected $primaryKey = 'keyculture_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'keyculture_id',
        'objective_id',
        'element',
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
            'trs_keycultureguidance',  // pivot table
            'keyculture_id',           // this model’s FK on pivot
            'guidance_id'            // other model’s FK on pivot
        );
    }
}
