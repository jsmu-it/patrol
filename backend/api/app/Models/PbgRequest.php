<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbgRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tgl_pbg'        => 'date',
        'tgl_penggunaan' => 'date',
        'barang'         => 'array',
        'kabag_pada'     => 'datetime',
        'gudang_pada'    => 'datetime',
    ];

    /** Kode formulir pada kop dokumen. */
    public const KODE_FORMULIR = 'GA/FM-01-01';

    public static function daftarStatus(): array
    {
        return [
            'menunggu_kabag'  => 'Menunggu Kepala Bagian',
            'menunggu_gudang' => 'Menunggu Gudang',
            'selesai'         => 'Barang Diserahkan',
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
            'selesai' => 'pt-i-hijau',
            'ditolak' => 'pt-i-merah',
            default   => 'pt-i-kuning',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kepalaBagian()
    {
        return $this->belongsTo(User::class, 'kabag_oleh');
    }

    public function petugasGudang()
    {
        return $this->belongsTo(User::class, 'gudang_oleh');
    }

    /** Baris barang yang benar-benar terisi. */
    public function barisBarang(): array
    {
        return array_values(array_filter($this->barang ?? [], fn ($b) => filled($b['nama'] ?? null)));
    }

    public function jumlahJenis(): int
    {
        return count($this->barisBarang());
    }

    /** Nomor urut permintaan: PBG/2609/0001 */
    public static function nomorBerikutnya(): string
    {
        $awalan = 'PBG/' . now()->format('ym') . '/';
        $urut   = static::where('nomor', 'like', $awalan . '%')->count() + 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }
}
