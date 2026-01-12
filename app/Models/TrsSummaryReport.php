<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrsSummaryReport extends Model
{
    use HasFactory;

    protected $table = 'trs_summaryreport';

    protected $guarded = ['id'];
}
