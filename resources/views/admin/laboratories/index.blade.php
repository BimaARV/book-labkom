@extends('layouts.admin')
@section('title', 'Data Labkom | Techub Admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Data Laboratorium Komputer</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i> Tambah Lab</button>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Nama Labkom</th>
                        <th>Kapasitas (PC)</th>
                        <th>Status</th>
                        <th>Total Booking</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laboratories as $lab)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $lab->name }}</td>
                        <td>
                            @php
                                $totalMapped = $lab->labPcs->count();
                                if ($totalMapped > 0) {
                                    $active = $lab->labPcs->where('status', 'active')->count();
                                    $broken = $lab->labPcs->where('status', 'broken')->count();
                                    $maintenance = $lab->labPcs->where('status', 'maintenance')->count();
                                    $available = $active;
                                } else {
                                    $available = $lab->capacity;
                                    $broken = 0;
                                    $maintenance = 0;
                                }
                            @endphp
                            
                            <div class="fw-bold">{{ $available }} PC Tersedia</div>
                            @if($broken > 0 || $maintenance > 0)
                                <a href="{{ route('admin.pc-damages.index') }}" class="text-decoration-none d-inline-block mt-1" style="font-size: 0.85rem;">
                                    <i class="bi bi-circle-fill {{ $broken > 0 ? 'text-danger' : 'text-warning' }} blink-dot" style="font-size: 0.6rem; vertical-align: middle;"></i> 
                                    @if($broken > 0)
                                        <span class="text-danger fw-semibold ms-1">{{ $broken }} PC Rusak</span>
                                    @endif
                                    @if($maintenance > 0)
                                        <span class="text-warning-emphasis fw-semibold {{ $broken > 0 ? 'ms-1' : 'ms-1' }}">{{ $maintenance }} Maintenance</span>
                                    @endif
                                </a>
                            @else
                                <span class="text-muted small">Kapasitas: {{ $lab->capacity }} PC</span>
                            @endif
                        </td>
                        <td>
                            @if($lab->status == 'active')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Maintenance</span>
                            @endif
                        </td>
                        <td>{{ $lab->bookings_count }} kali</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $lab->id }}"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('admin.laboratories.destroy', $lab) }}" method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    
                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal{{ $lab->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Labkom</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.laboratories.update', $lab) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body text-start">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Labkom</label>
                                            <input type="text" name="name" class="form-control" value="{{ $lab->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kapasitas PC</label>
                                            <input type="number" name="capacity" class="form-control" value="{{ $lab->capacity }}" required min="1">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="active" {{ $lab->status == 'active' ? 'selected' : '' }}>Aktif</option>
                                                <option value="maintenance" {{ $lab->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                            </select>
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
                    @empty
                    <tr><td colspan="6" class="text-center py-4">Belum ada data labkom</td></tr>
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
                <h5 class="modal-title fw-bold">Tambah Labkom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.laboratories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Labkom</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kapasitas PC</label>
                        <input type="number" name="capacity" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active">Aktif</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
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

<style>
@keyframes blink {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
    100% { opacity: 1; transform: scale(1); }
}
.blink-dot {
    animation: blink 1.5s infinite ease-in-out;
    display: inline-block;
}
</style>
@endsection
