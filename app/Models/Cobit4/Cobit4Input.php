<?php

namespace App\Models\Cobit4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobit4Input extends Model
{
    use HasFactory;

    protected $table = 'trs_cobit4_inputs';

    protected $fillable = [
        'process_id',
        'from_source',
        'input_description',
        'order_no',
    ];

    public function process()
    {
        return $this->belongsTo(Cobit4Process::class, 'process_id');
    }
}
