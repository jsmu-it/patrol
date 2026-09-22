<?php

namespace App\Services;

use App\Models\PortalItem;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Tiket akses untuk editor dokumen daring.
 *
 * Editor (Collabora) mengambil dan menyimpan berkas lewat panggilan
 * server-ke-server, jadi tidak membawa sesi login pengguna. Sebagai gantinya
 * ia membawa tiket ini: isinya siapa yang menyunting, berkas mana, boleh
 * menulis atau tidak, dan sampai kapan berlaku.
 *
 * Tiketnya dienkripsi dengan kunci aplikasi — tidak bisa dipalsukan maupun
 * dibaca dari luar, dan tidak perlu tabel tambahan untuk menyimpannya.
 */
class WopiToken
{
    /** Berlaku selama sesi menyunting yang wajar, lalu mati sendiri. */
    private const MASA_BERLAKU_DETIK = 4 * 3600;

    public static function buat(PortalItem $item, User $user, bool $bolehTulis): string
    {
        return Crypt::encryptString(json_encode([
            'i' => $item->id,
            'u' => $user->id,
            'w' => $bolehTulis,
            'e' => now()->addSeconds(self::MASA_BERLAKU_DETIK)->timestamp,
        ]));
    }

    /** @return array{item: PortalItem, user: User, bolehTulis: bool}|null */
    public static function buka(?string $token, PortalItem $item): ?array
    {
        if (! $token) {
            return null;
        }

        try {
            $isi = json_decode(Crypt::decryptString($token), true);
        } catch (DecryptException) {
            return null;
        }

        if (! is_array($isi) || ($isi['e'] ?? 0) < now()->timestamp) {
            return null;
        }

        // Tiket terikat pada satu berkas: dipakai untuk berkas lain, ditolak.
        if ((int) ($isi['i'] ?? 0) !== (int) $item->id) {
            return null;
        }

        $user = User::find($isi['u'] ?? 0);

        return $user ? ['item' => $item, 'user' => $user, 'bolehTulis' => (bool) ($isi['w'] ?? false)] : null;
    }

    public static function masaBerlakuDetik(): int
    {
        return self::MASA_BERLAKU_DETIK;
    }
}
