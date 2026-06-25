<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laboratory;
use App\Models\BusinessUnit;
use App\Models\Booking;

class PublicController extends Controller
{
    public function index()
    {
        $laboratories = Laboratory::where('status', 'active')->with('labPcs')->get();
        $businessUnits = BusinessUnit::with('subUnits')->get();
        
        return view('welcome', compact('laboratories', 'businessUnits'));
    }

    public function check(Request $request)
    {
        // Fitur pencarian ketersediaan labkom
        // Mengambil semua lab beserta booking yang ada di tanggal tertentu.
        $date = $request->query('date', date('Y-m-d'));
        
        $allLabsBookings = Booking::where('is_all_labs', true)
            ->whereDate('date', $date)
            ->whereIn('status', ['accepted', 'pending'])
            ->get();

        $laboratories = Laboratory::where('status', 'active')->with([
            'bookings' => function($query) use ($date) {
                $query->whereDate('date', $date)->whereIn('status', ['accepted', 'pending']);
            },
            'labPcs'
        ])->get();

        foreach ($laboratories as $lab) {
            foreach ($allLabsBookings as $allBooking) {
                $lab->bookings->push($allBooking);
            }
            $lab->bookings = $lab->bookings->sortBy('start_time')->values();
        }

        return view('check', compact('laboratories', 'date'));
    }

    public function track($code)
    {
        $booking = Booking::with('laboratory')->where('tracking_code', $code)->firstOrFail();
        return view('track', compact('booking'));
    }

    public function downloadPdf($code)
    {
        $booking = Booking::with(['laboratory', 'businessUnit'])->where('tracking_code', $code)->firstOrFail();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.booking_report', compact('booking'));
        return $pdf->stream('Laporan_Booking_'.$code.'.pdf');
    }

    public function bookingList(Request $request)
    {
        $search = $request->input('search');
        $bookings = null;
        $laboratories = Laboratory::where('status', 'active')->get();

        if ($search) {
            $request->validate([
                'search' => 'required|string'
            ]);
            $bookings = Booking::with(['laboratory', 'changeRequests' => function($q) {
                $q->where('status', 'pending');
            }])->where('email', $search)->orWhere('tracking_code', $search)->orderBy('created_at', 'desc')->get();
        }

        return view('booking-list', compact('bookings', 'search', 'laboratories'));
    }

    public function storeChangeRequest(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'type' => 'required|in:cancellation,reschedule,relocation',
            'reason' => 'required|string',
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        // Verifikasi kepemilikan booking
        $request->validate(['email' => 'required|email']);
        if (strtolower($booking->email) !== strtolower($request->email)) {
            return back()->with('error', 'Anda tidak berhak mengajukan perubahan untuk booking ini.');
        }
        
        if ($booking->changeRequests()->where('status', 'pending')->exists()) {
            return back()->with('error', 'Anda sudah memiliki pengajuan perubahan yang sedang diproses untuk booking ini.');
        }

        $data = [
            'booking_id' => $booking->id,
            'type' => $request->type,
            'reason' => $request->reason,
            'status' => 'pending'
        ];

        if ($request->type == 'reschedule') {
            $request->validate([
                'requested_date' => 'required|date',
                'requested_start_time' => 'required|date_format:H:i',
                'requested_end_time' => 'required|date_format:H:i|after:requested_start_time',
            ]);

            $isSameDate = $booking->date === $request->requested_date;
            $isSameStartTime = \Carbon\Carbon::parse($booking->start_time)->format('H:i') === \Carbon\Carbon::parse($request->requested_start_time)->format('H:i');
            $isSameEndTime = \Carbon\Carbon::parse($booking->end_time)->format('H:i') === \Carbon\Carbon::parse($request->requested_end_time)->format('H:i');

            if ($isSameDate && $isSameStartTime && $isSameEndTime) {
                return back()->with('error', 'Tanggal dan waktu yang diajukan sama dengan jadwal saat ini.');
            }

            $data['requested_date'] = $request->requested_date;
            $data['requested_start_time'] = $request->requested_start_time;
            $data['requested_end_time'] = $request->requested_end_time;
        } elseif ($request->type == 'relocation') {
            if ($request->requested_laboratory_id !== 'all') {
                $request->validate([
                    'requested_laboratory_id' => 'required|exists:laboratories,id'
                ]);
            }

            $requestedIsAllLabs = $request->requested_laboratory_id === 'all';
            $requestedLabId = $requestedIsAllLabs ? null : $request->requested_laboratory_id;

            if ($booking->is_all_labs == $requestedIsAllLabs && $booking->laboratory_id == $requestedLabId) {
                return back()->with('error', 'Lab yang diajukan sama dengan lab saat ini.');
            }

            $data['requested_laboratory_id'] = $requestedLabId;
            $data['requested_is_all_labs'] = $requestedIsAllLabs;
            $data['original_laboratory_id'] = $booking->laboratory_id;
            $data['original_is_all_labs'] = $booking->is_all_labs;
        }

        $changeReq = \App\Models\BookingChangeRequest::create($data);

        try {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->sendChangeRequestNotification($changeReq);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to trigger change request notification: " . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan perubahan berhasil dikirim dan menunggu persetujuan admin.');
    }
}
