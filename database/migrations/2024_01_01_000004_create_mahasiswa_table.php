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
            $table->string('idmhs', 50)->unique();
            $table->string('fakid', 20)->nullable();
            $table->string('jrsid', 20)->nullable();
            $table->string('pkid', 20)->nullable();
            $table->string('mnid', 20)->nullable();

            $table->string('kodefak', 20)->nullable();
            $table->string('kodejrs', 20)->nullable();
            $table->string('kodepk', 20)->nullable();
            $table->string('kodemn', 20)->nullable();
            $table->string('kodepa', 20)->nullable();

            $table->string('kurikulum', 50)->nullable();
            $table->string('nosel', 50)->nullable();
            $table->string('nim', 50)->nullable();
            $table->string('no_transkrip', 50)->nullable();
            $table->string('no_pin', 50)->nullable();

            $table->string('nama', 150)->nullable();
            $table->string('tem_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('marital', 20)->nullable();
            $table->string('no_ktp', 50)->nullable();

            $table->text('alm1_surat')->nullable();
            $table->text('alm2_surat')->nullable();
            $table->string('rtrw_surat', 20)->nullable();
            $table->string('kot_surat', 100)->nullable();
            $table->string('kdp_surat', 10)->nullable();

            $table->string('telepon', 50)->nullable();
            $table->string('hp1', 50)->nullable();
            $table->string('hp2', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('tinggal', 50)->nullable();

            $table->string('nama_ayah', 150)->nullable();
            $table->string('nama_ibu', 150)->nullable();
            $table->string('kerja_ayah', 100)->nullable();
            $table->string('kerja_ibu', 100)->nullable();
            $table->string('didik_ayah', 50)->nullable();
            $table->string('didik_ibu', 50)->nullable();
            $table->string('nik_ayah', 50)->nullable();
            $table->string('nik_ibu', 50)->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();

            $table->string('id_didik_ayah', 20)->nullable();
            $table->string('id_didik_ibu', 20)->nullable();
            $table->string('id_penghasilan_ayah', 20)->nullable();
            $table->string('id_penghasilan_ibu', 20)->nullable();
            $table->string('id_kerja_ayah', 20)->nullable();
            $table->string('id_kerja_ibu', 20)->nullable();
            $table->string('id_npwp_mhs', 50)->nullable();

            $table->text('alamat_ortu')->nullable();
            $table->string('kota_ortu', 100)->nullable();
            $table->string('kodepos_ortu', 10)->nullable();
            $table->string('telp_ortu', 50)->nullable();
            $table->string('hp_ortu', 50)->nullable();

            $table->string('nama_sklh', 150)->nullable();
            $table->text('alam_sklh')->nullable();
            $table->string('jj_sklh', 50)->nullable();

            $table->string('perusahaan', 150)->nullable();
            $table->text('alm_perush')->nullable();
            $table->string('kot_perush', 100)->nullable();
            $table->string('kdp_perush', 10)->nullable();
            $table->string('tlp_perush', 50)->nullable();
            $table->string('fax_perush', 50)->nullable();

            // Data MSMHS
            $table->string('kdptimsmhs', 20)->nullable();
            $table->string('kdjenmsmhs', 20)->nullable();
            $table->string('kdpstmsmhs', 20)->nullable();
            $table->string('nimhsmsmhs', 50)->nullable();
            $table->string('nmmhsmsmhs', 150)->nullable();
            $table->string('shiftmsmhs', 50)->nullable();
            $table->string('tplhrmsmhs', 100)->nullable();
            $table->date('tglhrmsmhs')->nullable();
            $table->string('kdjekmsmhs', 20)->nullable();
            $table->string('tahunmsmhs', 10)->nullable();
            $table->string('smawlmsmhs', 10)->nullable();
            $table->string('btstumsmhs', 50)->nullable();
            $table->string('assmamsmhs', 100)->nullable();
            $table->date('tgmskmsmhs')->nullable();
            $table->date('tgllsmsmhs')->nullable();
            $table->string('stmhsmsmhs', 50)->nullable();
            $table->string('stpidmsmhs', 50)->nullable();
            $table->string('sksdimsmhs', 50)->nullable();
            $table->string('asnimmsmhs', 50)->nullable();
            $table->string('asptimsmhs', 50)->nullable();
            $table->string('asjenmsmhs', 50)->nullable();
            $table->string('aspstmsmhs', 50)->nullable();

            $table->string('smthnlulus', 10)->nullable();
            $table->string('nosklulus', 50)->nullable();
            $table->string('id_perguruan_tinggi_asal', 50)->nullable();
            $table->string('nama_perguruan_tinggi_asal', 150)->nullable();
            $table->string('id_prodi_asal', 50)->nullable();
            $table->string('nama_program_studi_asal', 150)->nullable();

            $table->string('foto')->nullable();
            $table->string('asnmpti', 50)->nullable();
            $table->string('asnmpst', 50)->nullable();

            $table->integer('jummk')->nullable();
            $table->integer('jumsks')->nullable();
            $table->integer('jumutu')->nullable();
            $table->decimal('ipk', 4, 2)->nullable();
            $table->string('logika', 50)->nullable();

            $table->timestamp('createdate')->nullable();
            $table->timestamp('moddate')->nullable();

            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('jalan', 150)->nullable();
            $table->string('dusun', 150)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('kelurahan', 150)->nullable();

            $table->string('id_wilayah', 50)->nullable();
            $table->string('nama_wilayah', 150)->nullable();
            $table->integer('biaya_masuk')->nullable();
            $table->string('kd_daftar', 50)->nullable();

            $table->string('kode_agama', 20)->nullable();
            $table->string('nama_agama', 100)->nullable();
            $table->string('id_reg_pd', 50)->nullable();
            $table->string('id_mahasiswa', 50)->nullable();
            $table->string('id_jalur_masuk', 50)->nullable();
            $table->string('id_jns_tinggal', 50)->nullable();
            $table->string('id_jns_keluar', 50)->nullable();
            $table->string('id_jenj_didik', 50)->nullable();
            $table->string('id_jns_daftar', 50)->nullable();
            $table->string('id_alat_transport', 50)->nullable();
            $table->string('id_penghasilan', 50)->nullable();
            $table->string('id_pembiayaan', 50)->nullable();
            $table->string('id_kps', 50)->nullable();

            $table->decimal('total_tagihan', 12, 2)->nullable();

            $table->string('namafak', 150)->nullable();
            $table->string('namajrs', 150)->nullable();
            $table->string('jenjang', 50)->nullable();
            $table->string('nama_jjg', 150)->nullable();
            $table->string('kdjen', 20)->nullable();
            $table->string('kdpst', 20)->nullable();
            $table->string('batastudi', 20)->nullable();
            $table->string('namapk', 150)->nullable();
            $table->string('namamn', 150)->nullable();

            $table->string('group', 50)->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->string('status_mhs_ket', 50)->nullable();
            $table->string('temtgl_lahir', 100)->nullable();
            $table->integer('sks_total')->default(0);

            $table->timestamps();

            $table->index('nim');
            $table->index('kodejrs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
