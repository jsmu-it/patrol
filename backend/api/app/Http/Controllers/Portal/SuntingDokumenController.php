<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PortalItem;
use App\Models\User;
use App\Services\WopiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Membuka dokumen Office langsung di peramban.
 *
 * Halaman ini hanya memuat editor dalam bingkai; berkasnya sendiri diambil
 * editor lewat jalur WOPI. Jadi tidak ada berkas yang perlu diunduh dulu, dan
 * hasil suntingan tersimpan kembali ke folder yang sama.
 */
class SuntingDokumenController extends Controller
{
    /** Jenis berkas yang bisa disunting editor. */
    public const BISA_DISUNTING = [
        'docx', 'doc', 'odt', 'rtf',
        'xlsx', 'xls', 'ods', 'csv',
        'pptx', 'ppt', 'odp',
    ];

    /** Bisa dibuka editor, tetapi hanya untuk dibaca. */
    public const HANYA_BACA = ['pdf'];

    public static function jenis(PortalItem $item): ?string
    {
        $ext = strtolower(pathinfo((string) $item->name, PATHINFO_EXTENSION));

        if (in_array($ext, self::BISA_DISUNTING, true)) {
            return 'sunting';
        }

        return in_array($ext, self::HANYA_BACA, true) ? 'baca' : null;
    }

    public function __invoke(Request $request, PortalItem $item): View
    {
        $saya = $request->user();

        abort_if($item->isFolder() || $item->trashed_at, 404);
        abort_unless(self::jenis($item) !== null, 415, 'Jenis berkas ini belum bisa dibuka di peramban.');
        abort_unless($this->bolehMembaca($item, $saya), 403);

        $bolehTulis = self::jenis($item) === 'sunting' && $this->bolehMenulis($item, $saya);

        return view('portal.storage.sunting', [
            'item'       => $item,
            'urlEditor'  => $this->urlEditor($item),
            'wopiSrc'    => route('wopi.checkFileInfo', $item),
            'token'      => WopiToken::buat($item, $saya, $bolehTulis),
            'kedaluwarsa'=> now()->addSeconds(WopiToken::masaBerlakuDetik())->valueOf(),
            'bolehTulis' => $bolehTulis,
            'kembali'    => $this->urlKembali($item, $saya),
        ]);
    }

    /**
     * Alamat editor untuk jenis berkas ini, diambil dari daftar kemampuan
     * editor. Jawabannya disimpan sebentar karena isinya jarang berubah dan
     * dipanggil setiap kali dokumen dibuka.
     */
    private function urlEditor(PortalItem $item): string
    {
        $ext = strtolower(pathinfo((string) $item->name, PATHINFO_EXTENSION));

        $daftar = Cache::remember('collabora_discovery', now()->addHours(6), function () {
            try {
                return Http::timeout(10)->get(config('services.collabora.discovery'))->body();
            } catch (\Throwable) {
                return '';
            }
        });

        if ($daftar && preg_match('/<action[^>]*ext="' . preg_quote($ext, '/') . '"[^>]*urlsrc="([^"]+)"/', $daftar, $cocok)) {
            return $cocok[1];
        }

        // Jalur bawaan bila daftar kemampuan belum terbaca.
        return rtrim(config('services.collabora.url'), '/') . '/browser/dist/cool.html?';
    }

    private function bolehMembaca(PortalItem $item, User $user): bool
    {
        if ($item->user_id === $user->id) {
            return true;
        }

        // Berkas di dalam folder yang dibagikan: hak mengikuti foldernya.
        foreach (array_reverse($item->jejak()) as $induk) {
            if ($induk->isFolder() && $induk->bolehDibukaOleh($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menyunting terbatas pada pemilik berkas dan anggota divisi pemilik arsip.
     * Folder yang sekadar dibagikan ke seluruh karyawan tetap dibuka baca-saja,
     * supaya dokumen orang lain tidak berubah tanpa sepengetahuannya.
     */
    private function bolehMenulis(PortalItem $item, User $user): bool
    {
        if ($item->user_id === $user->id) {
            return true;
        }

        return $item->divisi_pemilik !== null
            && ($user->profile->division ?? null) === $item->divisi_pemilik;
    }

    private function urlKembali(PortalItem $item, User $user): string
    {
        if ($item->user_id === $user->id) {
            return route('portal.storage.index', $item->parent_id ? ['folder' => $item->parent_id] : []);
        }

        foreach (array_reverse($item->jejak()) as $induk) {
            if ($induk->isFolder() && $induk->bolehDibukaOleh($user)) {
                return route('portal.storage.divisi', $induk);
            }
        }

        return route('portal.storage.index');
    }
}
