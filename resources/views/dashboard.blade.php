@extends('layouts.admin')

@section('title', 'Dashboard | Techub Admin')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Dashboard</h3>
    <p class="text-muted-custom">Ringkasan aktivitas dan status peminjaman Labkom terkini.</p>
</div>

<div class="row g-4 mb-5">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm interactive-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-muted-custom fw-semibold mb-0 text-uppercase">Total Booking</h6>
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0">{{ $totalBookings }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm interactive-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-muted-custom fw-semibold mb-0 text-uppercase">Pending</h6>
                    <div class="bg-warning bg-opacity-10 text-warning p-2 rounded">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0">{{ $pendingBookings }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm interactive-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-muted-custom fw-semibold mb-0 text-uppercase">Diterima</h6>
                    <div class="bg-success bg-opacity-10 text-success p-2 rounded">
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0">{{ $acceptedBookings }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm interactive-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-muted-custom fw-semibold mb-0 text-uppercase">Ditolak</h6>
                    <div class="bg-danger bg-opacity-10 text-danger p-2 rounded">
                        <i class="bi bi-x-circle fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0">{{ $rejectedBookings }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3 mt-4">
        <div class="card border-0 shadow-sm interactive-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-muted-custom fw-semibold mb-0 text-uppercase">Selesai</h6>
                    <div class="bg-info bg-opacity-10 text-info p-2 rounded">
                        <i class="bi bi-check2-all fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0">{{ $completedBookings }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3 mt-4">
        <div class="card border-0 shadow-sm interactive-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-muted-custom fw-semibold mb-0 text-uppercase">Dibatalkan</h6>
                    <div class="bg-secondary bg-opacity-10 text-secondary p-2 rounded">
                        <i class="bi bi-slash-circle fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-0">{{ $cancelledBookings }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Booking Terbaru</h5>
                <a href="{{ url('/admin/bookings') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>PIC / Instansi</th>
                                <th>Labkom</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings as $booking)
                            <tr>
                                  <td>
                                      <div class="fw-semibold">{{ $booking->pic_name }}</div>
                                      <div class="small text-muted-custom">
                                          {{ optional($booking->businessUnit)->name }}
                                          @if($booking->subBusinessUnit)
                                              - {{ $booking->subBusinessUnit->name }}
                                          @endif
                                      </div>
                                  </td>
                                  <td>{{ optional($booking->laboratory)->name }}</td>
                                  <td>
                                      <div class="small fw-medium text-primary">{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}</div>
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
                                  </td>
                                  <td class="text-center">
                                      @php
                                        $instansi = optional($booking->businessUnit)->name;
                                        if($booking->subBusinessUnit) {
                                            $instansi .= ' (' . $booking->subBusinessUnit->name . ')';
                                        }
                                      @endphp
                                      <button type="button" class="btn btn-sm btn-primary" title="Detail" onclick="showDetail('{{ addslashes($booking->pic_name) }}', '{{ addslashes($instansi) }}', '{{ $booking->whatsapp }}', '{{ $booking->email }}', '{{ optional($booking->laboratory)->name }}', '{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}', '{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}', '{{ addslashes($booking->purpose) }}')"><i class="bi bi-eye"></i></button>
                                  </td>
                              </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data booking.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Statistik Instansi (Donut Chart) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 text-center">
                <h6 class="text-uppercase fw-semibold mb-0" style="letter-spacing: 1px;">Statistik Instansi</h6>
                <button id="buChartBackBtn" class="btn btn-sm btn-outline-primary mt-2" style="display: none;"><i class="bi bi-arrow-left me-1"></i> Kembali ke parent</button>
            </div>
            <div class="card-body p-4 d-flex justify-content-center">
                @if($validTotalBookings > 0)
                    <div style="width: 100%; max-width: 320px;">
                        <canvas id="dashboardBuChart"></canvas>
                    </div>
                @else
                    <p class="text-muted small">Belum ada data</p>
                @endif
            </div>
        </div>

        <!-- Statistik Labkom (Donut Chart) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 text-center">
                <h6 class="text-uppercase fw-semibold mb-0" style="letter-spacing: 1px;">Statistik Labkom</h6>
            </div>
            <div class="card-body p-4 d-flex justify-content-center">
                @if($validTotalBookings > 0)
                    <div style="width: 100%; max-width: 320px;">
                        <canvas id="dashboardLabChart"></canvas>
                    </div>
                @else
                    <p class="text-muted small">Belum ada data</p>
                @endif
            </div>
        </div>

        <!-- Tombol Selengkapnya -->
        <a href="{{ route('admin.statistics.index') }}" class="btn btn-outline-primary w-100 p-3 fw-medium d-flex align-items-center justify-content-center">
            <i class="bi bi-bar-chart-line me-2 fs-5"></i> Lihat Statistik Lengkap
        </a>
    </div>
</div>

@push('scripts')
<script>
    function showDetail(pic, unit, wa, email, lab, date, time, purpose) {
        Swal.fire({
            title: 'Detail Booking',
            html: `
                <div class="text-start fs-6 mt-3">
                    <p class="mb-2"><strong>Labkom:</strong> <span class="text-primary">${lab}</span></p>
                    <p class="mb-2"><strong>Waktu:</strong> ${date} | ${time}</p>
                    <hr>
                    <p class="mb-2"><strong>PIC / Instansi:</strong> ${pic} (${unit})</p>
                    <p class="mb-2"><strong>Kontak:</strong> ${wa} | ${email}</p>
                    <p class="mb-2 mt-3"><strong>Keperluan:</strong><br><span>${purpose}</span></p>
                </div>
            `,
            icon: 'info',
            confirmButtonColor: '#002B5C',
            confirmButtonText: 'Tutup'
        });
    }

    const getColors = (count, isChild) => {
        const baseColors = [
            '#002B5C', '#1E88E5', '#43A047', '#FDD835', 
            '#E53935', '#8E24AA', '#00ACC1', '#FB8C00',
            '#3949AB', '#00897B', '#7CB342', '#F4511E'
        ];
        return baseColors.slice(0, count).map(color => isChild ? color + 'CC' : color);
    };

    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    
    // Pastikan plugin ter-register sebelum chart dibuat
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    if(document.getElementById('dashboardBuChart')) {
        const buCtx = document.getElementById('dashboardBuChart').getContext('2d');
        const buData = @json($businessUnitChartData);
        const backBtn = document.getElementById('buChartBackBtn');
        const globalSum = buData.parentData.reduce((a, b) => a + b, 0);
        
        let currentChart = null;

        const renderParentChart = () => {
            backBtn.style.display = 'none';
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

            backBtn.style.display = 'inline-block';
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

        backBtn.addEventListener('click', renderParentChart);
        renderParentChart();
    }

    const validTotal = {{ $validTotalBookings }};
    if (validTotal > 0) {
        const labData = @json($labChartData);
        
        const ctxLab = document.getElementById('dashboardLabChart');
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
                                    const pct = Math.round((val/validTotal)*100);
                                    return `${label}: ${pct}% (${val} Peminjaman)`;
                                }
                            }
                        },
                        legend: { display: false }
                    }
                }
            });
        }
    }
</script>
@endpush
@endsection
