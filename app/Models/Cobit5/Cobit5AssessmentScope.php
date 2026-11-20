<?php

namespace App\Models\Cobit5;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cobit5AssessmentScope extends Model
{
    protected $table = 'trs_cobit5_assessment_scopes';

    protected $fillable = [
        'organization_id',
        'user_id',
        'scope_data',
    ];

    protected $casts = [
        'scope_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
