<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #002B5C;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .status-accepted {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .changes-list {
            background-color: #f1f5f9;
            padding: 15px 20px;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
            margin-bottom: 25px;
        }
        .changes-list ul {
            margin: 0;
            padding-left: 20px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th, .details-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .details-table th {
            width: 40%;
            color: #64748b;
        }
        .footer {
            background-color: #f1f5f9;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="font-size: 1.25rem;">Pemberitahuan Status Booking</h2>
            <p style="margin:0; opacity: 0.8; font-size: 0.9rem;">Laboratorium Komputer</p>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $booking->pic_name }}</strong>,</p>
            <p>Permintaan peminjaman Laboratorium Komputer Anda telah diproses. Berikut adalah status terbarunya:</p>
            
            <div style="text-align: center;">
                @if($booking->status == 'accepted')
                    <div class="status-badge status-accepted">DISETUJUI</div>
                @elseif($booking->status == 'completed')
                    <div class="status-badge" style="background-color: #e0f2fe; color: #0284c7;">SELESAI</div>
                @elseif($booking->status == 'cancelled')
                    <div class="status-badge" style="background-color: #f3f4f6; color: #4b5563;">DIBATALKAN</div>
                @else
                    <div class="status-badge status-rejected">DITOLAK</div>
                @endif
            </div>

            @if($booking->status == 'completed')
                <div class="changes-list">
                    <p style="margin-top: 0; margin-bottom: 10px; font-weight: bold; color: #002B5C;">Laporan Pasca Peminjaman:</p>
                    <ul>
                        <li><strong>Keadaan Bersih:</strong> {{ $booking->is_clean ? 'Ya, Labkom dalam keadaan bersih' : 'Tidak, Labkom kotor/berantakan' }}</li>
                        @if(!empty($booking->report_note))
                            <li><strong>Catatan Tambahan:</strong> {{ $booking->report_note }}</li>
                        @endif
                        <li>Berkas PDF detail laporan dapat diunduh melalui tautan di bawah ini.</li>
                    </ul>
                </div>
            @endif

            <table class="details-table">
                <tr>
                    <th>Instansi</th>
                    <td>
                        {{ optional($booking->businessUnit)->name }}
                        @if($booking->subBusinessUnit)
                            - {{ $booking->subBusinessUnit->name }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Labkom</th>
                    <td>{{ $booking->lab_name }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <th>Waktu</th>
                    <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
                </tr>
                <tr>
                    <th>Keperluan</th>
                    <td>{{ $booking->purpose }}</td>
                </tr>
                <tr>
                    <th>Keadaan Bersih</th>
                    <td>{{ $booking->is_clean ? 'Ya' : 'Tidak' }}</td>
                </tr>
                @if(!empty($booking->report_note))
                <tr>
                    <th>Catatan Laporan</th>
                    <td>{{ $booking->report_note }}</td>
                </tr>
                @endif
                <tr>
                    <th>Lampiran Laporan</th>
                    <td>
                        <a href="{{ url('/track/' . $booking->tracking_code . '/pdf') }}" target="_blank" style="display: inline-block; padding: 6px 12px; background: #dc2626; border-radius: 4px; text-decoration: none; color: #ffffff; font-size: 12px; font-weight: bold;">
                            Unduh PDF Laporan
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Kode Booking</th>
                    <td><strong>{{ $booking->tracking_code }}</strong></td>
                </tr>
                <tr>
                    <th>Diproses Oleh</th>
                    <td><strong>{{ $booking->handled_by ?? 'Tim Admin' }}</strong></td>
                </tr>
                @if(in_array($booking->status, ['rejected', 'cancelled']) && !empty($booking->rejection_reason))
                <tr>
                    <th>Alasan</th>
                    <td style="color: #dc2626; font-weight: bold;">{{ $booking->rejection_reason }}</td>
                </tr>
                @endif
            </table>

            @if($booking->status == 'accepted')
                <p>Silakan gunakan fasilitas labkom sesuai dengan jadwal yang telah disetujui. Jangan lupa untuk mematuhi tata tertib penggunaan labkom yang berlaku.</p>
            @elseif($booking->status == 'completed')
                <p>Terima kasih telah menggunakan fasilitas Laboratorium Komputer. Kami harap kegiatan Anda berjalan dengan baik dan lancar.</p>
            @elseif($booking->status == 'cancelled')
                <p>Peminjaman Laboratorium Komputer Anda telah dibatalkan. Silakan ajukan ulang atau hubungi admin jika Anda memiliki pertanyaan.</p>
            @else
                <p>Mohon maaf, permintaan Anda belum dapat disetujui saat ini. Silakan ajukan jadwal di waktu lain atau hubungi admin untuk informasi lebih lanjut.</p>
            @endif
            
            <div style="margin-top: 25px; background: #f8fafc; padding: 15px; border-left: 4px solid #002B5C;">
                <p style="margin: 0;">Anda dapat memantau status terkini peminjaman Anda secara langsung melalui tautan berikut:</p>
                <p style="margin-top: 10px; margin-bottom: 0;">
                    <a href="{{ url('/track/' . $booking->tracking_code) }}" style="color: #002B5C; font-weight: bold; text-decoration: none;">Cek Status Peminjaman &rarr;</a>
                </p>
            </div>
            
            <p style="margin-top: 20px; font-size: 0.9em; color: #6c757d;">
                Dengan melakukan peminjaman, Anda menyetujui <a href="{{ url('/tos') }}" style="color: #1F6FEB; text-decoration: underline;">Syarat dan Ketentuan (ToS)</a> yang berlaku.
            </p>

            <p style="margin-top: 30px;">Salam hangat,<br><strong>Tim IT Infrastruktur</strong></p>
        </div>
        <div class="footer" style="font-size: 11px; line-height: 1.4;">
            This message is intended only for the designated recipient. If you have received this email in error, please notify the sender and delete this email. Thank you for your cooperation.<br><br>
            &copy; 2026 PT Binawan Inti Teknologi
        </div>
    </div>
</body>
</html>
