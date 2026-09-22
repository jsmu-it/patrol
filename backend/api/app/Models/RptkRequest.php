<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RptkRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /** Kode formulir pada kop dokumen. */
    public const KODE_FORMULIR = 'HR/FM-04-01';

    protected $casts = [
        'hrga_pada'     => 'datetime',
        'direktur_pada' => 'datetime',
    ];

    public static function daftarAlasan(): array
    {
        return [
            'karyawan_keluar' => 'Karyawan Keluar',
            'mutasi'          => 'Mutasi',
            'penambahan'      => 'Penambahan Karyawan',
            'lain'            => 'Lain-lain',
        ];
    }

    public static function daftarStatusKaryawan(): array
    {
        return [
            'bulanan_tetap'   => 'Bulanan Tetap',
            'bulanan_kontrak' => 'Bulanan Kontrak',
            'harian'          => 'Harian',
            'lain'            => 'Lain-lain',
        ];
    }

    public static function daftarKelamin(): array
    {
        return ['laki' => 'Laki-laki', 'perempuan' => 'Perempuan', 'bebas' => 'Tidak dibatasi'];
    }

    public static function daftarSumber(): array
    {
        return ['eksternal' => 'Eksternal', 'internal' => 'Internal (Mutasi, Demosi, Promosi)'];
    }

    public static function daftarStatus(): array
    {
        return [
            'menunggu_hrga'     => 'Menunggu HR & GA Manager',
            'menunggu_direktur' => 'Menunggu Direktur',
            'disetujui'         => 'Disetujui',
            'ditolak'           => 'Ditolak',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function labelStatus(): string
    {
        return self::daftarStatus()[$this->status] ?? $this->status;
    }

    public function warnaStatus(): string
    {
        return match ($this->status) {
            'disetujui' => 'pt-i-hijau',
            'ditolak'   => 'pt-i-merah',
            default     => 'pt-i-kuning',
        };
    }

    /** Kode formulir baku HR/FM-04-01 beserta tanggal pengisian. */
    public static function nomorDokumen(?\DateTimeInterface $tanggal = null): string
    {
        return self::KODE_FORMULIR . ' - ' . ($tanggal ? \Carbon\Carbon::instance($tanggal) : now())->format('d/m/Y');
    }

    public static function nomorBerikutnya(): string
    {
        $awalan = 'RPTK/' . now()->format('ym') . '/';

        return $awalan . str_pad((string) (static::where('nomor', 'like', $awalan . '%')->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
