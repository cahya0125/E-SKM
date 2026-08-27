<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanSurvei extends Model
{
    protected $table = 'jawaban_surveis';

    protected $fillable = ['survei_id', 'unsur_pelayanan_id', 'nilai'];

    public function survei()
    {
        return $this->belongsTo(Survei::class, 'survei_id');
    }

    public function unsurPelayanan()
    {
        return $this->belongsTo(UnsurPelayanan::class, 'unsur_pelayanan_id');
    }
}