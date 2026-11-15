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
    <a href="{{ route('guru.tugas.export', $tugas->id) }}" class="btn btn-sm btn-success text-nowrap fw-semibold">
            📥 Export Nilai (CSV)
        </a>
    <a href="{{ route('guru.kelas.show', $tugas->mapel_id) }}" class="btn btn-sm btn-outline-secondary text-nowrap">
        ← Kembali ke Kelas
    </a>
</div>

@php
    $deadlineTugas = $tugas->deadline ? \Carbon\Carbon::parse($tugas->deadline, 'Asia/Jakarta') : null;
    $totalSiswa = $daftarSiswa->count();
    $tepatWaktu = 0;
    $terlambat = 0;
    $belumKumpul = 0;
    $sudahDinilai = 0;

    foreach ($daftarSiswa as $item) {
        if ($item->pengumpulan_id) {
            $wk = $item->waktu_kumpul ? \Carbon\Carbon::parse($item->waktu_kumpul, 'Asia/Jakarta') : null;
            if ($deadlineTugas && $wk && $wk->gt($deadlineTugas)) {
                $terlambat++;
            } else {
                $tepatWaktu++;
            }
            if (!is_null($item->nilai)) {
                $sudahDinilai++;
            }
        } else {
            $belumKumpul++;
        }
    }
@endphp

{{-- Baris Statistik Ringkas --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md">
        <div class="bg-white border rounded p-2 px-3 text-center shadow-sm">
            <div class="small text-muted">Total Siswa</div>
            <div class="fw-bold fs-5 text-dark">{{ $totalSiswa }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="bg-white border rounded p-2 px-3 text-center shadow-sm">
            <div class="small text-muted">⏱️ Tepat Waktu</div>
            <div class="fw-bold fs-5 text-success">{{ $tepatWaktu }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="bg-white border rounded p-2 px-3 text-center shadow-sm">
            <div class="small text-muted">⚠️ Terlambat</div>
            <div class="fw-bold fs-5 text-danger">{{ $terlambat }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="bg-white border rounded p-2 px-3 text-center shadow-sm">
            <div class="small text-muted">⏳ Belum Kumpul</div>
            <div class="fw-bold fs-5 text-secondary">{{ $belumKumpul }}</div>
        </div>
    </div>
    <div class="col-12 col-md">
        <div class="bg-white border rounded p-2 px-3 text-center shadow-sm">
            <div class="small text-muted">✅ Sudah Dinilai</div>
            <div class="fw-bold fs-5 text-primary">{{ $sudahDinilai }} / {{ $tepatWaktu + $terlambat }}</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 45px;">No</th>
                    <th>NIS / Nama Siswa</th>
                    <th class="text-nowrap">Status & Waktu Kumpul</th>
                    <th class="text-nowrap">Jawaban Siswa</th>
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
                    <td>
                        @if(($s->link_jawaban ?? null) || ($s->teks_jawaban ?? null))
                            <div class="d-flex flex-wrap gap-1">
                                @if($s->link_jawaban && preg_match('~^https?://~i', $s->link_jawaban))
    <a href="{{ $s->link_jawaban }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary text-nowrap">
        🔗 Buka Link
    </a>
@endif

                                @if($s->teks_jawaban)
                                    <button type="button" class="btn btn-sm btn-outline-dark text-nowrap" data-bs-toggle="modal" data-bs-target="#modalJawaban{{ $s->siswa_id }}">
                                        📝 Baca Teks
                                    </button>

                                    {{-- Modal Baca Jawaban Teks --}}
                                    <div class="modal fade" id="modalJawaban{{ $s->siswa_id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Jawaban Teks: {{ $s->name }} ({{ $s->nomor_induk }})</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body bg-light">
                                                    <div class="bg-white p-3 rounded border text-dark" style="white-space: pre-line;">{{ $s->teks_jawaban }}</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
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