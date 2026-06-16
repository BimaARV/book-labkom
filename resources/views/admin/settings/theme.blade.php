@extends('layouts.admin')
@section('title', 'Tema & Tampilan | Techub Admin')
@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Tema & Tampilan</h3>
    <p class="text-muted-custom">Pengaturan tampilan antarmuka aplikasi.</p>
</div>
<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body p-4">
        <form action="{{ route('admin.settings.theme.update') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama Aplikasi (Judul Web)</label>
                <input type="text" name="APP_NAME" class="form-control" value="{{ $settings['APP_NAME'] ?? 'Labkom Booking System' }}" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Deskripsi Web (SEO)</label>
                <textarea name="APP_DESCRIPTION" class="form-control" rows="3">{{ $settings['APP_DESCRIPTION'] ?? 'Sistem peminjaman Laboratorium Komputer' }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </form>
    </div>
</div>
@endsection
