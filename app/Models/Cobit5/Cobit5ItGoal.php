<?php

namespace App\Models\Cobit5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cobit5ItGoal extends Model
{
    protected $table = 'mst_cobit5_it_goals';

    protected $fillable = ['code', 'bsc_dimension', 'title', 'order_no'];

    public function enterpriseGoals(): BelongsToMany
    {
        return $this->belongsToMany(
            Cobit5EnterpriseGoal::class,
            'trs_cobit5_enterprise_it_goal_maps',
            'it_goal_id',
            'enterprise_goal_id'
        )->withPivot('level');
    }

    public function processes(): BelongsToMany
    {
        return $this->belongsToMany(
            Cobit5Process::class,
            'trs_cobit5_it_goal_process_maps',
            'it_goal_id',
            'process_id'
        )->withPivot('level');
    }
}
