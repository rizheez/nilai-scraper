<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'jid',
        'nama_mk',
        'kelas',
        'nama_dosen',
        'cetak',
        'info_mk',
        'kode_jrs',
        'kode_mk',
        'kode_pk',
        'smtthnakd',
        'nama_jrs',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'kode_jrs', 'kode_jrs');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'smtthnakd', 'smtthnakd');
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class, 'mata_kuliah_id');
    }

    public function bobot()
    {
        return $this->hasOne(Bobot::class, 'mata_kuliah_id');
    }
}
