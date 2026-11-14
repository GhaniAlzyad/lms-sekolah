<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('id')->constrained('units')->nullOnDelete();
            $table->string('nomor_induk')->unique()->after('unit_id'); // NISN untuk siswa, NUPTK/NIP untuk guru
            $table->enum('role', ['admin', 'guru', 'siswa'])->default('siswa')->after('password');
            $table->string('email')->nullable()->change(); // Siswa MTs/MA sering belum punya email aktif
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'nomor_induk', 'role']);
        });
    }
};