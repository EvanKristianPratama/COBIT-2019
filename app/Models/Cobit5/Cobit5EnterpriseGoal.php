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
}
