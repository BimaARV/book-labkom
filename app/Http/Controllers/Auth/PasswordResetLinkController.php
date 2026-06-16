<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                        ->withErrors(['email' => 'Pastikan email anda sudah terdaftar oleh tim IT Infrastructure']);
        }

        // Generate 8 character random password
        $newPassword = \Illuminate\Support\Str::random(8);

        // Update user's password and flag
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
            'must_change_password' => true
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
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\NewPasswordMail($newPassword));
            return back()->with('status', 'Password baru telah dikirim ke email Anda. Harap cek kotak masuk atau folder spam.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send Password Reset Email: " . $e->getMessage());
            return back()->withInput($request->only('email'))
                        ->withErrors(['email' => 'Gagal mengirim email. Pastikan konfigurasi SMTP di panel admin sudah benar.']);
        }
    }
}
