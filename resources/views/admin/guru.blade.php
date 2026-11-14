@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Manajemen Data Guru ARROJA</h5>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">← Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Tambah Guru Baru</div>
            <div class="card-body">
                <form action="{{ route('admin.guru.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">NUPTK / NIP / Username Login</label>
                        <input type="text" name="nomor_induk" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nama Lengkap & Gelar</label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Password Awal</label>
                        <input type="text" name="password" class="form-control form-control-sm" value="password123" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100">Simpan Guru</button>
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
                            <th>NUPTK / ID</th>
                            <th>Nama Guru</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gurus as $g)
                        <tr>
                            <td>{{ $g->nomor_induk }}</td>
                            <td class="fw-semibold">{{ $g->name }}</td>
                            <td class="text-end">
                                <button class="btn btn-xs btn-outline-warning py-0 px-2" data-bs-toggle="modal" data-bs-target="#editGuru{{ $g->id }}">Edit</button>
                                <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus guru ini?')">
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
@foreach($gurus as $g)
<div class="modal fade" id="editGuru{{ $g->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form action="{{ route('admin.guru.update', $g->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Edit Data Guru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small">NUPTK / ID</label>
                    <input type="text" name="nomor_induk" class="form-control form-control-sm" value="{{ $g->nomor_induk }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="{{ $g->name }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Password Baru (Kosongkan jika tetap)</label>
                    <input type="text" name="password" class="form-control form-control-sm" placeholder="••••••••">
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