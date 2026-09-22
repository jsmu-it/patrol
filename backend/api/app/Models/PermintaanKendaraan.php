<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanKendaraan extends Model
{
    use HasFactory;

    protected $table = 'permintaan_kendaraan';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pakai' => 'date',
        'atasan_pada'   => 'datetime',
        'ga_pada'       => 'datetime',
    ];

    public static function daftarStatus(): array
    {
        return [
            'menunggu_atasan' => 'Menunggu Atasan Langsung',
            'menunggu_ga'     => 'Menunggu Bagian GA',
            'siap'            => 'Kendaraan Disiapkan',
            'ditolak'         => 'Ditolak',
        ];
    }

    public function labelStatus(): string
    {
        return self::daftarStatus()[$this->status] ?? $this->status;
    }

    public function warnaStatus(): string
    {
        return match ($this->status) {
            'siap'    => 'pt-i-hijau',
            'ditolak' => 'pt-i-merah',
            default   => 'pt-i-kuning',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_oleh');
    }

    public function petugasGa()
    {
        return $this->belongsTo(User::class, 'ga_oleh');
    }

    /** "Senin / 02-09-2026", mengikuti baris HARI / TANGGAL pada formulir. */
    public function labelHariTanggal(): string
    {
        return \App\Support\TanggalIndonesia::hari($this->tanggal_pakai)
            . ' / ' . $this->tanggal_pakai->format('d-m-Y');
    }

    public function labelJam(): string
    {
        return substr((string) $this->jam, 0, 5);
    }

    /** Nomor urut permintaan: PK/2609/0001 */
    public static function nomorBerikutnya(): string
    {
        $awalan = 'PK/' . now()->format('ym') . '/';
        $urut   = static::where('nomor', 'like', $awalan . '%')->count() + 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }
}
