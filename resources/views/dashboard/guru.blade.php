@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Ruang Mengajar Guru</h5>
    <span class="badge bg-danger">Tugas Belum Dinilai: {{ $tugasBelumDinilai }}</span>
</div>

<div class="row g-3 mb-4">
    @forelse ($mapels as $mapel)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge {{ $mapel->unit == 'MTs' ? 'bg-primary' : 'bg-info text-dark' }} mb-2">
                            {{ $mapel->unit }} • Kelas {{ $mapel->nama_kelas }}
                        </span>
                        <h6 class="fw-bold">{{ $mapel->nama_mapel }}</h6>
                    </div>
                    <div class="mt-3 d-grid gap-2">
                        <a href="{{ route('guru.kelas.show', $mapel->id) }}" class="btn btn-sm btn-success fw-semibold">
                            📘 Materi & Tugas
                        </a>
                        <a href="{{ route('guru.presensi.index', $mapel->id) }}" class="btn btn-sm btn-outline-secondary">
                            📋 Input Presensi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">Belum ada mata pelajaran yang ditugaskan.</p>
    @endforelse
</div>

@if($kelasPerwalian)
<hr class="my-4">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span>Kelas Perwalian Saya: {{ $kelasPerwalian->unit }} - {{ $kelasPerwalian->nama_kelas }}</span>
        <a href="{{ route('guru.wali_kelas.rekap') }}" class="btn btn-sm btn-primary">
            Lihat Rekap Lengkap ({{ count($siswaPerwalian) }} Siswa)
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswaPerwalian as $index => $s)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $s->nomor_induk }}</td>
                    <td class="fw-semibold">{{ $s->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-3">Belum ada siswa di kelas perwalian ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection