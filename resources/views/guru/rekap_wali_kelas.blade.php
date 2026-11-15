@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span class="badge bg-success">{{ $kelas->unit }} • Kelas {{ $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }} - {{ $kelas->semester }})</span>
        <h5 class="fw-bold mb-0 mt-1">Rekapitulasi Kehadiran & Akademik Wali Kelas</h5>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary">🖨️ Cetak / PDF</button>
        <a href="{{ route('guru.wali_kelas.export') }}" class="btn btn-sm btn-success text-nowrap fw-semibold">
            📥 Export Rekap (CSV)
        </a>
        <a href="{{ route('guru.dashboard') }}" class="btn btn-sm btn-outline-secondary">← Dashboard</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0 table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0 small text-center">
            <thead class="table-light">
                <tr>
                    <th rowspan="2" style="width: 40px;">No</th>
                    <th rowspan="2">NISN</th>
                    <th rowspan="2" class="text-start">Nama Siswa</th>
                    <th colspan="4">Rekap Presensi Harian (Hari)</th>
                    <th colspan="2">Akademik & Tugas</th>
                </tr>
                <tr>
                    <th class="text-success">Hadir</th>
                    <th class="text-info">Izin</th>
                    <th class="text-warning">Sakit</th>
                    <th class="text-danger">Alpa</th>
                    <th>Tugas Selesai</th>
                    <th>Rata-rata Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswas as $index => $s)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $s->nomor_induk }}</td>
                    <td class="text-start fw-semibold">{{ $s->name }}</td>
                    <td class="fw-bold text-success">{{ $s->hadir }}</td>
                    <td>{{ $s->izin }}</td>
                    <td>{{ $s->sakit }}</td>
                    <td class="fw-bold text-danger">{{ $s->alpa }}</td>
                    <td>
                        <span class="badge {{ $s->tugas_dikumpulkan == $totalTugasKelas && $totalTugasKelas > 0 ? 'bg-success' : 'bg-secondary' }}">
                            {{ $s->tugas_dikumpulkan }} / {{ $totalTugasKelas }}
                        </span>
                    </td>
                    <td class="fw-bold">{{ $s->rata_nilai }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection