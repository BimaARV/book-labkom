@extends('layouts.admin')

@section('title', 'Statistik Penggunaan | Techub Admin')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
        <h3 class="fw-bold mb-1">Statistik Penggunaan</h3>
        <p class="text-muted-custom">Detail persentase tingkat penggunaan Labkom dan keaktifan Unit Bisnis.</p>
    </div>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" id="downloadReportBtn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-file-earmark-pdf me-1"></i> Download Laporan
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="downloadReportBtn">
            <li><a class="dropdown-item py-2" href="{{ route('admin.reports.pdf', ['range' => 'this_week']) }}"><i class="bi bi-calendar-week me-2 text-primary"></i> Minggu Ini</a></li>
            <li><a class="dropdown-item py-2" href="{{ route('admin.reports.pdf', ['range' => 'this_month']) }}"><i class="bi bi-calendar-month me-2 text-success"></i> Bulan Ini</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item py-2" type="button" data-bs-toggle="modal" data-bs-target="#customReportModal"><i class="bi bi-calendar-range me-2 text-warning"></i> Kustom Tanggal...</button></li>
        </ul>
    </div>
</div>

<!-- Modal Kustom Laporan -->
<div class="modal fade" id="customReportModal" tabindex="-1" aria-labelledby="customReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="customReportModalLabel">Download Laporan Kustom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET">
                <input type="hidden" name="range" value="custom">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Dari Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" required max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Sampai Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date" required max="{{ date('Y-m-d') }}">
                    </div>
                    <hr class="my-3">
                    <p class="text-muted small mb-2"><i class="bi bi-funnel me-1"></i>Filter opsional (kosongkan untuk semua data)</p>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Unit Bisnis</label>
                        <select name="business_unit_id" id="report_business_unit_id" class="form-select">
                            <option value="">Semua Unit Bisnis</option>
                            @foreach($businessUnits as $unit)
                                <option value="{{ $unit->id }}" data-subunits="{{ json_encode($unit->subUnits) }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0" id="report_sub_unit_container" style="display: none;">
                        <label class="form-label fw-medium">Sub Unit Bisnis</label>
                        <select name="sub_business_unit_id" id="report_sub_business_unit_id" class="form-select">
                            <option value="">Semua Sub Unit</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" formaction="{{ route('admin.reports.pdf') }}"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                    <button type="submit" class="btn btn-success" formaction="{{ route('admin.reports.excel') }}"><i class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                    <button type="submit" class="btn btn-secondary" formaction="{{ route('admin.reports.csv') }}"><i class="bi bi-filetype-csv me-1"></i> CSV</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($validTotalBookings == 0)
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i> Belum ada data peminjaman yang dapat dianalisis.
    </div>
