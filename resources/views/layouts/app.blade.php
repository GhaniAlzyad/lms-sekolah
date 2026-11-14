<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning ARROJA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @auth
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">LMS ARROJA</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 small">
                    {{ Auth::user()->name }} 
                    <span class="badge bg-warning text-dark text-uppercase">{{ Auth::user()->role }}</span>
                </span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" class="btn btn-sm btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#modalGantiPassword">🔑 Password</button>
                    <button type="submit" class="btn btn-sm btn-outline-light">Keluar</button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    <div class="container">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @auth
<div class="modal fade" id="modalGantiPassword" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form action="{{ route('password.change') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Ganti Password</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small">Password Lama</label>
                    <input type="password" name="current_password" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Password Baru (Min. 6 Karakter)</label>
                    <input type="password" name="new_password" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Ulangi Password Baru</label>
                    <input type="password" name="new_password_confirmation" class="form-control form-control-sm" required>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="submit" class="btn btn-sm btn-success w-100">Simpan Password</button>
            </div>
        </form>
    </div>
</div>
@endauth
</body>
</html>