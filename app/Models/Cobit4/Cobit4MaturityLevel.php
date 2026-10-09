<?php

namespace App\Models\Cobit4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobit4MaturityLevel extends Model
{
    use HasFactory;

    protected $table = 'mst_cobit4_maturity_levels';

    protected $fillable = [
        'process_id',
        'level',
        'name',
        'description',
    ];

    public function process()
    {
        return $this->belongsTo(Cobit4Process::class, 'process_id');
    }
}
