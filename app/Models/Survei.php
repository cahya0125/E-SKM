<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survei extends Model
{
    protected $table = 'survei';
    protected $primaryKey = 'id_survei';
    public $timestamps = false;

    protected $fillable = [
        'id_responden',
        'id_jenis_layanan',
        'id_hasil_ikm',
        'tanggal_isi',
    ];

    protected $casts = [
        'tanggal_isi' => 'date',
    ];

    public function responden()
    {
        return $this->belongsTo(Respondens::class, 'id_responden', 'id_responden');
    }

    public function jenisLayanan()
    {
        return $this->belongsTo(JenisLayanan::class, 'id_jenis_layanan', 'id_jenis_layanan');
    }

    public function saranKritik()
    {
        return $this->hasMany(SaranKritik::class, 'id_survei', 'id_survei');
    }
}