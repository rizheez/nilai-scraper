<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('id_mhs')->nullable();
            $table->string('fak_id')->nullable();
            $table->string('jrs_id')->nullable();
            $table->string('pk_id')->nullable();
            $table->string('mn_id')->nullable();
            $table->string('kode_fak')->nullable();
            $table->string('kode_jrs');
            $table->string('kode_pk')->nullable();
            $table->string('kode_mn')->nullable();
            $table->string('kode_pa')->nullable();
            $table->string('kurikulum')->nullable();
            $table->string('no_sel')->nullable();
            $table->string('nim')->unique();
            $table->string('no_transkrip')->nullable();
            $table->string('no_pin')->nullable();
            $table->string('nama');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('gender')->nullable();
            $table->string('agama')->nullable();
            $table->string('marital')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('alamat_surat1')->nullable();
            $table->string('alamat_surat2')->nullable();
            $table->string('rt_rw_surat')->nullable();
            $table->string('kota_surat')->nullable();
            $table->string('kode_pos_surat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('hp1')->nullable();
            $table->string('hp2')->nullable();
            $table->string('email')->nullable();
            $table->text('tinggal')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('kerja_ayah')->nullable();
            $table->string('kerja_ibu')->nullable();
            $table->string('didik_ayah')->nullable();
            $table->string('didik_ibu')->nullable();
            $table->string('nik_ayah')->nullable();
            $table->string('nik_ibu')->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('id_didik_ayah')->nullable();
            $table->string('id_didik_ibu')->nullable();
            $table->string('id_penghasilan_ayah')->nullable();
            $table->string('id_penghasilan_ibu')->nullable();
            $table->string('id_kerja_ayah')->nullable();
            $table->string('id_kerja_ibu')->nullable();
            $table->string('id_npwp_mhs')->nullable();
            $table->string('alamat_ortu')->nullable();
            $table->string('kota_ortu')->nullable();
            $table->string('kode_pos_ortu')->nullable();
            $table->string('telp_ortu')->nullable();
            $table->string('hp_ortu')->nullable();
            $table->string('nama_sekolah')->nullable();
            $table->text('alamat_sekolah')->nullable();
            $table->string('jenis_sekolah')->nullable();
            $table->string('perusahaan')->nullable();
            $table->text('alamat_perusahaan')->nullable();
            $table->string('kota_perusahaan')->nullable();
            $table->string('kode_pos_perusahaan')->nullable();
            $table->string('telp_perusahaan')->nullable();
            $table->string('fax_perusahaan')->nullable();
            $table->string('total_tagihan')->nullable();
            $table->string('nama_fak')->nullable();
            $table->string('nama_jrs')->nullable();
            $table->string('jenjang')->nullable();
            $table->string('nama_jjg')->nullable();
            $table->string('kd_jen')->nullable();
            $table->string('kd_pst')->nullable();
            $table->string('batas_studi')->nullable();
            $table->string('nama_pk')->nullable();
            $table->string('nama_mn')->nullable();
            $table->string('group_mhs')->nullable();
            $table->string('ipk')->nullable();
            $table->string('foto')->nullable();
            $table->datetime('create_date')->nullable();
            $table->datetime('mod_date')->nullable();
            $table->timestamps();

            $table->index('nim');
            $table->index('kode_jrs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
