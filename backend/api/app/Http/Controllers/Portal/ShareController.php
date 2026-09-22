<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PortalItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Halaman tautan berbagi folder Portal.
 *
 * Dapat dibuka tanpa login — cukup punya tautannya. Bila folder diberi kata
 * sandi, pengunjung harus memasukkannya lebih dulu; status terbuka disimpan
 * di sesi, per folder.
 *
 * Yang boleh diakses hanya isi di dalam folder yang dibagikan. Setiap berkas
 * diperiksa jejak leluhurnya, jadi tautan satu folder tidak bisa dipakai
 * untuk mengintip folder lain milik pemilik yang sama.
 */
class ShareController extends Controller
{
    private const KUNCI_SESI = 'portal_folder_terbuka';

    public function show(Request $request, string $token, ?int $sub = null): View|RedirectResponse
    {
        $akar = $this->folderBerbagi($token);

        if ($akar->pakaiSandi() && ! $this->sudahTerbuka($request, $akar->id)) {
            return view('portal.share.kunci', ['folder' => $akar, 'token' => $token]);
        }

        // Sub-folder yang diminta harus benar-benar berada di dalam folder yang dibagikan.
        $folder = $akar;
        if ($sub) {
            $kandidat = PortalItem::aktif()->where('type', PortalItem::TYPE_FOLDER)->findOrFail($sub);
            abort_unless($this->beradaDiDalam($kandidat, $akar), 404);
            $folder = $kandidat;
        }

        $items = PortalItem::aktif()
            ->where('parent_id', $folder->id)
            ->orderByRaw("FIELD(type, 'folder', 'file')")
            ->orderBy('name')
            ->get();

        // Remah roti hanya sampai folder yang dibagikan, bukan sampai akar milik pemilik.
        $jejak = [];
        foreach (array_reverse($folder->jejak()) as $n) {
            $jejak[] = $n;
            if ($n->id === $akar->id) {
                break;
            }
        }

        return view('portal.share.show', [
            'akar'   => $akar,
            'folder' => $folder,
            'token'  => $token,
            'items'  => $items,
            'jejak'  => array_reverse($jejak),
        ]);
    }

    /**
     * Membuka folder yang dibagikan dari sidebar, untuk karyawan yang sudah
     * login. Tidak memakai token, jadi folder bermode "internal" tetap tidak
     * bisa dijangkau orang luar — hanya yang punya akun portal.
     */
    public function internal(Request $request, PortalItem $folder, ?int $sub = null): View
    {
        abort_unless($folder->isFolder() && ! $folder->trashed_at, 404);
        // Termasuk memeriksa mode "divisi": hanya anggota divisi itu yang boleh.
        abort_unless($folder->bolehDibukaOleh($request->user()), 404);

        $akar = $folder;
        if ($sub) {
            $kandidat = PortalItem::aktif()->where('type', PortalItem::TYPE_FOLDER)->findOrFail($sub);
            abort_unless($this->beradaDiDalam($kandidat, $akar), 404);
            $folder = $kandidat;
        }

        $items = PortalItem::aktif()
            ->where('parent_id', $folder->id)
            ->orderByRaw("FIELD(type, 'folder', 'file')")
            ->orderBy('name')
            ->get();

        $jejak = [];
        foreach (array_reverse($folder->jejak()) as $n) {
            $jejak[] = $n;
            if ($n->id === $akar->id) {
                break;
            }
        }

        return view('portal.share.show', [
            'akar'   => $akar,
            'folder' => $folder,
            'token'  => null,          // null = mode internal, URL tanpa token
            'items'  => $items,
            'jejak'  => array_reverse($jejak),
        ]);
    }

    /** Unduh berkas dari folder internal, hanya untuk pengguna yang login. */
    public function unduhInternal(Request $request, PortalItem $folder, PortalItem $item): StreamedResponse
    {
        abort_unless($folder->isFolder() && ! $folder->trashed_at, 404);
        abort_unless($folder->bolehDibukaOleh($request->user()), 404);
        abort_if($item->isFolder() || ! $item->path || $item->trashed_at, 404);
        abort_unless($this->beradaDiDalam($item, $folder), 404);
        abort_unless(Storage::disk('local')->exists($item->path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->download($item->path, $item->name);
    }

    public function buka(Request $request, string $token): RedirectResponse
    {
        $akar = $this->folderBerbagi($token);
        $data = $request->validate(['password' => ['required', 'string']]);

        if (! $akar->pakaiSandi() || ! Hash::check($data['password'], $akar->share_password)) {
            return back()->withErrors(['password' => 'Kata sandi salah.']);
        }

        $terbuka = $request->session()->get(self::KUNCI_SESI, []);
        $terbuka[] = $akar->id;
        $request->session()->put(self::KUNCI_SESI, array_unique($terbuka));

        return redirect()->route('portal.share.show', $token);
    }

    public function unduh(Request $request, string $token, PortalItem $item): StreamedResponse
    {
        $akar = $this->folderBerbagi($token);

        abort_if($akar->pakaiSandi() && ! $this->sudahTerbuka($request, $akar->id), 403,
            'Buka folder dengan kata sandi terlebih dahulu.');

        abort_if($item->isFolder() || ! $item->path || $item->trashed_at, 404);
        abort_unless($this->beradaDiDalam($item, $akar), 404);
        abort_unless(Storage::disk('local')->exists($item->path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->download($item->path, $item->name);
    }

    // ------------------------------------------------------------- bantuan

    private function folderBerbagi(string $token): PortalItem
    {
        return PortalItem::aktif()
            ->where('type', PortalItem::TYPE_FOLDER)
            ->where('share_mode', PortalItem::BAGI_TAUTAN)
            ->where('share_token', $token)
            ->firstOrFail();
    }

    private function sudahTerbuka(Request $request, int $folderId): bool
    {
        return in_array($folderId, $request->session()->get(self::KUNCI_SESI, []), true);
    }

    /** Benarkah $item berada di dalam $akar (atau $akar itu sendiri)? */
    private function beradaDiDalam(PortalItem $item, PortalItem $akar): bool
    {
        foreach ($item->jejak() as $node) {
            if ($node->id === $akar->id) {
                return true;
            }
            if ($node->trashed_at) {
                return false;
            }
        }

        return false;
    }
}
