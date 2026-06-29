<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8fafc; color: #1e293b; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #002B5C; color: #ffffff; text-align: center; padding: 20px; }
        .header h2 { margin: 0; }
        .content { padding: 30px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .details-table th { width: 40%; color: #64748b; }
        .footer { background-color: #f1f5f9; text-align: center; padding: 15px; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Pemberitahuan Booking</h2>
        <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 14px;">Laboratorium Komputer</p>
    </div>
    <div class="content">
        <p>Halo <strong>{{ $booking->pic_name }}</strong>,</p>
        <p>Permintaan peminjaman Laboratorium Komputer Anda telah kami terima dan sedang menunggu persetujuan dari tim IT Infrastructure.</p>

        <div style="text-align:center; margin: 24px 0;">
            <div style="display:inline-block; background:#f0f9ff; border:2px solid #0ea5e9; border-radius:8px; padding:12px 32px;">
                <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Kode Booking Anda</div>
                <div style="font-size:22px; font-weight:bold; color:#002B5C; letter-spacing:2px; font-family:monospace;">{{ $booking->tracking_code }}</div>
                <div style="font-size:11px; color:#94a3b8; margin-top:4px;">Simpan kode ini untuk memantau status booking Anda</div>
            </div>
        </div>

        <table class="details-table">
            <tr>
                <th>Nama PIC</th>
                <td>
                    <strong>{{ $booking->pic_name }}</strong><br>
                    <small style="color:#64748b;">{{ $booking->whatsapp }} | {{ $booking->email }}</small>
                </td>
            </tr>
            <tr>
                <th>Instansi</th>
                <td>{{ optional($booking->businessUnit)->name }}{{ $booking->subBusinessUnit ? ' / ' . $booking->subBusinessUnit->name : '' }}</td>
            </tr>
            <tr>
                <th>Labkom</th>
                <td>{{ $booking->lab_name }}</td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td>
                    {{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}
                    @if($booking->group_id && $totalSessions > 1 && $frequency)
                        @php 
                            $recurringEnd = \App\Models\Booking::where('group_id', $booking->group_id)->max('date'); 
                            $freqLabel = $frequency === 'daily' ? 'Harian' : 'Setiap Minggu';
                            $sesiLabel = $frequency === 'daily' ? "{$totalSessions} Hari" : "{$totalSessions} Minggu";
                        @endphp
                        @if($recurringEnd && $recurringEnd != $booking->date)
                            <br><small style="color:#64748b; font-weight: bold;">Pemesanan Rutin: Ya</small>
                            <br><small style="color:#64748b;">Frekuensi: {{ $freqLabel }}</small>
                            <br><small style="color:#64748b;">Periode Rutin: {{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($recurringEnd)->format('d M Y') }}</small>
                            <br><small style="color:#64748b;">Total Sesi: {{ $sesiLabel }}</small>
                        @endif
                    @endif
                </td>
            </tr>
            <tr>
                <th>Waktu</th>
                <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
            </tr>
            <tr>
                <th>Keperluan</th>
                <td>{{ $booking->purpose }}</td>
            </tr>
        </table>

        <div style="margin-top: 25px; background: #f8fafc; padding: 15px; border-left: 4px solid #002B5C;">
            <p style="margin: 0;">Anda dapat memantau status terkini peminjaman Anda secara langsung melalui tautan berikut:</p>
            <p style="margin-top: 10px; margin-bottom: 0;">
                <a href="{{ secure_url('/track/' . $booking->tracking_code) }}" style="color: #002B5C; font-weight: bold; text-decoration: none;">Cek Status Peminjaman &rarr;</a>
            </p>
        </div>

        <p style="margin-top: 20px; font-size: 0.9em; color: #6c757d;">
            Dengan melakukan peminjaman, Anda menyetujui <a href="{{ secure_url('/tos') }}" style="color: #1F6FEB; text-decoration: underline;">Syarat dan Ketentuan (ToS)</a> yang berlaku.
        </p>

        <p style="margin-top: 30px;">Salam hangat,<br><strong>Tim IT Infrastruktur</strong></p>
    </div>
    <div class="footer">
        This message is intended only for the designated recipient. If you have received this email in error, please notify the sender and delete this email. Thank you for your cooperation.<br><br>
        &copy; 2026 PT Binawan Inti Teknologi
    </div>
</div>
</body>
</html>