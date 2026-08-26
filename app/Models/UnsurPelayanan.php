<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnsurPelayanan extends Model
{
    protected $table = 'unsur_pelayanans';

    protected $fillable = ['nama_unsur', 'pertanyaan', 'opsi_jawaban', 'status'];

    protected $casts = [
        'opsi_jawaban' => 'array',
    ];

    public function jawabanSurveis()
    {
        return $this->hasMany(JawabanSurvei::class, 'unsur_pelayanan_id');
    }
}