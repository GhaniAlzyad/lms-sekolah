@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span class="badge {{ $mapel->unit == 'MTs' ? 'bg-primary' : 'bg-info text-dark' }}">{{ $mapel->unit }} • Kelas {{ $mapel->nama_kelas }}</span>
        <h5 class="fw-bold mb-0 mt-1">Presensi: {{ $mapel->nama_mapel }}</h5>
    </div>
    <a href="{{ route('guru.dashboard') }}" class="btn btn-sm btn-outline-secondary">← Dashboard</a>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <form action="{{ route('guru.presensi.index', $mapel->id) }}" method="GET" class="d-flex align-items-center gap-2">
            <label class="small fw-semibold mb-0">Tanggal Pertemuan:</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control form-control-sm" onchange="this.form.submit()">
        </form>
        <span class="small text-muted">Menampilkan presensi tanggal: <strong>{{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</strong></span>
    </div>
</div>

<form action="{{ route('guru.presensi.store', $mapel->id) }}" method="POST">
    @csrf
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th style="width: 280px;">Status Kehadiran</th>
                        <th>Keterangan (Opsional)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $index => $s)
                        @php $currentStatus = $s->status ?? 'Hadir'; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $s->nomor_induk }}</td>
                            <td class="fw-semibold">{{ $s->name }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @foreach(['Hadir' => 'success', 'Izin' => 'info', 'Sakit' => 'warning', 'Alpa' => 'danger'] as $st => $color)
                                        <input type="radio" class="btn-check" name="status[{{ $s->siswa_id }}]" id="st_{{ $s->siswa_id }}_{{ $st }}" value="{{ $st }}" {{ $currentStatus === $st ? 'checked' : '' }}>
                                        <label class="btn btn-outline-{{ $color }}" for="st_{{ $s->siswa_id }}_{{ $st }}">{{ $st }}</label>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <input type="text" name="keterangan[{{ $s->siswa_id }}]" value="{{ $s->keterangan }}" class="form-control form-control-sm" placeholder="Catatan...">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada siswa di kelas ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(count($siswas) > 0)
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-sm btn-success fw-semibold px-4">Simpan Presensi</button>
            </div>
        @endif
    </div>
</form>
@endsection