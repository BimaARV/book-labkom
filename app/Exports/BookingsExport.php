<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingsExport implements FromCollection, WithHeadings, WithMapping
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
            'Waktu Mulai',
            'Waktu Selesai',
            'Keperluan',
            'Status',
            'Alasan Penolakan/Batal'
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->id,
            $booking->tracking_code,
            $booking->pic_name,
            $booking->whatsapp,
            $booking->email,
            optional($booking->businessUnit)->name,
            optional($booking->subBusinessUnit)->name,
            $booking->lab_name,
            \Carbon\Carbon::parse($booking->date)->format('d M Y'),
            \Carbon\Carbon::parse($booking->start_time)->format('H:i'),
            \Carbon\Carbon::parse($booking->end_time)->format('H:i'),
            $booking->purpose,
            $booking->status,
            $booking->rejection_reason ?? '-'
        ];
    }
}
