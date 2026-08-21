<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaranKritik extends Model
{
    protected $table = 'saran_kritik';
    protected $primaryKey = 'id_saran';
    public $timestamps = false;

    protected $fillable = [
        'id_survei',
        'saran',
        'status',
    ];

    public function survei()
    {
        // return $this->belongsTo(Survei::class, 'id_survei', 'id_survei');
    }
}