<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsMaturityScore extends Model
{
    use HasFactory;

    protected $table = 'trs_maturity_score';

    protected $fillable = [
        'eval_id',
        'score'
    ];

    protected $casts = [
        'score' => 'float'
    ];

    public function evaluation()
    {
        return $this->belongsTo(MstEval::class, 'eval_id', 'eval_id');
    }
}
