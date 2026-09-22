<?php

namespace App\Http\Controllers;

use App\Models\PortalItem;
use App\Services\WopiToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Jembatan WOPI: cara editor dokumen daring membaca dan menyimpan berkas.
 *
 * Dipanggil oleh kontainer editor, bukan oleh peramban pengguna, jadi jalurnya
 * berada di luar middleware sesi. Yang menjaganya adalah tiket akses pada
 * parameter `access_token`, yang sudah terikat pada satu berkas, satu pengguna,
 * dan satu masa berlaku.
 */
class WopiController extends Controller
{
    /** Keterangan berkas — dipanggil editor sebelum membuka dokumen. */
    public function checkFileInfo(Request $request, PortalItem $item): JsonResponse
    {
        $tiket = $this->tiket($request, $item);

        return response()->json([
            'BaseFileName'        => $item->name,
            'Size'                => (int) $item->size,
            'Version'             => (string) $item->updated_at?->timestamp,
            'LastModifiedTime'    => $item->updated_at?->toIso8601ZuluString(),
            'OwnerId'             => (string) ($item->user_id ?? 'divisi-' . $item->divisi_pemilik),
            'UserId'              => (string) $tiket['user']->id,
            'UserFriendlyName'    => $tiket['user']->name,
            'UserCanWrite'        => $tiket['bolehTulis'],
            'UserCanNotWriteRelative' => true,   // "Simpan sebagai" tidak dipakai
            'SupportsUpdate'      => true,
            'SupportsLocks'       => false,
            'DisablePrint'        => false,
            'DisableExport'       => false,
            'DisableCopy'         => false,
        ]);
    }

    /** Isi berkas yang dibuka editor. */
    public function getFile(Request $request, PortalItem $item): Response
    {
        $this->tiket($request, $item);

        abort_unless($item->path && Storage::disk('local')->exists($item->path), 404);

        return response(Storage::disk('local')->get($item->path), 200, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /** Menyimpan hasil suntingan kembali ke penyimpanan portal. */
    public function putFile(Request $request, PortalItem $item): Response
    {
        $tiket = $this->tiket($request, $item);

        abort_unless($tiket['bolehTulis'], 403, 'Berkas ini dibuka hanya untuk dibaca.');

        $isi = $request->getContent();
        abort_if($isi === '' || $isi === false, 400, 'Isi berkas kosong.');

        Storage::disk('local')->put($item->path, $isi);
        $item->update(['size' => strlen($isi)]);

        // Editor memakai nilai ini untuk mengenali versi terakhir yang disimpan.
        return response('', 200, ['X-WOPI-ItemVersion' => (string) $item->fresh()->updated_at?->timestamp]);
    }

    /** @return array{item: PortalItem, user: \App\Models\User, bolehTulis: bool} */
    private function tiket(Request $request, PortalItem $item): array
    {
        $tiket = WopiToken::buka($request->query('access_token'), $item);

        abort_if($tiket === null, 401, 'Tiket akses tidak sah atau sudah kedaluwarsa.');
        abort_if($item->isFolder() || $item->trashed_at !== null, 404);

        return $tiket;
    }
}
