@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
        <span class="badge bg-info text-dark mb-1">{{ $mapel->unit }} • {{ $mapel->nama_kelas }}</span>
        <h4 class="fw-bold mb-0">{{ $mapel->nama_mapel }}</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('guru.presensi.index', $mapel->id) }}" class="btn btn-sm btn-outline-primary">
            📋 Presensi Kelas
        </a>
        <a href="{{ route('guru.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            ← Dashboard
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
@endif

<div class="row g-4" x-data="{ tipeKonten: 'materi' }">
    <!-- Form Tambah Materi / Tugas -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Buat Materi / Tugas Baru</div>
            <div class="card-body">
                <form action="{{ route('guru.konten.store', $mapel->id) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Jenis Postingan</label>
                        <select name="tipe" x-model="tipeKonten" class="form-select form-select-sm">
                            <option value="materi">📘 Materi Pembelajaran</option>
                            <option value="tugas">📝 Tugas / Latihan</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Judul</label>
                        <input type="text" name="judul" class="form-control form-control-sm" placeholder="Contoh: Pertemuan 1 - Logika Dasar" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Instruksi / Ringkasan</label>
                        <textarea name="deskripsi" rows="3" class="form-control form-control-sm" placeholder="Tulis penjelasan atau soal di sini..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Link Lampiran (Drive / YouTube / Colab)</label>
                        <input type="url" name="link_lampiran" class="form-control form-control-sm" placeholder="https://...">
                    </div>
                    <div class="mb-3" x-show="tipeKonten === 'tugas'">
                        <label class="form-label small fw-semibold text-danger">Batas Waktu (Deadline)</label>
                        <input type="datetime-local" name="deadline" class="form-control form-control-sm">
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100 fw-semibold">Publikasikan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Materi & Tugas yang Sudah Diposting -->
    <div class="col-md-8">
        @forelse($kontens as $k)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                {{-- Baris 1: Lencana Tipe, Judul Utama (Highlight), & Tombol Edit/Hapus di Kanan Atas --}}
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <span class="badge {{ $k->tipe === 'tugas' ? 'bg-warning text-dark' : 'bg-info text-dark' }} mb-1">
                            {{ $k->tipe === 'tugas' ? '📝 Tugas' : '📘 Materi' }}
                        </span>
                        <h6 class="fw-bold text-dark fs-5 mb-0">{{ $k->judul }}</h6>
                    </div>

                    <div class="d-flex gap-1 flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2" data-bs-toggle="modal" data-bs-target="#editKonten{{ $k->id }}">
                            Edit
                        </button>
                        <form action="{{ route('guru.konten.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus postingan ini beserta seluruh data terkait?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Hapus</button>
                        </form>
                    </div>
                </div>

                {{-- Baris 2: Isi / Instruksi Tugas (Ter-highlight dengan latar lembut) --}}
                @if($k->deskripsi)
                <div class="bg-light rounded p-2 px-3 text-dark small mb-3 border-start border-3 {{ $k->tipe === 'tugas' ? 'border-warning' : 'border-info' }}" style="white-space: pre-line;">{{ $k->deskripsi }}</div>
                @endif

                {{-- Baris 3: Footer Kartu (Lampiran, Deadline, & Tombol Periksa Jawaban) --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if($k->link_lampiran)
                        <a href="{{ $k->link_lampiran }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            📎 Buka Lampiran
                        </a>
                        @endif

                        @if($k->tipe === 'tugas' && $k->deadline)
                        <span class="small text-danger fw-semibold">
                            ⏰ Deadline: {{ \Carbon\Carbon::parse($k->deadline)->format('d M Y, H:i') }}
                        </span>
                        @endif
                    </div>

                    @if($k->tipe === 'tugas')
                    <a href="{{ route('guru.tugas.nilai', $k->id) }}" class="btn btn-sm btn-success fw-semibold">
                        Periksa Jawaban ({{ $k->jumlah_kumpul ?? 0 }} Masuk
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty

        <div class="alert alert-light border text-center text-muted py-4">
            Belum ada materi atau tugas yang dibagikan di kelas ini.
        </div>
    @endforelse
</div>
</div>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@foreach($kontens as $k)
<div class="modal fade" id="editKonten{{ $k->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('guru.konten.update', $k->id) }}" method="POST" class="modal-content" x-data="{ tipeEdit: '{{ $k->tipe }}' }">
            @csrf @method('PUT')
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Edit Postingan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Jenis Postingan</label>
                    <select name="tipe" x-model="tipeEdit" class="form-select form-select-sm">
                        <option value="materi">📘 Materi Pembelajaran</option>
                        <option value="tugas">📝 Tugas / Latihan</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Judul</label>
                    <input type="text" name="judul" class="form-control form-control-sm" value="{{ $k->judul }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Instruksi / Ringkasan</label>
                    <textarea name="deskripsi" rows="3" class="form-control form-control-sm">{{ $k->deskripsi }}</textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Link Lampiran</label>
                    <input type="url" name="link_lampiran" class="form-control form-control-sm" value="{{ $k->link_lampiran }}">
                </div>
                <div class="mb-2" x-show="tipeEdit === 'tugas'">
                    <label class="form-label small fw-semibold text-danger">Batas Waktu (Deadline)</label>
                    <input type="datetime-local" name="deadline" class="form-control form-control-sm"
                        value="{{ $k->deadline ? \Carbon\Carbon::parse($k->deadline)->format('Y-m-d\TH:i') : '' }}">
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="submit" class="btn btn-sm btn-success w-100">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection