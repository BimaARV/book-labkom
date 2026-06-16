<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Techub</title>
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
                    <h5 class="fw-bold mb-4 text-center">Login Admin</h5>
                    
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4 text-success" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" required autofocus autocomplete="off">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-medium mb-0">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary">Lupa Password?</a>
                                @endif
                            </div>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label text-muted-custom small" for="remember">Ingat Saya</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">Log in</button>
                    </form>

                    <div class="text-center mt-3">
                        <button id="theme-toggle" class="btn btn-link text-body p-0 small text-decoration-none">
                            <i id="theme-icon" class="bi bi-sun-fill me-1"></i> Ganti Tema
                        </button>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ url('/') }}" class="text-muted-custom small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Utama
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('force-password-success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('force-password-success') }}",
                icon: 'success',
                confirmButtonColor: '#002B5C',
                confirmButtonText: 'Login Sekarang'
            });
            @endif
        });
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
