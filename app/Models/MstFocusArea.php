<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MstFocusArea extends Model
{
    protected $table = 'mst_focusarea';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'version',
        'description',
    ];

    /**
     * Scope query to a specific version.
     */
    public function scopeVersion($query, string $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Scope query for COBIT 2019.
     */
    public function scopeCobit2019($query)
    {
        return $query->where(function ($q) {
            $q->where('version', '2019')
              ->orWhereNull('version');
        });
    }

    /**
     * Scope query for COBIT 5.
     */
    public function scopeCobit5($query)
    {
        return $query->where('version', '5');
    }

    /**
     * Scope query for COBIT 4.1.
     */
    public function scopeCobit4($query)
    {
        return $query->where('version', '4.1');
    }

    /**
     * Human-friendly framework label.
     */
    public function getFrameworkLabelAttribute(): string
    {
        $v = trim((string) ($this->version ?? '2019'));
        if ($v === '2019') return 'COBIT 2019';
        if ($v === '5') return 'COBIT 5';
        if ($v === '4.1' || $v === '4') return 'COBIT 4.1';
        return 'COBIT ' . $v;
    }

    /**
     * The objectives that belong to this focus area.
     * Now using direct FK focus_area_id on mst_objective.
     */
    public function objectives()
    {
        return $this->hasMany(MstObjective::class, 'focus_area_id', 'id');
    }
}