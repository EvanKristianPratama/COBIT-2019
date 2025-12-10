<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetCapability extends Model
{
    use HasFactory;

    protected $table = 'mst_targetcap';
    protected $primaryKey = 'target_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'organisasi', 'tahun',
        'EDM01','EDM02','EDM03','EDM04','EDM05',
        'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
        'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
        'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
        'MEA01','MEA02','MEA03','MEA04',
    ];
}
