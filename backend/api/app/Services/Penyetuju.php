<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Hasil pencarian pemberi persetujuan untuk satu tahap.
 *
 * `pengganti` menandai bahwa orang yang ditemukan bukan pemegang jabatan yang
 * seharusnya, melainkan penampung sementara — dipakai agar dokumen tidak macet
 * ketika jabatan tersebut belum ada akunnya.
 */
class Penyetuju
{
    public function __construct(
        public readonly Collection $orang,
        public readonly string $peran,
        public readonly bool $pengganti = false,
        public readonly ?string $catatan = null,
    ) {}

    public function ada(): bool
    {
        return $this->orang->isNotEmpty();
    }

    /** Nama-nama untuk ditampilkan, mis. "Yunanto (HRGA Manager)". */
    public function label(): string
    {
        if (! $this->ada()) {
            return 'belum ada akunnya';
        }

        return $this->orang
            ->map(fn ($u) => $u->name . ($u->profile?->position ? ' (' . $u->profile->position . ')' : ''))
            ->join(', ');
    }
}
