<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BusinessUnit;
use App\Models\Laboratory;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['laboratory', 'businessUnit']);
        
        if ($request->filled('sort_by') && $request->sort_by === 'asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pic_name', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('tracking_code', 'like', "%{$search}%")
                  ->orWhereHas('laboratory', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('business_unit_id')) {
            $query->where('business_unit_id', $request->business_unit_id);
        }

        if ($request->filled('laboratory_id')) {
            $query->where(function($q) use ($request) {
                $q->where('laboratory_id', $request->laboratory_id)
                  ->orWhere('is_all_labs', true);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $bookings = $query->paginate(15)->withQueryString();
        
        $laboratories = Laboratory::orderBy('name')->get();
        $businessUnits = BusinessUnit::orderBy('name')->get();

        return view('admin.bookings.index', compact('bookings', 'laboratories', 'businessUnits'));
    }

    public function checkNew()
    {
        $latestId = Booking::max('id') ?? 0;
        $pendingCount = Booking::where('status', 'pending')->count();
        
        return response()->json([
            'latest_id' => $latestId,
            'pending_count' => $pendingCount
        ]);
    }

    public function show(Booking $booking)
    {
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $laboratories = Laboratory::where('status', 'active')->get();
        $businessUnits = BusinessUnit::with('subUnits')->get();
        return view('admin.bookings.edit', compact('booking', 'laboratories', 'businessUnits'));
    }

    public function update(Request $request, Booking $booking)
    {
        if (!$request->has('pic_name')) {
            // Quick status update from index
            $request->validate([
                'status' => 'required|in:accepted,rejected,completed,cancelled',
                'rejection_reason' => 'required_if:status,rejected,cancelled',
                'report_images.*' => 'file|mimes:jpeg,png,jpg,gif,svg,webp,heic,heif|max:10240',
                'report_note' => 'nullable|string'
            ]);

            $updateData = [
                'status' => $request->status,
                'rejection_reason' => in_array($request->status, ['rejected', 'cancelled']) ? $request->rejection_reason : null,
                'handled_by' => Auth::user()->name ?? 'Admin',
            ];

            if ($request->status === 'completed') {
                $updateData['is_clean'] = $request->has('is_clean') && $request->is_clean == '1';
                $updateData['report_note'] = $request->report_note;
                
                $existingImages = is_array($booking->report_images) ? $booking->report_images : [];
                if ($request->hasFile('report_images')) {
                    foreach ($request->file('report_images') as $file) {
                        $path = $file->store('booking_reports', 'public');
                        $existingImages[] = $path;
                    }
                }
                $updateData['report_images'] = $existingImages;
            }

            $booking->update($updateData);

            if (in_array($request->status, ['accepted', 'rejected', 'completed', 'cancelled'])) {
                $notificationService = new NotificationService();
                $notificationService->sendBookingStatusNotification($booking);
            }

            return back()->with('success', 'Status booking berhasil diperbarui.');
        }

        // Full update from edit page
        $request->validate([
            'pic_name' => 'required|string|max:255',
            'whatsapp' => 'required|string',
            'email' => 'required|email',
            'business_unit_id' => 'required|exists:business_units,id',
            'sub_business_unit_id' => 'nullable|exists:sub_business_units,id',
            'laboratory_id' => 'required|string',
            'purpose' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:pending,accepted,rejected,completed,cancelled',
            'rejection_reason' => 'nullable|string',
            'report_images' => 'nullable|array',
            'report_images.*' => 'file|mimes:jpeg,png,jpg,gif,svg,webp,heic,heif|max:10240',
            'report_note' => 'nullable|string'
        ]);

        if ($request->laboratory_id !== 'all') {
            $request->validate(['laboratory_id' => 'exists:laboratories,id']);
        }

        $originalData = $booking->getOriginal();
        
        $whatsapp = $request->whatsapp;
        if (str_starts_with($whatsapp, '08')) {
            $whatsapp = '628' . substr($whatsapp, 2);
        }

        $booking->fill([
            'pic_name' => $request->pic_name,
            'whatsapp' => $whatsapp,
            'email' => $request->email,
            'business_unit_id' => $request->business_unit_id,
            'sub_business_unit_id' => $request->sub_business_unit_id,
            'laboratory_id' => $request->laboratory_id === 'all' ? null : $request->laboratory_id,
            'is_all_labs' => $request->laboratory_id === 'all',
            'purpose' => $request->purpose,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
            'rejection_reason' => in_array($request->status, ['rejected', 'cancelled']) ? $request->rejection_reason : null,
            'handled_by' => Auth::user()->name ?? 'Admin',
            'report_note' => $request->report_note,
            'is_clean' => $request->has('is_clean'),
        ]);

        $existingImages = is_array($booking->report_images) ? $booking->report_images : [];

        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                if (($key = array_search($path, $existingImages)) !== false) {
                    unset($existingImages[$key]);
                }
            }
            $existingImages = array_values($existingImages);
        }

        if ($request->hasFile('report_images')) {
            foreach ($request->file('report_images') as $file) {
                $path = $file->store('booking_reports', 'public');
                $existingImages[] = $path;
            }
        }
        
        $booking->report_images = $existingImages;

        $dirty = $booking->getDirty();
        $changes = [];

        if (!empty($dirty)) {
            // Mapping field names for readable WA message
            $fieldNames = [
                'pic_name' => 'PIC',
                'whatsapp' => 'WhatsApp',
                'email' => 'Email',
                'business_unit_id' => 'Unit Bisnis',
                'sub_business_unit_id' => 'Sub Unit Bisnis',
                'laboratory_id' => 'Labkom',
                'purpose' => 'Keperluan',
                'date' => 'Tanggal',
                'start_time' => 'Waktu Mulai',
                'end_time' => 'Waktu Selesai',
                'status' => 'Status',
                'rejection_reason' => 'Alasan',
            ];

            foreach ($dirty as $key => $newValue) {
                if ($key === 'updated_at' || $key === 'handled_by' || $key === 'report_images') continue;

                $oldValue = $originalData[$key] ?? '-';
                
                if ($key === 'is_clean' || $key === 'is_all_labs') {
                    if ($key === 'is_clean') {
                        $oldStr = $oldValue ? 'Ya' : 'Tidak';
                        $newStr = $newValue ? 'Ya' : 'Tidak';
                        $changes[] = "Keadaan Bersih: {$oldStr} -> {$newStr}";
                    }
                    continue;
                }

                // Fetch relation names for IDs
                if ($key === 'laboratory_id') {
                    $oldValue = $originalData['is_all_labs'] ?? false ? 'Semua Labkom' : (Laboratory::find($oldValue)->name ?? $oldValue);
                    $newValue = $booking->is_all_labs ? 'Semua Labkom' : (Laboratory::find($newValue)->name ?? $newValue);
                } elseif ($key === 'business_unit_id') {
                    $oldValue = BusinessUnit::find($oldValue)->name ?? $oldValue;
                    $newValue = BusinessUnit::find($newValue)->name ?? $newValue;
                } elseif ($key === 'sub_business_unit_id') {
                    $oldValue = \App\Models\SubBusinessUnit::find($oldValue)->name ?? '-';
                    $newValue = \App\Models\SubBusinessUnit::find($newValue)->name ?? '-';
                } elseif ($key === 'date') {
                    $oldValue = \Carbon\Carbon::parse($oldValue)->format('Y-m-d');
                    $newValue = \Carbon\Carbon::parse($newValue)->format('Y-m-d');
                } elseif ($key === 'start_time' || $key === 'end_time') {
                    $oldValue = \Carbon\Carbon::parse($oldValue)->format('H:i');
                    $newValue = \Carbon\Carbon::parse($newValue)->format('H:i');
                }

                // Skip if after formatting they are actually the same
                if ($oldValue === $newValue) {
                    continue;
                }

                $label = $fieldNames[$key] ?? $key;
                $changes[] = "{$label}: {$oldValue} -> {$newValue}";
            }

            $booking->save();

            if (!empty($changes)) {
                $notificationService = new NotificationService();
                $notifyPic = $request->has('notify_pic');
                $notificationService->sendBookingEditedNotification($booking, Auth::user(), $changes, $notifyPic);
            }
        }

        return redirect()->route('admin.bookings.index')->with('success', 'Data booking berhasil diperbarui.');
    }

    public function destroy(Booking $booking)
    {
        $bookingData = [
            'pic_name' => $booking->pic_name,
            'lab_name' => optional($booking->laboratory)->name,
            'date' => \Carbon\Carbon::parse($booking->date)->format('d M Y'),
            'time' => \Carbon\Carbon::parse($booking->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('H:i')
        ];
        
        $admin = Auth::user();
        
        $booking->delete();

        $notificationService = new NotificationService();
        $notificationService->sendBookingDeletedNotification($bookingData, $admin);

        return back()->with('success', 'Data booking berhasil dihapus.');
    }
}
