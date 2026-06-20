@extends('layouts.admin')
@section('title', 'Manajemen Booking | Techub Admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Manajemen Booking</h3>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body pb-3">
        <form action="{{ route('admin.bookings.index') }}" method="GET">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-medium mb-1">Pencarian (PIC / WA / Email)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-medium mb-1">Instansi</label>
                    <select name="business_unit_id" class="form-select form-select-sm">
                        <option value="">Semua Instansi</option>
                        @foreach($businessUnits as $unit)
                            <option value="{{ $unit->id }}" {{ request('business_unit_id') == $unit->id ? 'selected' : '' }}>{{ Str::limit($unit->name, 30) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted fw-medium mb-1">Labkom</label>
                    <select name="laboratory_id" class="form-select form-select-sm">
                        <option value="">Semua Labkom</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted fw-medium mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Diterima</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                    @if(collect(request()->only(['search', 'business_unit_id', 'laboratory_id', 'date', 'created_at', 'status']))->filter()->isNotEmpty())
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary btn-sm ms-1" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-medium mb-1">Tgl Jadwal Peminjaman</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-medium mb-1">Tgl Pemesanan Dibuat</label>
                    <input type="date" name="created_at" class="form-control form-control-sm" value="{{ request('created_at') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-medium mb-1">Urutkan Data</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="desc" {{ request('sort_by') == 'desc' || !request()->has('sort_by') ? 'selected' : '' }}>Terbaru - Terlama</option>
                        <option value="asc" {{ request('sort_by') == 'asc' ? 'selected' : '' }}>Terlama - Terbaru</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tgl Dibuat</th>
                        <th>Kode Booking</th>
                        <th>PIC</th>
                        <th>Instansi</th>
                        <th>Keperluan</th>
                        <th>Labkom</th>
                        <th>Jadwal Peminjaman</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td>{{ $booking->created_at->format('d M Y, H:i') }}</td>
                        <td><span class="badge bg-secondary font-monospace">{{ $booking->tracking_code }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $booking->pic_name }}</div>
                            <div class="small mt-1 text-muted-custom"><i class="bi bi-whatsapp"></i> {{ $booking->whatsapp }}</div>
                            <div class="small text-muted-custom"><i class="bi bi-envelope"></i> {{ $booking->email }}</div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">
                                {{ optional($booking->businessUnit)->name }}{{ $booking->subBusinessUnit ? ' / ' . $booking->subBusinessUnit->name : '' }}
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="showPurpose('{{ addslashes($booking->purpose) }}')"><i class="bi bi-eye"></i> Lihat Keperluan</button>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $booking->lab_name }}</div>
                        </td>
                        <td>
                            <div class="fw-medium text-primary">{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}</div>
                            <div class="small">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</div>
                        </td>
                        <td>
                            @if($booking->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($booking->status == 'accepted')
                                <span class="badge bg-success">Diterima</span>
                            @elseif($booking->status == 'rejected')
                                <span class="badge bg-danger">Ditolak</span>
                            @elseif($booking->status == 'completed')
                                <span class="badge bg-info text-dark">Selesai</span>
                            @else
                                <span class="badge bg-secondary">{{ $booking->status }}</span>
                            @endif
                            
                            @if($booking->handled_by)
                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-person-circle"></i> {{ $booking->handled_by }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2 align-items-center">
                                @if($booking->status == 'pending')
                                <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="m-0 p-0">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="btn btn-sm btn-success accept-btn" title="Terima"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger reject-btn" title="Tolak" onclick="promptReject('{{ route('admin.bookings.update', $booking) }}')"><i class="bi bi-x-lg"></i></button>
                                @elseif($booking->status == 'accepted')
                                <button type="button" class="btn btn-sm btn-info text-dark accept-btn" title="Selesaikan" onclick="promptComplete('{{ route('admin.bookings.update', $booking) }}')"><i class="bi bi-check-all"></i></button>
                                <button type="button" class="btn btn-sm btn-warning" title="Batalkan" onclick="promptCancel('{{ route('admin.bookings.update', $booking) }}')"><i class="bi bi-slash-circle"></i></button>
                                @endif
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="m-0 p-0 delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4">Belum ada booking</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $bookings->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Hidden Reject Form -->
<form id="rejectForm" method="POST" style="display: none;">
    @csrf @method('PUT')
    <input type="hidden" name="status" value="rejected">
    <input type="hidden" name="rejection_reason" id="rejection_reason_input">
</form>

<form id="cancelForm" method="POST" style="display: none;">
    @csrf @method('PUT')
    <input type="hidden" name="status" value="cancelled">
    <input type="hidden" name="rejection_reason" id="cancel_reason_input">
</form>

<form id="completeForm" method="POST" style="display: none;" enctype="multipart/form-data">
    @csrf @method('PUT')
    <input type="hidden" name="status" value="completed">
</form>

@push('scripts')
<script>
    function showPurpose(purpose) {
        Swal.fire({
            title: '<h4 class="fw-bold mb-0 text-primary"><i class="bi bi-info-circle me-2"></i>Keperluan Booking</h4>',
            html: `<div class="text-start"><h6 class="fw-bold text-muted mb-2">Keperluan:</h6><div class="bg-light p-3 rounded mb-4 border"><p class="mb-0 fs-6" style="line-height: 1.6;">${purpose}</p></div></div>`,
            showConfirmButton: true,
            confirmButtonText: 'Tutup',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-secondary px-4',
                popup: 'rounded-4 border-0 shadow p-0'
            }
        });
    }

    function promptReject(actionUrl) {
        Swal.fire({
            title: 'Tolak Booking',
            text: 'Masukkan alasan penolakan:',
            input: 'text',
            inputPlaceholder: 'Contoh: Lab sedang digunakan untuk ujian...',
            showCancelButton: true,
            confirmButtonText: 'Tolak Booking',
            cancelButtonText: 'Batal',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary ms-2'
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'Anda harus memasukkan alasan penolakan!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('rejectForm');
                form.action = actionUrl;
                document.getElementById('rejection_reason_input').value = result.value;
                form.submit();
            }
        });
    }

    function promptCancel(actionUrl) {
        Swal.fire({
            title: 'Batalkan Booking',
            text: 'Masukkan alasan pembatalan:',
            input: 'text',
            inputPlaceholder: 'Contoh: Terjadi perubahan jadwal mendadak...',
            showCancelButton: true,
            confirmButtonText: 'Batalkan Booking',
            cancelButtonText: 'Kembali',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-warning',
                cancelButton: 'btn btn-secondary ms-2'
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'Anda harus memasukkan alasan pembatalan!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('cancelForm');
                form.action = actionUrl;
                document.getElementById('cancel_reason_input').value = result.value;
                form.submit();
            }
        });
    }

    function promptComplete(actionUrl) {
        Swal.fire({
            title: '<h4 class="fw-bold mb-0 text-primary"><i class="bi bi-check-circle me-2"></i>Selesaikan Booking</h4>',
            background: 'var(--bs-body-bg, #fff)',
            color: 'var(--bs-body-color, #000)',
            html: `
                <div class="text-start mt-3">
                    <p class="mb-3" style="font-size: 0.9rem; opacity: 0.8;">Pastikan kondisi labkom setelah digunakan.</p>
                    <div class="form-check form-switch form-check-lg d-flex align-items-center gap-2 p-3 rounded mb-3" style="border: 1px solid var(--bs-border-color, #dee2e6);">
                        <input class="form-check-input ms-0 mt-0 border-secondary" type="checkbox" id="swal_is_clean" value="1" style="width: 2.5em; height: 1.25em; cursor: pointer; border-width: 2px;">
                        <label class="form-check-label fw-bold text-success fs-5 ms-2" for="swal_is_clean" style="cursor: pointer;">Labkom dalam Keadaan Bersih</label>
                    </div>
                    <div id="swal_report_container" style="display: block;">
                        <div class="mb-3">
                            <label class="form-label fw-medium small">Upload Foto Bukti <span style="opacity: 0.7; font-weight: normal;">(Pilih beberapa file sekaligus)</span></label>
                            <input type="file" id="swal_report_images" name="report_images[]" class="form-control" accept="image/*" multiple>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium small">Catatan / Detail Laporan</label>
                            <textarea id="swal_report_note" rows="3" class="form-control" placeholder="Misal: Banyak sampah botol..."></textarea>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Selesaikan Booking',
            cancelButtonText: 'Kembali',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary ms-2'
            },
            didOpen: () => {
                const isCleanCb = document.getElementById('swal_is_clean');
                const container = document.getElementById('swal_report_container');
                isCleanCb.addEventListener('change', (e) => {
                    container.style.display = e.target.checked ? 'none' : 'block';
                });
            },
            preConfirm: () => {
                const isClean = document.getElementById('swal_is_clean').checked;
                const fileInput = document.getElementById('swal_report_images');
                const noteInput = document.getElementById('swal_report_note').value;
                
                const form = document.getElementById('completeForm');
                form.action = actionUrl;
                
                form.querySelectorAll('.dynamic-input').forEach(el => el.remove());
                
                const inputClean = document.createElement('input');
                inputClean.type = 'hidden';
                inputClean.name = 'is_clean';
                inputClean.value = isClean ? '1' : '0';
                inputClean.className = 'dynamic-input';
                form.appendChild(inputClean);

                if (!isClean) {
                    fileInput.name = 'report_images[]';
                    fileInput.className = 'dynamic-input';
                    fileInput.style.display = 'none';
                    form.appendChild(fileInput);
                    
                    const inputNote = document.createElement('input');
                    inputNote.type = 'hidden';
                    inputNote.name = 'report_note';
                    inputNote.value = noteInput;
                    inputNote.className = 'dynamic-input';
                    form.appendChild(inputNote);
                }

                form.submit();
            }
        });
    }
</script>
@endpush
@endsection
