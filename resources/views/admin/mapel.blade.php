@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Plotting Mata Pelajaran & Guru Pengampu</h5>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">← Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Tambah Plotting Mapel</div>
            <div class="card-body">
                <form action="{{ route('admin.mapel.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Unit & Kelas</label>
                        <select name="kelas_id" class="form-select form-select-sm" required>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id }}">{{ $k->unit }} - {{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nama Mata Pelajaran</label>
                        <input type="text" name="nama_mapel" class="form-control form-control-sm" placeholder="Contoh: Koding & KA, Matematika" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Guru Pengampu</label>
                        <select name="guru_id" class="form-select form-select-sm" required>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-warning fw-semibold w-100">Simpan Mapel</button>
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
                            <th>Unit / Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru Pengampu</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mapels as $m)
                        <tr>
                            <td><span class="badge {{ $m->unit == 'MTs' ? 'bg-primary' : 'bg-info text-dark' }}">{{ $m->unit }} • {{ $m->nama_kelas }}</span></td>
                            <td class="fw-bold">{{ $m->nama_mapel }}</td>
                            <td>{{ $m->nama_guru }}</td>
                            <td class="text-end">
                                <button class="btn btn-xs btn-outline-warning py-0 px-2" data-bs-toggle="modal" data-bs-target="#editMapel{{ $m->id }}">Edit</button>
                                <form action="{{ route('admin.mapel.destroy', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus mapel ini?')">
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
@foreach($mapels as $m)
<div class="modal fade" id="editMapel{{ $m->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form action="{{ route('admin.mapel.update', $m->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Edit Mata Pelajaran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small">Unit & Kelas</label>
                    <select name="kelas_id" class="form-select form-select-sm" required>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $m->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->unit }} - {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" class="form-control form-control-sm" value="{{ $m->nama_mapel }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Guru Pengampu</label>
                    <select name="guru_id" class="form-select form-select-sm" required>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}" {{ $m->guru_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="submit" class="btn btn-sm btn-warning fw-semibold w-100">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection