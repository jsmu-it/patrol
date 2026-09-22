<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SasaranMutu extends Model
{
    use HasFactory;

    protected $table = 'sasaran_mutu_reports';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal'       => 'date',
        'sasaran'       => 'array',
        'tindak_lanjut' => 'array',
        'manager_pada'  => 'datetime',
        'direktur_pada' => 'datetime',
    ];

    /** Kode formulir pada kop dokumen. */
    public const KODE_FORMULIR = 'MR/FM-07-02a';
    public const REVISI        = '03';

    /** Tahap persetujuan dokumen, berurutan seperti kolom tanda tangannya. */
    public static function daftarStatus(): array
    {
        return [
            'menunggu_manager'  => 'Menunggu Kepala Departemen',
            'menunggu_direktur' => 'Menunggu Direktur',
            'disetujui'         => 'Disetujui',
            'ditolak'           => 'Ditolak',
        ];
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

    public function pemeriksa()
    {
        return $this->belongsTo(User::class, 'manager_oleh');
    }

    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'direktur_oleh');
    }

    public static function daftarStatusTindakLanjut(): array
    {
        return ['open' => 'Open', 'proses' => 'Proses', 'selesai' => 'Selesai'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Nomor urut laporan: SM/2608/0001 */
    public static function nomorBerikutnya(): string
    {
        $awalan = 'SM/' . now()->format('ym') . '/';
        $urut   = static::where('nomor', 'like', $awalan . '%')->count() + 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    /** "2026-08" menjadi "Agustus 2026". */
    public function labelBulan(): string
    {
        [$tahun, $bulan] = array_pad(explode('-', (string) $this->bulan), 2, null);
        $nama = $bulan ? \App\Support\TanggalIndonesia::bulan($bulan) : '';

        return $nama ? $nama . ' ' . $tahun : (string) $this->bulan;
    }

    public function labelDepartemen(): string
    {
        return $this->sub_departemen
            ? $this->departemen . ' / ' . $this->sub_departemen
            : $this->departemen;
    }

    /** Baris sasaran yang benar-benar terisi. */
    public function barisSasaran(): array
    {
        return array_values(array_filter($this->sasaran ?? [], fn ($b) => filled($b['sasaran'] ?? null)));
    }

    public function barisTindakLanjut(): array
    {
        return array_values(array_filter($this->tindak_lanjut ?? [], fn ($b) => filled($b['kegiatan'] ?? null)));
    }
}
