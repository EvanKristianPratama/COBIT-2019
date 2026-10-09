<?php

namespace App\Models\Cobit4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobit4GoalsMetric extends Model
{
    use HasFactory;

    protected $table = 'trs_cobit4_goals_metrics';

    protected $fillable = [
        'process_id',
        'category',
        'content',
        'order_no',
    ];

    public function process()
    {
        return $this->belongsTo(Cobit4Process::class, 'process_id');
    }
}
