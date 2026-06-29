@extends('layouts.admin')
@section('title', 'Permintaan Perubahan Booking - Techub')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Permintaan Perubahan</h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Pemohon</th>
                        <th>Status Booking</th>
                        <th>Jenis Perubahan</th>
                        <th>Detail Permintaan</th>
                        <th>Alasan</th>
                        <th>Status Pengajuan</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $req->booking->pic_name }}</div>
                            <div class="text-muted small">{{ $req->booking->whatsapp }}</div>
                        </td>
                        <td>
                            @if($req->booking->status == 'pending') <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($req->booking->status == 'accepted') <span class="badge bg-success">Diterima</span>
                            @elseif($req->booking->status == 'completed') <span class="badge bg-info text-dark">Selesai</span>
                            @elseif($req->booking->status == 'cancelled') <span class="badge bg-secondary">Dibatalkan</span>
                            @else <span class="badge bg-danger">Ditolak</span> @endif
                        </td>
                        <td>
                            @if($req->type == 'cancellation') <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Pembatalan</span>
                            @elseif($req->type == 'reschedule') <span class="badge bg-warning text-dark"><i class="bi bi-calendar-event me-1"></i>Reschedule</span>
                            @elseif($req->type == 'relocation') <span class="badge bg-info text-dark"><i class="bi bi-building me-1"></i>Pindah Lab</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                @if($req->type == 'reschedule')
                                    Tgl Baru: <strong>{{ \Carbon\Carbon::parse($req->requested_date)->format('d/m/Y') }}</strong><br>
                                    Waktu: <strong>{{ \Carbon\Carbon::parse($req->requested_start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($req->requested_end_time)->format('H:i') }}</strong>
                                @elseif($req->type == 'relocation')
                                  Lab Awal: <br>
                                    <strong>
                                        @if($req->original_is_all_labs)
                                            {{ \App\Models\Laboratory::getAllLabsName() }}
                                        @elseif($req->original_laboratory_id)
                                            {{ optional($req->originalLaboratory)->name ?? '-' }}
                                        @else
                                            <span class="text-muted fst-italic small">Data lama</span>
                                        @endif
                                    </strong><br>
                                    Lab Tujuan: <br><strong>{{ $req->requested_is_all_labs ? \App\Models\Laboratory::getAllLabsName() : optional($req->requestedLaboratory)->name }}</strong>
                                @elseif($req->type == 'cancellation')
                                    @if($req->cancel_mode == 'partial')
                                        <span class="text-danger">Dibatalkan sebagian</span><br>
                                        Mulai tgl: <strong>{{ \Carbon\Carbon::parse($req->cancel_from_date)->format('d/m/Y') }}</strong>
                                    @elseif($req->cancel_mode == 'all')
                                        <span class="text-danger">Dibatalkan seluruh jadwal rutin</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary fw-bold" onclick="openReasonModal('{{ addslashes($req->reason) }}', '{{ addslashes($req->admin_note) }}')">
                                <i class="bi bi-eye"></i> Lihat Alasan
                            </button>
                        </td>
                        <td>
                            @if($req->status == 'pending') <span class="badge bg-warning text-dark">Menunggu</span>
                            @elseif($req->status == 'approved') <span class="badge bg-success">Disetujui</span>
                            @else <span class="badge bg-danger">Ditolak</span> @endif
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                @if($req->status == 'pending')
                                    <button class="btn btn-sm btn-success" title="Setujui" onclick="openProcessModal({{ $req->id }}, 'approve')"><i class="bi bi-check-lg"></i></button>
                                    <button class="btn btn-sm btn-danger" title="Tolak" onclick="openProcessModal({{ $req->id }}, 'reject')"><i class="bi bi-x-lg"></i></button>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus Data" onclick="openDeleteModal({{ $req->id }})"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada permintaan perubahan booking.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Proses -->
<div class="modal fade" id="processModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form id="processForm" method="POST">
                @csrf
                <input type="hidden" name="action" id="processAction">
                <div class="modal-header">
                    <h5 class="modal-title" id="processModalTitle">Proses Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="processModalDesc"></p>
                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea name="admin_note" class="form-control" rows="3" placeholder="Tambahkan catatan jika perlu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" id="processSubmitBtn">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Alasan -->
<div class="modal fade" id="reasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-chat-left-text me-2"></i>Detail Alasan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold text-muted mb-2">Alasan Pemohon:</h6>
                <div class="bg-light p-3 rounded mb-4 border" id="reasonModalText"></div>

                <div id="adminNoteContainer" style="display: none;">
                    <h6 class="fw-bold text-muted mb-2">Catatan Admin (Saat Diproses):</h6>
                    <div class="bg-light p-3 rounded border border-warning" id="adminNoteText"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Riwayat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus riwayat pengajuan ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openReasonModal(reason, adminNote) {
        document.getElementById('reasonModalText').textContent = reason || '-';
        
        const noteContainer = document.getElementById('adminNoteContainer');
        if (adminNote && adminNote.trim() !== '') {
            document.getElementById('adminNoteText').textContent = adminNote;
            noteContainer.style.display = 'block';
        } else {
            noteContainer.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('reasonModal')).show();
    }

    function openProcessModal(id, action) {
        document.getElementById('processForm').action = `/admin/change-requests/${id}/process`;
        document.getElementById('processAction').value = action;
        
        const title = document.getElementById('processModalTitle');
        const desc = document.getElementById('processModalDesc');
        const btn = document.getElementById('processSubmitBtn');

        if (action === 'approve') {
            title.textContent = 'Setujui Perubahan';
            desc.textContent = 'Apakah Anda yakin ingin menyetujui perubahan ini? Data booking asli akan di-update otomatis.';
            btn.className = 'btn btn-success';
            btn.textContent = 'Ya, Setujui';
        } else {
            title.textContent = 'Tolak Perubahan';
            desc.textContent = 'Apakah Anda yakin ingin menolak perubahan ini? Data booking asli tidak akan berubah.';
            btn.className = 'btn btn-danger';
            btn.textContent = 'Ya, Tolak';
        }

        new bootstrap.Modal(document.getElementById('processModal')).show();
    }

    function openDeleteModal(id) {
        document.getElementById('deleteForm').action = `/admin/change-requests/${id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
