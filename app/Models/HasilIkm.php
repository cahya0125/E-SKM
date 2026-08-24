<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilIkm extends Model
{
    protected $fillable = [
        'survei_id',
        'nilai_skm',
        'nilai_ikm',
        'mutu_pelayanan',
        'kinerja_pelayanan',
    ];

    public function survei()
    {
        return $this->belongsTo(Survei::class);
    }
}
