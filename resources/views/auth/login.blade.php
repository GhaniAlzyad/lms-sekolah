@extends('layouts.app')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 85vh;">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-success">E-Learning ARROJA</h4>
                    <p class="text-muted small">Portal Pembelajaran MTs & MA</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">NISN / NUPTK / Username</label>
                        <input type="text" name="nomor_induk" class="form-control" value="{{ old('nomor_induk') }}" placeholder="Masukkan Nomor Induk" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-semibold">Masuk ke Kelas</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection