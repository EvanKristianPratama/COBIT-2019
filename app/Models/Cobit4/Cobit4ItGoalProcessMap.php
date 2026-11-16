<?php

namespace App\Models\Cobit4;

use Illuminate\Database\Eloquent\Model;

class Cobit4ItGoalProcessMap extends Model
{
    protected $table = 'trs_cobit4_it_goal_process_maps';

    protected $fillable = ['it_goal_id', 'process_code', 'order_no'];
}
