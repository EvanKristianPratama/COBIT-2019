<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsObjectiveScore extends Model
{
    use HasFactory;

    protected $table = 'trs_objective_score';

    protected $fillable = [
        'eval_id',
        'objective_id',
        'level'
    ];

    public function evaluation()
    {
        return $this->belongsTo(MstEval::class, 'eval_id', 'eval_id');
    }
}
