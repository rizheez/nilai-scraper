<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bobot extends Model
{
    use HasFactory;

    protected $table = 'bobot';

    protected $fillable = [
        'mata_kuliah_id',
        'hadir',
        'projek',
        'quiz',
        'tugas',
        'uts',
        'uas',
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}
