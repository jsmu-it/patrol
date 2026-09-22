<?php

namespace App\Support;

use DateTimeInterface;

/**
 * Nama hari dan bulan dalam bahasa Indonesia.
 *
 * Locale aplikasi masih 'en', sedangkan seluruh dokumen cetak berbahasa
 * Indonesia. Daripada mengubah locale global — yang ikut memengaruhi bagian
 * lain aplikasi — penamaannya dikumpulkan di sini supaya tidak ditulis ulang
 * di tiap modul.
 */
class TanggalIndonesia
{
    private const HARI = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
    ];

    private const BULAN = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',     '04' => 'April',
        '05' => 'Mei',     '06' => 'Juni',     '07' => 'Juli',      '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
    ];

    /** "Rabu" */
    public static function hari(DateTimeInterface $tanggal): string
    {
        return self::HARI[$tanggal->format('l')] ?? '';
    }

    /** "September", menerima objek tanggal maupun nomor bulan "09". */
    public static function bulan(DateTimeInterface|string $bulan): string
    {
        $kunci = $bulan instanceof DateTimeInterface ? $bulan->format('m') : str_pad($bulan, 2, '0', STR_PAD_LEFT);

        return self::BULAN[$kunci] ?? '';
    }

    /** "2 September 2026" */
    public static function panjang(DateTimeInterface $tanggal): string
    {
        return (int) $tanggal->format('d') . ' ' . self::bulan($tanggal) . ' ' . $tanggal->format('Y');
    }
}
