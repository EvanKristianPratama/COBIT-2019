<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsSummaryReport extends Model
{
    use HasFactory;

    protected $table = 'trs_summaryreport';

    protected $fillable = [
        'eval_id',
        'objective_id',
        'kesimpulan',
        'rekomendasi',
        'roadmap_rekomendasi',
    ];

    protected $casts = [
        'roadmap_rekomendasi' => 'array',
    ];
}
