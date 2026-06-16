<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pasca-Peminjaman Labkom</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #002B5C; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #002B5C; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table th, .info-table td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .info-table th { width: 30%; background-color: #f8f9fa; }
        .images-container { margin-top: 20px; text-align: center; }
        .images-container img { max-width: 100%; max-height: 400px; margin-bottom: 15px; border: 1px solid #ccc; padding: 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = public_path('Techub-Logo.png');
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoSrc = 'data:image/png;base64,' . $logoData;
            } else {
                $logoSrc = '';
            }
        @endphp
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Techub Logo" style="max-height: 60px; margin-bottom: 10px;">
        @endif
        <h2>Laporan Pasca-Peminjaman Labkom</h2>
    </div>

    <table class="info-table">
        <tr>
            <th>Kode Booking</th>
            <td>{{ $booking->tracking_code }}</td>
        </tr>
        <tr>
            <th>PIC Pemesan</th>
            <td>{{ $booking->pic_name }}</td>
        </tr>
        <tr>
            <th>Instansi</th>
            <td>{{ optional($booking->businessUnit)->name }}</td>
        </tr>
        <tr>
            <th>Labkom</th>
            <td>{{ optional($booking->laboratory)->name }}</td>
        </tr>
        <tr>
            <th>Waktu Pelaksanaan</th>
            <td>{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }} ({{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }})</td>
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
    </table>

    @if(!$booking->is_clean)
    <div class="images-container">
        <h3 style="text-align: left; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Lampiran Foto Bukti</h3>
        @if(is_array($booking->report_images) && count($booking->report_images) > 0)
            @foreach($booking->report_images as $index => $image)
                @php
                    $path = storage_path('app/public/' . $image);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    if (file_exists($path)) {
                        $data = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    } else {
                        $base64 = '';
                    }
                @endphp
                @if($base64)
                    <img src="{{ $base64 }}" alt="Foto Laporan {{ $index + 1 }}">
                    @if(($index + 1) % 2 == 0 && !$loop->last)
                        <div class="page-break"></div>
                    @endif
                @endif
            @endforeach
        @else
            <p style="text-align: left; color: #666; font-style: italic; font-size: 13px;">Admin tidak melampirkan foto bukti.</p>
        @endif
    </div>
    @endif
</body>
</html>
