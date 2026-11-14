<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiswaKelasController extends Controller
{
    // 1. Halaman Ruang Kelas Siswa (Daftar Materi, Tugas, & Form Pengumpulan)
    public function show($mapelId)
    {
        $siswaId = Auth::id();

        // Cek kelas siswa saat ini
        $kelasSiswa = DB::table('kelas_siswa')
            ->where('user_id', $siswaId)
            ->first();

        if (!$kelasSiswa) {
            abort(403, 'Anda belum terdaftar di kelas mana pun.');
        }

        // Ambil detail mapel dan pastikan mapel tersebut memang untuk kelas siswa ini
        $mapel = DB::table('mapels')
            ->join('units', 'mapels.unit_id', '=', 'units.id')
            ->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
            ->join('users', 'mapels.guru_id', '=', 'users.id')
            ->where('mapels.id', $mapelId)
            ->where('mapels.kelas_id', $kelasSiswa->kelas_id)
            ->select(
                'mapels.*',
                'units.singkatan as unit',
                'kelas.nama_kelas',
                'users.name as nama_guru'
            )
            ->first();

        if (!$mapel) {
            abort(403, 'Anda tidak memiliki akses ke mata pelajaran ini.');
        }

        // Ambil daftar materi & tugas beserta status pengumpulan milik siswa ini
        $kontens = DB::table('materi_tugas')
            ->leftJoin('pengumpulan_tugas', function ($join) use ($siswaId) {
                $join->on('materi_tugas.id', '=', 'pengumpulan_tugas.materi_tugas_id')
                     ->where('pengumpulan_tugas.siswa_id', '=', $siswaId);
            })
            ->where('materi_tugas.mapel_id', $mapelId)
            ->select(
                'materi_tugas.*',
                'pengumpulan_tugas.id as pengumpulan_id',
                'pengumpulan_tugas.link_jawaban',
                'pengumpulan_tugas.nilai',
                'pengumpulan_tugas.catatan_guru',
                'pengumpulan_tugas.updated_at as waktu_kumpul'
            )
            ->orderBy('materi_tugas.created_at', 'desc')
            ->get();

        return view('siswa.kelas_show', compact('mapel', 'kontens'));
    }

    // 2. Kirim atau Perbarui Jawaban Tugas Siswa
    public function kumpulTugas(Request $request, $tugasId)
    {
        $request->validate([
            'link_jawaban' => 'required|url|max:500',
        ]);

        $siswaId = Auth::id();

        // Cek apakah siswa sudah pernah mengumpulkan sebelumnya
        $existing = DB::table('pengumpulan_tugas')
            ->where('materi_tugas_id', $tugasId)
            ->where('siswa_id', $siswaId)
            ->first();

        if ($existing) {
            // Cegah edit jika sudah dinilai oleh guru
            if ($existing->nilai !== null) {
                return back()->withErrors(['link_jawaban' => 'Tugas yang sudah dinilai guru tidak dapat diubah lagi.']);
            }

            DB::table('pengumpulan_tugas')
                ->where('id', $existing->id)
                ->update([
                    'link_jawaban' => $request->link_jawaban,
                    'updated_at' => now(),
                ]);

            return back()->with('success', 'Link jawaban tugas berhasil diperbarui.');
        }

        DB::table('pengumpulan_tugas')->insert([
            'materi_tugas_id' => $tugasId,
            'siswa_id' => $siswaId,
            'link_jawaban' => $request->link_jawaban,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Jawaban tugas berhasil dikumpulkan!');
    }
}