@else
    <div class="row g-4 mb-4">
        <!-- Statistik Unit Bisnis / Sub Unit Bisnis -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 text-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-building text-primary me-2"></i> Unit Bisnis Teraktif</h5>
                    <button id="backToParentBtnStats" class="btn btn-sm btn-outline-primary mt-2" style="display:none;" onclick="resetBuChartStats()"><i class="bi bi-arrow-left me-1"></i> Kembali ke parent</button>
                </div>
                <div class="card-body mt-3 d-flex flex-column align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 500px; position: relative;">
                        <canvas id="statsBuChart"></canvas>
                    </div>
                    <div class="mt-4 text-muted small text-center px-4">
                        <i class="bi bi-info-circle-fill me-1"></i> Klik pada bagian grafik (Induk Unit) untuk melihat persentase detail untuk level Sub-Unit.
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Labkom -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 text-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pc-display text-primary me-2"></i> Labkom Terpopuler</h5>
                </div>
                <div class="card-body mt-3 d-flex flex-column align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 500px;">
                        <canvas id="statsLabChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    const validTotal = {{ $validTotalBookings }};
    if (validTotal > 0) {
        Chart.register(ChartDataLabels);
        const labData = @json($labChartData);
        
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        
        const getColors = (count, isChild) => {
            const baseColors = [
                '#002B5C', '#1E88E5', '#43A047', '#FDD835', 
                '#E53935', '#8E24AA', '#00ACC1', '#FB8C00',
                '#3949AB', '#00897B', '#7CB342', '#F4511E'
            ];
            return baseColors.slice(0, count).map(color => isChild ? color + 'CC' : color);
        };

        if(document.getElementById('statsBuChart')) {
            const buCtx = document.getElementById('statsBuChart').getContext('2d');
            const buData = @json($businessUnitChartData);
            const globalSum = buData.parentData.reduce((a, b) => a + b, 0);
            
            let currentChart = null;

            const renderParentChart = () => {
                document.getElementById('backToParentBtnStats').style.display = 'none';
                if (currentChart) currentChart.destroy();
                
                currentChart = new Chart(buCtx, {
                    type: 'doughnut',
                    data: {
                        labels: buData.parentLabels,
                        datasets: [{
                            data: buData.parentData,
                            backgroundColor: getColors(buData.parentData.length, false),
                            borderWidth: 1,
                            borderColor: isDark ? '#1a1d21' : '#fff'
                        }]
                    },
                    options: {
                        responsive: true, cutout: '45%',
                        plugins: {
                            legend: { position: 'bottom', labels: { color: isDark ? '#e0e0e0' : '#333' } },
                            datalabels: {
                                color: '#fff',
                                font: { weight: 'bolder', size: 15 },
                                textStrokeColor: 'rgba(0,0,0,0.4)',
                                textStrokeWidth: 2,
                                formatter: (value, context) => {
                                    if (globalSum === 0) return '';
                                    let pct = (value * 100) / globalSum;
                                    let displayPct = pct % 1 === 0 ? pct : pct.toFixed(1);
                                    return pct > 4 ? displayPct + '%' : '';
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let value = context.parsed;
                                        let percentage = globalSum > 0 ? (value * 100 / globalSum) : 0;
                                        let displayPct = percentage % 1 === 0 ? percentage : percentage.toFixed(1);
                                        return `${context.label}: ${value} (${displayPct}%) - Klik untuk detail`;
                                    }
                                }
                            }
                        },
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                renderChildChart(index);
                            }
                        }
                    }
                });
            };

            const renderChildChart = (parentIndex) => {
                const childMap = buData.childrenMap[parentIndex];
                if (!childMap || childMap.labels.length === 0) return;

                document.getElementById('backToParentBtnStats').style.display = 'inline-block';
                if (currentChart) currentChart.destroy();

                currentChart = new Chart(buCtx, {
                    type: 'doughnut',
                    data: {
                        labels: childMap.labels,
                        datasets: [{
                            data: childMap.data,
                            backgroundColor: getColors(childMap.data.length, true),
                            borderWidth: 1,
                            borderColor: isDark ? '#1a1d21' : '#fff'
                        }]
                    },
                    options: {
                        responsive: true, cutout: '45%',
                        plugins: {
                            legend: { position: 'bottom', labels: { color: isDark ? '#e0e0e0' : '#333' } },
                            datalabels: {
                                color: '#fff',
                                font: { weight: 'bolder', size: 15 },
                                textStrokeColor: 'rgba(0,0,0,0.4)',
                                textStrokeWidth: 2,
                                formatter: (value, context) => {
                                    if (globalSum === 0) return '';
                                    let pct = (value * 100) / globalSum;
                                    let displayPct = pct % 1 === 0 ? pct : pct.toFixed(1);
                                    return pct > 4 ? displayPct + '%' : '';
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let value = context.parsed;
                                        let percentage = globalSum > 0 ? (value * 100 / globalSum) : 0;
                                        let displayPct = percentage % 1 === 0 ? percentage : percentage.toFixed(1);
                                        
                                        // Hitung persentase lokal (relatif terhadap induknya) untuk konteks tambahan di tooltip
                                        let localSum = context.dataset.data.reduce((a, b) => a + b, 0);
                                        let localPct = localSum > 0 ? (value * 100 / localSum) : 0;
                                        let localDisplayPct = localPct % 1 === 0 ? localPct : localPct.toFixed(1);

                                        return `${context.label}: ${value} (${displayPct}% dari Keseluruhan | ${localDisplayPct}% dari Induk)`;
                                    }
                                }
                            }
                        }
                    }
                });
            };

            window.resetBuChartStats = renderParentChart;
            renderParentChart();
        }

        const ctxLab = document.getElementById('statsLabChart');
        if(ctxLab) {
            new Chart(ctxLab, {
                type: 'doughnut',
                data: {
                    labels: labData.labels,
                    datasets: [{
                        data: labData.data,
                        backgroundColor: getColors(labData.data.length, false),
                        borderWidth: 1,
                        borderColor: isDark ? '#1a1d21' : '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '45%',
                    plugins: {
                        datalabels: {
                            color: '#fff',
                            font: { weight: 'bolder', size: 15 },
                            textStrokeColor: 'rgba(0,0,0,0.4)',
                            textStrokeWidth: 2,
                            formatter: (value) => {
                                if (validTotal === 0) return '';
                                let pct = (value / validTotal) * 100;
                                let displayPct = pct % 1 === 0 ? pct : pct.toFixed(1);
                                return pct > 4 ? displayPct + '%' : '';
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label;
                                    const val = context.parsed;
                                    const pct = (Math.round((val / validTotal) * 1000) / 10);
                                    return `${label}: ${pct}% (${val} Peminjaman)`;
                                }
                            }
                        },
                        legend: { 
                            display: true, 
                            position: 'bottom',
                            labels: { color: isDark ? '#e0e0e0' : '#333' }
                        }
                    }
                }
            });
        }
    }

    // Report modal: dynamic sub unit dropdown
    const reportBuSelect = document.getElementById('report_business_unit_id');
    const reportSubContainer = document.getElementById('report_sub_unit_container');
    const reportSubSelect = document.getElementById('report_sub_business_unit_id');

    if (reportBuSelect) {
        reportBuSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const subUnits = selected.dataset.subunits ? JSON.parse(selected.dataset.subunits) : [];

            reportSubSelect.innerHTML = '<option value="">Semua Sub Unit</option>';

            if (subUnits.length > 0) {
                subUnits.forEach(sub => {
                    reportSubSelect.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
                });
                reportSubContainer.style.display = '';
            } else {
                reportSubContainer.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
