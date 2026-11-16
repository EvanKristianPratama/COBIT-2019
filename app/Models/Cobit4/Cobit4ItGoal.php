<?php

namespace App\Models\Cobit4;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cobit4ItGoal extends Model
{
    public const CRITERIA = [
        'effectiveness' => 'Effectiveness',
        'efficiency' => 'Efficiency',
        'confidentiality' => 'Confidentiality',
        'integrity' => 'Integrity',
        'availability' => 'Availability',
        'compliance' => 'Compliance',
        'reliability' => 'Reliability',
    ];

    protected $table = 'mst_cobit4_it_goals';

    protected $fillable = [
        'code', 'title', 'order_no',
        'criteria_effectiveness', 'criteria_efficiency', 'criteria_confidentiality',
        'criteria_integrity', 'criteria_availability', 'criteria_compliance', 'criteria_reliability',
    ];

    public function businessGoals(): BelongsToMany
    {
        return $this->belongsToMany(
            Cobit4BusinessGoal::class,
            'trs_cobit4_business_it_goal_maps',
            'it_goal_id',
            'business_goal_id'
        );
    }

    public function processMaps(): HasMany
    {
        return $this->hasMany(Cobit4ItGoalProcessMap::class, 'it_goal_id')->orderBy('order_no');
    }
}
