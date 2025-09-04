<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester', function (Blueprint $table) {
            $table->id();
            $table->string('keterangan');
            $table->string('smtthnakd')->unique();
            $table->timestamps();

            $table->index('smtthnakd');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester');
    }
};
