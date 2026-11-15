@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <span class="badge bg-info text-dark mb-1">{{ $mapel->unit ?? '' }} • {{ $mapel->nama_kelas ?? '' }}</span>
        <h4 class="fw-bold mb-0">{{ $mapel->nama_mapel }}</h4>
        <div class="text-muted small">Guru Pengampu: {{ $mapel->nama_guru }}</div>
    </div>
    <a href="{{ route('siswa.dashboard') }}" class="btn btn-sm btn-outline-secondary">
        ← Dashboard
    </a>
</div>

@forelse($kontens as $k)
@php
    $now = \Carbon\Carbon::now('Asia/Jakarta');
    $deadline = $k->deadline ? \Carbon\Carbon::parse($k->deadline, 'Asia/Jakarta') : null;
    $waktuKumpul = $k->waktu_kumpul ? \Carbon\Carbon::parse($k->waktu_kumpul, 'Asia/Jakarta') : null;

    $lewatDeadline = $k->tipe === 'tugas' && $deadline && $now->gt($deadline);
    $kumpulTerlambat = $k->pengumpulan_id && $deadline && $waktuKumpul && $waktuKumpul->gt($deadline);
    $bolehKumpul = !$lewatDeadline || !empty($k->izin_susulan_id);
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        {{-- Baris 1: Lencana Tipe, Judul Utama (Highlight), & Status Pengumpulan --}}
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <div>
                <span class="badge {{ $k->tipe === 'tugas' ? 'bg-warning text-dark' : 'bg-info text-dark' }} mb-1">
                    {{ $k->tipe === 'tugas' ? '📝 Tugas' : '📘 Materi' }}
                </span>
                <h6 class="fw-bold text-dark fs-5 mb-0">{{ $k->judul }}</h6>
            </div>

            @if($k->tipe === 'tugas')
                <div class="text-end flex-shrink-0">
                    @if($k->pengumpulan_id)
                        <span class="badge bg-success">✅ Sudah Dikumpulkan</span>
                        @if($kumpulTerlambat)
                            <span class="badge bg-danger">⚠️ Terlambat</span>
                        @else
                            <span class="badge bg-info text-dark">⏱️ Tepat Waktu</span>
                        @endif

                        @if(!is_null($k->nilai))
                            <div class="mt-1">
                                <span class="badge bg-primary fs-6">Nilai: {{ $k->nilai }}</span>
                            </div>
                        @endif
                    @else
                        @if($lewatDeadline)
                            <span class="badge bg-danger">⚠️ Terlambat (Terkunci)</span>
                            @if($k->izin_susulan_id)
                                <div class="mt-1">
                                    <span class="badge bg-warning text-dark">🔓 Izin Susulan Aktif</span>
                                </div>
                            @endif
                        @else
                            <span class="badge bg-secondary">⏳ Belum Dikerjakan</span>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        {{-- Baris 2: Isi / Instruksi Materi & Tugas (Ter-highlight) --}}
        @if($k->deskripsi)
        <div class="bg-light rounded p-2 px-3 text-dark small mb-3 border-start border-3 {{ $k->tipe === 'tugas' ? 'border-warning' : 'border-info' }}" style="white-space: pre-line;">{{ $k->deskripsi }}</div>
        @endif

        {{-- Catatan Evaluasi dari Guru --}}
        @if($k->tipe === 'tugas' && !empty($k->catatan_guru))
        <div class="alert alert-info py-2 px-3 small mb-3">
            <strong>💬 Catatan Guru:</strong> {{ $k->catatan_guru }}
        </div>
        @endif

        {{-- Baris 3: Lampiran & Deadline --}}
        @if($k->link_lampiran || ($k->tipe === 'tugas' && $k->deadline))
        <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
            @if($k->link_lampiran)
            <a href="{{ $k->link_lampiran }}" target="_blank" class="btn btn-sm btn-outline-primary">
                📎 Buka Lampiran
            </a>
            @endif

            @if($k->tipe === 'tugas' && $k->deadline)
            <span class="small text-danger fw-semibold">
                ⏰ Deadline: {{ $deadline->format('d M Y, H:i') }} WIB
            </span>
            @endif
        </div>
        @endif

        {{-- Baris 4: Form Kumpul Tugas ATAU Pesan Terkunci --}}
        @if($k->tipe === 'tugas')
        <div class="mt-3 pt-3 border-top">
            @if($bolehKumpul)
                <form action="{{ route('siswa.tugas.kumpul', $k->id) }}" method="POST" class="row g-2 align-items-center">
                    @csrf
                    <div class="{{ $k->link_jawaban ? 'col-md-7' : 'col-md-9' }}">
                        <input type="url" name="link_jawaban" class="form-control form-control-sm"
                            placeholder="Tempel link Google Drive / Docs / YouTube / GitHub di sini..."
                            value="{{ $k->link_jawaban }}" required>
                    </div>
                    @if($k->link_jawaban)
                    <div class="col-md-2">
                        <a href="{{ $k->link_jawaban }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                            🔗 Cek Link
                        </a>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm {{ $k->pengumpulan_id ? 'btn-outline-success' : 'btn-success' }} w-100 fw-semibold">
                            {{ $k->pengumpulan_id ? '✏️ Update Jawaban' : '📤 Kirim Jawaban' }}
                        </button>
                    </div>
                </form>
            @else
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 {{ $k->pengumpulan_id ? 'bg-light text-dark border' : 'bg-danger-subtle text-danger' }} rounded p-2 px-3 small">
                    <div>
                        @if($k->pengumpulan_id)
                            <strong>🔒 Pengumpulan Selesai & Dikunci:</strong> Batas waktu telah berakhir, jawaban tidak dapat diubah lagi.
                        @else
                            <strong>🔒 Pengumpulan Ditutup:</strong> Batas waktu telah berakhir. Temui guru pengampu mata pelajaran untuk meminta izin susulan.
                        @endif
                    </div>
                    @if($k->link_jawaban)
                        <a href="{{ $k->link_jawaban }}" target="_blank" class="btn btn-sm btn-outline-primary flex-shrink-0">
                            🔗 Lihat Jawaban Terkirim
                        </a>
                    @endif
                </div>
            @endif
        </div>
        @endif
    </div>
</div>
@empty
<div class="alert alert-light border text-center text-muted py-4">
    Belum ada materi atau tugas yang dibagikan pada mata pelajaran ini.
</div>
@endforelse
@endsection