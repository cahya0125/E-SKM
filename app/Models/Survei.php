<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survei extends Model
{
    protected $table = 'surveis';

    // Sesuaikan dengan kolom asli: hanya responden_id dan jenis_layanan
    protected $fillable = ['responden_id', 'jenis_layanan'];

    public function responden()
    {
        return $this->belongsTo(Respondens::class, 'responden_id');
    }

    public function jawabanSurveis()
    {
        return $this->hasMany(JawabanSurvei::class, 'survei_id');
    }

    public function saranKritiks()
    {
        return $this->hasMany(SaranKritik::class, 'survei_id');
    }
}