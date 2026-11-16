<?php

namespace App\Models\Cobit4;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cobit4BusinessGoal extends Model
{
    protected $table = 'mst_cobit4_business_goals';

    protected $fillable = ['code', 'perspective', 'title', 'order_no'];

    public function itGoals(): BelongsToMany
    {
        return $this->belongsToMany(
            Cobit4ItGoal::class,
            'trs_cobit4_business_it_goal_maps',
            'business_goal_id',
            'it_goal_id'
        )->withPivot('order_no')->orderBy('trs_cobit4_business_it_goal_maps.order_no');
    }
}
