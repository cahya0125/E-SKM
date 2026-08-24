<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaranKritik extends Model
{
    protected $table = 'saran_kritiks';

    protected $fillable = [
        'survei_id',
        'saran',
        'status',
    ];

    public function survei()
    {
        return $this->belongsTo(Survei::class, 'survei_id');
    }
}