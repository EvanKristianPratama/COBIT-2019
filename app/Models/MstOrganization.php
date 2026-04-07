<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstOrganization extends Model
{
    protected $table = 'mst_organization';

    protected $primaryKey = 'organization_id';

    protected $fillable = [
        'organization_name',
        'organization_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function userMappings(): HasMany
    {
        return $this->hasMany(TrsUserOrganization::class, 'organization_id', 'organization_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trs_userorganization', 'organization_id', 'user_id')
            ->withPivot(['is_primary', 'assigned_by'])
            ->withTimestamps();
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'organization_id', 'organization_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(MstEval::class, 'organization_id', 'organization_id');
    }
}
