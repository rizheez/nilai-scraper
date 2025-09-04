<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->onDelete('cascade');
            $table->string('nim');
            $table->string('nama');
            $table->string('nil_angka')->nullable();
            $table->string('nil_huruf')->nullable();
            $table->string('hadir')->nullable();
            $table->string('projek')->nullable();
            $table->string('quiz')->nullable();
            $table->string('tugas')->nullable();
            $table->string('uts')->nullable();
            $table->string('uas')->nullable();
            $table->timestamps();

            $table->index(['mata_kuliah_id', 'nim']);
            $table->index('nim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};
