<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;

/**
 * Mencari siapa yang berhak memeriksa dan menyetujui sebuah dokumen.
 *
 * Perusahaan tidak punya tabel struktur organisasi, jadi jabatan dibaca dari
 * kolom `position` di profil karyawan. Penulisannya belum seragam — sebagian
 * besar divisi belum punya akun ber-jabatan "Manager" — sehingga pencarian
 * dilakukan bertingkat, dan bila tetap tidak ketemu dokumen dilempar ke
 * Superadmin agar tidak mengendap tanpa pemutus.
 *
 * Penunjukan manual selalu menang: isi setelan `penyetuju_kepala_<DIVISI>`
 * atau `penyetuju_direktur` dengan id user yang dikehendaki.
 */
class PenyetujuDokumen
{
    /** Urutan jabatan yang dianggap kepala departemen, dari yang paling tepat. */
    private const TINGKAT_KEPALA = [
        ['manager'],
        ['kepala', 'head'],
        ['supervisor', 'koordinator', 'korlap'],
    ];

    /** Yang memeriksa: kepala departemen dari divisi pembuat dokumen. */
    public function kepalaDepartemen(string $divisi): Penyetuju
    {
        $peran = 'Kepala Departemen ' . $divisi;

        if ($ditunjuk = $this->ditunjuk('penyetuju_kepala_' . $divisi)) {
            return new Penyetuju(collect([$ditunjuk]), $peran);
        }

        $anggota = User::whereHas('profile', fn ($q) => $q->where('division', $divisi))
            ->with('profile')->orderBy('id')->get();

        foreach (self::TINGKAT_KEPALA as $kata) {
            $cocok = $anggota->filter(fn ($u) => $this->jabatanMengandung($u, $kata));
            if ($cocok->isNotEmpty()) {
                return new Penyetuju($cocok->values(), $peran);
            }
        }

        return new Penyetuju(
            $this->superadmin(), $peran, pengganti: true,
            catatan: 'Belum ada akun kepala departemen di divisi ' . $divisi
                . ', jadi pemeriksaan dilimpahkan ke Superadmin.',
        );
    }

    /** Yang menyetujui: Direktur. */
    public function direktur(): Penyetuju
    {
        if ($ditunjuk = $this->ditunjuk('penyetuju_direktur')) {
            return new Penyetuju(collect([$ditunjuk]), 'Direktur');
        }

        $direktur = User::whereHas('profile', fn ($q) => $q->where('position', 'like', '%Direktur%'))
            ->with('profile')->get();

        if ($direktur->isNotEmpty()) {
            return new Penyetuju($direktur, 'Direktur');
        }

        return new Penyetuju(
            $this->superadmin(), 'Direktur', pengganti: true,
            catatan: 'Belum ada akun Direktur, jadi persetujuan akhir dilimpahkan ke Superadmin.',
        );
    }

    private function ditunjuk(string $kunci): ?User
    {
        $id = Setting::get($kunci);

        return $id ? User::with('profile')->find($id) : null;
    }

    private function jabatanMengandung(User $user, array $kata): bool
    {
        $jabatan = strtolower($user->profile->position ?? '');

        foreach ($kata as $k) {
            if ($jabatan !== '' && str_contains($jabatan, $k)) {
                return true;
            }
        }

        return false;
    }

    private function superadmin()
    {
        return User::where('role', User::ROLE_SUPERADMIN)->with('profile')->get();
    }
}
