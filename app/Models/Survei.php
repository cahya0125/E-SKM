<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survei extends Model
{
    protected $table = 'surveis';

    protected $fillable = [
        'responden_id',
        'jenis_layanan',
    ];

    public function responden()
    {
        return $this->belongsTo(Respondens::class, 'responden_id');
    }

    public function saranKritik()
    {
        return $this->hasMany(SaranKritik::class, 'survei_id');
    }
}