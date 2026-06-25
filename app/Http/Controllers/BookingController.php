<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pic_name' => 'required|string|max:255',
            'laboratory_id' => 'required',
            'business_unit_id' => 'required|exists:business_units,id',
            'sub_business_unit_id' => 'nullable|exists:sub_business_units,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'whatsapp' => 'required|string',
            'email' => 'required|email',
            'purpose' => 'required|string',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly',
            'recurring_duration' => 'nullable|string',
            'recurring_end_date' => 'nullable|required_if:recurring_duration,custom|date|after_or_equal:date',
            'captcha' => 'required|captcha',
            'tos_agreed' => 'required|accepted',
        ], [
            'tos_agreed.required' => 'Anda harus menyetujui Ketentuan Penggunaan Labkom.',
            'tos_agreed.accepted' => 'Anda harus menyetujui Ketentuan Penggunaan Labkom.',
            'captcha.captcha' => 'Jawaban Captcha salah. Silakan coba lagi.'
        ]);

        $restrictedEmails = \App\Models\RestrictedEmail::pluck('email')->toArray();
        if (count($restrictedEmails) > 0) {
            $isAllowed = false;
            foreach ($restrictedEmails as $allowed) {
                if (str_ends_with(strtolower($request->email), strtolower($allowed))) {
                    $isAllowed = true;
                    break;
                }
            }
            if (!$isAllowed) {
                return back()->withErrors(['email' => "Mohon maaf. Terjadi kesalahan<br><span style='font-size: small'>Error code: ERR-0010</span>"])->withInput();
            }
        }

        $isAllLabs = $request->laboratory_id === 'all';
        if (!$isAllLabs && !\App\Models\Laboratory::where('id', $request->laboratory_id)->exists()) {
            return back()->withErrors(['laboratory_id' => 'Labkom yang dipilih tidak valid.'])->withInput();
        }

        $datesToBook = [$request->date];

        if ($request->is_recurring) {
    $frequency = $request->recurring_frequency ?? 'weekly';

    if ($request->recurring_duration === 'custom' && $request->recurring_end_date) {
        $endDate = \Carbon\Carbon::parse($request->recurring_end_date);
        $maxEndDate = \Carbon\Carbon::parse($request->date)->addYear();

        if ($endDate->gt($maxEndDate)) {
            return back()->withErrors([
                'recurring_end_date' => 'Maksimal booking berulang adalah 1 tahun dari tanggal awal.'
            ])->withInput();
        }

        $currentDate = \Carbon\Carbon::parse($request->date)->addDay();
        while ($currentDate->lte($endDate)) {
            $datesToBook[] = $currentDate->format('Y-m-d');
            $frequency === 'daily' ? $currentDate->addDay() : $currentDate->addWeek();
        }

    } elseif (is_numeric($request->recurring_duration)) {
        $weeks = (int) $request->recurring_duration;
        $currentDate = \Carbon\Carbon::parse($request->date)->addWeek();
        if ($weeks > 1) {
            for ($i = 1; $i < $weeks; $i++) {
                $datesToBook[] = $currentDate->format('Y-m-d');
                $currentDate->addWeek();
            }
        }
    }
}

        $newEndWithBuffer = date('H:i', strtotime($request->end_time . ' +1 hour'));

        $firstConflict = null;
        $conflictingDates = [];
        foreach ($datesToBook as $bookDate) {
            $conflict = Booking::with(['businessUnit', 'subBusinessUnit'])
                ->where('date', $bookDate)
                ->whereIn('status', ['accepted', 'completed', 'pending'])
                ->where(function ($query) use ($request, $newEndWithBuffer, $isAllLabs) {
                    $query->whereRaw('CAST(? AS TIME) < ADDTIME(end_time, "01:00:00")', [$request->start_time])
                          ->whereTime('start_time', '<', $newEndWithBuffer);

                    if (!$isAllLabs) {
                        $query->where(function($q) use ($request) {
                            $q->where('laboratory_id', $request->laboratory_id)
                              ->orWhere('is_all_labs', true);
                        });
                    }
                })->first();

            if ($conflict) {
                if (!$firstConflict) $firstConflict = $conflict;
                $conflictingDates[] = \Carbon\Carbon::parse($bookDate)->format('d M Y');
            }
        }

        if (count($conflictingDates) > 0) {
            $unitName = optional($firstConflict->businessUnit)->name;
            $subUnitName = optional($firstConflict->subBusinessUnit)->name;
            $instansi = $subUnitName ? "{$unitName}/{$subUnitName}" : $unitName;

            $errorMsg = "Labkom sudah terpakai pada waktu yang sama oleh {$instansi}";
            return back()->withErrors(['error' => $errorMsg])->withInput();
        }

        // Format nomor WhatsApp: 08x menjadi 628x
        $whatsapp = $request->whatsapp;
        if (str_starts_with($whatsapp, '08')) {
            $whatsapp = '628' . substr($whatsapp, 2);
        }

        $groupId = $request->is_recurring ? (string) Str::uuid() : null;
        $firstBooking = null;

        foreach ($datesToBook as $index => $bookDate) {
            $trackingCode = 'cbt-' . date('Ymd', strtotime($bookDate)) . '-' . strtoupper(Str::random(4));

            $booking = Booking::create([
                'tracking_code' => $trackingCode,
                'group_id' => $groupId,
                'pic_name' => $request->pic_name,
                'laboratory_id' => $isAllLabs ? null : $request->laboratory_id,
                'is_all_labs' => $isAllLabs,
                'business_unit_id' => $request->business_unit_id,
                'sub_business_unit_id' => $request->sub_business_unit_id,
                'date' => $bookDate,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'whatsapp' => $whatsapp,
                'email' => $request->email,
                'purpose' => $request->purpose,
                'status' => 'pending',
            ]);

            if ($index === 0) {
                $firstBooking = $booking;
            }
        }

        // Kirim notifikasi setelah semua booking tersimpan di DB
        $notificationService = new NotificationService();
        $totalWeeks = count($datesToBook);
        $notificationService->sendNewBookingNotification($firstBooking, $totalWeeks);

        return redirect()->route('booking.track', ['code' => $firstBooking->tracking_code]);
    }
}