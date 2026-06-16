<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SettingController extends Controller
{
    public function smtp()
    {
        $settings = Setting::whereIn('key', ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME', 'MAIL_CC_ADDRESSES'])->pluck('value', 'key');
        return view('admin.settings.smtp', compact('settings'));
    }

    public function whatsapp()
    {
        $settings = Setting::whereIn('key', ['WA_GATEWAY_URL', 'WA_API_KEY', 'WA_GROUP_NAME', 'WA_GROUP_ID'])->pluck('value', 'key');
        return view('admin.settings.whatsapp', compact('settings'));
    }

    public function updateSmtp(Request $request)
    {
        $keys = ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME', 'MAIL_CC_ADDRESSES'];
        
        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->$key]);
            }
        }

        // Handle password separately for encryption
        if ($request->filled('MAIL_PASSWORD')) {
            $existingPassword = Setting::where('key', 'MAIL_PASSWORD')->value('value');
            if ($request->MAIL_PASSWORD !== $existingPassword) {
                Setting::updateOrCreate(
                    ['key' => 'MAIL_PASSWORD'],
                    ['value' => Crypt::encryptString($request->MAIL_PASSWORD)]
                );
            }
        }

        return back()->with('success', 'Konfigurasi SMTP berhasil disimpan.');
    }

    public function updateWhatsapp(Request $request)
    {
        $keys = ['WA_GATEWAY_URL', 'WA_API_KEY', 'WA_GROUP_NAME', 'WA_GROUP_ID'];
        
        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->$key]);
            }
        }
        return back()->with('success', 'Konfigurasi WhatsApp berhasil disimpan.');
    }

    public function domain()
    {
        $settings = Setting::whereIn('key', ['APP_URL', 'APP_TIMEZONE'])->pluck('value', 'key');
        return view('admin.settings.domain', compact('settings'));
    }

    public function updateDomain(Request $request)
    {
        $keys = ['APP_URL', 'APP_TIMEZONE'];
        
        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->$key]);
            }
        }
        return back()->with('success', 'Konfigurasi Sistem berhasil disimpan.');
    }

    public function theme()
    {
        $settings = Setting::whereIn('key', ['APP_NAME', 'APP_DESCRIPTION'])->pluck('value', 'key');
        return view('admin.settings.theme', compact('settings'));
    }

    public function updateTheme(Request $request)
    {
        $keys = ['APP_NAME', 'APP_DESCRIPTION'];
        
        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->$key]);
            }
        }
        return back()->with('success', 'Pengaturan Tema berhasil disimpan.');
    }
}
