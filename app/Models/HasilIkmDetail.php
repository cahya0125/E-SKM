<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilIkmDetail extends Model
{
    protected $fillable = [
        'unsur_pelayanan_id',
        'hasil_ikm_id',
        'jumlah_responden',
        'nilai_rata_rata',
        'bobot_nilai',
        'nrr_tertimbang',
        'mutu_unsur',
    ];
}
