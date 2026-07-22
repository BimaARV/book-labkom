<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('booking:send-reminders')]
#[Description('Kirim pengingat 15 menit sebelum waktu peminjaman berakhir')]
class SendBookingReminderNotification extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $checkTime = $now->copy()->addMinutes(15)->format('H:i');
        $currentDate = $now->format('Y-m-d');

        // Cari booking berstatus 'accepted', di tanggal hari ini, 
        // dan end_time kurang dari atau sama dengan 15 menit dari sekarang, 
        // serta end_time masih di masa depan
        $upcomingEndedBookings = \App\Models\Booking::where('status', 'accepted')
            ->where('date', $currentDate)
            ->where('end_time', '<=', $checkTime)
            ->where('end_time', '>', $now->format('H:i'))
            ->get();

        if ($upcomingEndedBookings->count() > 0) {
            $notificationService = new \App\Services\NotificationService();
            foreach ($upcomingEndedBookings as $booking) {
                // Prevent duplicate notifications within 12 hours
                $cacheKey = "notified_reminder_booking_{$booking->id}";
                if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    $notificationService->sendBookingReminderUserNotification($booking);
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(12));
                }
            }
        }
    }
}
