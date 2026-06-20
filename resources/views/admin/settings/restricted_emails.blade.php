@extends('layouts.admin')
@section('title', 'Restrict Email - Techub')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 text-primary fw-bold"><i class="bi bi-shield-lock me-2"></i>Restrict Email</h4>
        <p class="text-muted small mt-1 mb-0">Atur daftar domain atau alamat email spesifik yang diizinkan untuk melakukan peminjaman.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Domain / Email
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Domain / Email yang Diizinkan</th>
                        <th>Ditambahkan Pada</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emails as $item)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $item->email }}</td>
                        <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2 align-items-center">
                                <button class="btn btn-sm btn-outline-primary" onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->email) }}')"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('admin.restricted-emails.destroy', $item->id) }}" method="POST" class="m-0 p-0 delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle me-1"></i> Belum ada restrict email yang didaftarkan. Saat ini <strong>semua email</strong> dapat digunakan untuk peminjaman.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.restricted-emails.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Restrict Email</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Domain atau Email Lengkap</label>
                    <input type="text" name="email" class="form-control" placeholder="Contoh: @binawan.ac.id atau bima@gmail.com" required>
                    <div class="form-text">Gunakan awalan <code>@</code> untuk mengizinkan seluruh domain, atau ketik alamat email spesifik.</div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Restrict Email</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Domain atau Email Lengkap</label>
                    <input type="text" name="email" id="editEmailInput" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEditModal(id, email) {
        document.getElementById('editForm').action = `/admin/settings/restricted-emails/${id}`;
        document.getElementById('editEmailInput').value = email;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }
</script>
@endpush
