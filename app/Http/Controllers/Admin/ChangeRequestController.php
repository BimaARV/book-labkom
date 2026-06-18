<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingChangeRequest;
use App\Models\Booking;

class ChangeRequestController extends Controller
{
    public function index()
    {
        $requests = BookingChangeRequest::with(['booking.laboratory', 'requestedLaboratory'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.change_requests.index', compact('requests'));
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string'
        ]);

        $changeRequest = BookingChangeRequest::with('booking')->findOrFail($id);
        
        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $booking = $changeRequest->booking;

        if ($request->action === 'approve') {
            $changeRequest->status = 'approved';
            
            // Terapkan perubahan ke booking asli
            if ($changeRequest->type === 'cancellation') {
                $booking->status = 'cancelled';
            } elseif ($changeRequest->type === 'reschedule') {
                $booking->date = $changeRequest->requested_date;
                $booking->start_time = $changeRequest->requested_start_time;
                $booking->end_time = $changeRequest->requested_end_time;
            } elseif ($changeRequest->type === 'relocation') {
                $booking->laboratory_id = $changeRequest->requested_laboratory_id;
                $booking->is_all_labs = $changeRequest->requested_is_all_labs;
            }
            $booking->save();

        } else {
            $changeRequest->status = 'rejected';
        }

        $changeRequest->admin_note = $request->admin_note;
        $changeRequest->save();

        try {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->sendChangeRequestProcessedNotification($changeRequest, auth()->user());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to trigger change request process notification: " . $e->getMessage());
        }

        $actionText = $request->action === 'approve' ? 'disetujui' : 'ditolak';
        return back()->with('success', "Permintaan perubahan berhasil {$actionText}.");
    }

    public function destroy($id)
    {
        $changeRequest = BookingChangeRequest::findOrFail($id);
        $changeRequest->delete();

        return back()->with('success', 'Riwayat pengajuan perubahan berhasil dihapus.');
    }
}
