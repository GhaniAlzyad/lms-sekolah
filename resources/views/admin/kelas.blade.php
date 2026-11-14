@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Data Kelas & Wali Kelas (MTs & MA)</h5>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">← Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Tambah Rombel / Kelas</div>
            <div class="card-body">
                <form action="{{ route('admin.kelas.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Unit Sekolah</label>
                        <select name="unit_id" class="form-select form-select-sm" required>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nama Kelas (Contoh: VII-B, XI-IPS-1)</label>
                        <input type="text" name="nama_kelas" class="form-control form-control-sm" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <label class="form-label small">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" class="form-control form-control-sm" value="2026/2027" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label small">Semester</label>
                            <select name="semester" class="form-select form-select-sm">
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Wali Kelas (Opsional)</label>
                        <select name="wali_kelas_id" class="form-select form-select-sm">
                            <option value="">-- Pilih Wali Kelas --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Simpan Kelas</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Unit</th>
                            <th>Kelas</th>
                            <th>Tahun / Sem</th>
                            <th>Wali Kelas</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kelas as $k)
                        <tr>
                            <td><span class="badge {{ $k->unit == 'MTs' ? 'bg-primary' : 'bg-info text-dark' }}">{{ $k->unit }}</span></td>
                            <td class="fw-bold">{{ $k->nama_kelas }}</td>
                            <td>{{ $k->tahun_ajaran }} ({{ $k->semester }})</td>
                            <td>{{ $k->wali_kelas ?? '-' }}</td>
                            <td class="text-end">
                                <button class="btn btn-xs btn-outline-warning py-0 px-2" data-bs-toggle="modal" data-bs-target="#editKelas{{ $k->id }}">Edit</button>
                                <form action="{{ route('admin.kelas.destroy', $k->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger py-0 px-2">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@foreach($kelas as $k)
<div class="modal fade" id="editKelas{{ $k->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form action="{{ route('admin.kelas.update', $k->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Edit Kelas</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small">Unit Sekolah</label>
                    <select name="unit_id" class="form-select form-select-sm" required>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $k->unit_id == $u->id ? 'selected' : '' }}>{{ $u->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Nama Kelas</label>
                    <input type="text" name="nama_kelas" class="form-control form-control-sm" value="{{ $k->nama_kelas }}" required>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-7">
                        <label class="form-label small">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-control form-control-sm" value="{{ $k->tahun_ajaran }}" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label small">Semester</label>
                        <select name="semester" class="form-select form-select-sm">
                            <option value="Ganjil" {{ $k->semester == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ $k->semester == 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Wali Kelas</label>
                    <select name="wali_kelas_id" class="form-select form-select-sm">
                        <option value="">-- Tanpa Wali Kelas --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}" {{ $k->wali_kelas_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection