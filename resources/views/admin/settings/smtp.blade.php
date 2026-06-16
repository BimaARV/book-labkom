@extends('layouts.admin')
@section('title', 'SMTP Email | Techub Admin')
@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Pengaturan SMTP Email</h3>
    <p class="text-muted-custom">Konfigurasi server email untuk mengirim notifikasi booking.</p>
</div>
<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body p-4">
        <form action="{{ route('admin.settings.smtp.update') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Mail Mailer</label>
                <input type="text" name="MAIL_MAILER" class="form-control" value="{{ $settings['MAIL_MAILER'] ?? 'smtp' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mail Host</label>
                <input type="text" name="MAIL_HOST" class="form-control" value="{{ $settings['MAIL_HOST'] ?? 'smtp.gmail.com' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mail Port</label>
                <input type="text" name="MAIL_PORT" class="form-control" value="{{ $settings['MAIL_PORT'] ?? '465' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mail Username</label>
                <input type="text" name="MAIL_USERNAME" class="form-control" value="{{ $settings['MAIL_USERNAME'] ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mail Password / App Password</label>
                <input type="password" name="MAIL_PASSWORD" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password" autocomplete="new-password" value="{{ $settings['MAIL_PASSWORD'] ?? '' }}">
                <div class="form-text text-muted"><i class="bi bi-shield-lock-fill text-success"></i> Kata sandi Anda tersimpan dalam format terenkripsi (aman).</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Mail From Address</label>
                <input type="text" name="MAIL_FROM_ADDRESS" class="form-control" value="{{ $settings['MAIL_FROM_ADDRESS'] ?? 'noreply@techub.id' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mail From Name (Nama Pengirim)</label>
                <input type="text" name="MAIL_FROM_NAME" class="form-control" value="{{ $settings['MAIL_FROM_NAME'] ?? 'Techub Notification' }}" required>
                <div class="form-text text-muted">Contoh: Admin Labkom, Sistem Peminjaman Ruangan, dsb.</div>
            </div>
            <div class="mb-4">
                <label class="form-label">CC Emails</label>
                <input type="text" name="MAIL_CC_ADDRESSES" class="form-control" value="{{ $settings['MAIL_CC_ADDRESSES'] ?? 'info@techub.id, noc@techub.id' }}">
                <div class="form-text">Gunakan koma (,) untuk memisahkan beberapa email.</div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
        </form>
    </div>
</div>
@endsection
