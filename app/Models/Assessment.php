<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Assessment extends Model
{
    // Nama tabel (singular name in the database)
    protected $table = 'assessment';
    
    // Primary key
    protected $primaryKey = 'assessment_id';

    // Auto-increment
    public $incrementing = true;
    protected $keyType    = 'int';

    // Timestamps
    public $timestamps = true;

    // Mass-assignable fields
    protected $fillable = [
        'instansi',
        'user_id',
        'kode_assessment',
    ];

    /**
     * Relasi many-to-many ke User (jika diperlukan)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Relasi one-to-many: creator (owner) of this assessment
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ————— Relasi Design Factor 1–10 —————

    public function df1(): HasMany
    {
        return $this->hasMany(DesignFactor1::class, 'assessment_id', 'assessment_id');
    }
    public function df2(): HasMany
    {
        return $this->hasMany(DesignFactor2::class, 'assessment_id', 'assessment_id');
    }
    public function df3(): HasMany
    {
        return $this->hasMany(DesignFactor3a::class, 'assessment_id', 'assessment_id');
    }
    public function df4(): HasMany
    {
        return $this->hasMany(DesignFactor4::class, 'assessment_id', 'assessment_id');
    }
    public function df5(): HasMany
    {
        return $this->hasMany(DesignFactor5::class, 'assessment_id', 'assessment_id');
    }
    public function df6(): HasMany
    {
        return $this->hasMany(DesignFactor6::class, 'assessment_id', 'assessment_id');
    }
    public function df7(): HasMany
    {
        return $this->hasMany(DesignFactor7::class, 'assessment_id', 'assessment_id');
    }
    public function df8(): HasMany
    {
        return $this->hasMany(DesignFactor8::class, 'assessment_id', 'assessment_id');
    }
    public function df9(): HasMany
    {
        return $this->hasMany(DesignFactor9::class, 'assessment_id', 'assessment_id');
    }
    public function df10(): HasMany
    {
        return $this->hasMany(DesignFactor10::class, 'assessment_id', 'assessment_id');
    }

    // ————— Relasi Score untuk DF1–DF10 —————

    public function df1Scores(): HasMany
    {
        return $this->hasMany(DesignFactor1Score::class, 'assessment_id', 'assessment_id');
    }
    public function df2Scores(): HasMany
    {
        return $this->hasMany(DesignFactor2Score::class, 'assessment_id', 'assessment_id');
    }
    public function df3Scores(): HasMany
    {
        return $this->hasMany(DesignFactor3Score::class, 'assessment_id', 'assessment_id');
    }
    public function df4Scores(): HasMany
    {
        return $this->hasMany(DesignFactor4Score::class, 'assessment_id', 'assessment_id');
    }
    public function df5Scores(): HasMany
    {
        return $this->hasMany(DesignFactor5Score::class, 'assessment_id', 'assessment_id');
    }
    public function df6Scores(): HasMany
    {
        return $this->hasMany(DesignFactor6Score::class, 'assessment_id', 'assessment_id');
    }
    public function df7Scores(): HasMany
    {
        return $this->hasMany(DesignFactor7Score::class, 'assessment_id', 'assessment_id');
    }
    public function df8Scores(): HasMany
    {
        return $this->hasMany(DesignFactor8Score::class, 'assessment_id', 'assessment_id');
    }
    public function df9Scores(): HasMany
    {
        return $this->hasMany(DesignFactor9Score::class, 'assessment_id', 'assessment_id');
    }
    public function df10Scores(): HasMany
    {
        return $this->hasMany(DesignFactor10Score::class, 'assessment_id', 'assessment_id');
    }

    // ————— Relasi Relative Importance untuk DF1–DF10 —————

    public function df1RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor1RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df2RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor2RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df3RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor3RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df4RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor4RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df5RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor5RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df6RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor6RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df7RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor7RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df8RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor8RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df9RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor9RelativeImportance::class, 'assessment_id', 'assessment_id');
    }
    public function df10RelativeImportances(): HasMany
    {
        return $this->hasMany(DesignFactor10RelativeImportance::class, 'assessment_id', 'assessment_id');
    }

    /**
     * Ambil assessment dengan kode 'guest'
     */
    public static function getGuestAssessment(): ?self
    {
        return self::where('kode_assessment', 'guest')->first();
    }
}
