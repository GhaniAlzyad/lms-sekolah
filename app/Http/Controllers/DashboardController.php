<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function admin()
    {
        $stats = [
            'guru' => DB::table('users')->where('role', 'guru')->count(),
            'siswa_mts' => DB::table('users')->where('role', 'siswa')->where('unit_id', 1)->count(),
            'siswa_ma' => DB::table('users')->where('role', 'siswa')->where('unit_id', 2)->count(),
            'kelas_mts' => DB::table('kelas')->where('unit_id', 1)->count(),
            'kelas_ma' => DB::table('kelas')->where('unit_id', 2)->count(),
            'mapel' => DB::table('mapels')->count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    public function guru()
    {
        $guruId = Auth::id();

        // 1. Daftar Mapel yang diampu di MTs & MA
        $mapels = DB::table('mapels')
            ->join('units', 'mapels.unit_id', '=', 'units.id')
            ->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
            ->where('mapels.guru_id', $guruId)
            ->select('mapels.*', 'units.singkatan as unit', 'kelas.nama_kelas')
            ->orderBy('units.id')
            ->get();

        // 2. Cek apakah guru ini juga merupakan Wali Kelas
        $kelasPerwalian = DB::table('kelas')
            ->join('units', 'kelas.unit_id', '=', 'units.id')
            ->where('kelas.wali_kelas_id', $guruId)
            ->select('kelas.*', 'units.singkatan as unit')
            ->first();

        $siswaPerwalian = [];
        if ($kelasPerwalian) {
            $siswaPerwalian = DB::table('kelas_siswa')
                ->join('users', 'kelas_siswa.user_id', '=', 'users.id')
                ->where('kelas_siswa.kelas_id', $kelasPerwalian->id)
                ->select('users.id', 'users.nomor_induk', 'users.name')
                ->get();
        }

        // 3. Hitung tugas masuk yang belum diberi nilai
        $tugasBelumDinilai = DB::table('pengumpulan_tugas')
            ->join('materi_tugas', 'pengumpulan_tugas.materi_tugas_id', '=', 'materi_tugas.id')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('mapels.guru_id', $guruId)
            ->whereNull('pengumpulan_tugas.nilai')
            ->count();

        return view('dashboard.guru', compact('mapels', 'kelasPerwalian', 'siswaPerwalian', 'tugasBelumDinilai'));
    }

    public function siswa()
    {
        $siswaId = Auth::id();

        // 1. Info Kelas & Unit Siswa
        $kelasSiswa = DB::table('kelas_siswa')
            ->join('kelas', 'kelas_siswa.kelas_id', '=', 'kelas.id')
            ->join('units', 'kelas.unit_id', '=', 'units.id')
            ->where('kelas_siswa.user_id', $siswaId)
            ->select('kelas.*', 'units.singkatan as unit')
            ->first();

        $mapels = [];
        $tugasAktif = [];

        if ($kelasSiswa) {
            // 2. Daftar Mapel di Kelasnya
            $mapels = DB::table('mapels')
                ->join('users', 'mapels.guru_id', '=', 'users.id')
                ->where('mapels.kelas_id', $kelasSiswa->id)
                ->select('mapels.*', 'users.name as nama_guru')
                ->get();

            // 3. Daftar Tugas di Kelasnya + Status Pengumpulan Siswa
            $tugasAktif = DB::table('materi_tugas')
                ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
                ->leftJoin('pengumpulan_tugas', function ($join) use ($siswaId) {
                    $join->on('materi_tugas.id', '=', 'pengumpulan_tugas.materi_tugas_id')
                         ->where('pengumpulan_tugas.siswa_id', '=', $siswaId);
                })
                ->where('mapels.kelas_id', $kelasSiswa->id)
                ->where('materi_tugas.tipe', 'tugas')
                ->select(
                    'materi_tugas.*',
                    'mapels.nama_mapel',
                    'pengumpulan_tugas.id as pengumpulan_id',
                    'pengumpulan_tugas.nilai'
                )
                ->orderBy('materi_tugas.deadline', 'asc')
                ->get();
        }

        return view('dashboard.siswa', compact('kelasSiswa', 'mapels', 'tugasAktif'));
    }
}