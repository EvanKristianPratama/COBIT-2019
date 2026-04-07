<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetMaturity extends Model
{
    use HasFactory;

    protected $table = 'target_maturities';
    
    // Explicitly disabling timestamps if user only wants simple storage, 
    // but plan said "timestamps", so we keep default true.
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'organization_id',
        'tahun',
        'organisasi',
        'target_maturity'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(MstOrganization::class, 'organization_id', 'organization_id');
    }

    public function getOrganisasiAttribute($value): ?string
    {
        if ($this->relationLoaded('organization')) {
            return $this->organization?->organization_name ?? $value;
        }

        $organizationId = $this->getRawOriginal('organization_id');

        if ($organizationId) {
            return $this->organization()->value('organization_name') ?? $value;
        }

        return $value;
    }
}
