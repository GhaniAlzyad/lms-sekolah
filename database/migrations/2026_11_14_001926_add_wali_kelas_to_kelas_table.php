<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('wali_kelas_id')->nullable()->after('unit_id')->constrained('users')->nullOnDelete();
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil')->after('tahun_ajaran');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['wali_kelas_id']);
            $table->dropColumn(['wali_kelas_id', 'semester']);
        });
    }
};