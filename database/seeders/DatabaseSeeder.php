<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Unit Sekolah
        $mtsId = DB::table('units')->insertGetId([
            'nama_unit' => 'Madrasah Tsanawiyah (MTs)',
            'singkatan' => 'MTs',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $maId = DB::table('units')->insertGetId([
            'nama_unit' => 'Madrasah Aliyah (MA)',
            'singkatan' => 'MA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Akun Admin Yayasan
        DB::table('users')->insert([
            'unit_id' => null,
            'nomor_induk' => 'admin',
            'name' => 'Operator Yayasan',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Akun Guru (Kamu: Mengajar di MTs & MA)
        $guruId = DB::table('users')->insertGetId([
            'unit_id' => null,
            'nomor_induk' => '2207011',
            'name' => 'Ghani Al-Zyad, S.Kom, ITS',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Kelas di MTs & MA (Misal: kamu juga menjadi Wali Kelas di X-IPA-1 MA)
        $kelasMtsId = DB::table('kelas')->insertGetId([
            'unit_id' => $mtsId,
            'wali_kelas_id' => null,
            'nama_kelas' => 'VII-A',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kelasMaId = DB::table('kelas')->insertGetId([
            'unit_id' => $maId,
            'wali_kelas_id' => $guruId, // Ditunjuk sebagai Wali Kelas
            'nama_kelas' => 'X-IPA-1',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Siswa MTs
        $siswaMtsId = DB::table('users')->insertGetId([
            'unit_id' => $mtsId,
            'nomor_induk' => '1001',
            'name' => 'Ade Saripudin',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kelas_siswa')->insert([
            'kelas_id' => $kelasMtsId,
            'user_id' => $siswaMtsId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Siswa MA
        $siswaMaId = DB::table('users')->insertGetId([
            'unit_id' => $maId,
            'nomor_induk' => '2001',
            'name' => 'Ilham Zuliansyah',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kelas_siswa')->insert([
            'kelas_id' => $kelasMaId,
            'user_id' => $siswaMaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Mata Pelajaran (Koding & KA serta Matematika)
        $mapelKodingMaId = DB::table('mapels')->insertGetId([
            'unit_id' => $maId,
            'kelas_id' => $kelasMaId,
            'guru_id' => $guruId,
            'nama_mapel' => 'Koding dan Kecerdasan Artifisial',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mapels')->insert([
            [
                'unit_id' => $maId,
                'kelas_id' => $kelasMaId,
                'guru_id' => $guruId,
                'nama_mapel' => 'Matematika',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_id' => $mtsId,
                'kelas_id' => $kelasMtsId,
                'guru_id' => $guruId,
                'nama_mapel' => 'Koding dan Kecerdasan Artifisial',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 8. Contoh 1 Materi & 1 Tugas untuk menguji Dashboard Siswa & Guru
        DB::table('materi_tugas')->insert([
            [
                'mapel_id' => $mapelKodingMaId,
                'judul' => 'Pengenalan Algoritma & Logika Pemrograman',
                'tipe' => 'materi',
                'deskripsi' => 'Pelajari modul dasar algoritma sebelum mengerjakan latihan.',
                'link_lampiran' => 'https://drive.google.com',
                'deadline' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mapel_id' => $mapelKodingMaId,
                'judul' => 'Tugas 1: Membuat Flowchart Sederhana',
                'tipe' => 'tugas',
                'deskripsi' => 'Buat flowchart menentukan bilangan ganjil/genap di buku tulis, foto, lalu unggah link Google Drive-nya.',
                'link_lampiran' => null,
                'deadline' => now()->addDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}