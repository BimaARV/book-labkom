@extends('layouts.admin')
@section('title', 'Edit Booking | Techub Admin')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Edit Booking</h3>
    <a href="{{ route('admin.bookings.index') }}" class="btn bg-light text-dark border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge me-2"></i>Informasi Pemesan</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-medium">PIC / Unit Bisnis <span class="text-danger">*</span></label>
                    <input type="text" name="pic_name" class="form-control" value="{{ old('pic_name', $booking->pic_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Nomor WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $booking->whatsapp) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $booking->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Unit Bisnis <span class="text-danger">*</span></label>
                    <select name="business_unit_id" id="business_unit_id" class="form-select" required>
                        <option value="">Pilih Unit Bisnis</option>
                        @foreach($businessUnits as $unit)
                            <option value="{{ $unit->id }}" {{ old('business_unit_id', $booking->business_unit_id) == $unit->id ? 'selected' : '' }} data-subunits="{{ json_encode($unit->subUnits) }}">
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Sub Unit Bisnis</label>
                    <select name="sub_business_unit_id" id="sub_business_unit_id" class="form-select">
                        <option value="">Pilih Sub Unit (Opsional)</option>
                        <!-- Options populated via JS -->
                    </select>
                </div>
            </div>

            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-pc-display me-2"></i>Detail Peminjaman</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Labkom <span class="text-danger">*</span></label>
                    <select name="laboratory_id" id="laboratory_id" class="form-select @error('laboratory_id') is-invalid @enderror" required>
                        <option value="" disabled>Pilih Labkom</option>
                        <option value="all" {{ old('laboratory_id', $booking->is_all_labs ? 'all' : $booking->laboratory_id) == 'all' ? 'selected' : '' }}>Semua Labkom (Labkom 1 - Labkom {{ $laboratories->count() }})</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ old('laboratory_id', $booking->laboratory_id) == $lab->id && !$booking->is_all_labs ? 'selected' : '' }}>
                                {{ $lab->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', \Carbon\Carbon::parse($booking->date)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time', \Carbon\Carbon::parse($booking->start_time)->format('H:i')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time', \Carbon\Carbon::parse($booking->end_time)->format('H:i')) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Keperluan <span class="text-danger">*</span></label>
                    <textarea name="purpose" rows="3" class="form-control" required>{{ old('purpose', $booking->purpose) }}</textarea>
                </div>
            </div>

            @if($booking->group_id && $maxRecurringDate)
            <div class="p-4 rounded-3 border mb-4" style="background-color: rgba(var(--bs-warning-rgb), 0.05);">
                <h5 class="fw-bold text-warning mb-2"><i class="bi bi-arrow-repeat me-2"></i>Jadwal Rutin</h5>
                <p class="text-muted small mb-3">Booking ini merupakan bagian dari jadwal rutin. Anda dapat mengubah tanggal berakhir jadwal rutin di bawah ini.</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Tanggal Berakhir Saat Ini</label>
                        <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($maxRecurringDate)->format('d M Y') }}" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Tanggal Berakhir Baru</label>
                        <input type="date" name="recurring_end_date" class="form-control" value="{{ old('recurring_end_date', \Carbon\Carbon::parse($maxRecurringDate)->format('Y-m-d')) }}">
                        <div class="form-text small text-muted mt-1">Kosongkan jika tidak ingin mengubah.</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            Jika diperpendek, jadwal setelah tanggal tersebut akan dihapus (status pending/accepted). Jika diperpanjang, jadwal baru akan dibuat otomatis.
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Status Booking</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="pending" {{ old('status', $booking->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="accepted" {{ old('status', $booking->status) == 'accepted' ? 'selected' : '' }}>Diterima</option>
                        <option value="rejected" {{ old('status', $booking->status) == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="completed" {{ old('status', $booking->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-8" id="rejection_reason_container" style="{{ in_array(old('status', $booking->status), ['rejected', 'cancelled']) ? '' : 'display: none;' }}">
                    <label class="form-label fw-medium">Alasan Penolakan/Pembatalan <span class="text-danger">*</span></label>
                    <input type="text" name="rejection_reason" id="rejection_reason" class="form-control" value="{{ old('rejection_reason', $booking->rejection_reason) }}">
                </div>
                <div class="col-12 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="notify_pic" id="notify_pic" value="1" checked>
                        <label class="form-check-label fw-medium" for="notify_pic">Kirim Notifikasi Perubahan ke WhatsApp & Email PIC</label>
                    </div>
                </div>
                </div>
            </div>

            <div class="mt-5 p-4 rounded-3 border" style="background-color: rgba(var(--bs-primary-rgb), 0.03);">
                <h5 class="fw-bold text-primary mb-2"><i class="bi bi-file-earmark-text me-2"></i>Laporan Pasca-Peminjaman</h5>
                <p class="text-muted small mb-4">Gunakan area ini untuk melaporkan kondisi Labkom setelah selesai digunakan oleh unit bisnis.</p>
                
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_clean" id="is_clean" value="1" {{ old('is_clean', $booking->is_clean) ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.1); margin-left: -2em;">
                    <label class="form-check-label fw-bold text-success ms-2" for="is_clean" style="cursor: pointer; font-size: 1.05rem;">Labkom dalam Keadaan Bersih</label>
                </div>

                <div class="row g-4 report-field" style="{{ old('is_clean', $booking->is_clean) ? 'display: none;' : '' }}">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Upload Foto Bukti <span class="text-muted fw-normal ms-1" style="font-size: 0.85rem;">(Bisa pilih banyak foto)</span></label>
                        <input type="file" name="report_images[]" class="form-control" accept="image/*" multiple>
                        <div class="form-text mt-1" style="opacity: 0.8; font-size: 0.85rem;">Tekan Ctrl/Shift saat memilih foto. Foto lama yang sudah tersimpan tidak akan terhapus.</div>
                        
                        @if(is_array($booking->report_images) && count($booking->report_images) > 0)
                            <div class="mt-3">
                                <label class="form-label fw-medium text-muted">Foto Tersimpan:</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($booking->report_images as $index => $image)
                                        <div class="position-relative border rounded p-1" style="width: 120px; height: 120px; background-color: var(--bs-body-bg);">
                                            <a href="{{ asset('storage/' . $image) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $image) }}" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: cover;">
                                            </a>
                                            <div class="position-absolute top-0 end-0 p-1">
                                                <div class="form-check m-0 bg-white rounded shadow-sm px-2 py-1">
                                                    <input class="form-check-input mt-1" type="checkbox" name="delete_images[]" value="{{ $image }}" id="delete_img_{{ $index }}">
                                                    <label class="form-check-label text-danger small fw-bold ms-1" for="delete_img_{{ $index }}">Hapus</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i>Centang tombol 'Hapus' pada foto lalu klik 'Simpan Perubahan' di bawah untuk menghapusnya.</div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Catatan / Detail Laporan</label>
                        <textarea name="report_note" rows="5" class="form-control" placeholder="Misal: Di PC nomor 15 ada banyak sampah botol, meja nomor 20 patah.">{{ old('report_note', $booking->report_note) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end border-top pt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const unitSelect = document.getElementById('business_unit_id');
    const subUnitSelect = document.getElementById('sub_business_unit_id');
    const oldSubUnitId = '{{ old('sub_business_unit_id', $booking->sub_business_unit_id) }}';

    function updateSubUnits() {
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        subUnitSelect.innerHTML = '<option value="">Pilih Sub Unit (Opsional)</option>';
        
        if (selectedOption && selectedOption.dataset.subunits) {
            const subunits = JSON.parse(selectedOption.dataset.subunits);
            subunits.forEach(sub => {
                const option = document.createElement('option');
                option.value = sub.id;
                option.textContent = sub.name;
                if (sub.id == oldSubUnitId) {
                    option.selected = true;
                }
                subUnitSelect.appendChild(option);
            });
        }
    }

    unitSelect.addEventListener('change', updateSubUnits);
    // Initial call
    updateSubUnits();

    const statusSelect = document.getElementById('status');
    const rejectionContainer = document.getElementById('rejection_reason_container');
    const rejectionInput = document.getElementById('rejection_reason');

    statusSelect.addEventListener('change', function() {
        if (this.value === 'rejected' || this.value === 'cancelled') {
            rejectionContainer.style.display = 'block';
            rejectionInput.required = true;
        } else {
            rejectionContainer.style.display = 'none';
            rejectionInput.required = false;
        }
    });

    const isCleanCheckbox = document.getElementById('is_clean');
    const reportFields = document.querySelectorAll('.report-field');

    isCleanCheckbox.addEventListener('change', function() {
        reportFields.forEach(field => {
            field.style.display = this.checked ? 'none' : 'block';
        });
    });
});
</script>
@endpush

@endsection
