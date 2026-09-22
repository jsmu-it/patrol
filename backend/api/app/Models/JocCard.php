<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JocCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor', 'user_id', 'project_id', 'tanggal', 'jam', 'lokasi', 'nama',
        'jenis_temuan', 'kategori', 'kategori_lain', 'rincian_temuan',
        'rincian_tindakan', 'catatan', 'ttd', 'foto', 'status', 'tanggapan_hse',
        'ditangani_oleh', 'ditangani_pada',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'kategori'       => 'array',
        'foto'           => 'array',
        'ditangani_pada' => 'datetime',
    ];

    /** Kategori temuan beserta pertanyaan pemandunya, mengikuti kartu cetak. */
    public static function daftarKategori(): array
    {
        return [
            'apd'                 => ['APD (Alat Pelindung Diri)', 'Pekerja menggunakan kelengkapan APD dan digunakan sesuai fungsinya?'],
            'prosedur_kerja'      => ['Prosedur Kerja', 'SOP tersedia, dipahami, dan dijalankan?'],
            'instalasi_material'  => ['Instalasi & Material', 'Instalasi listrik, material, serta bahan berbahaya sudah memadai?'],
            'mesin_peralatan'     => ['Mesin dan Peralatan', 'Terdapat pengaman pada mesin dan peralatan yang dipakai bekerja?'],
            'gangguan_keamanan'   => ['Gangguan Keamanan', 'Adakah potensi gangguan keamanan di tempat kerja?'],
            'lingkungan'          => ['Lingkungan', 'Apakah terdapat sumber bahaya lingkungan yang belum terkendali?'],
            'perilaku_pekerja'    => ['Perilaku Pekerja', 'Pekerja melakukan tindakan aman atau tidak aman?'],
            'lain_lain'           => ['Lain-Lain', 'Sebutkan kondisi temuan lain yang tidak tersedia pada pilihan di atas.'],
        ];
    }

    public static function daftarJenis(): array
    {
        return [
            'tindakan_tidak_aman' => 'Tindakan Tidak Aman',
            'kondisi_tidak_aman'  => 'Kondisi Tidak Aman',
        ];
    }

    public static function daftarStatus(): array
    {
        return ['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function labelJenis(): string
    {
        return self::daftarJenis()[$this->jenis_temuan] ?? $this->jenis_temuan;
    }

    public function labelKategori(): array
    {
        $semua = self::daftarKategori();

        return collect($this->kategori ?? [])
            ->map(fn ($k) => $semua[$k][0] ?? $k)
            ->all();
    }

    /** Nomor urut kartu: JOC/2608/0001 */
    public static function nomorBerikutnya(): string
    {
        $awalan = 'JOC/' . now()->format('ym') . '/';
        $urut   = static::where('nomor', 'like', $awalan . '%')->count() + 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }
}
