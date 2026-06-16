<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RestrictedEmail;
use App\Services\NotificationService;

class RestrictedEmailController extends Controller
{
    public function index()
    {
        $emails = RestrictedEmail::orderBy('created_at', 'desc')->get();
        return view('admin.settings.restricted_emails', compact('emails'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|unique:restricted_emails,email'
        ], [
            'email.required' => 'Domain atau email wajib diisi.',
            'email.unique' => 'Domain atau email ini sudah terdaftar.'
        ]);

        $restricted = RestrictedEmail::create([
            'email' => $request->email
        ]);

        try {
            $notificationService = new NotificationService();
            $notificationService->sendMasterDataNotification('Restrict Email', 'ditambahkan', $restricted->email, auth()->user());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim WA Notifikasi Restrict Email: " . $e->getMessage());
        }

        return back()->with('success', 'Domain/Email berhasil ditambahkan ke daftar Restrict.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|string|unique:restricted_emails,email,' . $id
        ]);

        $restricted = RestrictedEmail::findOrFail($id);
        $oldEmail = $restricted->email;
        $restricted->update([
            'email' => $request->email
        ]);

        try {
            $notificationService = new NotificationService();
            $notificationService->sendMasterDataNotification('Restrict Email', 'diperbarui', $oldEmail . ' -> ' . $restricted->email, auth()->user());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim WA Notifikasi Restrict Email: " . $e->getMessage());
        }

        return back()->with('success', 'Domain/Email berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $restricted = RestrictedEmail::findOrFail($id);
        $emailName = $restricted->email;
        $restricted->delete();

        try {
            $notificationService = new NotificationService();
            $notificationService->sendMasterDataNotification('Restrict Email', 'dihapus', $emailName, auth()->user());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim WA Notifikasi Restrict Email: " . $e->getMessage());
        }

        return back()->with('success', 'Domain/Email berhasil dihapus dari daftar Restrict.');
    }
}
