@extends('layouts.admin')
@section('title', 'Domain Settings | Techub Admin')
@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Pengaturan Sistem (Domain & Waktu)</h3>
    <p class="text-muted-custom">Konfigurasi alamat URL utama aplikasi dan zona waktu server.</p>
</div>
<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body p-4">
        <form action="{{ route('admin.settings.domain.update') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-medium">Application URL (APP_URL)</label>
                <input type="url" name="APP_URL" class="form-control" value="{{ $settings['APP_URL'] ?? url('/') }}" required placeholder="https://example.com">
                <div class="form-text text-muted">Masukkan URL lengkap beserta protokolnya (http:// atau https://), tanpa garis miring (/) di akhir.</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-medium">Zona Waktu (APP_TIMEZONE)</label>
                <select name="APP_TIMEZONE" class="form-select" required>
                    @php $currentTimezone = $settings['APP_TIMEZONE'] ?? env('APP_TIMEZONE', 'UTC'); @endphp
                    <option value="Asia/Jakarta" {{ $currentTimezone == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                    <option value="Asia/Makassar" {{ $currentTimezone == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                    <option value="Asia/Jayapura" {{ $currentTimezone == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                    <option value="UTC" {{ $currentTimezone == 'UTC' ? 'selected' : '' }}>UTC (Waktu Standar Universal)</option>
                </select>
                <div class="form-text text-muted">Pengaturan zona waktu akan memengaruhi stempel waktu (tanggal/jam) pada pemesanan yang masuk.</div>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
        </form>
    </div>
</div>
@endsection
