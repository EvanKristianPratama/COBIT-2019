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
        'description',
    ];

    /**
     * The objectives that belong to this focus area.
     * Now using direct FK focus_area_id on mst_objective.
     */
    public function objectives()
    {
        return $this->hasMany(MstObjective::class, 'focus_area_id', 'id');
    }
}