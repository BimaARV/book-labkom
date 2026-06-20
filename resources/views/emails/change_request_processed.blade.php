<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #002B5C; padding: 20px; text-align: center; }
        .header h2 { margin: 0; color: #ffffff; font-size: 24px; font-weight: 600; }
        .content { padding: 30px; }
        .footer { background-color: #f1f5f9; text-align: center; padding: 15px; font-size: 11px; color: #64748b; line-height: 1.4; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #002B5C; color: #ffffff !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .status-badge { padding: 8px 15px; border-radius: 4px; font-weight: bold; display: inline-block; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .table-info { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-info th, .table-info td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .table-info th { width: 30%; color: #555; }
    </style>
</head>
<body>
@php
    $statusClass = $changeRequest->status === 'approved' ? 'status-approved' : 'status-rejected';
    $statusText = $changeRequest->status === 'approved' ? 'DISETUJUI' : 'DITOLAK';
    
    $typeLabel = '';
    $detailText = '';
    if ($changeRequest->type === 'cancellation') {
        $typeLabel = 'Pembatalan';
        $detailText = "<ul style='margin:0; padding-left:20px;'><li>Alasan Pembatalan: {$changeRequest->reason}</li></ul>";
    } elseif ($changeRequest->type === 'reschedule') {
        $typeLabel = 'Perubahan Jadwal';
        $date = \Carbon\Carbon::parse($changeRequest->requested_date)->format('d M Y');
        $time = \Carbon\Carbon::parse($changeRequest->requested_start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($changeRequest->requested_end_time)->format('H:i');
        $detailText = "<ul style='margin:0; padding-left:20px;'><li>Labkom: {$changeRequest->original_lab_name}</li><li>Jadwal Baru: {$date} | {$time}</li></ul>";
    } elseif ($changeRequest->type === 'relocation') {
        $typeLabel = 'Pindah Labkom';
        $oldLab = $changeRequest->original_lab_name;
        $newLab = $changeRequest->requested_is_all_labs ? \App\Models\Laboratory::getAllLabsName() : optional($changeRequest->requestedLaboratory)->name;
        $detailText = "<ul style='margin:0; padding-left:20px;'><li>Dari: {$oldLab}</li><li>Menjadi: {$newLab}</li></ul>";
    }
@endphp

    <div class="container">
        <div class="header">
            <h2>Hasil Pengajuan Perubahan</h2>
        </div>
        
        <div class="content">
            <p>Halo <strong>{{ $changeRequest->booking->pic_name }}</strong>,</p>
            <p>Pengajuan perubahan untuk booking Labkom Anda telah diproses oleh tim IT Infrastructure dengan hasil:</p>
            
            <div style="text-align: center; margin: 20px 0;">
                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
            </div>

            <table class="table-info">
                <tr>
                    <th>Pemohon</th>
                    <td>
                        <ul style="margin:0; padding-left:20px;">
                            <li>Nama: {{ $changeRequest->booking->pic_name }}</li>
                            <li>Email: {{ $changeRequest->booking->email }}</li>
                            <li>Unit Bisnis: 
                                {{ optional($changeRequest->booking->businessUnit)->name }}
                                @if($changeRequest->booking->subBusinessUnit)
                                    - {{ $changeRequest->booking->subBusinessUnit->name }}
                                @endif
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>Jenis Pengajuan</th>
                    <td>{{ $typeLabel }}</td>
                </tr>
                <tr>
                    <th>Detail Perubahan</th>
                    <td>{!! $detailText !!}</td>
                </tr>
                <tr>
                    <th>Diproses Oleh IT Infrastructure</th>
                    <td>{{ $admin->name }}</td>
                </tr>
                @if(!empty($changeRequest->admin_note))
                <tr>
                    <th>Catatan IT Infrastructure</th>
                    <td>{{ $changeRequest->admin_note }}</td>
                </tr>
                @endif
            </table>
            
            <p style="margin-top: 20px;">
                Anda dapat memantau status terkini peminjaman Anda secara langsung melalui tautan berikut:
            </p>
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ secure_url('/track/' . $changeRequest->booking->tracking_code) }}" class="btn">Lihat Status Booking</a>
            </div>
            
            <p style="margin-top: 30px;">Salam hangat,<br><strong>Tim IT Infrastruktur</strong></p>
        </div>
        
        <div class="footer">
            This message is intended only for the designated recipient. If you have received this email in error, please notify the sender and delete this email. Thank you for your cooperation.<br><br>
            &copy; {{ date('Y') }} PT Binawan Inti Teknologi
        </div>
    </div>
</body>
</html>
