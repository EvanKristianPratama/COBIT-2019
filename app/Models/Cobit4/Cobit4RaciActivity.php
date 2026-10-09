<?php

namespace App\Models\Cobit4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobit4RaciActivity extends Model
{
    use HasFactory;

    protected $table = 'trs_cobit4_raci_activities';

    protected $fillable = [
        'process_id',
        'activity',
        'raci_matrix',
        'order_no',
    ];

    protected $casts = [
        'raci_matrix' => 'array',
    ];

    public function process()
    {
        return $this->belongsTo(Cobit4Process::class, 'process_id');
    }
}
