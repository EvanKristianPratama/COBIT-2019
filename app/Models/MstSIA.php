<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstSIA extends Model
{
    use HasFactory;

    protected $table = 'mst_SIA';

    protected $primaryKey = 'sia_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'sia_id',
        'objective_id',
        'description',
    ];

    public function objective()
    {
        return $this->belongsTo(MstObjective::class, 'objective_id');
    }
}
