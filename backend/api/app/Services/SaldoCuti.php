<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Saldo cuti yang berlaku untuk seorang karyawan.
 *
 * Dua aturan perusahaan yang berlaku di sini:
 *
 * 1. Jatah cuti hanya untuk karyawan Head Office. Penempatan di project klien
 *    tidak mendapat saldo cuti sama sekali — bukan nol, melainkan memang tidak
 *    ada, dan aplikasi menampilkannya sebagai "tidak ada cuti".
 * 2. Saldo hanya sebesar yang disetel HR di `leave_balances`. Tidak ada jatah
 *    bawaan yang diberikan diam-diam; selama belum disetel, saldonya nol.
 *
 * Perhitungannya dikumpulkan di satu tempat supaya angka di aplikasi dan di
 * dashboard tidak mungkin berbeda — dulu API memakai jatah bawaan jenis cuti
 * sedangkan dashboard membaca tabelnya langsung, sehingga orang yang sama
 * tampil 12 hari di aplikasi tetapi 0 di dashboard.
 */
class SaldoCuti
{
    /** Project yang karyawannya mendapat jatah cuti. */
    private const PROJECT_BAWAAN = 'Head Office';

    /**
     * Berhakkah karyawan ini atas saldo cuti?
     *
     * Ditentukan oleh penempatannya. Nama project-nya bisa diganti lewat
     * setelan `project_berhak_cuti` tanpa mengubah kode.
     */
    public static function berhakCuti(User $user): bool
    {
        $project = \App\Models\Setting::get('project_berhak_cuti', self::PROJECT_BAWAAN);

        return $user->activeProject?->name === $project;
    }

    /**
     * @param  Collection<int, LeaveType>|null     $tipeAktif  jenis cuti aktif, bila sudah dimuat
     * @param  Collection<int, LeaveBalance>|null  $tersimpan  saldo tersimpan tahun itu, bila sudah dimuat
     * @return Collection<int, array<string, mixed>>
     */
    public static function untuk(User $user, ?int $tahun = null, ?Collection $tipeAktif = null, ?Collection $tersimpan = null): Collection
    {
        if (! self::berhakCuti($user)) {
            return collect();
        }

        $tahun     = $tahun ?? (int) now()->year;
        $tipeAktif = $tipeAktif ?? LeaveType::active()->orderBy('name')->get();
        $tersimpan = $tersimpan ?? $user->leaveBalances()->where('year', $tahun)->with('leaveType')->get();

        $perTipe = $tersimpan->keyBy('leave_type_id');

        $hasil = $tipeAktif->map(function (LeaveType $tipe) use ($perTipe) {
            $saldo = $perTipe->get($tipe->id);

            // Tanpa baris tersimpan, saldonya nol — jatah bawaan jenis cuti
            // hanya dipakai sebagai saran angka di formulir HR, bukan hak yang
            // diberikan otomatis.
            return self::baris(
                (int) $tipe->id,
                (string) $tipe->name,
                $saldo ? (int) $saldo->quota : 0,
                $saldo ? (int) $saldo->used : 0,
                tersimpan: $saldo !== null,
            );
        });

        // Saldo yang sudah pernah disetel untuk jenis cuti yang kini dinonaktifkan
        // tetap ditampilkan — angkanya sudah menjadi hak karyawan.
        foreach ($tersimpan as $saldo) {
            if (! $tipeAktif->contains('id', $saldo->leave_type_id)) {
                $hasil->push(self::baris(
                    (int) $saldo->leave_type_id,
                    (string) ($saldo->leaveType?->name ?? 'Tidak dikenal'),
                    (int) $saldo->quota,
                    (int) $saldo->used,
                    tersimpan: true,
                ));
            }
        }

        return $hasil->values();
    }

    /**
     * Mengurangi saldo ketika sebuah pengajuan disetujui.
     *
     * Baris saldo dibuat bila belum ada, memakai jatah bawaan jenis cutinya —
     * sehingga angka yang selama ini hanya "bawaan" menjadi tersimpan pada saat
     * pertama kali dipakai. Dipanggil dari seluruh jalur persetujuan agar
     * saldonya ikut bergerak, bukan hanya statusnya.
     */
    public static function pakai(\App\Models\LeaveRequest $pengajuan): void
    {
        $hari = self::jumlahHari($pengajuan);
        if ($hari < 1 || ! $pengajuan->leave_type_id) {
            return;
        }

        $tahun = (int) $pengajuan->date_from?->year;

        $saldo = LeaveBalance::firstOrCreate(
            [
                'user_id'       => $pengajuan->user_id,
                'leave_type_id' => $pengajuan->leave_type_id,
                'year'          => $tahun,
            ],
            ['quota' => 0, 'used' => 0],   // jatahnya hak HR untuk menetapkan
        );

        // Sengaja tidak memakai deduct(): pengajuan yang sudah disetujui atasan
        // tidak boleh gagal tersimpan hanya karena saldonya kurang — selisihnya
        // justru perlu terlihat agar bisa ditindaklanjuti HR.
        $saldo->used = $saldo->used + $hari;
        $saldo->save();
    }

    /** Mengembalikan saldo ketika persetujuan dicabut. */
    public static function kembalikan(\App\Models\LeaveRequest $pengajuan): void
    {
        $hari = self::jumlahHari($pengajuan);
        if ($hari < 1 || ! $pengajuan->leave_type_id) {
            return;
        }

        $saldo = LeaveBalance::where('user_id', $pengajuan->user_id)
            ->where('leave_type_id', $pengajuan->leave_type_id)
            ->where('year', (int) $pengajuan->date_from?->year)
            ->first();

        if ($saldo) {
            $saldo->used = max(0, $saldo->used - $hari);
            $saldo->save();
        }
    }

    /** Lama cuti dalam hari, termasuk hari pertama dan terakhir. */
    public static function jumlahHari(\App\Models\LeaveRequest $pengajuan): int
    {
        if (! $pengajuan->date_from || ! $pengajuan->date_to) {
            return 0;
        }

        return (int) $pengajuan->date_from->startOfDay()->diffInDays($pengajuan->date_to->startOfDay()) + 1;
    }

    /** Saldo satu jenis cuti saja. */
    public static function satuTipe(User $user, int $leaveTypeId, ?int $tahun = null): ?array
    {
        return self::untuk($user, $tahun)->firstWhere('leave_type_id', $leaveTypeId);
    }

    private static function baris(int $id, string $nama, int $kuota, int $terpakai, bool $tersimpan): array
    {
        return [
            'leave_type_id'   => $id,
            'leave_type_name' => $nama,
            'quota'           => $kuota,
            'used'            => $terpakai,
            'remaining'       => max(0, $kuota - $terpakai),
            // false = masih memakai jatah bawaan, belum pernah disetel HR
            'tersimpan'       => $tersimpan,
        ];
    }
}
