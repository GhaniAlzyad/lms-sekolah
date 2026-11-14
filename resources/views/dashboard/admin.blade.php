@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Dashboard Operator ARROJA</h5>
    <span class="badge bg-dark">Tahun Ajaran 2026/2027 • Ganjil</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Guru</div>
                <h3 class="fw-bold text-success mb-0">{{ $stats['guru'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Siswa MTs ({{ $stats['kelas_mts'] }} Kelas)</div>
                <h3 class="fw-bold text-primary mb-0">{{ $stats['siswa_mts'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Siswa MA ({{ $stats['kelas_ma'] }} Kelas)</div>
                <h3 class="fw-bold text-info mb-0">{{ $stats['siswa_ma'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Mata Pelajaran</div>
                <h3 class="fw-bold text-warning mb-0">{{ $stats['mapel'] }}</h3>
            </div>
        </div>
    </div>
</div>

<h6 class="fw-bold mb-3">Menu Manajemen Data Master</h6>
<div class="row g-3">
    <div class="col-md-3">
        <a href="{{ route('admin.guru.index') }}" class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body">
                <div class="fw-bold text-success">1. Data Guru</div>
                <small class="text-muted">Kelola akun & NUPTK guru MTs/MA</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.kelas.index') }}" class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body">
                <div class="fw-bold text-primary">2. Data Kelas & Wali Kelas</div>
                <small class="text-muted">Atur rombel VII–XII & wali kelas</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.siswa.index') }}" class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body">
                <div class="fw-bold text-info">3. Data Siswa</div>
                <small class="text-muted">Input NISN & penempatan kelas</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.mapel.index') }}" class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body">
                <div class="fw-bold text-warning">4. Plotting Mata Pelajaran</div>
                <small class="text-muted">Hubungkan Guru, Mapel, & Kelas</small>
            </div>
        </a>
    </div>
</div>
@endsection