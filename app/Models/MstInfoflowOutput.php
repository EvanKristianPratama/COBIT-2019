<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstInfoflowOutput extends Model
{
    use HasFactory;

    protected $table = 'mst_infoflowoutput';

    protected $primaryKey = 'output_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'output_id',
        'practice_id',
        'to',
        'description',
        // 'skill',
        // 'objective_purpose',
    ];

    public function practice()
    {
        return $this->belongsTo(MstPractice::class, 'practice_id', 'practice_id');
    }

    public function connectedinputs()
    {
        return $this->belongsToMany(
            MstInfoflowInput::class,
            'trs_infoflowio',
            'output_id',
            'input_id'
        );
    }
}
