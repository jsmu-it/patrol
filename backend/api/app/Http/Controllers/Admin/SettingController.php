<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $tableExists = Schema::hasTable('settings');
        
        return view('admin.settings.index', [
            'tableExists' => $tableExists,
            'logo' => Setting::get('logo'),
            'footer_address' => Setting::get('footer_address'),
            'footer_email' => Setting::get('footer_email'),
            'footer_phone' => Setting::get('footer_phone'),
            'footer_copyright' => Setting::get('footer_copyright'),
            'social_facebook' => Setting::get('social_facebook'),
            'social_instagram' => Setting::get('social_instagram'),
            'social_twitter' => Setting::get('social_twitter'),
            'social_linkedin' => Setting::get('social_linkedin'),
            'social_youtube' => Setting::get('social_youtube'),
            'social_whatsapp' => Setting::get('social_whatsapp'),
        ]);
    }

    public function update(Request $request)
    {
        if (!Schema::hasTable('settings')) {
            return redirect()->route('admin.settings.index')->with('error', 'Tabel settings belum dibuat. Jalankan migrasi database terlebih dahulu.');
        }

        $request->validate([
            'logo' => 'nullable|image|max:2048',
            'footer_address' => 'nullable|string',
            'footer_email' => 'nullable|email',
            'footer_phone' => 'nullable|string',
            'footer_copyright' => 'nullable|string',
            'social_facebook' => 'nullable|url',
            'social_instagram' => 'nullable|url',
            'social_twitter' => 'nullable|url',
            'social_linkedin' => 'nullable|url',
            'social_youtube' => 'nullable|url',
            'social_whatsapp' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('logo', $path);
        }

        if ($request->has('footer_address')) Setting::set('footer_address', $request->footer_address);
        if ($request->has('footer_email')) Setting::set('footer_email', $request->footer_email);
        if ($request->has('footer_phone')) Setting::set('footer_phone', $request->footer_phone);
        if ($request->has('footer_copyright')) Setting::set('footer_copyright', $request->footer_copyright);

        // Social media
        Setting::set('social_facebook', $request->social_facebook);
        Setting::set('social_instagram', $request->social_instagram);
        Setting::set('social_twitter', $request->social_twitter);
        Setting::set('social_linkedin', $request->social_linkedin);
        Setting::set('social_youtube', $request->social_youtube);
        Setting::set('social_whatsapp', $request->social_whatsapp);

        return redirect()->route('admin.settings.index')->with('status', 'Pengaturan berhasil diperbarui.');
    }
}
