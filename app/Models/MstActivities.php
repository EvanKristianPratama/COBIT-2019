<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstActivities extends Model
{
    use HasFactory;

    protected $table = 'mst_activities';

    protected $primaryKey = 'activity_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'activity_id',
        'practice_id',
        'description',
        'capability_lvl',
        // 'objective_purpose',
    ];

    public function practice()
    {
        return $this->belongsTo(MstPractice::class, 'practice_id');
    }

    /**
     * Get all evaluations for this activity
     */
    public function evaluations()
    {
        return $this->hasMany(TrsActivityeval::class, 'activity_id', 'activity_id');
    }
}
