@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <span class="badge bg-info text-dark mb-1">{{ $tugas->unit }} • {{ $tugas->nama_kelas }} • {{ $tugas->nama_mapel }}</span>
        <h4 class="fw-bold mb-1">Periksa Jawaban: {{ $tugas->judul }}</h4>
        @if($tugas->deadline)
            <div class="small text-danger fw-semibold">
                ⏰ Deadline: {{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y, H:i') }} WIB
            </div>
        @endif
    </div>
    <a href="{{ route('guru.kelas.show', $tugas->mapel_id) }}" class="btn btn-sm btn-outline-secondary text-nowrap">
        ← Kembali ke Kelas
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 45px;">No</th>
                    <th>NIS / Nama Siswa</th>
                    <th class="text-nowrap">Status & Waktu Kumpul</th>
                    <th class="text-nowrap">Link Jawaban</th>
                    <th style="min-width: 320px;">Penilaian & Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daftarSiswa as $index => $s)
                @php
                    $now = \Carbon\Carbon::now('Asia/Jakarta');
                    $deadline = $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline, 'Asia/Jakarta') : null;
                    $waktuKumpul = $s->waktu_kumpul ? \Carbon\Carbon::parse($s->waktu_kumpul, 'Asia/Jakarta') : null;

                    $lewatDeadline = $deadline && $now->gt($deadline);
                    $kumpulTerlambat = $s->pengumpulan_id && $deadline && $waktuKumpul && $waktuKumpul->gt($deadline);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-semibold">{{ $s->name }}</div>
                        <small class="text-muted">{{ $s->nomor_induk }}</small>
                    </td>
                    <td class="text-nowrap">
                        @if($s->pengumpulan_id)
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-success">✅ Sudah Kumpul</span>
                                @if($kumpulTerlambat)
                                    <span class="badge bg-danger">⚠️ Terlambat</span>
                                @else
                                    <span class="badge bg-info text-dark">⏱️ Tepat Waktu</span>
                                @endif
                            </div>
                            <div class="small text-muted mt-1">
                                {{ $waktuKumpul->format('d M Y, H:i') }} WIB
                            </div>
                        @else
                            <span class="badge bg-secondary">⏳ Belum Kumpul</span>
                            @if($lewatDeadline)
                                <div class="mt-2">
                                    <form action="{{ route('guru.tugas.izin_susulan', [$tugas->id, $s->siswa_id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $s->izin_susulan_id ? 'btn-warning text-dark fw-semibold' : 'btn-outline-secondary' }}">
                                            {{ $s->izin_susulan_id ? '🔓 Izin Susulan Aktif' : '🔒 Beri Izin Susulan' }}
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endif
                    </td>
                    <td class="text-nowrap">
                        @if($s->link_jawaban)
                            <a href="{{ $s->link_jawaban }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                🔗 Buka Jawaban
                            </a>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        @if($s->pengumpulan_id)
                            <form action="{{ route('guru.tugas.simpan_nilai', $s->pengumpulan_id) }}" method="POST" class="d-flex gap-1">
                                @csrf
                                <input type="number" name="nilai" class="form-control form-control-sm" style="width: 75px;"
                                    min="0" max="100" placeholder="Nilai" value="{{ $s->nilai }}" required>
                                <input type="text" name="catatan_guru" class="form-control form-control-sm"
                                    placeholder="Catatan (opsional)" value="{{ $s->catatan_guru }}">
                                <button type="submit" class="btn btn-sm btn-success">Simpan</button>
                            </form>
                        @else
                            <span class="text-muted fst-italic small">Menunggu jawaban siswa</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection