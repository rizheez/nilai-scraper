<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';

    protected $fillable = [
        'jrs_id',
        'kode_jrs',
        'nama_jrs',
    ];

    public function mataKuliah()
    {
        return $this->hasMany(MataKuliah::class, 'kode_jrs', 'kode_jrs');
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'kode_jrs', 'kode_jrs');
    }
}
