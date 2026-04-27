<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstInfoflowInput extends Model
{
    use HasFactory;

    protected $table = 'mst_infoflowinput';

    protected $primaryKey = 'input_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'input_id',
        'practice_id',
        'from',
        'description',
        // 'skill',
        // 'objective_purpose',
    ];

    public function practice()
    {
        return $this->belongsTo(MstPractice::class, 'practice_id', 'practice_id');
    }

    public function connectedoutputs()
    {
        return $this->belongsToMany(
            MstInfoflowOutput::class,
            'trs_infoflowio',
            'input_id',
            'output_id'
        );
    }
}
