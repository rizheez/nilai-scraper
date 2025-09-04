<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'semester';

    protected $fillable = [
        'keterangan',
        'smtthnakd',
    ];

    public function mataKuliah()
    {
        return $this->hasMany(MataKuliah::class, 'smtthnakd', 'smtthnakd');
    }
}
