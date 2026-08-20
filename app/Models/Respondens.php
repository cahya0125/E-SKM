<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respondens extends Model
{
    protected $fillable = ['nama', 'jenis_kelamin', 'usia', 'pendidikan', 'pekerjaan', 'no_hp'];
}
