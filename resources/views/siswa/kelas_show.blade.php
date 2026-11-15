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

{{-- Notifikasi Validasi Input --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
        <div class="fw-bold mb-1">⚠️ Gagal mengirim jawaban:</div>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->unique() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@forelse($kontens as $k)
@php
    $now = \Carbon\Carbon::now('Asia/Jakarta');
    $deadline = $k->deadline ? \Carbon\Carbon::parse($k->deadline, 'Asia/Jakarta') : null;
    $waktuKumpul = $k->waktu_kumpul ? \Carbon\Carbon::parse($k->waktu_kumpul, 'Asia/Jakarta') : null;

    $lewatDeadline = $deadline && $now->gt($deadline);
    $kumpulTerlambat = $k->pengumpulan_id && $deadline && $waktuKumpul && $waktuKumpul->gt($deadline);
    $sudahDinilai = !is_null($k->nilai);
    $bolehKumpul = !$sudahDinilai && (!$lewatDeadline || !is_null($k->izin_susulan_id));
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
                            @if($k->izin_susulan_id)
                                <span class="badge bg-warning text-dark">🔓 Izin Susulan Aktif (Terlambat)</span>
                            @else
                                <span class="badge bg-danger">⚠️ Terlambat (Terkunci)</span>
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
                @if($k->link_lampiran && preg_match('~^https?://~i', $k->link_lampiran))
                    <a href="{{ $k->link_lampiran }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
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

        {{-- Baris 4: Form Kumpul Tugas (Collapsible) ATAU Pesan Terkunci --}}
        @if($k->tipe === 'tugas')
            <div class="mt-3 pt-3 border-top">
                @if($bolehKumpul)
                    @php
                        $sedangErrorIni = $errors->any() && old('tugas_id') == $k->id;
                    @endphp

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <button class="btn btn-sm {{ $k->pengumpulan_id ? 'btn-outline-success' : 'btn-primary' }} fw-semibold"
                                type="button" data-bs-toggle="collapse" data-bs-target="#formTugas{{ $k->id }}"
                                aria-expanded="{{ $sedangErrorIni ? 'true' : 'false' }}" aria-controls="formTugas{{ $k->id }}">
                            {{ $k->pengumpulan_id ? '✏️ Lihat / Edit Jawaban' : '✍️ Kerjakan & Kumpulkan Tugas' }}
                        </button>

                        @if($k->link_jawaban && preg_match('~^https?://~i', $k->link_jawaban))
                            <a href="{{ $k->link_jawaban }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                                🔗 Cek Link Terkirim
                            </a>
                        @endif
                    </div>

                    {{-- Area Form yang Bisa Buka-Tutup (Hanya terbuka otomatis jika tugas ini yang error) --}}
                    <div class="collapse {{ $sedangErrorIni ? 'show' : '' }} mt-3" id="formTugas{{ $k->id }}">
                        <div class="bg-light rounded p-3 border">
                            <form action="{{ route('siswa.tugas.kumpul', $k->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="tugas_id" value="{{ $k->id }}">

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-muted mb-1">🔗 Link Jawaban (Google Drive / Docs / YouTube / GitHub)</label>
                                    <input type="text" name="link_jawaban" class="form-control form-control-sm {{ $sedangErrorIni && $errors->has('link_jawaban') ? 'is-invalid' : '' }}"
                                           placeholder="https://drive.google.com/... (kosongkan jika hanya mengetik jawaban di bawah)"
                                           value="{{ $sedangErrorIni ? old('link_jawaban') : $k->link_jawaban }}">
                                    @if($sedangErrorIni && $errors->has('link_jawaban'))
                                        <div class="invalid-feedback">{{ $errors->first('link_jawaban') }}</div>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-muted mb-1">📝 Atau Ketik Jawaban Langsung</label>
                                    <textarea name="teks_jawaban" rows="3" class="form-control form-control-sm {{ $sedangErrorIni && $errors->has('teks_jawaban') ? 'is-invalid' : '' }}"
                                              placeholder="Tulis jawaban esai, ringkasan, atau potongan kode program di sini...">{{ $sedangErrorIni ? old('teks_jawaban') : $k->teks_jawaban }}</textarea>
                                    @if($sedangErrorIni && $errors->has('teks_jawaban'))
                                        <div class="invalid-feedback">{{ $errors->first('teks_jawaban') }}</div>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">*Isi link, ketik jawaban, atau keduanya.</small>
                                    <button type="submit" class="btn btn-sm btn-success fw-semibold px-3">
                                        📤 Kirim Jawaban
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="{{ $k->pengumpulan_id ? 'bg-light text-dark border' : 'bg-danger-subtle text-danger' }} rounded p-2 px-3 small">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                @if($sudahDinilai)
                                    <strong>🔒 Tugas Sudah Dinilai Guru:</strong> Jawaban dikunci permanen dan tidak dapat diubah lagi.
                                @elseif($k->pengumpulan_id)
                                    <strong>🔒 Pengumpulan Selesai & Dikunci:</strong> Batas waktu telah berakhir, jawaban tidak dapat diubah lagi.
                                @else
                                    <strong>🔒 Pengumpulan Ditutup:</strong> Batas waktu telah berakhir. Temui guru pengampu mata pelajaran untuk meminta izin susulan.
                                @endif
                            </div>
                            @if($k->link_jawaban && preg_match('~^https?://~i', $k->link_jawaban))
                                <a href="{{ $k->link_jawaban }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    🔗 Lihat Link Jawaban
                                </a>
                            @endif
                        </div>

                        @if($k->teks_jawaban)
                            <div class="mt-2 pt-2 border-top">
                                <div class="fw-semibold text-muted mb-1">📝 Jawaban Teks Terkirim:</div>
                                <div class="bg-white rounded p-2 border text-dark" style="white-space: pre-line;">{{ $k->teks_jawaban }}</div>
                            </div>
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