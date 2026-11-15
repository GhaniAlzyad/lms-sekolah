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
            ->select(
                'materi_tugas.*',
                DB::raw('(SELECT COUNT(*) FROM pengumpulan_tugas WHERE pengumpulan_tugas.materi_tugas_id = materi_tugas.id) as jumlah_pengumpulan'),
                DB::raw('(SELECT COUNT(*) FROM pengumpulan_tugas WHERE pengumpulan_tugas.materi_tugas_id = materi_tugas.id AND pengumpulan_tugas.nilai IS NOT NULL) as jumlah_dinilai')
            )
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
        if ($request->filled('link_lampiran') && !preg_match('~^https?://~i', $request->link_lampiran)) {
            $request->merge([
                'link_lampiran' => 'https://' . ltrim($request->link_lampiran, '/'),
            ]);
        }

        $request->validate([
            'tipe'          => 'required|in:materi,tugas',
            'judul'         => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'link_lampiran' => [
                'nullable',
                'url',
                'regex:~^https?://[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+~',
                'max:500',
            ],
            'deadline'      => 'nullable|date|required_if:tipe,tugas',
        ], [
            'link_lampiran.url'   => 'Format link lampiran tidak valid (contoh: https://drive.google.com/...).',
            'link_lampiran.regex' => 'Link lampiran harus berupa alamat web/domain yang valid.',
        ]);

        // Pastikan mapel milik guru ini
        $mapel = DB::table('mapels')->where('id', $mapelId)->where('guru_id', Auth::id())->first();
        if (!$mapel) abort(403);

        DB::table('materi_tugas')->insert([
            'mapel_id'      => $mapelId,
            'tipe'          => $request->tipe,
            'judul'         => $request->judul,
            'deskripsi'     => $request->deskripsi,
            'link_lampiran' => $request->link_lampiran,
            'deadline'      => $request->tipe === 'tugas' ? $request->deadline : null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return back()->with('success', ucfirst($request->tipe) . ' berhasil dipublikasikan ke kelas.');
    }

    // 3. Hapus Materi atau Tugas
    public function destroyKonten(Request $request, $id)
    {
        $konten = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('materi_tugas.id', $id)
            ->where('mapels.guru_id', Auth::id())
            ->select('materi_tugas.id', 'materi_tugas.tipe', 'materi_tugas.judul')
            ->first();

        if (!$konten) abort(403);

        if ($konten->tipe === 'tugas') {
            $jumlahKumpul = DB::table('pengumpulan_tugas')
                ->where('materi_tugas_id', $id)
                ->count();

            if ($jumlahKumpul > 0 && !$request->boolean('paksa_hapus')) {
                return back()->with('error', "Tugas \"{$konten->judul}\" sudah dikumpulkan oleh {$jumlahKumpul} siswa. Gunakan konfirmasi penghapusan permanen jika benar-benar ingin menghapus beserta seluruh nilai siswa.");
            }
        }

        DB::transaction(function () use ($id) {
            DB::table('pengumpulan_tugas')->where('materi_tugas_id', $id)->delete();
            DB::table('izin_susulan')->where('materi_tugas_id', $id)->delete();
            DB::table('materi_tugas')->where('id', $id)->delete();
        });

        return back()->with('success', ucfirst($konten->tipe) . ' dan seluruh data terkait berhasil dihapus.');
    }

    // 4. Halaman Pemeriksaan & Penilaian Tugas Siswa
    public function lihatPengumpulan($tugasId)
    {
        // Query 1: Ambil detail tugas & mapel saja
        $tugas = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->join('units', 'mapels.unit_id', '=', 'units.id')
            ->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
            ->where('materi_tugas.id', $tugasId)
            ->where('mapels.guru_id', Auth::id())
            ->select(
                'materi_tugas.*',
                'mapels.id as mapel_id',
                'mapels.nama_mapel',
                'mapels.kelas_id',
                'units.singkatan as unit',
                'kelas.nama_kelas'
            )
            ->first();

        if (!$tugas) abort(403);

        // Query 2: Ambil seluruh siswa di kelas tersebut beserta status pengumpulan & izin susulannya
        $daftarSiswa = DB::table('kelas_siswa')
            ->join('users', 'kelas_siswa.user_id', '=', 'users.id')
            ->leftJoin('pengumpulan_tugas', function ($join) use ($tugasId) {
                $join->on('users.id', '=', 'pengumpulan_tugas.siswa_id')
                     ->where('pengumpulan_tugas.materi_tugas_id', '=', $tugasId);
            })
            ->leftJoin('izin_susulan', function ($join) use ($tugasId) {
                $join->on('users.id', '=', 'izin_susulan.siswa_id')
                     ->where('izin_susulan.materi_tugas_id', '=', $tugasId);
            })
            ->where('kelas_siswa.kelas_id', $tugas->kelas_id)
            ->orderBy('users.name')
            ->select(
                'users.id as siswa_id',
                'users.name',
                'users.nomor_induk',
                'pengumpulan_tugas.id as pengumpulan_id',
                'pengumpulan_tugas.link_jawaban',
                'pengumpulan_tugas.teks_jawaban',
                'pengumpulan_tugas.nilai',
                'pengumpulan_tugas.catatan_guru',
                'pengumpulan_tugas.created_at as waktu_kumpul',
                'izin_susulan.id as izin_susulan_id'
            )
            ->get();

        return view('guru.tugas_nilai', compact('tugas', 'daftarSiswa'));
    }

    // 4b. Buka / Tutup Izin Susulan Siswa
    public function toggleIzinSusulan($tugasId, $siswaId)
    {
        // Pastikan tugas ini benar-benar milik mapel guru yang sedang login
        $tugasValid = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('materi_tugas.id', $tugasId)
            ->where('mapels.guru_id', Auth::id())
            ->exists();

        if (!$tugasValid) abort(403);

        $cek = DB::table('izin_susulan')
            ->where('materi_tugas_id', $tugasId)
            ->where('siswa_id', $siswaId)
            ->first();

        if ($cek) {
            DB::table('izin_susulan')->where('id', $cek->id)->delete();
            return back()->with('success', 'Izin susulan siswa ditutup kembali.');
        }

        DB::table('izin_susulan')->insert([
            'materi_tugas_id' => $tugasId,
            'siswa_id'        => $siswaId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return back()->with('success', 'Izin susulan diberikan. Siswa kini dapat mengumpulkan tugas dengan status Terlambat.');
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
        'nilai'        => $request->nilai,
        'catatan_guru' => $request->catatan_guru,
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
        if ($request->filled('link_lampiran') && !preg_match('~^https?://~i', $request->link_lampiran)) {
            $request->merge([
                'link_lampiran' => 'https://' . ltrim($request->link_lampiran, '/'),
            ]);
        }

        $request->validate([
            'tipe'          => 'required|in:materi,tugas',
            'judul'         => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'link_lampiran' => [
                'nullable',
                'url',
                'regex:~^https?://[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+~',
                'max:500',
            ],
            'deadline'      => 'nullable|date|required_if:tipe,tugas',
        ], [
            'link_lampiran.url'   => 'Format link lampiran tidak valid (contoh: https://drive.google.com/...).',
            'link_lampiran.regex' => 'Link lampiran harus berupa alamat web/domain yang valid.',
        ]);

        $konten = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('materi_tugas.id', $id)
            ->where('mapels.guru_id', Auth::id())
            ->select('materi_tugas.id')
            ->first();

        if (!$konten) abort(403);

        DB::table('materi_tugas')->where('id', $id)->update([
            'tipe'          => $request->tipe,
            'judul'         => $request->judul,
            'deskripsi'     => $request->deskripsi,
            'link_lampiran' => $request->link_lampiran,
            'deadline'      => $request->tipe === 'tugas' ? $request->deadline : null,
            'updated_at'    => now(),
        ]);

        return back()->with('success', 'Postingan berhasil diperbarui.');
    }
    // 9. Export Nilai Tugas ke CSV (Excel-Ready)
    public function exportNilaiTugas($tugasId)
    {
        $tugas = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->join('units', 'mapels.unit_id', '=', 'units.id')
            ->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
            ->where('materi_tugas.id', $tugasId)
            ->where('mapels.guru_id', Auth::id())
            ->select(
                'materi_tugas.*',
                'mapels.nama_mapel',
                'mapels.kelas_id',
                'units.singkatan as unit',
                'kelas.nama_kelas'
            )
            ->first();

        if (!$tugas) abort(403);

        $daftarSiswa = DB::table('kelas_siswa')
            ->join('users', 'kelas_siswa.user_id', '=', 'users.id')
            ->leftJoin('pengumpulan_tugas', function ($join) use ($tugasId) {
                $join->on('users.id', '=', 'pengumpulan_tugas.siswa_id')
                     ->where('pengumpulan_tugas.materi_tugas_id', '=', $tugasId);
            })
            ->where('kelas_siswa.kelas_id', $tugas->kelas_id)
            ->orderBy('users.name')
            ->select(
                'users.nomor_induk',
                'users.name',
                'pengumpulan_tugas.id as pengumpulan_id',
                'pengumpulan_tugas.created_at as waktu_kumpul',
                'pengumpulan_tugas.nilai',
                'pengumpulan_tugas.catatan_guru'
            )
            ->get();

        $namaFile = 'Nilai_' . str_replace(' ', '_', $tugas->nama_kelas) . '_' . str_replace(' ', '_', $tugas->judul) . '.csv';
        $deadline = $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline, 'Asia/Jakarta') : null;

        return response()->streamDownload(function () use ($daftarSiswa, $deadline) {
            $handle = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM agar Excel langsung membaca karakter dengan benar
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['No', 'NIS', 'Nama Siswa', 'Status Pengumpulan', 'Waktu Kumpul (WIB)', 'Nilai', 'Catatan Guru'], ';');

            foreach ($daftarSiswa as $index => $s) {
                if ($s->pengumpulan_id) {
                    $wk = \Carbon\Carbon::parse($s->waktu_kumpul, 'Asia/Jakarta');
                    $status = ($deadline && $wk->gt($deadline)) ? 'Terlambat' : 'Tepat Waktu';
                    $waktuStr = $wk->format('d/m/Y H:i');
                } else {
                    $status = 'Belum Kumpul';
                    $waktuStr = '-';
                }

                fputcsv($handle, [
                    $index + 1,
                    $s->nomor_induk,
                    $s->name,
                    $status,
                    $waktuStr,
                    !is_null($s->nilai) ? $s->nilai : '-',
                    $s->catatan_guru ?? '',
                ], ';');
            }

            fclose($handle);
        }, $namaFile, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // 10. Export Rekapitulasi Wali Kelas ke CSV (Excel-Ready)
    public function exportRekapWaliKelas()
    {
        $kelas = DB::table('kelas')
            ->join('units', 'kelas.unit_id', '=', 'units.id')
            ->where('kelas.wali_kelas_id', Auth::id())
            ->select('kelas.*', 'units.singkatan as unit')
            ->first();

        if (!$kelas) abort(403, 'Anda bukan Wali Kelas.');

        $siswas = DB::table('kelas_siswa')
            ->join('users', 'kelas_siswa.user_id', '=', 'users.id')
            ->where('kelas_siswa.kelas_id', $kelas->id)
            ->select('users.id', 'users.nomor_induk', 'users.name')
            ->orderBy('users.name')
            ->get();

        $totalTugasKelas = DB::table('materi_tugas')
            ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
            ->where('mapels.kelas_id', $kelas->id)
            ->where('materi_tugas.tipe', 'tugas')
            ->count();

        $namaFile = 'Rekap_Wali_Kelas_' . $kelas->unit . '_' . str_replace(' ', '_', $kelas->nama_kelas) . '.csv';

        return response()->streamDownload(function () use ($siswas, $kelas, $totalTugasKelas) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['No', 'NIS', 'Nama Siswa', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Tugas Terkumpul', 'Total Tugas', 'Rata-Rata Nilai'], ';');

            foreach ($siswas as $index => $s) {
                $presensiHarian = DB::table('presensis')
                    ->join('mapels', 'presensis.mapel_id', '=', 'mapels.id')
                    ->where('mapels.kelas_id', $kelas->id)
                    ->where('presensis.siswa_id', $s->id)
                    ->select('presensis.tanggal', 'presensis.status')
                    ->get()
                    ->groupBy('tanggal');

                $hadir = 0; $izin = 0; $sakit = 0; $alpa = 0;
                foreach ($presensiHarian as $catatanHariItu) {
                    $statuses = $catatanHariItu->pluck('status')->toArray();
                    if (in_array('Alpa', $statuses)) $alpa++;
                    elseif (in_array('Sakit', $statuses)) $sakit++;
                    elseif (in_array('Izin', $statuses)) $izin++;
                    else $hadir++;
                }

                $tugasStats = DB::table('pengumpulan_tugas')
                    ->join('materi_tugas', 'pengumpulan_tugas.materi_tugas_id', '=', 'materi_tugas.id')
                    ->join('mapels', 'materi_tugas.mapel_id', '=', 'mapels.id')
                    ->where('mapels.kelas_id', $kelas->id)
                    ->where('pengumpulan_tugas.siswa_id', $s->id)
                    ->selectRaw('COUNT(pengumpulan_tugas.id) as dikumpulkan, AVG(pengumpulan_tugas.nilai) as rata_nilai')
                    ->first();

                fputcsv($handle, [
                    $index + 1,
                    $s->nomor_induk,
                    $s->name,
                    $hadir,
                    $izin,
                    $sakit,
                    $alpa,
                    $tugasStats->dikumpulkan ?? 0,
                    $totalTugasKelas,
                    $tugasStats->rata_nilai ? round($tugasStats->rata_nilai, 1) : '-',
                ], ';');
            }

            fclose($handle);
        }, $namaFile, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}