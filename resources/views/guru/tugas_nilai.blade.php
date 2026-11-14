@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span class="badge bg-warning text-dark">{{ $tugas->unit }} • {{ $tugas->nama_kelas }} • {{ $tugas->nama_mapel }}</span>
        <h5 class="fw-bold mb-0 mt-1">Penilaian: {{ $tugas->judul }}</h5>
    </div>
    <a href="{{ route('guru.kelas.show', $tugas->mapel_id) }}" class="btn btn-sm btn-outline-secondary">← Kembali ke Kelas</a>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Status & Jawaban</th>
                    <th style="width: 320px;">Input Nilai (0–100) & Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daftarSiswa as $s)
                <tr>
                    <td>{{ $s->nomor_induk }}</td>
                    <td class="fw-semibold">{{ $s->name }}</td>
                    <td>
                        @if($s->pengumpulan_id)
                            <a href="{{ $s->link_jawaban }}" target="_blank" class="btn btn-xs btn-primary py-0 px-2">Lihat Jawaban</a>
                            @if($tugas->deadline && \Carbon\Carbon::parse($s->waktu_kumpul)->gt(\Carbon\Carbon::parse($tugas->deadline)))
                                <span class="badge bg-danger">Terlambat</span>
                            @endif
                            <div class="text-muted" style="font-size: 0.7rem;">
                                Dikirim: {{ \Carbon\Carbon::parse($s->waktu_kumpul)->format('d/m H:i') }}
                            </div>
                        @else
                            <span class="badge bg-secondary">Belum Mengumpulkan</span>
                        @endif
                    </td>
                    <td>
                        @if($s->pengumpulan_id)
                            <form action="{{ route('guru.tugas.simpan_nilai', $s->pengumpulan_id) }}" method="POST" class="d-flex gap-1">
                                @csrf
                                <input type="number" name="nilai" min="0" max="100" class="form-control form-control-sm" style="width: 75px;" value="{{ $s->nilai }}" placeholder="Nilai" required>
                                <input type="text" name="catatan_guru" class="form-control form-control-sm" value="{{ $s->catatan_guru }}" placeholder="Catatan...">
                                <button type="submit" class="btn btn-sm btn-success">Simpan</button>
                            </form>
                        @else
                            <span class="text-muted fst-italic">Menunggu jawaban siswa</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection