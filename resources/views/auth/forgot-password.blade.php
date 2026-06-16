<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Techub</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css?v=3') }}">
    <!-- Custom Theme JS (in head to prevent flash) -->
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <style>
        body {
            background-color: var(--bs-body-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                <div class="text-center mb-4">
                    <a href="{{ url('/') }}" class="text-decoration-none d-inline-flex align-items-center fw-bold fs-3 mb-2">
                        <img src="{{ asset('Techub-Logo.png') }}" alt="Techub Logo" height="64" class="me-2">
                    </a>
                    <p class="text-muted-custom">Sistem Manajemen Booking Labkom</p>
                </div>

                <div class="card border-0 shadow-sm interactive-card p-4">
                    <h5 class="fw-bold mb-3 text-center">Lupa Password</h5>
                    
                    <div class="mb-4 text-sm text-muted-custom small">
                        Lupa password Anda? Tidak masalah. Masukkan alamat email Anda di bawah ini, dan sistem akan membuatkan password baru secara otomatis lalu mengirimkannya ke email Anda.
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4 text-success small fw-medium text-center" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" autocomplete="off">
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label fw-medium">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Reset Password</button>
                    </form>

                    <div class="text-center mt-2">
                        <a href="{{ route('login') }}" class="text-primary small text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                        </a>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button id="theme-toggle" class="btn btn-link text-body p-0 small text-decoration-none me-3">
                        <i id="theme-icon" class="bi bi-sun-fill me-1"></i> Ganti Tema
                    </button>
                    <a href="{{ url('/') }}" class="text-muted-custom small text-decoration-none">
                        <i class="bi bi-house me-1"></i> Halaman Utama
                    </a>
                </div>

            </div>
        </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
