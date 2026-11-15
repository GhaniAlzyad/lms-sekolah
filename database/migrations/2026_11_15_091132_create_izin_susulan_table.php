<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('izin_susulan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('materi_tugas_id')->constrained('materi_tugas')->onDelete('cascade');
        $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
        $table->timestamps();

        $table->unique(['materi_tugas_id', 'siswa_id']);
    });
}
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_susulan');
    }
};
