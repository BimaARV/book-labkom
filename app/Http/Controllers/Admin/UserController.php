<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Admin Store Request: ', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $statusMsg = 'admin_added_success';
        $message = 'Admin berhasil ditambahkan';

        if ($request->has('send_notification')) {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->sendNewAdminNotification($user, auth()->user(), $request->password);
            $statusMsg = 'admin_added_success_notif';
            $message = 'Admin berhasil ditambahkan dan notifikasi berhasil di kirim';
        }

        return back()->with($statusMsg, $message);
    }

    public function destroy(User $user)
    {
        if (User::count() <= 1 && $user->is_active) {
            return back()->with('error', 'Tidak dapat menonaktifkan admin terakhir.');
        }
        
        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        // Uncomment or update notification if needed
        // $notificationService = new \App\Services\NotificationService();
        // $notificationService->sendAdminDeletedNotification($user->name, auth()->user());

        return back()->with('success', "Admin berhasil {$action}.");
    }

    public function update(Request $request, User $user)
    {
        \Illuminate\Support\Facades\Log::info('Admin Update Request: ', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class.',email,'.$user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $changes = [];
        if ($user->name !== $request->name) {
            $changes[] = "Nama: {$user->name} -> {$request->name}";
            $user->name = $request->name;
        }
        if ($user->email !== $request->email) {
            $changes[] = "Email: {$user->email} -> {$request->email}";
            $user->email = $request->email;
        }
        if ($request->filled('password')) {
            $changes[] = "Password: telah diubah";
            $user->password = Hash::make($request->password);
            $user->must_change_password = false;
        }

        if (empty($changes)) {
            return back()->with('info', 'Tidak ada perubahan yang dilakukan.');
        }

        $user->save();

        $statusMsg = 'admin_updated_success';
        $message = 'Perubahan berhasil dilakukan';

        if ($request->has('send_notification')) {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->sendAdminUpdatedNotification($user, auth()->user(), $changes);
            $statusMsg = 'admin_updated_success_notif';
            $message = 'Perubahan berhasil dilakukan dan notifikasi berhasil di kirim';
        }

        return back()->with($statusMsg, $message);
    }
}
