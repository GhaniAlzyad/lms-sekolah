<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminMasterController;
use App\Http\Controllers\GuruKelasController;
use App\Http\Controllers\SiswaKelasController;

// Halaman Awal langsung arahkan ke Login
// Halaman Awal: cek apakah sudah login atau belum
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'admin') return redirect()->route('admin.dashboard');
        if ($role === 'guru') return redirect()->route('guru.dashboard');
        return redirect()->route('siswa.dashboard');
    }
    return redirect()->route('login');
});

// Guest (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/ganti-password', [AuthController::class, 'changePassword'])
    ->middleware('auth')
    ->name('password.change');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard per Role
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // CRUD Guru
    Route::get('/guru', [AdminMasterController::class, 'guruIndex'])->name('guru.index');
    Route::post('/guru', [AdminMasterController::class, 'guruStore'])->name('guru.store');
    Route::delete('/guru/{id}', [AdminMasterController::class, 'guruDestroy'])->name('guru.destroy');

    // CRUD Kelas
    Route::get('/kelas', [AdminMasterController::class, 'kelasIndex'])->name('kelas.index');
    Route::post('/kelas', [AdminMasterController::class, 'kelasStore'])->name('kelas.store');
    Route::delete('/kelas/{id}', [AdminMasterController::class, 'kelasDestroy'])->name('kelas.destroy');

    // CRUD Siswa
    Route::get('/siswa', [AdminMasterController::class, 'siswaIndex'])->name('siswa.index');
    Route::post('/siswa', [AdminMasterController::class, 'siswaStore'])->name('siswa.store');
    Route::delete('/siswa/{id}', [AdminMasterController::class, 'siswaDestroy'])->name('siswa.destroy');

    // CRUD Mapel
    Route::get('/mapel', [AdminMasterController::class, 'mapelIndex'])->name('mapel.index');
    Route::post('/mapel', [AdminMasterController::class, 'mapelStore'])->name('mapel.store');
    Route::delete('/mapel/{id}', [AdminMasterController::class, 'mapelDestroy'])->name('mapel.destroy');
    Route::put('/guru/{id}', [AdminMasterController::class, 'guruUpdate'])->name('guru.update');
    Route::put('/kelas/{id}', [AdminMasterController::class, 'kelasUpdate'])->name('kelas.update');
    Route::put('/siswa/{id}', [AdminMasterController::class, 'siswaUpdate'])->name('siswa.update');
    Route::put('/mapel/{id}', [AdminMasterController::class, 'mapelUpdate'])->name('mapel.update');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');
    
    // Ruang Kelas (Materi & Tugas)
    Route::get('/kelas/{mapelId}', [GuruKelasController::class, 'show'])->name('kelas.show');
    Route::post('/kelas/{mapelId}/konten', [GuruKelasController::class, 'storeKonten'])->name('konten.store');
    Route::delete('/konten/{id}', [GuruKelasController::class, 'destroyKonten'])->name('konten.destroy');
    Route::put('/konten/{id}', [GuruKelasController::class, 'updateKonten'])->name('konten.update');

    // Penilaian Tugas
    Route::get('/tugas/{tugasId}/nilai', [GuruKelasController::class, 'lihatPengumpulan'])->name('tugas.nilai');
    Route::post('/pengumpulan/{pengumpulanId}/nilai', [GuruKelasController::class, 'simpanNilai'])->name('tugas.simpan_nilai');
    Route::post('/tugas/{tugasId}/izin-susulan/{siswaId}', [GuruKelasController::class, 'toggleIzinSusulan'])->name('tugas.izin_susulan');

    // Presensi Siswa
    Route::get('/kelas/{mapelId}/presensi', [GuruKelasController::class, 'presensiIndex'])->name('presensi.index');
    Route::post('/kelas/{mapelId}/presensi', [GuruKelasController::class, 'presensiStore'])->name('presensi.store');

    // Rekap Wali Kelas
    Route::get('/wali-kelas/rekap', [GuruKelasController::class, 'rekapWaliKelas'])->name('wali_kelas.rekap');

    Route::get('/wali-kelas/export-csv', [GuruKelasController::class, 'exportRekapWaliKelas'])->name('wali_kelas.export');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'siswa'])->name('dashboard');
    Route::get('/kelas/{mapelId}', [SiswaKelasController::class, 'show'])->name('kelas.show');
    Route::post('/tugas/{tugasId}/kumpul', [SiswaKelasController::class, 'kumpulTugas'])->name('tugas.kumpul');
});