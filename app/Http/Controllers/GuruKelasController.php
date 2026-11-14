<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuruKelasController extends Controller
{
    // 1. Halaman Utama Ruang Kelas (Daftar Materi & Tugas per Mapel)
    public function show($mapelId)
    {
        $mapel = DB::table('mapels')
            ->join('units', 'mapels.unit_id', '=', 'units.id')
            ->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
            ->where('mapels.id', $mapelId)
            ->where('mapels.guru_id', Auth::id()) // Pastikan hanya guru pengampu yang bisa buka
            ->select('mapels.*', 'units.singkatan as unit', 'kelas.nama_kelas')
            ->first();

        if (!$mapel) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }

        $kontens = DB::table('materi_tugas')
            ->where('mapel_id', $mapelId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung jumlah siswa yang sudah mengumpulkan untuk setiap tugas
        foreach ($kontens as $k) {
            if ($k->tipe === 'tugas') {
                $k->jumlah_kumpul = DB::table('pengumpulan_tugas')
                    ->where('materi_tugas_id', $k->id)
                    ->count();
            }
        }

        return view('guru.kelas_show', compact('mapel', 'kontens'));
    }

    // 2. Simpan Materi atau Tugas Baru
    public function storeKonten(Request $request, $mapelId)
    {
        $request->validate([
            'tipe' => 'required|in:materi,tugas',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link_lampiran' => 'nullable|url|max:500',
            'deadline' => 'required_if:tipe,tugas|nullable|date',
        ]);

        // Pastikan mapel milik guru ini
        $mapel = DB::table('mapels')->where('id', $mapelId)->where('guru_id', Auth::id())->first();
        if (!$mapel) abort(403);

        DB::table('materi_tugas')->insert([
            'mapel_id' => $mapelId,
            'tipe' => $request->tipe,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'link_lampiran' => $request->link_lampiran,
            'deadline' => $request->tipe === 'tugas' ? $request->deadline : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', ucfirst($request->tipe) . ' berhasil dipublikasikan ke kelas.');
    }

    // 3. Hapus Materi atau Tugas
    public function destroyKonten($id)
    {
        $konten = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('materi_tugas.id', $id)
            ->where('mapels.guru_id', Auth::id())
            ->select('materi_tugas.id')
            ->first();

        if (!$konten) abort(403);

        DB::table('materi_tugas')->where('id', $id)->delete();
        return back()->with('success', 'Konten berhasil dihapus.');
    }

    // 4. Halaman Pemeriksaan & Penilaian Tugas Siswa
    public function lihatPengumpulan($tugasId)
    {
        $tugas = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->join('units', 'mapels.unit_id', '=', 'units.id')
            ->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
            ->where('materi_tugas.id', $tugasId)
            ->where('mapels.guru_id', Auth::id())
            ->select('materi_tugas.*', 'mapels.id as mapel_id', 'mapels.nama_mapel', 'mapels.kelas_id', 'units.singkatan as unit', 'kelas.nama_kelas')
            ->first();

        if (!$tugas) abort(403);

        // Ambil seluruh siswa di kelas tersebut beserta status pengumpulannya
        $daftarSiswa = DB::table('kelas_siswa')
            ->join('users', 'kelas_siswa.user_id', '=', 'users.id')
            ->leftJoin('pengumpulan_tugas', function ($join) use ($tugasId) {
                $join->on('users.id', '=', 'pengumpulan_tugas.siswa_id')
                     ->where('pengumpulan_tugas.materi_tugas_id', '=', $tugasId);
            })
            ->where('kelas_siswa.kelas_id', $tugas->kelas_id)
            ->select(
                'users.id as siswa_id',
                'users.nomor_induk',
                'users.name',
                'pengumpulan_tugas.id as pengumpulan_id',
                'pengumpulan_tugas.link_jawaban',
                'pengumpulan_tugas.nilai',
                'pengumpulan_tugas.catatan_guru',
                'pengumpulan_tugas.updated_at as waktu_kumpul'
            )
            ->orderBy('users.name')
            ->get();

        return view('guru.tugas_nilai', compact('tugas', 'daftarSiswa'));
    }

    // 5. Simpan Nilai & Catatan Guru
    public function simpanNilai(Request $request, $pengumpulanId)
    {
        $request->validate([
            'nilai' => 'required|integer|min:0|max:100',
            'catatan_guru' => 'nullable|string|max:500',
        ]);

        // Pastikan pengumpulan ini benar-benar milik mapel yang diampu guru yang sedang login
        $valid = DB::table('pengumpulan_tugas')
            ->join('materi_tugas', 'pengumpulan_tugas.materi_tugas_id', '=', 'materi_tugas.id')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('pengumpulan_tugas.id', $pengumpulanId)
            ->where('mapels.guru_id', Auth::id())
            ->exists();

        if (!$valid) {
            abort(403, 'Anda tidak berhak menilai tugas ini.');
        }

        DB::table('pengumpulan_tugas')
            ->where('id', $pengumpulanId)
            ->update([
                'nilai' => $request->nilai,
                'catatan_guru' => $request->catatan_guru,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Nilai siswa berhasil disimpan.');
    }

    // 6. Halaman Input Presensi oleh Guru Mapel
    public function presensiIndex(Request $request, $mapelId)
    {
        $mapel = DB::table('mapels')
            ->join('units', 'mapels.unit_id', '=', 'units.id')
            ->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
            ->where('mapels.id', $mapelId)
            ->where('mapels.guru_id', Auth::id())
            ->select('mapels.*', 'units.singkatan as unit', 'kelas.nama_kelas')
            ->first();

        if (!$mapel) abort(403);

        // Default tanggal hari ini jika tidak memilih tanggal lain
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        // Ambil daftar siswa beserta status presensi pada tanggal tersebut (jika sudah diinput)
        $siswas = DB::table('kelas_siswa')
            ->join('users', 'kelas_siswa.user_id', '=', 'users.id')
            ->leftJoin('presensis', function ($join) use ($mapelId, $tanggal) {
                $join->on('users.id', '=', 'presensis.siswa_id')
                     ->where('presensis.mapel_id', '=', $mapelId)
                     ->where('presensis.tanggal', '=', $tanggal);
            })
            ->where('kelas_siswa.kelas_id', $mapel->kelas_id)
            ->select(
                'users.id as siswa_id',
                'users.nomor_induk',
                'users.name',
                'presensis.status',
                'presensis.keterangan'
            )
            ->orderBy('users.name')
            ->get();

        return view('guru.presensi', compact('mapel', 'siswas', 'tanggal'));
    }

    // 7. Simpan / Perbarui Presensi Siswa
    public function presensiStore(Request $request, $mapelId)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'in:Hadir,Izin,Sakit,Alpa',
            'keterangan' => 'nullable|array',
        ]);

        $mapel = DB::table('mapels')->where('id', $mapelId)->where('guru_id', Auth::id())->first();
        if (!$mapel) abort(403);

        foreach ($request->status as $siswaId => $statusKehadiran) {
            DB::table('presensis')->updateOrInsert(
                [
                    'mapel_id' => $mapelId,
                    'siswa_id' => $siswaId,
                    'tanggal' => $request->tanggal,
                ],
                [
                    'status' => $statusKehadiran,
                    'keterangan' => $request->keterangan[$siswaId] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return back()->with('success', 'Data presensi tanggal ' . \Carbon\Carbon::parse($request->tanggal)->format('d M Y') . ' berhasil disimpan.');
    }

    // 8. Halaman Rekapitulasi Khusus Wali Kelas
    public function rekapWaliKelas()
    {
        $guruId = Auth::id();

        $kelas = DB::table('kelas')
            ->join('units', 'kelas.unit_id', '=', 'units.id')
            ->where('kelas.wali_kelas_id', $guruId)
            ->select('kelas.*', 'units.singkatan as unit')
            ->first();

        if (!$kelas) {
            abort(403, 'Anda bukan Wali Kelas.');
        }

        $siswas = DB::table('kelas_siswa')
            ->join('users', 'kelas_siswa.user_id', '=', 'users.id')
            ->where('kelas_siswa.kelas_id', $kelas->id)
            ->select('users.id', 'users.nomor_induk', 'users.name')
            ->orderBy('users.name')
            ->get();

        // Hitung total presensi (H, I, S, A) & progres tugas tiap siswa lintas semua mapel di kelas ini
        $totalTugasKelas = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('mapels.kelas_id', $kelas->id)
            ->where('materi_tugas.tipe', 'tugas')
            ->count();

        foreach ($siswas as $s) {
            // 1. Kelompokkan presensi per TANGGAL dulu agar 1 hari = 1 status kehadiran
            $presensiHarian = DB::table('presensis')
                ->join('mapels', 'presensis.mapel_id', '=', 'mapels.id')
                ->where('mapels.kelas_id', $kelas->id)
                ->where('presensis.siswa_id', $s->id)
                ->select('presensis.tanggal', 'presensis.status')
                ->get()
                ->groupBy('tanggal');

            $hadir = 0; $izin = 0; $sakit = 0; $alpa = 0;

            foreach ($presensiHarian as $tanggal => $catatanHariItu) {
                $statuses = $catatanHariItu->pluck('status')->toArray();

                // Prioritas status harian: Alpa > Sakit > Izin > Hadir
                if (in_array('Alpa', $statuses)) {
                    $alpa++;
                } elseif (in_array('Sakit', $statuses)) {
                    $sakit++;
                } elseif (in_array('Izin', $statuses)) {
                    $izin++;
                } else {
                    $hadir++;
                }
            }

            $s->hadir = $hadir;
            $s->izin = $izin;
            $s->sakit = $sakit;
            $s->alpa = $alpa;

            // 2. Hitung progres tugas & rata-rata nilai lintas mapel
            $tugasStats = DB::table('pengumpulan_tugas')
                ->join('materi_tugas', 'pengumpulan_tugas.materi_tugas_id', '=', 'materi_tugas.id')
                ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
                ->where('mapels.kelas_id', $kelas->id)
                ->where('pengumpulan_tugas.siswa_id', $s->id)
                ->selectRaw('COUNT(pengumpulan_tugas.id) as dikumpulkan, AVG(pengumpulan_tugas.nilai) as rata_nilai')
                ->first();

            $s->tugas_dikumpulkan = $tugasStats->dikumpulkan ?? 0;
            $s->rata_nilai = $tugasStats->rata_nilai ? round($tugasStats->rata_nilai, 1) : '-';
        }

        return view('guru.rekap_wali_kelas', compact('kelas', 'siswas', 'totalTugasKelas'));
    }

    // Update Materi atau Tugas
    public function updateKonten(Request $request, $id)
    {
        $request->validate([
            'tipe' => 'required|in:materi,tugas',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link_lampiran' => 'nullable|url|max:500',
            'deadline' => 'required_if:tipe,tugas|nullable|date',
        ]);

        $konten = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('materi_tugas.id', $id)
            ->where('mapels.guru_id', Auth::id())
            ->select('materi_tugas.id')
            ->first();

        if (!$konten) abort(403);

        DB::table('materi_tugas')->where('id', $id)->update([
            'tipe' => $request->tipe,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'link_lampiran' => $request->link_lampiran,
            'deadline' => $request->tipe === 'tugas' ? $request->deadline : null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Postingan berhasil diperbarui.');
    }
}