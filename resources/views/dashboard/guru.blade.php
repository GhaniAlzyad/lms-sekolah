@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Ruang Mengajar Guru</h5>
    <span class="badge bg-danger">Tugas Belum Dinilai: {{ $tugasBelumDinilai }}</span>
</div>

<div class="row g-3 mb-4">
    @forelse ($mapels as $mapel)
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge {{ $mapel->unit == 'MTs' ? 'bg-primary' : 'bg-info text-dark' }} mb-2">
                            {{ $mapel->unit }} • Kelas {{ $mapel->nama_kelas }}
                        </span>
                        <h6 class="fw-bold">{{ $mapel->nama_mapel }}</h6>
                    </div>
                    <div class="mt-3 d-grid gap-1">
                        <a href="#" class="btn btn-sm btn-success">Materi & Tugas</a>
                        <a href="#" class="btn btn-sm btn-outline-secondary">Input Presensi</a>
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
        <span class="badge bg-success">{{ count($siswaPerwalian) }} Siswa</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswaPerwalian as $s)
                <tr>
                    <td>{{ $s->nomor_induk }}</td>
                    <td>{{ $s->name }}</td>
                    <td class="text-end"><a href="#" class="btn btn-xs btn-outline-primary py-0 px-2">Rekap Nilai & Absen</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection