<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Kotak masuk pemberitahuan milik tiap akun portal. */
class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        return view('portal.notifikasi.index', [
            'daftar' => $request->user()->notifications()->paginate(20),
        ]);
    }

    public function baca(Request $request, string $id): RedirectResponse
    {
        $notif = $request->user()->notifications()->findOrFail($id);
        $notif->markAsRead();

        $tautan = $notif->data['tautan'] ?? null;

        return $tautan ? redirect()->to($tautan) : back();
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Semua pemberitahuan ditandai sudah dibaca.');
    }
}
