<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrsAccessProfilePermission extends Model
{
    protected $table = 'trs_access_profile_permissions';

    protected $fillable = [
        'access_profile_id',
        'permission_name',
    ];

    public function accessProfile(): BelongsTo
    {
        return $this->belongsTo(MstAccessProfile::class, 'access_profile_id', 'access_profile_id');
    }
}
