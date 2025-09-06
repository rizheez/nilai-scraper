<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $guarded = [];

    protected $dates = [
        'tgl_lahir',
        'tanggal_lahir_ayah',
        'tanggal_lahir_ibu',
        'createdate',
        'moddate',
        'tglhrmsmhs',
        'tgl_masuk',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'kodejrs', 'kode_jrs');
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class, 'nim', 'nim');
    }
}
