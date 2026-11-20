<?php

namespace App\Models\Cobit5;

use Illuminate\Database\Eloquent\Model;

class Cobit5EnterpriseGoal extends Model
{
    protected $table = 'mst_cobit5_enterprise_goals';

    protected $fillable = [
        'code', 'bsc_dimension', 'title', 'order_no',
        'relation_benefits_realisation', 'relation_risk_optimisation', 'relation_resource_optimisation',
    ];

    public function itGoals()
    {
        return $this->belongsToMany(
            Cobit5ItGoal::class,
            'trs_cobit5_enterprise_it_goal_maps',
            'enterprise_goal_id',
            'it_goal_id'
        )->withPivot(['level', 'id'])->orderBy('mst_cobit5_it_goals.order_no');
    }
}
