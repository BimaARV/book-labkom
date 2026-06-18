@extends('layouts.public')

@section('title', 'Syarat dan Ketentuan Penggunaan Labkom | Techub')

@section('content')
<main class="container py-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-4 text-center">Syarat dan Ketentuan (ToS) Penggunaan Labkom</h2>
                    
                    <div class="mb-4">
                        <h5 class="fw-bold">1. Ketentuan Umum</h5>
                        <p class="text-muted-custom">Seluruh fasilitas Laboratorium Komputer (Labkom) adalah milik perusahaan/institusi dan dikelola oleh tim IT Infrastructure. Penggunaan Labkom hanya diperuntukkan bagi kegiatan resmi, pembelajaran, atau ujian (CBT) yang telah mendapatkan persetujuan.</p>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold">2. Prosedur Peminjaman (Booking)</h5>
                        <ul class="text-muted-custom">
                            <li>Setiap peminjaman wajib dilakukan melalui sistem booking *Techub*.</li>
                            <li>Peminjaman diatur dengan mempertimbangkan jeda waktu (buffer) 1 jam antara satu sesi dengan sesi berikutnya untuk keperluan pembersihan dan pengecekan oleh tim IT.</li>
                            <li>Persetujuan booking sepenuhnya merupakan hak tim IT Infrastructure berdasarkan ketersediaan jadwal.</li>
                            <li>Tim IT Infrastructure berhak membatalkan request booking jika terdapat agenda kegiatan UKOM pada waktu yang bersamaan.</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold">3. Aturan Selama Penggunaan</h5>
                        <ul class="text-muted-custom">
                            <li>Dilarang keras membawa makanan dan minuman ke dalam area Labkom untuk menghindari kerusakan perangkat akibat tumpahan.</li>
                            <li>Tidak diperkenankan memindahkan, mencabut, atau merusak perangkat keras (PC, monitor, kabel, dll) yang ada di dalam Labkom.</li>
                            <li>Setiap unit bisnis / penanggung jawab (PIC) yang melakukan booking bertanggung jawab penuh atas segala kerusakan yang terjadi selama rentang waktu peminjamannya.</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold">4. Sanksi Pelanggaran</h5>
                        <p class="text-muted-custom">Pelanggaran terhadap syarat dan ketentuan ini dapat mengakibatkan penolakan pengajuan peminjaman di masa mendatang serta kewajiban penggantian kerugian jika terjadi kerusakan aset perusahaan.</p>
                    </div>

                    <div class="text-center mt-5">
                        <a href="{{ url('/') }}" class="btn btn-primary px-4 py-2">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
