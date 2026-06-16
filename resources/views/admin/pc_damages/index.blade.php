@extends('layouts.admin')

@section('title', 'Data Kerusakan PC | Techub Admin')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="fw-bold mb-1">Data Kerusakan PC</h3>
        <p class="text-muted-custom">Daftar PC yang sedang dalam status Rusak, Maintenance, atau Tidak Aktif.</p>
    </div>
    <form action="{{ route('admin.pc-damages.index') }}" method="GET" class="d-flex gap-2 align-items-center">
        <label for="laboratory_id" class="fw-medium text-nowrap d-none d-md-block">Filter Labkom:</label>
        <select name="laboratory_id" id="laboratory_id" class="form-select border-primary" onchange="this.form.submit()">
            <option value="">Semua Labkom</option>
            @foreach($laboratories as $lab)
                <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>
                    {{ $lab->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Labkom</th>
                        <th>Nomor/Nama PC</th>
                        <th>Detail Kerusakan</th>
                        <th>Status</th>
                        <th>Dilaporkan Pada</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($damages as $damage)
                    <tr>
                        <td class="fw-medium">{{ $damage->labPc->laboratory->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.lab-mappings.show', $damage->labPc->laboratory_id) }}" class="text-decoration-none text-primary" title="Buka Denah Pemetaan">
                                <div class="fw-bold d-inline-flex align-items-center gap-1 hover-underline">
                                    {{ $damage->labPc->name }}
                                    <i class="bi bi-box-arrow-up-right small"></i>
                                </div>
                            </a>
                            <div class="small text-muted-custom">Posisi: Baris {{ $damage->labPc->grid_row + 1 }}, Kolom {{ $damage->labPc->grid_col + 1 }}</div>
                        </td>
                        <td>
                            <div style="max-width: 300px; white-space: pre-wrap;" class="small">{{ $damage->description }}</div>
                        </td>
                        <td>
                            @if($damage->status == 'reported')
                                @if($damage->labPc->status == 'inactive')
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @elseif($damage->labPc->status == 'maintenance')
                                    <span class="badge bg-warning text-dark">Maintenance (Menunggu)</span>
                                @else
                                    <span class="badge bg-danger">Rusak (Menunggu)</span>
                                @endif
                            @elseif($damage->status == 'fixing')
                                <span class="badge bg-warning text-dark">Sedang Diperbaiki</span>
                            @else
                                <span class="badge bg-success">Selesai</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $damage->reported_at->format('d M Y') }}</div>
                            <div class="small text-muted">{{ $damage->reported_at->format('H:i') }}</div>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('admin.pc-damages.update-status', $damage->id) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm d-inline-block w-auto me-1" onchange="this.form.submit()">
                                    <option value="reported_broken" {{ $damage->status == 'reported' && $damage->labPc->status == 'broken' ? 'selected' : '' }}>Rusak (Menunggu)</option>
                                    <option value="reported_inactive" {{ $damage->status == 'reported' && $damage->labPc->status == 'inactive' ? 'selected' : '' }}>Tidak Aktif (Menunggu)</option>
                                    <option value="reported_maintenance" {{ $damage->status == 'reported' && $damage->labPc->status == 'maintenance' ? 'selected' : '' }}>Maintenance (Menunggu)</option>
                                    <option value="fixing" {{ $damage->status == 'fixing' ? 'selected' : '' }}>Sedang Diperbaiki</option>
                                    <option value="fixed">Tandai Selesai</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle fs-3 d-block mb-2 text-success opacity-50"></i>
                            Tidak ada PC yang rusak atau dalam maintenance saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
