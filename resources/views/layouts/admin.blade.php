<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard | Techub')</title>
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js DataLabels Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top border-bottom">
        <div class="container-fluid px-3 px-md-4">
            <button class="navbar-toggler border-0 me-2 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                <i class="bi bi-list fs-3"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center fw-bold" href="{{ route('dashboard') }}">
                <img src="{{ asset('Techub-Logo.png') }}" alt="Techub Logo" height="40" class="me-2">
            </a>
            
            <div class="d-flex align-items-center ms-auto">
                <button id="theme-toggle" class="btn btn-link text-body p-0 me-4" title="Toggle Theme">
                    <i id="theme-icon" class="bi bi-sun-fill fs-5"></i>
                </button>
                
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle border-0 d-flex align-items-center bg-transparent" type="button" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=0A4B91&color=fff" alt="Admin" class="rounded-circle me-2" width="32" height="32">
                        <span class="d-none d-md-inline text-body fw-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2 text-muted-custom"></i> Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid flex-grow-1 d-flex p-0">
        <!-- Sidebar -->
        <div class="sidebar offcanvas-lg offcanvas-start p-3" tabindex="-1" id="sidebarMenu" style="width: 250px; background-color: var(--card-bg); border-right: 1px solid var(--border-color);">
            <div class="offcanvas-header d-lg-none border-bottom mb-3">
                <h5 class="offcanvas-title fw-bold">Menu Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-0">
                <ul class="nav flex-column gap-1 w-100">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.index') ? 'active' : '' }}" href="{{ route('admin.statistics.index') }}"><i class="bi bi-bar-chart-line me-2"></i> Statistik Penggunaan</a>
                </li>
                
                <li class="nav-item mt-3 mb-1">
                    <span class="text-muted-custom text-uppercase small fw-bold px-3">Booking Management</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ url('/admin/bookings') }}">
                        <i class="bi bi-calendar-check me-2"></i> Semua Booking 
                        @php $pendingCount = \App\Models\Booking::where('status', 'pending')->count(); @endphp
                        <span id="pending-booking-badge" class="badge bg-danger rounded-pill float-end {{ $pendingCount > 0 ? '' : 'd-none' }}">{{ $pendingCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.change-requests.*') ? 'active' : '' }}" href="{{ route('admin.change-requests.index') }}">
                        <i class="bi bi-arrow-left-right me-2"></i> Permintaan Perubahan 
                        @php $pendingChangeCount = \App\Models\BookingChangeRequest::where('status', 'pending')->count(); @endphp
                        <span id="pending-change-badge" class="badge bg-danger rounded-pill float-end {{ $pendingChangeCount > 0 ? '' : 'd-none' }}">{{ $pendingChangeCount }}</span>
                    </a>
                </li>
                
                <li class="nav-item mt-3 mb-1">
                    <span class="text-muted-custom text-uppercase small fw-bold px-3">Master Data</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.laboratories.*') ? 'active' : '' }}" href="{{ url('/admin/laboratories') }}"><i class="bi bi-pc-display-horizontal me-2"></i> Data Labkom</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.lab-mappings.*') ? 'active' : '' }}" href="{{ route('admin.lab-mappings.index') }}"><i class="bi bi-grid-3x3-gap me-2"></i> Pemetaan Labkom</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pc-damages.*') ? 'active' : '' }}" href="{{ route('admin.pc-damages.index') }}"><i class="bi bi-tools me-2"></i> Data Kerusakan PC</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.business-units.*') ? 'active' : '' }}" href="{{ url('/admin/business-units') }}"><i class="bi bi-building me-2"></i> Unit Bisnis</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ url('/admin/users') }}"><i class="bi bi-people me-2"></i> Manajemen Admin</a>
                </li>

                <li class="nav-item mt-3 mb-1">
                    <span class="text-muted-custom text-uppercase small fw-bold px-3">Pengaturan</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.smtp') ? 'active' : '' }}" href="{{ url('/admin/settings/smtp') }}"><i class="bi bi-envelope-at me-2"></i> SMTP Email</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.restricted-emails.*') ? 'active' : '' }}" href="{{ route('admin.restricted-emails.index') }}"><i class="bi bi-shield-lock me-2"></i> Restrict Email</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.whatsapp') ? 'active' : '' }}" href="{{ url('/admin/settings/whatsapp') }}"><i class="bi bi-whatsapp me-2"></i> WhatsApp Gateway</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.domain') ? 'active' : '' }}" href="{{ url('/admin/settings/domain') }}"><i class="bi bi-globe me-2"></i> Domain Settings</a>
                </li>
                </ul>
            </div>
        </div>

        <!-- Content -->
        <div class="p-3 p-md-4 flex-grow-1" style="max-width: 100%; overflow-x: hidden;">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Modal Hapus Global -->
    <div class="modal fade" id="globalDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="globalDeleteConfirmBtn">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentLatestId = {{ \App\Models\Booking::max('id') ?? 0 }};
            
            setInterval(() => {
                fetch('{{ route("admin.bookings.check-new") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Check if there is a new booking. 
                    if (data.latest_id > currentLatestId) {
                        currentLatestId = data.latest_id;
                        
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: true,
                            confirmButtonText: 'Lihat',
                            confirmButtonColor: '#002B5C',
                            showCancelButton: true,
                            cancelButtonText: 'Tutup',
                            timer: 15000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: 'info',
                            title: 'Peminjaman Baru!',
                            text: 'Ada pengajuan peminjaman Labkom baru yang menunggu persetujuan.'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('admin.bookings.index') }}";
                            }
                        });
                        
                        try {
                            let audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                            audio.volume = 0.5;
                            audio.play();
                        } catch(e) {}
                    }
                    
                    let badge = document.getElementById('pending-booking-badge');
                    if (badge) {
                        if (data.pending_count > 0) {
                            badge.textContent = data.pending_count;
                            badge.classList.remove('d-none');
                        } else {
                            badge.classList.add('d-none');
                        }
                    }
                })
                .catch(error => console.error('Error checking new bookings:', error));
            }, 10000);
        });
    </script>
    
    <!-- SweetAlert Global Delete Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let currentDeleteForm = null;
            const globalDeleteModalEl = document.getElementById('globalDeleteModal');
            let globalDeleteModal = null;
            
            if (globalDeleteModalEl) {
                globalDeleteModal = new bootstrap.Modal(globalDeleteModalEl);
                document.getElementById('globalDeleteConfirmBtn').addEventListener('click', function() {
                    if (currentDeleteForm) {
                        currentDeleteForm.submit();
                    }
                });
            }

            const deleteForms = document.querySelectorAll('form.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (globalDeleteModal) {
                        currentDeleteForm = form;
                        globalDeleteModal.show();
                    } else {
                        if (confirm('Yakin ingin menghapus data ini?')) form.submit();
                    }
                });
            });

            @if(auth()->check() && auth()->user()->must_change_password)
            Swal.fire({
                title: 'Wajib Ganti Password',
                html: `
                    @if($errors->any())
                        <div class="alert alert-danger small text-start">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="text-start mb-3">
                        Anda saat ini menggunakan password acak. Demi keamanan, Anda wajib mengganti password sebelum melanjutkan.
                    </div>
                    <form id="force-password-form" method="POST" action="{{ route('profile.force-change-password') }}">
                        @csrf
                        <div class="mb-3 text-start">
                            <label class="form-label fw-medium">Password Baru</label>
                            <input type="password" name="password" id="swal-password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label fw-medium">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" id="swal-password-confirm" class="form-control" required minlength="8">
                        </div>
                    </form>
                `,
                icon: 'warning',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: false,
                confirmButtonText: 'Simpan Password Baru',
                confirmButtonColor: '#002B5C',
                preConfirm: () => {
                    const password = document.getElementById('swal-password').value;
                    const confirm = document.getElementById('swal-password-confirm').value;
                    
                    if (!password || !confirm) {
                        Swal.showValidationMessage('Semua kolom harus diisi');
                        return false;
                    }
                    if (password.length < 8) {
                        Swal.showValidationMessage('Password minimal 8 karakter');
                        return false;
                    }
                    if (password !== confirm) {
                        Swal.showValidationMessage('Konfirmasi password tidak cocok');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('force-password-form').submit();
                }
            });
            @endif

            @if(session('admin_added_success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('admin_added_success') }}",
                icon: 'success',
                confirmButtonColor: '#002B5C',
                confirmButtonText: 'Tutup'
            });
            @endif

            @if(session('admin_added_success_notif'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('admin_added_success_notif') }}",
                icon: 'success',
                confirmButtonColor: '#002B5C',
                confirmButtonText: 'Tutup'
            });
            @endif

            @if(session('admin_updated_success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('admin_updated_success') }}",
                icon: 'success',
                confirmButtonColor: '#002B5C',
                confirmButtonText: 'Tutup'
            });
            @endif

            @if(session('admin_updated_success_notif'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('admin_updated_success_notif') }}",
                icon: 'success',
                confirmButtonColor: '#002B5C',
                confirmButtonText: 'Tutup'
            });
            @endif

            @if(session('info'))
            Swal.fire({
                title: 'Info',
                text: "{{ session('info') }}",
                icon: 'info',
                confirmButtonColor: '#002B5C',
                confirmButtonText: 'Tutup'
            });
            @endif

            @if($errors->any())
            Swal.fire({
                title: 'Gagal Menyimpan!',
                html: `
                    <div class="alert alert-danger small text-start mb-0">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                `,
                icon: 'error',
                confirmButtonColor: '#002B5C',
                confirmButtonText: 'Tutup'
            });
            @endif

        });
    </script>
    
    @stack('scripts')
</body>
</html>
