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
    ->leftJoin('pengumpulan_tugas', function ($join) {
        $join->on('materi_tugas.id', '=', 'pengumpulan_tugas.materi_tugas_id')
             ->where('pengumpulan_tugas.siswa_id', '=', Auth::id());
    })
    ->leftJoin('izin_susulan', function ($join) {
        $join->on('materi_tugas.id', '=', 'izin_susulan.materi_tugas_id')
             ->where('izin_susulan.siswa_id', '=', Auth::id());
    })
    ->where('materi_tugas.mapel_id', $mapelId)
    ->select(
        'materi_tugas.*',
        'pengumpulan_tugas.id as pengumpulan_id',
        'pengumpulan_tugas.link_jawaban',
        'pengumpulan_tugas.nilai',
        'pengumpulan_tugas.catatan_guru',
        'pengumpulan_tugas.updated_at as waktu_kumpul',
        'izin_susulan.id as izin_susulan_id'
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

        $tugas = DB::table('materi_tugas')->where('id', $tugasId)->firstOrFail();

        // Cek apakah deadline sudah lewat
        $lewatDeadline = $tugas->deadline && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($tugas->deadline));
        
        if ($lewatDeadline) {
            $punyaIzin = DB::table('izin_susulan')
            ->where('materi_tugas_id', $tugasId)
            ->where('siswa_id', Auth::id())
            ->exists();

        if (!$punyaIzin) {
            return back()->with('error', 'Waktu pengumpulan tugas sudah habis. Silakan temui guru pengampu untuk meminta izin susulan.');
        }
    }

    DB::table('pengumpulan_tugas')->updateOrInsert(
        ['materi_tugas_id' => $tugasId, 'siswa_id' => Auth::id()],
        [
            'link_jawaban' => $request->link_jawaban,
            'updated_at'   => now(),
            'created_at'   => now(),
        ]
    );

    // Cabut izin susulan setelah digunakan agar siswa tidak bisa mengubah jawaban lagi lewat deadline
    DB::table('izin_susulan')
        ->where('materi_tugas_id', $tugasId)
        ->where('siswa_id', Auth::id())
        ->delete();

    return back()->with('success', 'Jawaban tugas berhasil dikirim.');
}
}