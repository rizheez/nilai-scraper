<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('jid');
            $table->string('nama_mk');
            $table->string('kelas');
            $table->string('nama_dosen');
            $table->string('cetak');
            $table->text('info_mk');
            $table->string('kode_jrs');
            $table->string('kode_mk');
            $table->string('kode_pk');
            $table->string('smtthnakd');
            $table->string('nama_jrs');
            $table->timestamps();

            $table->index(['kode_jrs', 'smtthnakd']);
            $table->index('kode_mk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
