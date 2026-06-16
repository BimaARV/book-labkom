@extends('layouts.admin')
@section('title', 'Unit Bisnis | Techub Admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Data Unit Bisnis</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i> Tambah Unit Bisnis</button>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode Unit</th>
                        <th>Nama Unit Bisnis</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($businessUnits as $unit)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $unit->code }}</span></td>
                        <td class="fw-semibold">
                            {{ $unit->name }}
                            @if($unit->subUnits->count() > 0)
                            <br><small class="text-muted">{{ $unit->subUnits->count() }} Sub Unit</small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2 align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#subUnitModal{{ $unit->id }}" title="Kelola Sub Unit"><i class="bi bi-diagram-3"></i> Sub Unit</button>
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $unit->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('admin.business-units.destroy', $unit) }}" method="POST" class="m-0 p-0 delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal{{ $unit->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Unit Bisnis</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.business-units.update', $unit) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body text-start">
                                        <div class="mb-3">
                                            <label class="form-label">Kode Unit</label>
                                            <input type="text" name="code" class="form-control" value="{{ $unit->code }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Unit Bisnis</label>
                                            <input type="text" name="name" class="form-control" value="{{ $unit->name }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Modal Sub Unit -->
                    <div class="modal fade" id="subUnitModal{{ $unit->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold">Kelola Sub Unit: {{ $unit->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-start">
                                    <!-- List Sub Units -->
                                    <ul class="list-group mb-4">
                                        @forelse($unit->subUnits as $sub)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="bi bi-diagram-2 text-muted me-2"></i>{{ $sub->name }}</span>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-warning border-0" onclick="editSubUnit({{ $sub->id }}, '{{ addslashes($sub->name) }}')" title="Edit Sub Unit"><i class="bi bi-pencil"></i></button>
                                                <form action="{{ route('admin.sub-units.destroy', $sub) }}" method="POST" class="d-inline delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Hapus Sub Unit"><i class="bi bi-x-lg"></i></button>
                                                </form>
                                                <form id="edit-sub-unit-form-{{ $sub->id }}" action="{{ route('admin.sub-units.update', $sub) }}" method="POST" class="d-none">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="name" id="edit-sub-unit-input-{{ $sub->id }}">
                                                </form>
                                            </div>
                                        </li>
                                        @empty
                                        <li class="list-group-item text-center text-muted fst-italic">Belum ada Sub Unit</li>
                                        @endforelse
                                    </ul>
                                    
                                    <!-- Tambah Sub Unit Form -->
                                    <h6 class="fw-bold border-bottom pb-2 mb-3">Tambah Sub Unit Baru</h6>
                                    <form action="{{ route('admin.business-units.sub-units.store', $unit) }}" method="POST">
                                        @csrf
                                        <div class="input-group">
                                            <input type="text" name="name" class="form-control" placeholder="Nama Sub Unit (contoh: Fakultas Ilmu Komputer)" required>
                                            <button type="submit" class="btn btn-primary">Tambah</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="3" class="text-center py-4">Belum ada data unit bisnis</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Unit Bisnis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.business-units.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Unit</label>
                        <input type="text" name="code" class="form-control" required placeholder="Contoh: UB1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Unit Bisnis</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editSubUnit(id, currentName) {
    Swal.fire({
        title: 'Edit Nama Sub Unit',
        input: 'text',
        inputValue: currentName,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#002B5C',
        inputValidator: (value) => {
            if (!value) {
                return 'Nama Sub Unit tidak boleh kosong!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('edit-sub-unit-input-' + id).value = result.value;
            document.getElementById('edit-sub-unit-form-' + id).submit();
        }
    });
}
</script>
@endpush
@endsection
