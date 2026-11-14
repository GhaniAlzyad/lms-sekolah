@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Dashboard Siswa</h5>
    @if($kelasSiswa)
        <span class="badge bg-success">{{ $kelasSiswa->unit }} • Kelas {{ $kelasSiswa->nama_kelas }} ({{ $kelasSiswa->semester }})</span>
    @endif
</div>

<div class="row g-2 mb-4 text-center">
    <div class="col-3">
        <div class="card border-0 shadow-sm py-2">
            <small class="text-muted">Hadir</small>
            <strong class="text-success fs-5">{{ $rekapHadir['Hadir'] }}</strong>
        </div>
    </div>
    <div class="col-3">
        <div class="card border-0 shadow-sm py-2">
            <small class="text-muted">Izin</small>
            <strong class="text-info fs-5">{{ $rekapHadir['Izin'] }}</strong>
        </div>
    </div>
    <div class="col-3">
        <div class="card border-0 shadow-sm py-2">
            <small class="text-muted">Sakit</small>
            <strong class="text-warning fs-5">{{ $rekapHadir['Sakit'] }}</strong>
        </div>
    </div>
    <div class="col-3">
        <div class="card border-0 shadow-sm py-2">
            <small class="text-muted">Alpa</small>
            <strong class="text-danger fs-5">{{ $rekapHadir['Alpa'] }}</strong>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold text-danger">Daftar Tugas Kelas Anda</div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush small">
            @forelse($tugasAktif as $t)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-secondary me-1">{{ $t->nama_mapel }}</span>
                        <span class="fw-semibold">{{ $t->judul }}</span>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            Batas waktu: {{ \Carbon\Carbon::parse($t->deadline)->format('d M Y, H:i') }}
                        </div>
                    </div>
                    <div>
                        @if($t->pengumpulan_id)
                            <span class="badge bg-success">Sudah Dikumpulkan {{ $t->nilai !== null ? '(Nilai: '.$t->nilai.')' : '' }}</span>
                        @else
                            <a href="{{ route('siswa.kelas.show', $t->mapel_id) }}" class="btn btn-sm btn-warning">Kerjakan</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-3 text-muted small">Tidak ada tugas aktif saat ini.</div>
            @endforelse
        </div>
    </div>
</div>

<h6 class="fw-bold mb-3">Mata Pelajaran Kelas {{ $kelasSiswa->nama_kelas ?? '' }}</h6>
<div class="row g-3">
    @forelse ($mapels as $mapel)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-1">{{ $mapel->nama_mapel }}</h6>
                    <p class="text-muted small mb-3">Guru: {{ $mapel->nama_guru }}</p>
                    <a href="{{ route('siswa.kelas.show', $mapel->id) }}" class="btn btn-sm btn-success w-100">Masuk Kelas</a>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">Belum ada mata pelajaran.</p>
    @endforelse
</div>
@endsection