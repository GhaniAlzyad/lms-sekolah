<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminMasterController;

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

Route::middleware(['auth', 'role:guru'])->group(function () {
    Route::get('/guru/dashboard', [DashboardController::class, 'guru'])->name('guru.dashboard');
});

Route::middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('/siswa/dashboard', [DashboardController::class, 'siswa'])->name('siswa.dashboard');
});