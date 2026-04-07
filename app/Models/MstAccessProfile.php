<?php

namespace App\Models;

use App\Enums\UserAccessProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstAccessProfile extends Model
{
    protected $table = 'mst_access_profiles';

    protected $primaryKey = 'access_profile_id';

    protected $fillable = [
        'access_profile_key',
        'access_profile_label',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'access_profile_key';
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(TrsAccessProfilePermission::class, 'access_profile_id', 'access_profile_id');
    }

    public function enum(): ?UserAccessProfile
    {
        return UserAccessProfile::tryFrom((string) $this->access_profile_key);
    }
}
