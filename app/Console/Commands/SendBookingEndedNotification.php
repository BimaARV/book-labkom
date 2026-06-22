<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendBookingEndedNotification extends Command
{
    protected $signature = 'booking:check-ended';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek booking yang waktunya telah berakhir dan kirim notifikasi ke admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i');
        $currentDate = $now->format('Y-m-d');

        // Cari booking berstatus 'accepted' di mana date <= hari ini, 
        // dan jika date = hari ini, maka end_time <= waktu saat ini.
        $endedBookings = \App\Models\Booking::where('status', 'accepted')
            ->where(function ($query) use ($currentDate, $currentTime) {
                $query->where('date', '<', $currentDate)
                      ->orWhere(function ($q) use ($currentDate, $currentTime) {
                          $q->where('date', $currentDate)
                            ->where('end_time', '<=', $currentTime);
                      });
            })
            ->get();

        if ($endedBookings->count() > 0) {
            $notificationService = new \App\Services\NotificationService();
            foreach ($endedBookings as $booking) {
                // To avoid sending multiple notifications, we could add a `notified_ended` boolean to bookings table,
                // but since the requirement just said "Notification if ended", we will just send it if it's accepted.
                // Ideally, after sending, it might be better to mark it somehow, but we'll leave it as is 
                // unless we add `notified_ended` column.
                // Or maybe just change status to "completed"? But that requires admin check.
                // Let's assume there is an `is_notified_ended` flag? We haven't created one.
                // To prevent spamming, we will log it. We shouldn't run this every minute if it spams.
                
                // Let's add a temporary way to prevent spam without schema change by using cache
                $cacheKey = "notified_ended_booking_{$booking->id}";
                if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    $notificationService->sendBookingEndedAdminNotification($booking);
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDays(7));
                }
            }
        }
    }
}
