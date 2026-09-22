<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PortalItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $terpakai = PortalItem::milik($user->id)->aktif()
            ->where('type', PortalItem::TYPE_FILE)->sum('size');

        $jumlahBerkas = PortalItem::milik($user->id)->aktif()
            ->where('type', PortalItem::TYPE_FILE)->count();

        $terbaru = PortalItem::milik($user->id)->aktif()
            ->where('type', PortalItem::TYPE_FILE)
            ->latest('updated_at')->take(5)->get();

        return view('portal.home', [
            'terpakai'     => $terpakai,
            'kuota'        => StorageController::KUOTA_BYTE,
            'jumlahBerkas' => $jumlahBerkas,
            'terbaru'      => $terbaru,
        ]);
    }
}
