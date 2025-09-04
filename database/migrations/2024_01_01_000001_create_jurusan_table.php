<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurusan', function (Blueprint $table) {
            $table->id();
            $table->string('jrs_id');
            $table->string('kode_jrs')->unique();
            $table->string('nama_jrs');
            $table->timestamps();

            $table->index('kode_jrs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};
