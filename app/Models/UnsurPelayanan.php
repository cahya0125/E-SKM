<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnsurPelayanan extends Model
{
    protected $fillable = [
        'nama_unsur',
        'pertanyaan',
        'status',
    ];
}