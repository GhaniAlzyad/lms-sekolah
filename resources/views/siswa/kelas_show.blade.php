@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span class="badge {{ $mapel->unit == 'MTs' ? 'bg-primary' : 'bg-info text-dark' }}">{{ $mapel->unit }} • Kelas {{ $mapel->nama_kelas }}</span>
        <h5 class="fw-bold mb-0 mt-1">{{ $mapel->nama_mapel }}</h5>
        <small class="text-muted">Guru Pengampu: {{ $mapel->nama_guru }}</small>
    </div>
    <a href="{{ route('siswa.dashboard') }}" class="btn btn-sm btn-outline-secondary">← Dashboard</a>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
@endif

<div class="row">
    <div class="col-lg-9 mx-auto">
        @forelse($kontens as $k)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge {{ $k->tipe == 'materi' ? 'bg-primary' : 'bg-warning text-dark' }} text-uppercase">
                            {{ $k->tipe == 'materi' ? '📘 Materi' : '📝 Tugas' }}
                        </span>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($k->created_at)->format('d M Y') }}</small>
                    </div>

                    <h6 class="fw-bold mt-2">{{ $k->judul }}</h6>

                    @if($k->deskripsi)
                        <p class="small text-secondary mb-3">{!! nl2br(e($k->deskripsi)) !!}</p>
                    @endif

                    @if($k->link_lampiran)
                        <div class="mb-3">
                            <a href="{{ $k->link_lampiran }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                🔗 Buka Materi / Lampiran Guru
                            </a>
                        </div>
                    @endif

                    @if($k->tipe === 'tugas')
                        <div class="bg-light p-3 rounded border mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2 small">
                                <span class="text-danger fw-semibold">
                                    ⏰ Batas Waktu: {{ \Carbon\Carbon::parse($k->deadline)->format('d M Y, H:i') }}
                                </span>
                                @if($k->nilai !== null)
                                    <span class="badge bg-success fs-6">Nilai: {{ $k->nilai }} / 100</span>
                                @elseif($k->pengumpulan_id)
                                    <span class="badge bg-info text-dark">Sudah Dikumpulkan (Menunggu Dinilai)</span>
                                @else
                                    <span class="badge bg-secondary">Belum Dikumpulkan</span>
                                @endif
                            </div>

                            @if($k->catatan_guru)
                                <div class="alert alert-info py-1 px-2 small mb-2">
                                    <strong>Catatan Guru:</strong> {{ $k->catatan_guru }}
                                </div>
                            @endif

                            @if($k->nilai === null)
                                <form action="{{ route('siswa.tugas.kumpul', $k->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    <label class="form-label small fw-semibold mb-1">
                                        {{ $k->pengumpulan_id ? 'Perbarui Link Jawaban (Google Drive / Foto / Colab):' : 'Kumpulkan Link Jawaban (Google Drive / Foto / Colab):' }}
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="url" name="link_jawaban" class="form-control" value="{{ $k->link_jawaban }}" placeholder="https://drive.google.com/..." required>
                                        <button type="submit" class="btn btn-success fw-semibold">
                                            {{ $k->pengumpulan_id ? 'Update Jawaban' : 'Kirim Tugas' }}
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="small">
                                    Link jawabanmu: <a href="{{ $k->link_jawaban }}" target="_blank">{{ $k->link_jawaban }}</a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-4 small">
                    Belum ada materi atau tugas pada mata pelajaran ini.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection