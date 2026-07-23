<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Mail\BookingStatusMail;

class NotificationService
{
    /**
     * Send a notification for a newly created booking to the WA Group.
     */
    public function sendNewBookingNotification(Booking $booking, $totalSessions = 1, $frequency = null)
    {
        $settings = Setting::pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        $labName = $booking->lab_name;
        $unitName = optional($booking->businessUnit)->name;
        $subUnitName = optional($booking->subBusinessUnit)->name;
        $unitBisnis = $subUnitName ? "{$unitName} ({$subUnitName})" : $unitName;
        
        $date = \Carbon\Carbon::parse($booking->date)->format('d M Y');
        $time = \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i');

        $trackUrl = secure_url('/track/' . $booking->tracking_code);

        // 1. WhatsApp Notification to Group
        if ($gatewayUrl && $groupId) {
            $message = "INFO: PEMINJAMAN BARU\n\n";
            $message .= "Ada permintaan peminjaman Labkom baru:\n\n";
            $message .= "Kode Booking: {$booking->tracking_code}\n";
            $message .= "PIC: {$booking->pic_name} ({$unitBisnis})\n";
            $message .= "Labkom: {$labName}\n";
            $message .= "Tanggal: {$date}\n";
            $message .= "Waktu: {$time}\n";
            $message .= "WhatsApp: {$booking->whatsapp}\n";
            $message .= "Email: {$booking->email}\n";
            $message .= "Keperluan: {$booking->purpose}\n";
            
            if ($totalSessions > 1 && $frequency) {
                $lastBooking = \App\Models\Booking::where('group_id', $booking->group_id)->orderBy('date', 'desc')->first();
                $freqLabel = $frequency === 'daily' ? 'Harian' : 'Setiap Minggu';
                $sesiLabel = $frequency === 'daily' ? "{$totalSessions} Hari" : "{$totalSessions} Minggu";
                
                if ($lastBooking && $lastBooking->date != $booking->date) {
                    $endDateStr = \Carbon\Carbon::parse($lastBooking->date)->format('d M Y');
                    $message .= "Pemesanan Rutin: Ya\nFrekuensi: {$freqLabel}\nPeriode: {$date} - {$endDateStr}\nTotal Sesi: {$sesiLabel}\n\n";
                } else {
                    $message .= "Pemesanan Rutin: Ya\nFrekuensi: {$freqLabel}\nTotal Sesi: {$sesiLabel}\n\n";
                }
            } else {
                $message .= "\n";
            }

            $message .= "Cek Status: {$trackUrl}\n\n";
            $message .= "Mohon tim Infrastructure segera meninjau di Dashboard.";

            try {
                Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                    'phone' => $groupId,
                    'message' => $message
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send WA New Booking Notification: " . $e->getMessage());
            }
        } else {
            Log::info("WhatsApp Gateway or Group ID is not configured. Skipping WA group notification for new booking.");
        }

        // 2. WhatsApp Notification to PIC/user
        $userPhone = $booking->whatsapp;
        if ($userPhone && $gatewayUrl) {
            $userMessage  = "Halo *{$booking->pic_name}*,\n\n";
            $userMessage .= "Permintaan peminjaman Labkom Anda telah kami terima dan sedang menunggu persetujuan.\n\n";
            $userMessage .= "Kode Booking: *{$booking->tracking_code}*\n";
            $userMessage .= "Labkom: {$booking->lab_name}\n";
            $userMessage .= "Tanggal: {$date}\n";
            $userMessage .= "Waktu: {$time}\n\n";
            $userMessage .= "Simpan kode booking Anda untuk memantau status atau mengajukan perubahan.\n";
            $userMessage .= "Cek Status: {$trackUrl}";

            try {
                Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                    'phone' => $userPhone,
                    'message' => $userMessage,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send WA New Booking to user: " . $e->getMessage());
            }
        }

        // 3. Email Notification to user
        $this->configureMail($settings);
        if (!empty($settings['MAIL_HOST'])) {
            try {
                \Illuminate\Support\Facades\Mail::to($booking->email)
                    ->send(new \App\Mail\BookingReceivedMail($booking, $totalSessions, $frequency));
            } catch (\Exception $e) {
                Log::error("Failed to send email New Booking to user: " . $e->getMessage());
            }
        } else {
            Log::info("SMTP Configuration is incomplete. Skipping email notification for new booking.");
        }
    }


    


