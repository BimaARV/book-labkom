@extends('layouts.public')

@section('content')
<!-- Hero Banner -->
<div class="hero-banner w-100 position-relative overflow-hidden py-4 py-md-5">
    <!-- Decorative overlays -->
    <div class="position-absolute rounded-circle bg-primary" style="width: 600px; height: 600px; top: -300px; left: -200px; opacity: 0.05;"></div>
    <div class="position-absolute rounded-circle bg-primary" style="width: 800px; height: 800px; bottom: -400px; right: -300px; opacity: 0.05;"></div>
    
    <div class="container position-relative z-1 text-center">
        <img src="{{ asset('Techub-Logo.png') }}" alt="Techub Logo" height="80" class="hero-logo mb-4">
        <h2 class="fw-bold mb-3" style="letter-spacing: 0.5px;">Booking Laboratorium Komputer</h2>
        <p class="mb-0 fw-light text-muted-custom" style="font-size: 1.1rem;">Techub PT Binawan Inti Teknologi &mdash; Reservasi mudah, cepat, dan terorganisir</p>
    </div>
</div>

<main class="container py-5">


    @if(session('success'))
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>{{ session('success') }}</div>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <!-- Error Modal -->
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-danger text-white border-bottom-0">
                        <h5 class="modal-title fw-bold" id="errorModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Peringatan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="text-danger mb-3">
                            <i class="bi bi-x-circle" style="font-size: 3.5rem;"></i>
                        </div>
                        <h4 class="fw-bold text-body mb-2">Pemberitahuan</h4>
                        <p class="fs-5 text-muted-custom mb-0">{{ session('error') }}</p>
                    </div>
                    <div class="modal-footer border-top-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-danger px-4 py-2 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Booking Form Section -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg interactive-card p-4 p-md-5">
                <h3 class="fw-bold mb-4 border-bottom pb-3">Formulir Booking</h3>

                <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
                    @csrf
                    <div class="row g-4">
                        <!-- Nama PIC -->
                        <div class="col-md-12">
                            <label for="pic_name" class="form-label fw-medium">Nama PIC <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="pic_name" class="form-control" id="pic_name" placeholder="Masukkan nama lengkap Anda" value="{{ old('pic_name') }}" required>
                            </div>
                        </div>

                        <!-- Labkom -->
                        <div class="col-md-12">
                            <label for="laboratory_id" class="form-label fw-medium">Labkom <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-door-open"></i></span>
                                <select class="form-select" id="laboratory_id" name="laboratory_id" required>
                                    <option value="">-- Pilih Labkom --</option>
                                    <option value="all" {{ old('laboratory_id', request('lab_id')) == 'all' ? 'selected' : '' }}>Pilih Semua Labkom</option>
                                    @foreach($laboratories as $lab)
                                        @php
                                            $totalMapped = $lab->labPcs->count();
                                            $available = $totalMapped > 0 ? $lab->labPcs->where('status', 'active')->count() : $lab->capacity;
                                        @endphp
                                        <option value="{{ $lab->id }}" {{ old('laboratory_id', request('lab_id')) == $lab->id ? 'selected' : '' }}>{{ $lab->name }} ({{ $available }} PC Tersedia)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="col-md-4">
                            <label for="date" class="form-label fw-medium">Tanggal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="date" class="form-control" id="date" value="{{ old('date', request('date')) }}" min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Waktu Mulai -->
                        <div class="col-md-4">
                            <label for="start_time" class="form-label fw-medium">Waktu Mulai <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                <input type="time" name="start_time" class="form-control" id="start_time" value="{{ old('start_time') }}" required>
                            </div>
                        </div>

                        <!-- Waktu Selesai -->
                        <div class="col-md-4">
                            <label for="end_time" class="form-label fw-medium">Waktu Selesai <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                <input type="time" name="end_time" class="form-control" id="end_time" value="{{ old('end_time') }}" required>
                            </div>
                        </div>

                        <!-- Recurring Option -->
                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="is_recurring">Jadikan Pemesanan Rutin</label>
                            </div>
                        </div>
                        
                        <!-- Recurring Options -->
                        <div class="col-md-12" id="recurring_options_container" style="display: {{ old('is_recurring') ? 'block' : 'none' }};">
                            <div class="card bg-body-secondary border-0">
                                <div class="card-body">

                                    {{-- Frekuensi --}}
                                    <label for="recurring_frequency" class="form-label fw-medium">Frekuensi Rutin <span class="text-danger">*</span></label>
                                    <select class="form-select mb-3" name="recurring_frequency" id="recurring_frequency">
                                        <option value="weekly" {{ old('recurring_frequency', 'weekly') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                        <option value="daily" {{ old('recurring_frequency') == 'daily' ? 'selected' : '' }}>Harian</option>
                                    </select>

                                    {{-- Durasi --}}
                                    <label for="recurring_duration" class="form-label fw-medium">Durasi Rutin <span class="text-danger">*</span></label>
                                    <select class="form-select mb-3" name="recurring_duration" id="recurring_duration">
                                        <option value="4" {{ old('recurring_duration') == '4' ? 'selected' : '' }} class="weekly-only">1 Bulan (4 Minggu)</option>
                                        <option value="8" {{ old('recurring_duration') == '8' ? 'selected' : '' }} class="weekly-only">Setengah Semester (8 Minggu)</option>
                                        <option value="16" {{ old('recurring_duration', '16') == '16' ? 'selected' : '' }} class="weekly-only">1 Semester (16 Minggu)</option>
                                        <option value="custom" {{ old('recurring_duration') == 'custom' ? 'selected' : '' }}>Pilih Tanggal Berakhir Manual</option>
                                    </select>

                                    {{-- Tanggal berakhir (custom) --}}
                                    <div id="custom_end_date_wrapper" style="display: {{ old('recurring_duration') == 'custom' ? 'block' : 'none' }};">
                                        <label for="recurring_end_date" class="form-label fw-medium">Tanggal Berakhir Rutin <span class="text-danger">*</span></label>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                            <input type="date" name="recurring_end_date" class="form-control" id="recurring_end_date" value="{{ old('recurring_end_date') }}" min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>

                                    <small class="text-muted" id="recurring_hint">
                                        <i class="bi bi-info-circle"></i>
                                        <span id="recurring_hint_text">Sistem otomatis mem-booking setiap minggu di hari dan jam yang sama sesuai durasi yang dipilih.</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Unit Bisnis -->
                        <div class="col-md-12">
                            <label for="business_unit_id" class="form-label fw-medium">Unit Bisnis <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <select class="form-select" name="business_unit_id" id="business_unit_id" required>
                                    <option value="" selected disabled>Pilih Unit Bisnis</option>
                                    @foreach($businessUnits as $unit)
                                        <option value="{{ $unit->id }}" {{ old('business_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Sub Unit Bisnis (Fakultas dll) -->
                        <div class="col-md-12" id="sub_unit_container" style="display: none;">
                            <label for="sub_business_unit_id" class="form-label fw-medium">Sub Unit / Fakultas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                <select class="form-select" name="sub_business_unit_id" id="sub_business_unit_id">
                                    <option value="" selected>Pilih Sub Unit (Opsional)</option>
                                    <!-- Diisi oleh Javascript -->
                                </select>
                            </div>
                        </div>

                        <!-- Nomor WhatsApp -->
                        <div class="col-md-6">
                            <label for="whatsapp" class="form-label fw-medium">Nomor WhatsApp <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                <input type="text" name="whatsapp" class="form-control" id="whatsapp" placeholder="Contoh: 081234567890" value="{{ old('whatsapp') }}" required>
                            </div>
                            <div class="form-text">Mulai dengan 08...</div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="email@binawan.ac.id" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{!! $message !!}</div>
                                    <script>document.addEventListener("DOMContentLoaded", function() { document.getElementById('email').focus(); });</script>
                                @enderror
                            </div>
                        </div>

                        <!-- Keperluan -->
                        <div class="col-md-12">
                            <label for="purpose" class="form-label fw-medium">Keperluan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="purpose" id="purpose" rows="3" placeholder="Jelaskan secara singkat keperluan penggunaan Labkom" required>{{ old('purpose') }}</textarea>
                        </div>

                        <!-- Captcha -->
                        <div class="col-md-6 mt-4">
                            <label for="captcha" class="form-label fw-medium">Enter the CAPTCHA code <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3 border rounded p-1 bg-white">
                                    <img src="{{ captcha_src() }}" id="captcha-img" alt="captcha" style="cursor: pointer;" onclick="document.getElementById('captcha-img').src = '{{ captcha_src() }}' + Math.random()">
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none fw-bold px-0" onclick="document.getElementById('captcha-img').src = '{{ captcha_src() }}' + Math.random()">Refresh code</button>
                            </div>
                            <input type="text" class="form-control @error('captcha') is-invalid @enderror" name="captcha" id="captcha" required>
                            @error('captcha')
                                <div class="invalid-feedback">The CAPTCHA code did not match. Please try again.</div>
                            @enderror
                        </div>

                        <!-- Tombol Booking -->
                        <div class="col-12 mt-5 text-end d-flex flex-column align-items-end">
                            <button type="button" class="btn btn-primary btn-lg rounded-pill px-5" onclick="openTosModal()">
                                <i class="bi bi-send-fill me-2"></i> Ajukan Booking
                            </button>
                            <small class="mt-2 text-muted">Lihat <a href="#" data-bs-toggle="modal" data-bs-target="#tosModal" class="text-decoration-none">Syarat & Ketentuan Penggunaan Labkom</a></small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<!-- Modal Persetujuan (ToS) -->
<div class="modal fade" id="tosModal" tabindex="-1" aria-labelledby="tosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="tosModalLabel">Persetujuan Penggunaan Labkom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5">
                <p class="fw-bold mb-3 text-body">Ketentuan Penggunaan Labkom:</p>
                <ul class="mb-4 text-body" style="line-height: 1.8;">
                    <li>Pemohon bertanggung jawab atas penggunaan fasilitas Labkom selama periode booking.</li>
                    <li>Dilarang menginstal software atau mengubah konfigurasi perangkat tanpa izin pengelola.</li>
                    <li>Pemohon wajib menjaga kebersihan dan keamanan ruangan.</li>
                    <li>PIC wajib menyerahkan kartu identitas saat ingin menggunakan Labkom.</li>
                    <li>Booking akan kami batalkan jika dalam waktu 60 menit dari jadwal booking dimulai (tiap book-nya) PIC tidak konfirmasi kehadiran ke tim IT Infrastruktur (memberikan kartu identitas).</li>
                    <li>Kerusakan akibat kelalaian pengguna menjadi tanggung jawab pihak pemohon.</li>
                    <li>Tim IT Infrastructure berhak membatalkan request booking jika terdapat agenda kegiatan UKOM pada waktu yang bersamaan.</li>
                </ul>
                
                <div class="mt-4 border p-3 rounded theme-transition">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="tos_agreed" id="tos_agreed" value="1" form="bookingForm" onchange="toggleSubmitButton()" style="cursor: pointer;">
                        <label class="form-check-label fw-bold text-body" for="tos_agreed" style="cursor: pointer;">
                            Saya telah membaca dan menyetujui seluruh ketentuan penggunaan Labkom
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="bookingForm" class="btn btn-primary px-4" id="btnSubmitBooking" disabled>Setuju & Ajukan Booking</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        });
    </script>
    @endif

    <script>
        // Data Business Units beserta Sub Units
        const businessUnitsData = @json($businessUnits->keyBy('id'));
        
        function openTosModal() {
            const form = document.getElementById('bookingForm');
            if (form.reportValidity()) {
                const tosModal = new bootstrap.Modal(document.getElementById('tosModal'));
                tosModal.show();
            }
        }

        function toggleSubmitButton() {
            const checkbox = document.getElementById('tos_agreed');
            const btn = document.getElementById('btnSubmitBooking');
            btn.disabled = !checkbox.checked;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const isRecurringCheckbox = document.getElementById('is_recurring');
            const recurringContainer = document.getElementById('recurring_options_container');
            const durationSelect = document.getElementById('recurring_duration');
            const customDateWrapper = document.getElementById('custom_end_date_wrapper');
            const recurringInput = document.getElementById('recurring_end_date');
            
            const businessUnitSelect = document.getElementById('business_unit_id');
            const subUnitContainer = document.getElementById('sub_unit_container');
            const subUnitSelect = document.getElementById('sub_business_unit_id');
            const oldSubUnitId = "{{ old('sub_business_unit_id') }}";

            // Logic untuk Business Unit -> Sub Unit
            function handleBusinessUnitChange() {
                const unitId = businessUnitSelect.value;
                if (!unitId) {
                    subUnitContainer.style.display = 'none';
                    subUnitSelect.required = false;
                    return;
                }

                const unit = businessUnitsData[unitId];
                if (unit && unit.sub_units && unit.sub_units.length > 0) {
                    // Punya sub unit
                    subUnitSelect.innerHTML = '<option value="" selected>Pilih Sub Unit (Opsional)</option>';
                    unit.sub_units.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.textContent = sub.name;
                        if (oldSubUnitId == sub.id) option.selected = true;
                        subUnitSelect.appendChild(option);
                    });
                    subUnitContainer.style.display = 'block';
                    subUnitSelect.required = false;
                } else {
                    // Tidak punya sub unit
                    subUnitContainer.style.display = 'none';
                    subUnitSelect.required = false;
                    subUnitSelect.innerHTML = '<option value=""></option>'; // Kosongkan
                }
            }

            if (businessUnitSelect) {
                businessUnitSelect.addEventListener('change', handleBusinessUnitChange);
                // Trigger saat load (untuk handle validasi failed yg kembalikan old input)
                if(businessUnitSelect.value) {
                    handleBusinessUnitChange();
                }
            }

            if (isRecurringCheckbox) {
    isRecurringCheckbox.addEventListener('change', function() {
        if (this.checked) {
            recurringContainer.style.display = 'block';
            if (durationSelect.value === 'custom') {
                recurringInput.required = true;
            }
        } else {
            recurringContainer.style.display = 'none';
            recurringInput.required = false;
        }
    });
}

const frequencySelect = document.getElementById('recurring_frequency');
const recurringHintText = document.getElementById('recurring_hint_text');

function updateRecurringOptions() {
    const freq = frequencySelect ? frequencySelect.value : 'weekly';
    const weeklyOptions = ['4', '8', '16'];

    if (freq === 'daily') {
        // Kalau harian, semua opsi durasi minggu tidak relevan — force ke custom
        Array.from(durationSelect.options).forEach(opt => {
            if (weeklyOptions.includes(opt.value)) {
                opt.style.display = 'none';
            }
        });
        durationSelect.value = 'custom';
        document.getElementById('custom_end_date_wrapper').style.display = 'block';
        recurringInput.required = true;
        recurringHintText.textContent = 'Sistem otomatis mem-booking setiap hari di jam yang sama sesuai tanggal berakhir yang dipilih.';
    } else {
        // Mingguan — tampilkan semua opsi
        Array.from(durationSelect.options).forEach(opt => {
            opt.style.display = '';
        });
        if (durationSelect.value === 'custom') {
            document.getElementById('custom_end_date_wrapper').style.display = 'block';
            recurringInput.required = true;
        }
        recurringHintText.textContent = 'Sistem otomatis mem-booking setiap minggu di hari dan jam yang sama sesuai durasi yang dipilih.';
    }
}

if (frequencySelect) {
    frequencySelect.addEventListener('change', updateRecurringOptions);
    updateRecurringOptions(); // trigger saat load
}

if (durationSelect) {
    durationSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            document.getElementById('custom_end_date_wrapper').style.display = 'block';
            recurringInput.required = true;
        } else {
            document.getElementById('custom_end_date_wrapper').style.display = 'none';
            recurringInput.required = false;
            recurringInput.value = '';
        }
    });
}

    // Auto scroll and focus for pre-filled booking from Cek Ketersediaan
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('lab_id') && urlParams.has('date')) {
        const form = document.getElementById('bookingForm');
        if (form) {
            setTimeout(() => {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                const picInput = document.getElementById('pic_name');
                if (picInput) {
                    setTimeout(() => picInput.focus(), 500);
                }
            }, 100);
        }
    }
});
</script>
@endpush
@endsection
