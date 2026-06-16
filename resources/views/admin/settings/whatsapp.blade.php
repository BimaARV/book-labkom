@extends('layouts.admin')
@section('title', 'WhatsApp Gateway | Techub Admin')
@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">WhatsApp Gateway</h3>
    <p class="text-muted-custom">Integrasi dengan Baileys Microservice untuk pengiriman notifikasi WA.</p>
</div>
<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body p-4">
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i> Konfigurasi ini digunakan untuk berkomunikasi dengan microservice Node.js Baileys.
        </div>
        <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Gateway URL</label>
                <input type="url" name="WA_GATEWAY_URL" id="gateway_url" class="form-control" value="{{ $settings['WA_GATEWAY_URL'] ?? 'http://localhost:3000' }}" required>
                <div class="form-text">URL endpoint dari service Baileys (contoh: http://localhost:3000)</div>
            </div>
            <div class="mb-3">
                <label class="form-label">API Key (Opsional)</label>
                <input type="text" name="WA_API_KEY" class="form-control" value="{{ $settings['WA_API_KEY'] ?? '' }}">
            </div>
            
            <h5 class="fw-bold mt-4 mb-3 border-bottom pb-2">Notifikasi Grup WhatsApp (Opsional)</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Grup</label>
                    <input type="text" name="WA_GROUP_NAME" class="form-control" value="{{ $settings['WA_GROUP_NAME'] ?? '' }}" placeholder="Contoh: Tim IT Infrastruktur">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Group ID</label>
                    <input type="text" name="WA_GROUP_ID" class="form-control" value="{{ $settings['WA_GROUP_ID'] ?? '' }}" placeholder="Contoh: 1234567890@g.us">
                    <div class="form-text">ID Grup WhatsApp berakhiran <code>@g.us</code></div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
            <button type="button" id="btn-generate-qr" class="btn btn-outline-success ms-2"><i class="bi bi-qr-code-scan me-1"></i> Tampilkan QR Code</button>
            <button type="button" id="btn-disconnect" class="btn btn-outline-danger ms-2" style="display: none;"><i class="bi bi-power me-1"></i> Putuskan Koneksi (Disconnect)</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnGenerate = document.getElementById('btn-generate-qr');
        const btnDisconnect = document.getElementById('btn-disconnect');
        const gatewayUrlInput = document.getElementById('gateway_url');
        let statusInterval;
        let swalQrActive = false;

        // Check initial status
        if (gatewayUrlInput.value) {
            checkInitialStatus(gatewayUrlInput.value);
        }

        function checkInitialStatus(gatewayUrl) {
            fetch(`${gatewayUrl}/status`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'connected') {
                        btnDisconnect.style.display = 'inline-block';
                    }
                })
                .catch(err => console.log('Initial status check error (Service mungkin belum jalan):', err));
        }

        btnDisconnect.addEventListener('click', function () {
            const gatewayUrl = gatewayUrlInput.value;
            if(!gatewayUrl) return;

            Swal.fire({
                title: 'Putuskan Koneksi',
                text: "Apakah Anda yakin ingin memutuskan koneksi WhatsApp?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-power me-1"></i> Ya, Putuskan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btnDisconnect.disabled = true;
                    btnDisconnect.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

                    fetch(`${gatewayUrl}/disconnect`, { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                Swal.fire('Terputus', 'Koneksi WhatsApp berhasil diputuskan.', 'success');
                                btnDisconnect.style.display = 'none';
                                if(statusInterval) clearInterval(statusInterval);
                            }
                        })
                        .catch(err => {
                            alert('Gagal memutuskan koneksi. Pastikan service berjalan.');
                        })
                        .finally(() => {
                            btnDisconnect.disabled = false;
                            btnDisconnect.innerHTML = '<i class="bi bi-power me-1"></i> Putuskan Koneksi (Disconnect)';
                        });
                }
            });
        });

        btnGenerate.addEventListener('click', function () {
            const gatewayUrl = document.getElementById('gateway_url').value;
            if(!gatewayUrl) return Swal.fire('Error', 'Silakan isi Gateway URL terlebih dahulu!', 'error');

            btnGenerate.disabled = true;
            btnGenerate.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...';
            
            // Periksa status terlebih dahulu sebelum membuka modal
            fetch(`${gatewayUrl}/qr`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'connected') {
                        btnGenerate.disabled = false;
                        btnGenerate.innerHTML = '<i class="bi bi-qr-code-scan me-1"></i> Tampilkan QR Code';
                        Swal.fire('Terhubung', 'WhatsApp Anda sudah terhubung ke gateway!', 'success');
                        btnDisconnect.style.display = 'inline-block';
                    } else {
                        // Tampilkan loading state
                        Swal.fire({
                            title: 'Menyiapkan QR Code',
                            html: 'Silakan tunggu sebentar, sedang berkomunikasi dengan WhatsApp Gateway...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                swalQrActive = true;
                            },
                            willClose: () => {
                                swalQrActive = false;
                                if(statusInterval) clearInterval(statusInterval);
                            }
                        });
                        
                        // Mulai polling
                        if(statusInterval) clearInterval(statusInterval);
                        checkStatus(gatewayUrl); // Check immediately
                        statusInterval = setInterval(() => checkStatus(gatewayUrl), 3000);
                        
                        // Re-enable button after 2 seconds
                        setTimeout(() => {
                            btnGenerate.disabled = false;
                            btnGenerate.innerHTML = '<i class="bi bi-qr-code-scan me-1"></i> Tampilkan QR Code';
                        }, 2000);
                    }
                })
                .catch(err => {
                    btnGenerate.disabled = false;
                    btnGenerate.innerHTML = '<i class="bi bi-qr-code-scan me-1"></i> Tampilkan QR Code';
                    Swal.fire('Error', 'Gagal menghubungi Service WhatsApp. Pastikan service berjalan di ' + gatewayUrl, 'error');
                });
        });

        function checkStatus(gatewayUrl) {
            fetch(`${gatewayUrl}/qr`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'connected') {
                        btnDisconnect.style.display = 'inline-block';
                        clearInterval(statusInterval);
                        
                        Swal.close();

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'WhatsApp berhasil terhubung!',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else if (data.status === 'waiting_qr' && data.qr && swalQrActive) {
                        const img = document.getElementById('swal-qr-image');
                        if (img) {
                            // If modal is already showing QR, just update src
                            if (img.src !== data.qr) {
                                img.src = data.qr; // Update QR code gracefully
                            }
                        } else {
                            // Modal was in loading state, change it to show QR
                            Swal.fire({
                                title: 'Scan QR Code',
                                html: `
                                    <div class="mb-3 text-secondary fw-medium">Buka WhatsApp > Perangkat Tertaut > Tautkan Perangkat</div>
                                    <div class="bg-light d-inline-block p-3 rounded border shadow-sm">
                                        <img id="swal-qr-image" src="${data.qr}" alt="QR Code" width="250" height="250">
                                    </div>
                                    <div class="mt-4">
                                        <span class="spinner-border spinner-border-sm me-2 text-warning"></span><span class="fw-medium">Menunggu Scan...</span>
                                    </div>
                                `,
                                showConfirmButton: false,
                                showCancelButton: true,
                                cancelButtonText: 'Tutup',
                                didOpen: () => {
                                    swalQrActive = true;
                                },
                                willClose: () => {
                                    swalQrActive = false;
                                    if(statusInterval) clearInterval(statusInterval);
                                }
                            });
                        }
                    } else if (data.status === 'loading' && swalQrActive && !document.getElementById('swal-qr-image')) {
                        // Already in loading state, just wait
                    }
                })
                .catch(err => {
                    console.error('Status check error:', err);
                    if (swalQrActive && !document.getElementById('swal-qr-image')) {
                        Swal.fire('Error', 'Gagal menghubungi Service WhatsApp. Pastikan service berjalan.', 'error');
                        clearInterval(statusInterval);
                    }
                });
        }
    });
</script>
@endpush
    </div>
</div>
@endsection
