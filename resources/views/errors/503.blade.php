@extends('layouts.public')

@section('title', 'Sedang Dalam Pemeliharaan | Techub')

@section('content')
<main class="container py-5 d-flex flex-column justify-content-center align-items-center flex-grow-1 text-center">
    <div class="mb-4">
        <img src="{{ asset('Techub-Logo.png') }}" alt="Techub Logo" height="80" class="opacity-75">
    </div>
    <div class="mb-4">
        <i class="bi bi-tools text-warning" style="font-size: 5rem;"></i>
    </div>
    <h1 class="fw-bold mb-3 display-4">503</h1>
    <h2 class="fw-semibold mb-3">Sistem Sedang Dalam Pemeliharaan</h2>
    <p class="text-muted-custom mb-5 fs-5 max-w-md mx-auto" style="max-width: 600px;">
        Mohon maaf, layanan Booking Labkom saat ini sedang dalam proses pemeliharaan (maintenance) rutin atau pembaruan sistem oleh tim IT Infrastructure. Silakan kembali beberapa saat lagi.
    </p>
    <a href="{{ url('/') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
        <i class="bi bi-arrow-clockwise me-2"></i> Segarkan Halaman
    </a>
</main>
@endsection
