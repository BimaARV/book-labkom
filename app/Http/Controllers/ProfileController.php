<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        $changes = [];
        if ($user->name !== $request->name) {
            $changes[] = "Nama: {$user->name} -> {$request->name}";
        }
        if ($user->email !== $request->email) {
            $changes[] = "Email: {$user->email} -> {$request->email}";
        }

        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if (!empty($changes)) {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->sendAdminUpdatedNotification($request->user(), clone $request->user(), $changes);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Handle forced password change request.
     */
    public function forceChangePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'must_change_password' => false,
        ]);

        // Configure Mail using NotificationService logic
        $settings = \App\Models\Setting::pluck('value', 'key');
        
        $password = $settings['MAIL_PASSWORD'] ?? env('MAIL_PASSWORD');
        try {
            if (!empty($settings['MAIL_PASSWORD'])) {
                $password = \Illuminate\Support\Facades\Crypt::decryptString($settings['MAIL_PASSWORD']);
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
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
        \Illuminate\Support\Facades\Mail::clearResolvedInstances();

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordChangedMail());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send Password Changed Email: " . $e->getMessage());
        }

        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('force-password-success', 'Password Anda telah berhasil diubah. Silakan login kembali dengan password baru Anda.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = clone $request->user();

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendAccountDeletedEmail($user->email, $user->name);
        $notificationService->sendAdminDeletedNotification($user->name, $user);

        $userToDelete = $request->user();
        Auth::logout();

        $userToDelete->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
