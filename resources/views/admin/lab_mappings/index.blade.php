@extends('layouts.admin')

@section('title', 'Pemetaan Labkom | Techub Admin')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold mb-1">Pemetaan Labkom</h3>
        <p class="text-muted-custom">Pilih labkom untuk mengonfigurasi grid dan memetakan PC.</p>
    </div>
</div>

<div class="row g-4">
    @forelse($laboratories as $lab)
    <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm h-100 interactive-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary p-3 rounded me-3 text-white">
                        <i class="bi bi-grid-3x3-gap fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $lab->name }}</h5>
                        <span class="badge bg-secondary">{{ $lab->capacity }} Kapasitas Asli</span>
                    </div>
                </div>
                
                <div class="mb-3 small">
                    @if($lab->grid_rows && $lab->grid_cols)
                        <div class="text-muted-custom mb-1"><i class="bi bi-border-all me-1"></i> Layout: {{ $lab->grid_rows }} x {{ $lab->grid_cols }}</div>
                    @else
                        <div class="text-warning mb-1"><i class="bi bi-exclamation-triangle me-1"></i> Grid belum diatur</div>
                    @endif
                    <div class="text-muted-custom"><i class="bi bi-pc-display me-1"></i> PC Terpetakan: {{ $lab->lab_pcs_count }}</div>
                </div>
                
                <a href="{{ route('admin.lab-mappings.show', $lab->id) }}" class="btn btn-outline-primary w-100">Buka Denah</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">Belum ada data Labkom. Silakan tambahkan di Master Data Labkom.</div>
    </div>
    @endforelse
</div>
@endsection
