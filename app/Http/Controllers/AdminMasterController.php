<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminMasterController extends Controller
{
    // --- 1. MANAJEMEN GURU ---

    public function guruIndex()
    {
        $gurus = DB::table('users')->where('role', 'guru')->orderBy('name')->get();
        return view('admin.guru', compact('gurus'));
    }

    public function guruStore(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required|unique:users,nomor_induk',
            'name' => 'required|string|max:255',
            'password' => 'required|min:6',
        ]);

        DB::table('users')->insert([
            'unit_id' => null, // Guru fleksibel bisa mengajar di MTs & MA
            'nomor_induk' => $request->nomor_induk,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Data Guru berhasil ditambahkan.');
    }

    public function guruDestroy($id)
    {
        DB::table('users')->where('id', $id)->where('role', 'guru')->delete();
        return back()->with('success', 'Data Guru berhasil dihapus.');
    }
    // 1. Update Guru (tambahkan di bagian Manajemen Guru)
    public function guruUpdate(Request $request, $id)
    {
        $request->validate([
            'nomor_induk' => 'required|unique:users,nomor_induk,' . $id,
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6',
        ]);

        $data = [
            'nomor_induk' => $request->nomor_induk,
            'name' => $request->name,
            'updated_at' => now(),
        ];

        // Password hanya diubah jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->where('role', 'guru')->update($data);
        return back()->with('success', 'Data Guru berhasil diperbarui.');
    }

    // 2. Update Kelas (tambahkan di bagian Manajemen Kelas)
    public function kelasUpdate(Request $request, $id)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'nama_kelas' => 'required|string|max:50',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        DB::table('kelas')->where('id', $id)->update([
            'unit_id' => $request->unit_id,
            'wali_kelas_id' => $request->wali_kelas_id ?: null,
            'nama_kelas' => $request->nama_kelas,
            'tahun_ajaran' => $request->tahun_ajaran,
            'semester' => $request->semester,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Data Kelas berhasil diperbarui.');
    }

    // 3. Update Siswa (tambahkan di bagian Manajemen Siswa)
    public function siswaUpdate(Request $request, $id)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'nomor_induk' => 'required|unique:users,nomor_induk,' . $id,
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6',
        ]);

        $kelas = DB::table('kelas')->where('id', $request->kelas_id)->first();

        $data = [
            'unit_id' => $kelas->unit_id,
            'nomor_induk' => $request->nomor_induk,
            'name' => $request->name,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->where('role', 'siswa')->update($data);
        DB::table('kelas_siswa')->where('user_id', $id)->update([
            'kelas_id' => $kelas->id,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Data Siswa berhasil diperbarui.');
    }

    // 4. Update Mapel (tambahkan di bagian Plotting Mapel)
    public function mapelUpdate(Request $request, $id)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:users,id',
            'nama_mapel' => 'required|string|max:100',
        ]);

        $kelas = DB::table('kelas')->where('id', $request->kelas_id)->first();

        DB::table('mapels')->where('id', $id)->update([
            'unit_id' => $kelas->unit_id,
            'kelas_id' => $kelas->id,
            'guru_id' => $request->guru_id,
            'nama_mapel' => $request->nama_mapel,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Data Mata Pelajaran berhasil diperbarui.');
    }

    // --- 2. MANAJEMEN KELAS & WALI KELAS ---
    public function kelasIndex()
    {
        $units = DB::table('units')->get();
        $gurus = DB::table('users')->where('role', 'guru')->orderBy('name')->get();
        $kelas = DB::table('kelas')
            ->join('units', 'kelas.unit_id', '=', 'units.id')
            ->leftJoin('users', 'kelas.wali_kelas_id', '=', 'users.id')
            ->select('kelas.*', 'units.singkatan as unit', 'users.name as wali_kelas')
            ->orderBy('units.id')
            ->orderBy('kelas.nama_kelas')
            ->get();

        return view('admin.kelas', compact('units', 'gurus', 'kelas'));
    }

    public function kelasStore(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'nama_kelas' => 'required|string|max:50',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        DB::table('kelas')->insert([
            'unit_id' => $request->unit_id,
            'wali_kelas_id' => $request->wali_kelas_id ?: null,
            'nama_kelas' => $request->nama_kelas,
            'tahun_ajaran' => $request->tahun_ajaran,
            'semester' => $request->semester,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Kelas & Wali Kelas berhasil disimpan.');
    }

    public function kelasDestroy($id)
    {
        DB::table('kelas')->where('id', $id)->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    // --- 3. MANAJEMEN SISWA (OTOMATIS MASUK ROMBEL) ---
    public function siswaIndex()
    {
        $kelasList = DB::table('kelas')
            ->join('units', 'kelas.unit_id', '=', 'units.id')
            ->select('kelas.*', 'units.singkatan as unit')
            ->orderBy('units.id')
            ->orderBy('kelas.nama_kelas')
            ->get();

        $siswas = DB::table('users')
            ->join('units', 'users.unit_id', '=', 'units.id')
            ->leftJoin('kelas_siswa', 'users.id', '=', 'kelas_siswa.user_id')
            ->leftJoin('kelas', 'kelas_siswa.kelas_id', '=', 'kelas.id')
            ->where('users.role', 'siswa')
            ->select('users.*', 'units.singkatan as unit', 'kelas.nama_kelas')
            ->orderBy('units.id')
            ->orderBy('kelas.nama_kelas')
            ->orderBy('users.name')
            ->get();

        return view('admin.siswa', compact('kelasList', 'siswas'));
    }

    public function siswaStore(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'nomor_induk' => 'required|unique:users,nomor_induk',
            'name' => 'required|string|max:255',
            'password' => 'required|min:6',
        ]);

        $kelas = DB::table('kelas')->where('id', $request->kelas_id)->first();

        $siswaId = DB::table('users')->insertGetId([
            'unit_id' => $kelas->unit_id,
            'nomor_induk' => $request->nomor_induk,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kelas_siswa')->insert([
            'kelas_id' => $kelas->id,
            'user_id' => $siswaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function siswaDestroy($id)
    {
        DB::table('users')->where('id', $id)->where('role', 'siswa')->delete();
        return back()->with('success', 'Data Siswa berhasil dihapus.');
    }

    // --- 4. PLOTTING MATA PELAJARAN ---
    public function mapelIndex()
    {
        $kelasList = DB::table('kelas')
            ->join('units', 'kelas.unit_id', '=', 'units.id')
            ->select('kelas.*', 'units.singkatan as unit')
            ->orderBy('units.id')
            ->orderBy('kelas.nama_kelas')
            ->get();

        $gurus = DB::table('users')->where('role', 'guru')->orderBy('name')->get();

        $mapels = DB::table('mapels')
            ->join('units', 'mapels.unit_id', '=', 'units.id')
            ->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
            ->join('users', 'mapels.guru_id', '=', 'users.id')
            ->select('mapels.*', 'units.singkatan as unit', 'kelas.nama_kelas', 'users.name as nama_guru')
            ->orderBy('units.id')
            ->orderBy('kelas.nama_kelas')
            ->get();

        return view('admin.mapel', compact('kelasList', 'gurus', 'mapels'));
    }

    public function mapelStore(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:users,id',
            'nama_mapel' => 'required|string|max:100',
        ]);

        $kelas = DB::table('kelas')->where('id', $request->kelas_id)->first();

        DB::table('mapels')->insert([
            'unit_id' => $kelas->unit_id,
            'kelas_id' => $kelas->id,
            'guru_id' => $request->guru_id,
            'nama_mapel' => $request->nama_mapel,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Mata Pelajaran berhasil di-plotting.');
    }

    public function mapelDestroy($id)
    {
        DB::table('mapels')->where('id', $id)->delete();
        return back()->with('success', 'Mata Pelajaran berhasil dihapus.');
    }

    public function siswaImport(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('file_csv')->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (count($lines) < 2) {
            return back()->withErrors(['file_csv' => 'File CSV kosong atau tidak memiliki data.']);
        }

        // Deteksi otomatis pemisah koma (,) atau titik koma (;) dari Excel
        $delimiter = str_contains($lines[0], ';') ? ';' : ',';
        $berhasil = 0;

        foreach (array_slice($lines, 1) as $line) {
            $row = str_getcsv($line, $delimiter);
            if (count($row) < 3) continue;

            $nisn = trim($row[0]);
            $nama = trim($row[1]);
            $namaKelas = trim($row[2]);

            $kelas = DB::table('kelas')->whereRaw('LOWER(nama_kelas) = ?', [strtolower($namaKelas)])->first();
            if (!$kelas || empty($nisn) || empty($nama)) continue;

            $existingUser = DB::table('users')->where('nomor_induk', $nisn)->first();
            if ($existingUser) continue; // Lewati jika NISN sudah terdaftar

            $userId = DB::table('users')->insertGetId([
                'unit_id' => $kelas->unit_id,
                'nomor_induk' => $nisn,
                'name' => $nama,
                'role' => 'siswa',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('kelas_siswa')->insert([
                'kelas_id' => $kelas->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $berhasil++;
        }

        return back()->with('success', "$berhasil data siswa berhasil diimpor (Password default: password123).");
    }
}