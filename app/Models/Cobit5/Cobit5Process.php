<?php

namespace App\Models\Cobit5;

use Illuminate\Database\Eloquent\Model;

class Cobit5Process extends Model
{
    protected $table = 'mst_cobit5_processes';

    protected $fillable = ['code', 'domain_code', 'domain_name', 'title', 'order_no'];
}
