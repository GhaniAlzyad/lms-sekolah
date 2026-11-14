<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materi_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->string('judul');
            $table->enum('tipe', ['materi', 'tugas'])->default('materi');
            $table->text('deskripsi')->nullable();
            $table->string('link_lampiran')->nullable(); // Link Google Drive / YouTube
            $table->dateTime('deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi_tugas');
    }
};