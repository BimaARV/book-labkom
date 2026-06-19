<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #002B5C;
            padding-bottom: 20px;
        }
        .header img {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #002B5C;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        
        .section-title {
            color: #002B5C;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .stats-container {
            width: 100%;
            margin-bottom: 30px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats-table td {
            padding: 10px;
            width: 33.33%;
            vertical-align: top;
        }
        .stat-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        .stat-box h4 {
            margin: 0 0 10px 0;
            color: #6c757d;
            font-size: 12px;
            text-transform: uppercase;
        }
        .stat-box .value {
            font-size: 24px;
            font-weight: bold;
            color: #002B5C;
            margin: 0;
        }
        
        .highlights {
            background-color: #e3f2fd;
            border-left: 4px solid #1976d2;
            padding: 15px;
            margin-bottom: 30px;
        }
        .highlights p {
            margin: 5px 0;
            font-size: 13px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f4f6f8;
            color: #002B5C;
            font-weight: bold;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .status-badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        .status-pending { background-color: #ffc107; color: #333; }
        .status-accepted { background-color: #198754; }
        .status-completed { background-color: #0dcaf0; color: #333; }
        .status-rejected { background-color: #dc3545; }
        .status-cancelled { background-color: #6c757d; }
        
        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="header">
        <?php
            // Encode image to base64 so DOMPDF can render it without absolute URL issues
            $path = public_path('Techub-Logo.png');
            if(file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                echo '<img src="'.$base64.'" alt="Techub Logo">';
            }
        ?>
        <h2>{{ $title }}</h2>
    </div>

    <div class="section-title">Ringkasan Peminjaman</div>
    
    <div class="stats-container">
        <table class="stats-table">
            <tr>
                <td>
                    <div class="stat-box">
                        <h4>Total Booking</h4>
                        <p class="value">{{ $stats['total'] }}</p>
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <h4>Selesai</h4>
                        <p class="value">{{ $stats['completed'] }}</p>
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <h4>Diterima (Proses)</h4>
                        <p class="value">{{ $stats['accepted'] }}</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="stat-box">
                        <h4>Menunggu (Pending)</h4>
                        <p class="value" style="color:#ffc107;">{{ $stats['pending'] }}</p>
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <h4>Ditolak</h4>
                        <p class="value" style="color:#dc3545;">{{ $stats['rejected'] }}</p>
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <h4>Dibatalkan</h4>
                        <p class="value" style="color:#6c757d;">{{ $stats['cancelled'] }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Analisis Utilisasi Aset</div>
    <div style="margin-bottom: 30px;">
        <h4 style="color: #002B5C; margin-bottom: 5px;">Tingkat Utilisasi Laboratorium Komputer</h4>
        <p style="color: #666; font-size: 11px; margin-top: 0; margin-bottom: 15px;">(Persentase dihitung dari total peminjaman valid pada periode laporan ini)</p>
        <div style="text-align: center;">
            @if($labChartBase64)
                <img src="{{ $labChartBase64 }}" style="max-width: 450px; height: auto;">
            @else
                <p style="color: #777;">Tidak ada data grafik</p>
            @endif
        </div>
    </div>

    <div class="section-title">Analisis Pengguna Teratas</div>
    <div style="margin-bottom: 40px;">
        <h4 style="color: #002B5C; margin-bottom: 5px;">Distribusi Penggunaan Berdasarkan Instansi/Fakultas</h4>
        <p style="color: #666; font-size: 11px; margin-top: 0; margin-bottom: 15px;">(Menggambarkan pangsa pengguna yang paling aktif memanfaatkan fasilitas labkom)</p>
        <div style="text-align: center;">
            @if($buChartBase64)
                <img src="{{ $buChartBase64 }}" style="max-width: 450px; height: auto;">
            @else
                <p style="color: #777;">Tidak ada data grafik</p>
            @endif
        </div>
    </div>

    <div style="page-break-before: always;"></div>
    <div class="section-title" style="margin-top: 0;">Rincian Data Peminjaman</div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Nama PIC</th>
                <th width="30%">Instansi</th>
                <th width="15%">Labkom</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $index => $booking)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($booking->date)->format('d/m/Y') }}<br>
                    <span style="color: #666; font-size: 11px;">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</span>
                </td>
                <td>
                    <strong>{{ $booking->pic_name }}</strong><br>
                    <span style="color: #666; font-size: 11px;">{{ $booking->email }}<br>{{ $booking->whatsapp }}</span>
                </td>
                <td>
                    {{ optional($booking->businessUnit)->name }}{{ $booking->subBusinessUnit ? ' / ' . $booking->subBusinessUnit->name : '' }}
                </td>
                <td>{{ $booking->lab_name }}</td>
                <td>
                    @if($booking->status == 'pending')
                        <span class="status-badge status-pending">Pending</span>
                    @elseif($booking->status == 'accepted')
                        <span class="status-badge status-accepted">Diterima</span>
                    @elseif($booking->status == 'completed')
                        <span class="status-badge status-completed">Selesai</span>
                    @elseif($booking->status == 'rejected')
                        <span class="status-badge status-rejected">Ditolak</span>
                    @elseif($booking->status == 'cancelled')
                        <span class="status-badge status-cancelled">Dibatalkan</span>
                    @else
                        <span class="status-badge">{{ $booking->status }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">Tidak ada data peminjaman pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
        <p>Laporan di-generate secara otomatis oleh Sistem Techub</p>
    </div>

</body>
</html>
