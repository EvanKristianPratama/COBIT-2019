<?php

namespace App\Models;

use App\Enums\UserAccessProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccessAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'access_profile',
        'assigned_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function accessProfileEnum(): ?UserAccessProfile
    {
        return UserAccessProfile::tryFrom((string) $this->access_profile);
    }
}
