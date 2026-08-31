<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respondens extends Model
{
    protected $table = 'respondens';

    protected $fillable = ['nama', 'jenis_kelamin', 'usia', 'pendidikan', 'pekerjaan', 'no_hp'];

    public function surveis()
    {
        return $this->hasMany(Survei::class, 'responden_id');
    }
}