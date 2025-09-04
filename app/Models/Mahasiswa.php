<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'id_mhs',
        'fak_id',
        'jrs_id',
        'pk_id',
        'mn_id',
        'kode_fak',
        'kode_jrs',
        'kode_pk',
        'kode_mn',
        'kode_pa',
        'kurikulum',
        'no_sel',
        'nim',
        'no_transkrip',
        'no_pin',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'gender',
        'agama',
        'marital',
        'no_ktp',
        'alamat_surat1',
        'alamat_surat2',
        'rt_rw_surat',
        'kota_surat',
        'kode_pos_surat',
        'telepon',
        'hp1',
        'hp2',
        'email',
        'tinggal',
        'nama_ayah',
        'nama_ibu',
        'kerja_ayah',
        'kerja_ibu',
        'didik_ayah',
        'didik_ibu',
        'nik_ayah',
        'nik_ibu',
        'tanggal_lahir_ayah',
        'tanggal_lahir_ibu',
        'id_didik_ayah',
        'id_didik_ibu',
        'id_penghasilan_ayah',
        'id_penghasilan_ibu',
        'id_kerja_ayah',
        'id_kerja_ibu',
        'id_npwp_mhs',
        'alamat_ortu',
        'kota_ortu',
        'kode_pos_ortu',
        'telp_ortu',
        'hp_ortu',
        'nama_sekolah',
        'alamat_sekolah',
        'jenis_sekolah',
        'perusahaan',
        'alamat_perusahaan',
        'kota_perusahaan',
        'kode_pos_perusahaan',
        'telp_perusahaan',
        'fax_perusahaan',
        'total_tagihan',
        'nama_fak',
        'nama_jrs',
        'jenjang',
        'nama_jjg',
        'kd_jen',
        'kd_pst',
        'batas_studi',
        'nama_pk',
        'nama_mn',
        'group_mhs',
        'ipk',
        'foto',
        'create_date',
        'mod_date',
    ];

    protected $dates = [
        'tanggal_lahir',
        'tanggal_lahir_ayah',
        'tanggal_lahir_ibu',
        'create_date',
        'mod_date',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'kode_jrs', 'kode_jrs');
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class, 'nim', 'nim');
    }
}