    /**
     * Send a notification when a user requests a booking change.
     */
    public function sendChangeRequestNotification(\App\Models\BookingChangeRequest $changeRequest)
    {
        $settings = Setting::whereIn('key', ['WA_GATEWAY_URL', 'WA_GROUP_ID'])->pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        if (!$gatewayUrl || !$groupId) {
            Log::info("WhatsApp Gateway or Group ID is not configured. Skipping change request notification.");
            return;
        }

        $booking = $changeRequest->booking;
        $labName = $booking->lab_name;
        
        $typeLabel = '';
        $detailText = '';
        if ($changeRequest->type === 'cancellation') {
            $typeLabel = 'Pembatalan';
            $detailText = "- Alasan Pembatalan: {$changeRequest->reason}";
        } elseif ($changeRequest->type === 'reschedule') {
            $typeLabel = 'Perubahan Jadwal';
            $date = \Carbon\Carbon::parse($changeRequest->requested_date)->format('d M Y');
            $time = \Carbon\Carbon::parse($changeRequest->requested_start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($changeRequest->requested_end_time)->format('H:i');
            $detailText = "- Jadwal Baru: {$date} | {$time}";
        } elseif ($changeRequest->type === 'relocation') {
            $typeLabel = 'Pindah Labkom';
            $oldLab = $changeRequest->original_lab_name;
            $newLab = $changeRequest->requested_is_all_labs ? \App\Models\Laboratory::getAllLabsName() : optional($changeRequest->requestedLaboratory)->name;
            $detailText = "- Labkom Awal: {$oldLab}\n- Labkom Tujuan: {$newLab}";
        }

        $businessUnitText = optional($booking->businessUnit)->name;
        if ($booking->subBusinessUnit) {
            $businessUnitText .= ' - ' . $booking->subBusinessUnit->name;
        }

        $labLabel = $changeRequest->type === 'relocation' ? 'Lab Awal' : 'Labkom saat ini';
        
        $date = \Carbon\Carbon::parse($booking->date)->format('d M Y');
        $recurringInfo = '';
        if ($booking->group_id) {
            $recurringEnd = \App\Models\Booking::where('group_id', $booking->group_id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->max('date');
            if ($recurringEnd && $recurringEnd != $booking->date) {
                $recurringInfo = " (Rutin s/d " . \Carbon\Carbon::parse($recurringEnd)->format('d M Y') . ")";
            }
        }

        $message = "⚠️ *PENGAJUAN PERUBAHAN BOOKING*\n\n";
        $message .= "Kode Booking: {$booking->tracking_code}\n";
        $message .= "Tanggal Booking: {$date}{$recurringInfo}\n";
        $message .= "Pemohon:\n";
        $message .= "- Nama: *{$booking->pic_name}*\n";
        $message .= "- Email: {$booking->email}\n";
        $message .= "- Unit Bisnis: {$businessUnitText}\n\n";
        $message .= "{$labLabel}: {$labName}\n";
        $message .= "Jenis Pengajuan: *{$typeLabel}*\n";
        $message .= "Alasan: {$changeRequest->reason}\n\n";
        $message .= "Detail Pengajuan:\n{$detailText}\n\n";
        $message .= "Mohon tim IT Infrastructure segera meninjau pengajuan ini di menu Permintaan Perubahan.";

        try {
            Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                'phone' => $groupId,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send WA Change Request Notification: " . $e->getMessage());
        }
    }

    /**
     * Send a notification when a change request is approved or rejected.
     */
    public function sendChangeRequestProcessedNotification(\App\Models\BookingChangeRequest $changeRequest, \App\Models\User $admin)
    {
        $settings = Setting::whereIn('key', ['WA_GATEWAY_URL', 'WA_GROUP_ID'])->pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        $booking = $changeRequest->booking;
        $statusText = $changeRequest->status === 'approved' ? '*DISETUJUI*' : '*DITOLAK*';
        if ($changeRequest->type === 'cancellation') {
            $statusText = $changeRequest->status === 'approved' ? '*DITERIMA*' : '*DITOLAK*';
        }
        
        $typeLabel = '';
        $detailText = '';
        if ($changeRequest->type === 'cancellation') {
            $typeLabel = 'Pembatalan';
            $detailText = "- Alasan Pembatalan: {$changeRequest->reason}";
        } elseif ($changeRequest->type === 'reschedule') {
            $typeLabel = 'Perubahan Jadwal';
            $date = \Carbon\Carbon::parse($changeRequest->requested_date)->format('d M Y');
            $time = \Carbon\Carbon::parse($changeRequest->requested_start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($changeRequest->requested_end_time)->format('H:i');
            $detailText = "- Jadwal Baru: {$date} | {$time}";
        } elseif ($changeRequest->type === 'relocation') {
            $typeLabel = 'Pindah Labkom';
            $oldLab = $changeRequest->original_lab_name;
            $newLab = $changeRequest->requested_is_all_labs ? \App\Models\Laboratory::getAllLabsName() : optional($changeRequest->requestedLaboratory)->name;
            $detailText = "- Labkom Awal: {$oldLab}\n- Labkom Tujuan: {$newLab}";
        }

        $trackUrl = secure_url('/track/' . $booking->tracking_code);

        $businessUnitText = optional($booking->businessUnit)->name;
        if ($booking->subBusinessUnit) {
            $businessUnitText .= ' - ' . $booking->subBusinessUnit->name;
        }

        $actionTextGroup = $changeRequest->type === 'cancellation' 
            ? "Permintaan Pembatalan telah {$statusText} oleh IT Infrastructure *{$admin->name}*.\n\n"
            : "Pengajuan perubahan telah {$statusText} oleh IT Infrastructure *{$admin->name}*.\n\n";
        
        $actionTextUser = $changeRequest->type === 'cancellation'
            ? "Permintaan Pembatalan booking Anda telah {$statusText}.\n\n"
            : "Pengajuan perubahan booking Anda telah {$statusText}.\n\n";

        $date = \Carbon\Carbon::parse($booking->date)->format('d M Y');
        $recurringInfo = '';
        if ($booking->group_id) {
            $recurringEnd = \App\Models\Booking::where('group_id', $booking->group_id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->max('date');
            if ($recurringEnd && $recurringEnd != $booking->date) {
                $recurringInfo = " (Rutin s/d " . \Carbon\Carbon::parse($recurringEnd)->format('d M Y') . ")";
            }
        }

        // WA Message for Group
        $messageGroup = "⚠️ *HASIL PENGAJUAN PERUBAHAN BOOKING*\n\n";
        $messageGroup .= "Kode Booking: {$booking->tracking_code}\n";
        $messageGroup .= "Tanggal Booking: {$date}{$recurringInfo}\n";
        $messageGroup .= $actionTextGroup;
        $messageGroup .= "Pemohon:\n";
        $messageGroup .= "- Nama: *{$booking->pic_name}*\n";
        $messageGroup .= "- Email: {$booking->email}\n";
        $messageGroup .= "- Unit Bisnis: {$businessUnitText}\n\n";
        $messageGroup .= "Jenis: {$typeLabel}\n";
        $messageGroup .= "Detail:\n{$detailText}\n";
        $messageGroup .= "Catatan IT Infrastructure: " . ($changeRequest->admin_note ?? '-') . "\n\n";
        $messageGroup .= "Cek Detail: {$trackUrl}";

        // WA Message for User
        $messageUser = "Halo *{$booking->pic_name}*,\n\n";
        $messageUser .= $actionTextUser;
        $messageUser .= "Kode Booking: {$booking->tracking_code}\n";
        $messageUser .= "Tanggal Booking: {$date}{$recurringInfo}\n";
        $messageUser .= "Pemohon:\n";
        $messageUser .= "- Nama: *{$booking->pic_name}*\n";
        $messageUser .= "- Email: {$booking->email}\n";
        $messageUser .= "- Unit Bisnis: {$businessUnitText}\n\n";
        $messageUser .= "Jenis: {$typeLabel}\n";
        $messageUser .= "Detail:\n{$detailText}\n";
        $messageUser .= "Diproses Oleh IT Infrastructure: {$admin->name}\n";
        if (!empty($changeRequest->admin_note)) {
            $messageUser .= "Catatan IT Infrastructure: {$changeRequest->admin_note}\n";
        }
        $messageUser .= "\nCek Detail Terbaru: {$trackUrl}\n";
        $messageUser .= "Informasi ini juga telah dikirim ke email {$booking->email}.";

        if ($gatewayUrl) {
            try {
                if ($groupId) {
                    Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                        'phone' => $groupId,
                        'message' => $messageGroup
                    ]);
                }
                if ($booking->whatsapp) {
                    Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                        'phone' => $booking->whatsapp,
                        'message' => $messageUser
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WA Processed Notification: " . $e->getMessage());
            }
        }

        // Send Email to User
        $mailSettings = Setting::pluck('value', 'key');
        $this->configureMail($mailSettings);

        if (!empty($mailSettings['MAIL_HOST'])) {
            try {
                $mail = Mail::to($booking->email);
                
                if (!empty($mailSettings['MAIL_CC_ADDRESSES'])) {
                    $ccList = array_map('trim', explode(',', $mailSettings['MAIL_CC_ADDRESSES']));
                    $mail->cc($ccList);
                }

                $mail->send(new \App\Mail\ChangeRequestProcessedMail($changeRequest, $admin));
            } catch (\Exception $e) {
                Log::error("Failed to send Processed Email Notification: " . $e->getMessage());
            }
        }
    }

    /**
     * Send notification for a booking status update (Accepted/Rejected).
     * Sends WA to Group and Email to User.
     */
    public function sendBookingStatusNotification(Booking $booking)
    {
        // 1. WhatsApp Group Notification
        $settings = Setting::pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        $labName = $booking->lab_name;
        $unitName = optional($booking->businessUnit)->name;
        $subUnitName = optional($booking->subBusinessUnit)->name;
        $unitBisnis = $subUnitName ? "{$unitName} ({$subUnitName})" : $unitName;

        $date = \Carbon\Carbon::parse($booking->date)->format('d M Y');
        $recurringInfo = '';
        if ($booking->group_id) {
            $recurringEnd = \App\Models\Booking::where('group_id', $booking->group_id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->max('date');
            if ($recurringEnd && $recurringEnd != $booking->date) {
                $recurringInfo = " (Rutin s/d " . \Carbon\Carbon::parse($recurringEnd)->format('d M Y') . ")";
            }
        }
        $time = \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i');
        $statusMap = [
            'accepted' => '*DISETUJUI*',
            'rejected' => '*DITOLAK*',
            'completed' => '*SELESAI*',
            'cancelled' => '*DIBATALKAN*'
        ];
        $statusText = $statusMap[$booking->status] ?? '*DIPERBARUI*';
        $adminName = $booking->handled_by ?? 'Admin';
        $trackUrl = secure_url('/track/' . $booking->tracking_code);

        $dataTerkini = "*Detail Peminjaman:*\n";
        $dataTerkini .= "Unit Bisnis: {$unitBisnis}\n";
        $dataTerkini .= "Labkom: {$labName}\n";
        $dataTerkini .= "Tanggal: {$date}{$recurringInfo}\n";
        $dataTerkini .= "Waktu: {$time}\n";
        $dataTerkini .= "Keperluan: {$booking->purpose}\n";

        if ($booking->status === 'completed') {
            $dataTerkini .= "Keadaan Bersih: " . ($booking->is_clean ? "Ya" : "Tidak") . "\n";
            if (!empty($booking->report_note)) {
                $dataTerkini .= "Catatan Laporan: {$booking->report_note}\n";
            }
            $pdfUrl = secure_url('/track/' . $booking->tracking_code . '/pdf');
            
            $dataTerkiniGroup = $dataTerkini . "\n*Informasi Detail Laporan Peminjam Lihat Di:*\n{$pdfUrl}\n";
            $dataTerkiniUser = $dataTerkini . "\n*Informasi Detail Laporan Anda Lihat Di:*\n{$pdfUrl}\n";
        } else {
            $dataTerkiniGroup = $dataTerkini . "\n";
            $dataTerkiniUser = $dataTerkini . "\n";
        }

        if ($gatewayUrl) {
            $message = "⚠️ *STATUS BOOKING: {$statusText}*\n\n";
            $message .= "Peminjaman Labkom untuk *{$booking->pic_name}* telah diperbarui.\n\n";
            $message .= "Di Proses Oleh IT Infrastructure: {$adminName}\n";
            if (in_array($booking->status, ['rejected', 'cancelled']) && !empty($booking->rejection_reason)) {
                $message .= "Alasan: {$booking->rejection_reason}\n";
            }
            $message .= "\nKode Booking: {$booking->tracking_code}";
            $message .= "\n" . $dataTerkiniGroup;
            $message .= "Cek Detail: {$trackUrl}";

            try {
                // Send to Group
                if ($groupId) {
                    Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                        'phone' => $groupId,
                        'message' => $message
                    ]);
                }
                
                // Send to Borrower
                if ($booking->whatsapp) {
                    $borrowerMessage = "Halo *{$booking->pic_name}*,\n\n";
                    
                    if ($booking->status === 'completed') {
                        $borrowerMessage .= "Peminjaman Labkom Anda telah dinyatakan *SELESAI*.\nTerima kasih telah menggunakan fasilitas Labkom. Kami harap fasilitas yang kami sediakan dapat membantu kegiatan Anda dengan baik.\n\n";
                    } else if ($booking->status === 'cancelled') {
                        $borrowerMessage .= "Permintaan peminjaman Labkom Anda telah *DIBATALKAN*.\n\n";
                    } else {
                        $borrowerMessage .= "Permintaan peminjaman Labkom Anda telah diproses dengan status: {$statusText}\n\n";
                    }
                    
                    if (in_array($booking->status, ['rejected', 'cancelled']) && !empty($booking->rejection_reason)) {
                        $borrowerMessage .= "Alasan: {$booking->rejection_reason}\n\n";
                    }

                    $borrowerMessage .= "Kode Booking: {$booking->tracking_code}\n";
                    $borrowerMessage .= $dataTerkiniUser;
                    $borrowerMessage .= "Di Proses Oleh IT Infrastructure: {$adminName}\n\n";
                    $borrowerMessage .= "Cek Status Terkini: {$trackUrl}\n";
                    $borrowerMessage .= "Syarat & Ketentuan (ToS): " . secure_url('/tos') . "\n";
                    $borrowerMessage .= "\nInformasi ini juga telah dikirim ke email {$booking->email}";

                    Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                        'phone' => $booking->whatsapp,
                        'message' => $borrowerMessage
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WA Status Notification: " . $e->getMessage());
            }
        }

        // 2. Email Notification
        $this->configureMail($settings);

        if (!empty($settings['MAIL_HOST']) && !empty($settings['MAIL_USERNAME'])) {
            try {
                $mail = Mail::to($booking->email);
                
                if (!empty($settings['MAIL_CC_ADDRESSES'])) {
                    $ccList = array_map('trim', explode(',', $settings['MAIL_CC_ADDRESSES']));
                    $mail->cc($ccList);
                }

                $mail->send(new BookingStatusMail($booking));
            } catch (\Exception $e) {
                Log::error("Failed to send Email Status Notification: " . $e->getMessage());
            }
        } else {
            Log::info("SMTP Configuration is incomplete. Skipping Email notification.");
        }
    }

    /**
     * Send notification when a booking is edited.
     */
    public function sendBookingEditedNotification(Booking $booking, \App\Models\User $admin, array $changes, bool $notifyPic = false)
    {
        $settings = Setting::pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        $labName = $booking->lab_name;
        $unitName = optional($booking->businessUnit)->name;
        $subUnitName = optional($booking->subBusinessUnit)->name;
        $unitBisnis = $subUnitName ? "{$unitName} ({$subUnitName})" : $unitName;
        $date = \Carbon\Carbon::parse($booking->date)->format('d M Y');
        $recurringInfo = '';
        if ($booking->group_id) {
            $recurringEnd = \App\Models\Booking::where('group_id', $booking->group_id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->max('date');
            if ($recurringEnd) {
                $recurringInfo = " (Rutin s/d " . \Carbon\Carbon::parse($recurringEnd)->format('d M Y') . ")";
            }
        }
        $time = \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i');
        $trackUrl = secure_url('/track/' . $booking->tracking_code);

        // 1. WhatsApp Notification to Group
        if ($gatewayUrl && $groupId) {
            $changesText = implode("\n- ", $changes);
            $messageGroup = "⚠️ *PERUBAHAN DATA BOOKING*\n\n";
            $messageGroup .= "Data booking milik *{$booking->pic_name}* telah diubah oleh *{$admin->name}*.\n\n";
            $messageGroup .= "*Detail Perubahan:*\n- {$changesText}\n\n";
            $messageGroup .= "*Data Terkini:*\n";
            $messageGroup .= "Kode Booking: {$booking->tracking_code}\n";
            $messageGroup .= "Unit Bisnis: {$unitBisnis}\n";
            $messageGroup .= "Labkom: {$labName}\n";
            $messageGroup .= "Tanggal: {$date}{$recurringInfo}\n";
            $messageGroup .= "Waktu: {$time}\n";
            $messageGroup .= "Keperluan: {$booking->purpose}\n";
            
            if ($booking->status === 'completed') {
                $messageGroup .= "Keadaan Bersih: " . ($booking->is_clean ? "Ya" : "Tidak") . "\n";
                if (!empty($booking->report_note)) {
                    $messageGroup .= "Catatan Laporan: {$booking->report_note}\n";
                }
                $pdfUrl = secure_url('/track/' . $booking->tracking_code . '/pdf');
                $messageGroup .= "\n*Informasi Detail Laporan Peminjam Lihat Di:*\n{$pdfUrl}\n";
            }
            $messageGroup .= "\nCek Detail: {$trackUrl}";

            try {
                Http::post(rtrim($gatewayUrl, '/') . '/send', [
                    'phone' => $groupId,
                    'message' => $messageGroup,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send Booking Edited WA Notification to Group: " . $e->getMessage());
            }
        }
        
        // 2. Notification to PIC
        if ($notifyPic) {
            // WA to PIC
            if ($gatewayUrl && $booking->whatsapp) {
                // Re-fetch recurring end for PIC message (same as group)
                $recurringInfoPic = '';
                if ($booking->group_id) {
                    $recurringEndPic = \App\Models\Booking::where('group_id', $booking->group_id)
                        ->whereNotIn('status', ['cancelled', 'rejected'])
                        ->max('date');
                    if ($recurringEndPic) {
                        $recurringInfoPic = " (Rutin s/d " . \Carbon\Carbon::parse($recurringEndPic)->format('d M Y') . ")";
                    }
                }

                $messagePic = "Halo *{$booking->pic_name}*,\n\n";
                $messagePic .= "Terdapat perubahan pada detail peminjaman Labkom Anda yang diproses oleh tim IT Infrastructure. Berikut adalah data yang diperbarui:\n\n";
                $messagePic .= "- " . implode("\n- ", $changes) . "\n\n";
                $messagePic .= "*Detail Pemesanan Terkini:*\n";
                $messagePic .= "Kode Booking: {$booking->tracking_code}\n";
                $messagePic .= "Unit Bisnis: {$unitBisnis}\n";
                $messagePic .= "Labkom: {$labName}\n";
                $messagePic .= "Tanggal: {$date}{$recurringInfoPic}\n";
                $messagePic .= "Waktu: {$time}\n";
                $messagePic .= "Keperluan: {$booking->purpose}\n\n";
                
                if ($booking->status === 'completed') {
                    $messagePic .= "Keadaan Bersih: " . ($booking->is_clean ? "Ya" : "Tidak") . "\n";
                    if (!empty($booking->report_note)) {
                        $messagePic .= "Catatan Laporan: {$booking->report_note}\n";
                    }
                    $pdfUrl = secure_url('/track/' . $booking->tracking_code . '/pdf');
                    $messagePic .= "\n*Informasi Detail Laporan Anda Lihat Di:*\n{$pdfUrl}\n\n";
                    $messagePic .= "Peminjaman Labkom Anda telah dinyatakan *SELESAI*.\nTerima kasih telah menggunakan fasilitas Labkom. Kami harap fasilitas yang kami sediakan dapat membantu kegiatan Anda dengan baik.\n\n";
                }

                $messagePic .= "Cek Status Terkini: {$trackUrl}\n";
                $messagePic .= "Terima kasih.";

                try {
                    Http::post(rtrim($gatewayUrl, '/') . '/send', [
                        'phone' => $booking->whatsapp,
                        'message' => $messagePic,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to send Booking Edited WA Notification to PIC: " . $e->getMessage());
                }
            }

            // Email to PIC
            $this->configureMail($settings);
            if (!empty($settings['MAIL_HOST'])) {
                try {
                    $mail = Mail::to($booking->email);
                    if (!empty($settings['MAIL_CC_ADDRESSES'])) {
                        $ccList = array_map('trim', explode(',', $settings['MAIL_CC_ADDRESSES']));
                        $mail->cc($ccList);
                    }
                    $mail->send(new \App\Mail\BookingEditedMail($booking, $admin, $changes));
                } catch (\Exception $e) {
                    Log::error("Failed to send Booking Edited Email Notification: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Send notification when a booking is deleted.
     */
    public function sendBookingDeletedNotification(array $bookingData, \App\Models\User $admin)
    {
        $settings = Setting::pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        if ($gatewayUrl && $groupId) {
            $message = "🗑️ *PENGHAPUSAN DATA BOOKING*\n\n";
            $message .= "Data booking berikut telah *DIHAPUS* oleh *{$admin->name}*:\n\n";
            $message .= "PIC: {$bookingData['pic_name']}\n";
            $message .= "Labkom: {$bookingData['lab_name']}\n";
            $message .= "Tanggal: {$bookingData['date']}\n";
            $message .= "Waktu: {$bookingData['time']}";

            try {
                Http::post(rtrim($gatewayUrl, '/') . '/send', [
                    'phone' => $groupId,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send Booking Deleted WA Notification: " . $e->getMessage());
            }
        }
    }

    /**
     * Send notification when an Admin's data is updated.
     */
    public function sendAdminUpdatedNotification(\App\Models\User $updatedUser, \App\Models\User $changerAdmin, array $changes)
    {
        $settings = Setting::pluck('value', 'key');
        
        // WhatsApp Notification
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        if ($gatewayUrl && $groupId) {
            $changesText = implode("\n- ", $changes);
            $message = "⚠️ *Pembaruan Data Admin*\n\n";
            $message .= "Data akun Admin *{$updatedUser->name}* telah diperbarui oleh *{$changerAdmin->name}*.\n\n";
            $message .= "*Detail Perubahan:*\n- {$changesText}";

            try {
                Http::post("{$gatewayUrl}/send", [
                    'phone' => $groupId,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send Admin Updated WA Notification: " . $e->getMessage());
            }
        } else {
            Log::info("WhatsApp Gateway or Group ID is not configured. Skipping Admin Updated WA notification.");
        }

        // Email Notification
        $this->configureMail($settings);

        if (!empty($settings['MAIL_HOST'])) {
            try {
                Mail::to($updatedUser->email)->send(new \App\Mail\AdminUpdatedMail($updatedUser, $changerAdmin, $changes));
            } catch (\Exception $e) {
                Log::error("Failed to send Admin Updated Email Notification: " . $e->getMessage());
            }
        } else {
            Log::info("SMTP Configuration is incomplete. Skipping Admin Updated Email notification.");
        }
    }

    /**
     * Send notification when a new Admin is added.
     */
    public function sendNewAdminNotification(\App\Models\User $newAdmin, \App\Models\User $creatorAdmin, $rawPassword = null)
    {
        $settings = Setting::pluck('value', 'key');
        
        // WhatsApp Notification
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        if ($gatewayUrl && $groupId) {
            $message = "⚠️ *Penambahan Admin Baru*\n\n";
            $message .= "*{$creatorAdmin->name}* baru saja menambahkan *{$newAdmin->name}* sebagai Admin baru.";

            try {
                Http::post("{$gatewayUrl}/send", [
                    'phone' => $groupId,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send New Admin WA Notification: " . $e->getMessage());
            }
        } else {
            Log::info("WhatsApp Gateway or Group ID is not configured. Skipping New Admin WA notification.");
        }

        // Email Notification
        $this->configureMail($settings);

        if (!empty($settings['MAIL_HOST'])) {
            try {
                Mail::to($newAdmin->email)->send(new \App\Mail\NewAdminMail($newAdmin, $creatorAdmin, $rawPassword));
            } catch (\Exception $e) {
                Log::error("Failed to send New Admin Email Notification: " . $e->getMessage());
            }
        } else {
            Log::info("SMTP Configuration is incomplete. Skipping New Admin Email notification.");
        }
    }

    /**
     * Send notification when an Admin is deleted.
     */
    public function sendAdminDeletedNotification(string $deletedAdminName, \App\Models\User $changerAdmin)
    {
        $settings = Setting::pluck('value', 'key');
        
        // WhatsApp Notification
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        if ($gatewayUrl && $groupId) {
            $message = "⚠️ *Penghapusan Admin*\n\n";
            if ($changerAdmin->name === $deletedAdminName) {
                $message .= "*{$changerAdmin->name}* baru saja menghapus akunnya sendiri.";
            } else {
                $message .= "*{$changerAdmin->name}* baru saja menghapus akun Admin *{$deletedAdminName}*.";
            }

            try {
                Http::post("{$gatewayUrl}/send", [
                    'phone' => $groupId,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send Admin Deleted WA Notification: " . $e->getMessage());
            }
        }
    }

    /**
     * Send notification when master data is added, updated, or deleted.
     */
    public function sendMasterDataNotification(string $type, string $action, string $dataName, \App\Models\User $admin)
    {
        $settings = \App\Models\Setting::pluck('value', 'key');
        
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        if ($gatewayUrl && $groupId) {
            $emoji = '📝';
            if ($action === 'ditambahkan') $emoji = '⚠️';
            if ($action === 'dihapus') $emoji = '🗑️';

            $message = "{$emoji} *Pembaruan Data {$type}*\n\n";
            $message .= "*{$admin->name}* baru saja melakukan tindakan berikut:\n";
            $message .= "Tindakan: *{$action}*\n";
            $message .= "Data: *{$dataName}*";

            try {
                \Illuminate\Support\Facades\Http::post("{$gatewayUrl}/send", [
                    'phone' => $groupId,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send Master Data WA Notification: " . $e->getMessage());
            }
        }
    }

    public function sendBookingEndedAdminNotification(Booking $booking)
    {
        $settings = \App\Models\Setting::pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);
        $groupId = $settings['WA_GROUP_ID'] ?? null;

        if ($gatewayUrl && $groupId) {
            $message = "⚠️ *PENGINGAT: WAKTU BOOKING BERAKHIR*\n\n";
            $message .= "Peminjaman Labkom berikut telah mencapai batas waktu selesai:\n\n";
            $message .= "Kode Booking: {$booking->tracking_code}\n";
            $message .= "PIC: {$booking->pic_name}\n";
            $message .= "Labkom: {$booking->lab_name}\n";
            $message .= "Tanggal: " . \Carbon\Carbon::parse($booking->date)->format('d M Y') . "\n";
            $message .= "Waktu: " . \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i') . "\n\n";
            $message .= "Mohon tim Infrastructure untuk mengecek Labkom dan mengubah status booking menjadi *SELESAI*.";

            try {
                \Illuminate\Support\Facades\Http::post("{$gatewayUrl}/send", [
                    'phone' => $groupId,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send Booking Ended WA Notification: " . $e->getMessage());
            }
        }
    }

    /**
     * Send a notification to the PIC when a routine schedule is changed (shortened/extended).
     */
    public function sendRoutineScheduleChangedNotification(Booking $booking, $newEndDate)
    {
        $settings = Setting::whereIn('key', ['WA_GATEWAY_URL'])->pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);

        $userPhone = $booking->whatsapp;
        if ($userPhone && $gatewayUrl) {
            $newEndDateStr = \Carbon\Carbon::parse($newEndDate)->format('d M Y');
            $timeStr = \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i');
            $trackUrl = secure_url('/track/' . $booking->tracking_code);

            $userMessage  = "Halo *{$booking->pic_name}*,\n\n";
            $userMessage .= "Terdapat penyesuaian pada jadwal *Peminjaman Rutin* Anda oleh Admin.\n\n";
            $userMessage .= "Kode Booking: *{$booking->tracking_code}*\n";
            $userMessage .= "Labkom: {$booking->lab_name}\n";
            $userMessage .= "Waktu: {$timeStr}\n";
            $userMessage .= "Tanggal Berakhir Baru: *{$newEndDateStr}*\n\n";
            $userMessage .= "Silakan cek status terbaru secara berkala.\n";
            $userMessage .= "Cek Status: {$trackUrl}";

            try {
                \Illuminate\Support\Facades\Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                    'phone' => $userPhone,
                    'message' => $userMessage,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send WA Routine Schedule Changed to user: " . $e->getMessage());
            }
        }
        
        // You could also send an email here if required, but WhatsApp is standard for now.
    }

    /**
     * Send a notification to the PIC as a reminder before/when booking ends.
     */
    public function sendBookingReminderUserNotification(Booking $booking)
    {
        $settings = Setting::whereIn('key', ['WA_GATEWAY_URL'])->pluck('value', 'key');
        $gatewayUrl = $this->getInternalGatewayUrl($settings['WA_GATEWAY_URL'] ?? null);

        $userPhone = $booking->whatsapp;
        if ($userPhone && $gatewayUrl) {
            $endTime = \Carbon\Carbon::parse($booking->end_time)->format('H:i');
            
            $userMessage  = "Halo *{$booking->pic_name}*,\n\n";
            $userMessage .= "Pengingat: Waktu peminjaman Labkom Anda (*{$booking->lab_name}*) akan segera berakhir pada pukul *{$endTime}* hari ini.\n\n";
            $userMessage .= "Mohon bersiap untuk mengakhiri sesi dan pastikan Labkom dalam keadaan bersih dan rapi.\n\n";
            $userMessage .= "Terima kasih atas kerja samanya.";

            try {
                \Illuminate\Support\Facades\Http::timeout(5)->post(rtrim($gatewayUrl, '/') . '/send', [
                    'phone' => $userPhone,
                    'message' => $userMessage,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send WA Booking Reminder to user: " . $e->getMessage());
            }
        }
    }

    public function sendAccountDeletedEmail(string $email, string $name)
    {
        $settings = \App\Models\Setting::pluck('value', 'key');
        $this->configureMail($settings);

        if (!empty($settings['MAIL_HOST'])) {
            try {
                \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\AccountDeletedMail($name));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send Account Deleted Email: " . $e->getMessage());
            }
        }
    }

    /**
     * Dynamically configure Laravel Mailer based on Settings table.
     */
    private function configureMail($settings)
    {
        $password = $settings['MAIL_PASSWORD'] ?? env('MAIL_PASSWORD');
        try {
            if (!empty($settings['MAIL_PASSWORD'])) {
                $password = Crypt::decryptString($settings['MAIL_PASSWORD']);
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Jika gagal decrypt, berarti masih menggunakan plain text lama
            $password = $settings['MAIL_PASSWORD'];
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $settings['MAIL_HOST'] ?? env('MAIL_HOST', '127.0.0.1'),
            'mail.mailers.smtp.port' => $settings['MAIL_PORT'] ?? env('MAIL_PORT', 2525),
            'mail.mailers.smtp.username' => $settings['MAIL_USERNAME'] ?? env('MAIL_USERNAME'),
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => $settings['MAIL_ENCRYPTION'] ?? env('MAIL_ENCRYPTION', 'tls'),
            'mail.from.address' => $settings['MAIL_FROM_ADDRESS'] ?? env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'mail.from.name' => $settings['MAIL_FROM_NAME'] ?? env('MAIL_FROM_NAME', 'Techub Notification'),
        ]);

        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');
        Mail::clearResolvedInstances();
    }

    /**
     * Resolve the internal gateway URL for backend API calls.
     */
    private function getInternalGatewayUrl($gatewayUrl)
    {
        if (!$gatewayUrl) return null;

        // Force backend requests to always use the internal Docker service network
        // regardless of what the user sets for the frontend gateway URL.
        return 'http://whatsapp:3000';
    }
}
