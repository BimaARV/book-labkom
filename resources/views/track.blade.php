@extends('layouts.public')

@section('title', 'Pending Booking - Labkom Booking System | Techub')

@section('content')
<main class="container py-5 flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body px-5">
                    @if(isset($booking))
                        @if($booking->status == 'pending')
                            <div class="mb-4 text-warning">
                                <i class="bi bi-hourglass-split" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="fw-bold mb-3">Booking Sedang Diproses</h2>
                            <p class="text-muted-custom mb-4 px-md-4">
                                Permintaan peminjaman Labkom Anda (<strong>{{ $booking->lab_name }}</strong>) sedang menunggu persetujuan dari tim IT Infrastructure.
                            </p>
                            <div class="alert alert-warning d-inline-block px-4 py-2 mb-4">
                                <i class="bi bi-ticket-perforated me-2"></i>
                                Kode Booking Anda: <strong class="ms-1">{{ $booking->tracking_code }}</strong>
                            </div>
                            <p class="text-muted small">Simpan kode ini untuk memantau status atau mengajukan perubahan booking.</p>
                        @elseif($booking->status == 'accepted')
                            <div class="mb-4 text-success">
                                <i class="bi bi-check-circle-fill" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="fw-bold mb-3">Booking Disetujui!</h2>
                            <p class="text-muted-custom fs-5 mb-4">
                                Permintaan peminjaman Labkom Anda (<strong>{{ $booking->lab_name }}</strong>) telah disetujui. Silakan gunakan lab sesuai jadwal yang Anda pilih.
                            </p>
                            <div class="alert alert-warning d-inline-block px-4 py-2 mb-4">
                                <i class="bi bi-ticket-perforated me-2"></i>
                                Kode Booking Anda: <strong class="ms-1">{{ $booking->tracking_code }}</strong>
                            </div>
                            <p class="text-muted small">Simpan kode ini untuk memantau status atau mengajukan perubahan booking.</p>
                            @if($booking->handled_by)
                                <div class="badge bg-success bg-opacity-10 text-success border border-success p-2 mb-4">
                                    <i class="bi bi-person-check-fill me-1"></i> Diproses oleh: {{ $booking->handled_by }}
                                </div>
                            @endif
                        @elseif($booking->status == 'completed')
                            <div class="mb-4 text-info">
                                <i class="bi bi-check-all" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="fw-bold mb-3 text-info">Booking Selesai!</h2>
                            <p class="text-muted-custom fs-5 mb-4">
                                Peminjaman Labkom Anda (<strong>{{ $booking->lab_name }}</strong>) telah selesai. Terima kasih telah menggunakan fasilitas Labkom. Kami harap fasilitas yang kami sediakan dapat membantu kegiatan Anda dengan baik.
                            </p>
                            <div class="alert alert-warning d-inline-block px-4 py-2 mb-4">
                                <i class="bi bi-ticket-perforated me-2"></i>
                                Kode Booking Anda: <strong class="ms-1">{{ $booking->tracking_code }}</strong>
                            </div>
                            <p class="text-muted small">Simpan kode ini untuk memantau status atau mengajukan perubahan booking.</p>
                            @if($booking->handled_by)
                                <div class="badge bg-info bg-opacity-10 text-info border border-info p-2 mb-4">
                                    <i class="bi bi-person-check-fill me-1"></i> Diproses oleh: {{ $booking->handled_by }}
                                </div>
                            @endif
                        @elseif($booking->status == 'rejected' || $booking->status == 'cancelled')
                            <div class="mb-4 {{ $booking->status == 'rejected' ? 'text-danger' : 'text-secondary' }}">
                                <i class="bi {{ $booking->status == 'rejected' ? 'bi-x-circle-fill' : 'bi-slash-circle-fill' }}" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="fw-bold mb-3 {{ $booking->status == 'rejected' ? 'text-danger' : 'text-secondary' }}">
                                {{ $booking->status == 'rejected' ? 'Booking Ditolak' : 'Booking Dibatalkan' }}
                            </h2>
                            <p class="text-muted-custom fs-5 mb-4">
                                @if($booking->status == 'rejected')
                                    Mohon maaf, permintaan peminjaman Labkom Anda (<strong>{{ $booking->lab_name }}</strong>) tidak dapat disetujui.
                                @else
                                    Peminjaman Labkom Anda (<strong>{{ $booking->lab_name }}</strong>) telah dibatalkan.
                                @endif
                            </p>
                            <div class="alert alert-warning d-inline-block px-4 py-2 mb-4">
                                <i class="bi bi-ticket-perforated me-2"></i>
                                Kode Booking Anda: <strong class="ms-1">{{ $booking->tracking_code }}</strong>
                            </div>
                            <p class="text-muted small">Simpan kode ini untuk memantau status atau mengajukan perubahan booking.</p>
                            @if($booking->handled_by)
                                <div class="badge {{ $booking->status == 'rejected' ? 'bg-danger bg-opacity-10 text-danger border-danger' : 'bg-secondary bg-opacity-10 text-secondary border-secondary' }} border p-2 mb-2">
                                    <i class="bi {{ $booking->status == 'rejected' ? 'bi-person-x-fill' : 'bi-person-dash-fill' }} me-1"></i> Diproses oleh: {{ $booking->handled_by }}
                                </div>
                            @endif
                            <div class="alert {{ $booking->status == 'rejected' ? 'alert-danger' : 'alert-secondary' }} mx-auto mt-2" style="max-width: 500px;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Alasan:</strong> {{ $booking->rejection_reason ?? '-' }}
                            </div>
                        @endif
                    @else
                        <!-- Fallback jika akses langsung ke /pending-booking tanpa ID -->
                        <div class="mb-4 text-warning">
                            <i class="bi bi-hourglass-split" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="fw-bold mb-3">Booking Sedang Diproses</h2>
                        <p class="text-muted-custom fs-5 mb-4">
                            Permintaan peminjaman Labkom Anda telah terkirim dan sedang menunggu persetujuan dari tim IT Infrastructure.
                        </p>
                    @endif
                    
                    <p class="mb-5">Informasi lebih lanjut akan kami kirimkan melalui WhatsApp atau Email yang Anda daftarkan.</p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ url('/') }}" class="btn btn-primary px-4 py-2">Kembali ke Beranda</a>
                        <a href="{{ route('booking.list') }}" class="btn btn-outline-primary px-4 py-2">Cek Status Booking Lain</a>
                    </div>
                </div>
            </div>

            <!-- Tata Tertib Card -->
            <div class="card border-0 shadow-sm mt-4 text-start">
                <div class="card-header bg-transparent border-bottom pt-4 pb-3">
                    <h5 class="fw-bold mb-0 text-dark">TATA TERTIB PENGGUNAAN LABORATORIUM KOMPUTER (LABKOM)</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-dark mb-4">Untuk menjaga kenyamanan, keamanan, dan kelancaran penggunaan fasilitas Laboratorium Komputer, setiap pengguna wajib mematuhi ketentuan berikut:</p>
                    
                    <ol class="text-dark mb-4 ps-3" style="line-height: 1.8;">
                        <li class="mb-2">Menjaga kebersihan dan ketertiban selama berada di area Laboratorium Komputer.</li>
                        <li class="mb-2">Dilarang membawa serta mengonsumsi makanan maupun minuman di dalam Laboratorium Komputer.</li>
                        <li class="mb-2">Tidak diperkenankan memindahkan perangkat komputer (dan komponen pendukungnya) ke lokasi lain.</li>
                        <li class="mb-2">Menggunakan seluruh fasilitas Laboratorium Komputer sesuai dengan kebutuhan dan tujuan yang telah didaftarkan pada saat proses pemesanan (booking).</li>
                        <li class="mb-2">Mematuhi jadwal penggunaan yang telah disetujui oleh tim IT Infrastructure Laboratorium Komputer.</li>
                        <li class="mb-2">Menjaga kondisi dan kebersihan area Laboratorium Komputer serta fasilitas yang digunakan sebelum meninggalkan Laboratorium Komputer.</li>
                        <li class="mb-2">Segera melaporkan kepada tim IT Infrastructure apabila ditemukan kerusakan, gangguan, atau ketidaksesuaian pada fasilitas Laboratorium Komputer.</li>
                        <li>Mematuhi seluruh peraturan dan ketentuan yang berlaku demi menjaga kenyamanan, keamanan, dan keselamatan bersama.</li>
                    </ol>

                    <p class="text-dark mb-0">Terima kasih atas kerja sama Anda dalam menjaga fasilitas Laboratorium Komputer agar tetap nyaman, aman, dan dapat digunakan oleh seluruh pengguna dengan baik.</p>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection
