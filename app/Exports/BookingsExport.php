<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $bookings;

    public function __construct($bookings)
    {
        $this->bookings = $bookings;
    }

    public function collection()
    {
        return $this->bookings;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Tracking',
            'PIC',
            'WhatsApp',
            'Email',
            'Unit Bisnis',
            'Sub Unit Bisnis',
            'Labkom',
            'Tanggal',
            'Tanggal Mulai Rutin',
            'Tanggal Berakhir Rutin',
            'Waktu Mulai',
            'Waktu Selesai',
            'Keperluan',
            'Status',
            'Alasan Penolakan/Batal'
        ];
    }

    public function map($booking): array
    {
        // Determine recurring start & end dates
        $recurringStart = '-';
        $recurringEnd   = '-';

        if ($booking->group_id) {
            $groupMin = Booking::where('group_id', $booking->group_id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->min('date');
            $groupMax = Booking::where('group_id', $booking->group_id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->max('date');
            if ($groupMin) {
                $recurringStart = \Carbon\Carbon::parse($groupMin)->format('d M Y');
            }
            if ($groupMax) {
                $recurringEnd = \Carbon\Carbon::parse($groupMax)->format('d M Y');
            }
        }

        // Prefix WA number with tab character so Excel treats it as text
        $whatsapp = $booking->whatsapp ? "\t" . $booking->whatsapp : '-';

        return [
            $booking->id,
            $booking->tracking_code,
            $booking->pic_name,
            $whatsapp,
            $booking->email,
            optional($booking->businessUnit)->name,
            optional($booking->subBusinessUnit)->name,
            $booking->lab_name,
            \Carbon\Carbon::parse($booking->date)->format('d M Y'),
            $recurringStart,
            $recurringEnd,
            \Carbon\Carbon::parse($booking->start_time)->format('H:i'),
            \Carbon\Carbon::parse($booking->end_time)->format('H:i'),
            $booking->purpose,
            $booking->status,
            $booking->rejection_reason ?? '-'
        ];
    }

    /**
     * Force column D (WhatsApp) as text format so Excel does not convert to scientific notation.
     */
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
