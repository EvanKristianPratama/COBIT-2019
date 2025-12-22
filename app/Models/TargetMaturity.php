<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetMaturity extends Model
{
    use HasFactory;

    protected $table = 'target_maturities';
    
    // Explicitly disabling timestamps if user only wants simple storage, 
    // but plan said "timestamps", so we keep default true.
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'tahun',
        'organisasi',
        'target_maturity'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
