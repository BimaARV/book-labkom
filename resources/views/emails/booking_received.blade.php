@extends('emails.layout')

@section('content')
<p>Halo <strong>{{ $booking->pic_name }}</strong>,</p>
<p>Permintaan peminjaman Laboratorium Komputer Anda telah kami terima dan sedang menunggu persetujuan dari tim IT Infrastructure.</p>

<div style="text-align: center; margin: 24px 0;">
    <div style="display: inline-block; background: #f0f9ff; border: 2px solid #0ea5e9; border-radius: 8px; padding: 12px 32px;">
        <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Kode Booking Anda</div>
        <div style="font-size: 22px; font-weight: bold; color: #002B5C; letter-spacing: 2px; font-family: monospace;">{{ $booking->tracking_code }}</div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Simpan kode ini untuk memantau status booking Anda</div>
    </div>
</div>

<table style="width:100%; border-collapse: collapse; margin: 16px 0;">
    <tr>
        <th style="text-align:left; padding: 8px; background:#f8fafc; width:35%; color:#64748b; font-weight:600;">Nama PIC</th>
        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">
            <strong>{{ $booking->pic_name }}</strong><br>
            <small style="color:#64748b;">{{ $booking->whatsapp }} | {{ $booking->email }}</small>
        </td>
    </tr>
    <tr>
        <th style="text-align:left; padding: 8px; background:#f8fafc; color:#64748b; font-weight:600;">Instansi</th>
        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ optional($booking->businessUnit)->name }}{{ $booking->subBusinessUnit ? ' / ' . $booking->subBusinessUnit->name : '' }}</td>
    </tr>
    <tr>
        <th style="text-align:left; padding: 8px; background:#f8fafc; color:#64748b; font-weight:600;">Labkom</th>
        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ $booking->lab_name }}</td>
    </tr>
    <tr>
        <th style="text-align:left; padding: 8px; background:#f8fafc; color:#64748b; font-weight:600;">Tanggal</th>
        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">
            {{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}
            @if($booking->group_id && $totalWeeks > 1)
                @php $recurringEnd = \App\Models\Booking::where('group_id', $booking->group_id)->max('date'); @endphp
                @if($recurringEnd && $recurringEnd != $booking->date)
                    <br><small style="color:#64748b;">&#8635; Rutin s/d {{ \Carbon\Carbon::parse($recurringEnd)->format('d M Y') }} ({{ $totalWeeks }} Sesi)</small>
                @endif
            @endif
        </td>
    </tr>
    <tr>
        <th style="text-align:left; padding: 8px; background:#f8fafc; color:#64748b; font-weight:600;">Waktu</th>
        <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
    </tr>
    <tr>
        <th style="text-align:left; padding: 8px; background:#f8fafc; color:#64748b; font-weight:600;">Keperluan</th>
        <td style="padding: 8px;">{{ $booking->purpose }}</td>
    </tr>
</table>

<div style="text-align:center; margin-top: 24px;">
    <a href="{{ secure_url('/track/' . $booking->tracking_code) }}" style="display:inline-block; padding:10px 28px; background:#002B5C; border-radius:6px; text-decoration:none; color:#ffffff; font-weight:bold;">
        Pantau Status Booking
    </a>
</div>
@endsection