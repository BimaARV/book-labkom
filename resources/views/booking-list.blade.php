@extends('layouts.public')
@section('title', 'Cek Booking Saya - Techub')
@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3">Cek Booking Saya</h2>
                <p class="text-muted-custom">Masukkan email atau kode booking Anda untuk melihat status peminjaman</p>
            </div>

            <div class="card border-0 shadow-lg interactive-card p-3 p-md-4">
                <div class="card-body p-4">
                    <form action="{{ route('booking.list') }}" method="GET" class="d-flex w-100 flex-column flex-md-row gap-2">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Masukkan Email atau Kode Booking" value="{{ request('search') }}" required>
                            <button type="submit" class="btn btn-primary px-4">Cari Booking</button>
                        </div>
                    </form>
                    @error('email')
                        <div class="text-danger mt-2 small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if(isset($bookings))
                <div class="mt-4">
                    <h5 class="fw-bold mb-3">Hasil Pencarian untuk: <span class="text-primary">{{ $search }}</span></h5>
                    
                    @if($bookings->count() > 0)
                        <div class="row g-4">
                            @foreach($bookings as $booking)
                                <div class="col-12">
                                    <div class="card border border-light-subtle shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                                                <div>
                                                    <h5 class="fw-bold mb-1">{{ $booking->lab_name }}</h5>
                                                    <p class="text-muted-custom mb-0 small"><i class="bi bi-building"></i> {{ $booking->pic_name }}</p>
                                                </div>
                                                <div>
                                                    @if($booking->status == 'pending')
                                                        <span class="badge bg-warning text-dark px-3 py-2 fs-6">Pending</span>
                                                    @elseif($booking->status == 'accepted')
                                                        <span class="badge bg-success px-3 py-2 fs-6">Diterima</span>
                                                    @elseif($booking->status == 'rejected')
                                                        <span class="badge bg-danger px-3 py-2 fs-6">Ditolak</span>
                                                    @elseif($booking->status == 'completed')
                                                        <span class="badge bg-info text-dark px-3 py-2 fs-6">Selesai</span>
                                                    @else
                                                        <span class="badge bg-secondary px-3 py-2 fs-6">{{ $booking->status }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="row g-3 bg-light rounded p-3 mb-0">
                                                <div class="col-md-6">
                                                    <div class="text-muted-custom small mb-1">Tanggal Mulai</div>
                                                    <div class="fw-medium"><i class="bi bi-calendar-event text-primary me-2"></i>{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-muted-custom small mb-1">Waktu</div>
                                                    <div class="fw-medium"><i class="bi bi-clock text-primary me-2"></i>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</div>
                                                </div>
                                            </div>

                                            @if($booking->status == 'rejected' && $booking->rejection_reason)
                                                <div class="alert alert-danger mt-3 mb-0 p-2 small">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Alasan Penolakan:</strong> {{ $booking->rejection_reason }}
                                                </div>
                                            @endif
                                            
                                            @php
                                                $pendingRequest = $booking->changeRequests->where('status', 'pending')->first();
                                            @endphp

                                            @if($pendingRequest)
                                                <div class="alert alert-info mt-3 mb-0 p-2 small">
                                                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Pengajuan {{ ucfirst($pendingRequest->type) }}</strong> sedang diproses.
                                                </div>
                                            @elseif(in_array($booking->status, ['pending', 'accepted']))
                                                <div class="mt-3 text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openChangeModal({{ $booking->id }}, '{{ $booking->lab_name }}')">
                                                        <i class="bi bi-pencil-square me-1"></i> Ajukan Perubahan
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning text-center py-4">
                            <i class="bi bi-exclamation-circle text-warning fs-1 d-block mb-2"></i>
                            Tidak ada riwayat booking yang ditemukan untuk email tersebut.
                        </div>
                    @endif
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Pengajuan Perubahan -->
<div class="modal fade" id="changeRequestModal" tabindex="-1" aria-labelledby="changeRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
              <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title fw-bold" id="changeRequestModalLabel"><i class="bi bi-pencil-square me-2"></i>Ajukan Perubahan Booking</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
            <form action="{{ route('booking.change-request') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $search }}">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="change_booking_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Perubahan</label>
                        <select name="type" id="change_type" class="form-select" required onchange="toggleChangeFields()">
                            <option value="">-- Pilih Jenis Perubahan --</option>
                            <option value="cancellation">Pembatalan</option>
                            <option value="reschedule">Perubahan Jadwal (Reschedule)</option>
                            <option value="relocation">Pindah Labkom</option>
                        </select>
                    </div>

                    <div id="reschedule_fields" style="display: none;" class="bg-light p-3 rounded mb-3 border">
                        <h6 class="fw-bold mb-3">Jadwal Baru</h6>
                        <div class="mb-2">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="requested_date" class="form-control" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Waktu Mulai</label>
                                <input type="time" name="requested_start_time" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Waktu Selesai</label>
                                <input type="time" name="requested_end_time" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div id="relocation_fields" style="display: none;" class="bg-light p-3 rounded mb-3 border">
                        <h6 class="fw-bold mb-2">Labkom Tujuan</h6>
                        <div class="small text-muted mb-3">Lab saat ini: <span id="current_lab_name" class="fw-bold text-dark"></span></div>
                        <label class="form-label">Pilih Labkom Baru</label>
                        <select name="requested_laboratory_id" class="form-select">
                            <option value="">-- Pilih Labkom --</option>
                            <option value="all">Pilih Semua Labkom</option>
                            @if(isset($laboratories))
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan Pengajuan</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Jelaskan alasan pengajuan Anda..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openChangeModal(bookingId, labName) {
        document.getElementById('change_booking_id').value = bookingId;
        document.getElementById('current_lab_name').textContent = labName;
        document.getElementById('change_type').value = '';
        toggleChangeFields();
        new bootstrap.Modal(document.getElementById('changeRequestModal')).show();
    }

    function toggleChangeFields() {
        const type = document.getElementById('change_type').value;
        const resFields = document.getElementById('reschedule_fields');
        const relFields = document.getElementById('relocation_fields');

        resFields.style.display = type === 'reschedule' ? 'block' : 'none';
        relFields.style.display = type === 'relocation' ? 'block' : 'none';

        // Required toggles
        resFields.querySelectorAll('input').forEach(i => i.required = (type === 'reschedule'));
        relFields.querySelectorAll('select').forEach(s => s.required = (type === 'relocation'));
    }
</script>
@endpush
