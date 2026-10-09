<?php

namespace App\Models\Cobit4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobit4ControlObjective extends Model
{
    use HasFactory;

    protected $table = 'mst_cobit4_control_objectives';

    protected $fillable = [
        'process_id',
        'code',
        'title',
        'description',
        'order_no',
    ];

    public function process()
    {
        return $this->belongsTo(Cobit4Process::class, 'process_id');
    }
}
