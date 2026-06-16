<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Labkom Booking System | Techub')</title>
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
    <!-- Custom Theme JS -->
    <script src="{{ asset('assets/js/theme.js') }}"></script>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold" href="{{ url('/') }}">
                <img src="{{ asset('Techub-Logo.png') }}" alt="Techub Logo" height="40" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-lg-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active fw-medium' : '' }}" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('check') ? 'active' : '' }}" href="{{ route('check') }}">Cek Ketersediaan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('booking.list') || request()->routeIs('booking.search') ? 'active' : '' }}" href="{{ route('booking.list') }}">Cek Booking Saya</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <button id="theme-toggle" class="btn btn-link text-body p-0 me-3" title="Toggle Theme">
                        <i id="theme-icon" class="bi bi-sun-fill fs-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="py-4 text-center mt-auto border-top">
        <div class="container text-muted-custom">
            <p class="mb-1">&copy; {{ date('Y') }} Techub PT Binawan Inti Teknologi</p>
            <p class="mb-0 small">Developed by IT Infrastructure Team</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
