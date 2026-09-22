<?php

use App\Models\CmsService;
use Illuminate\Database\Migrations\Migration;

/**
 * Mengembalikan lima menu layanan yang semula ditulis tangan di tampilan.
 *
 * Menu "Our Services" kini disusun dari tabel `cms_services`. Karena tabelnya
 * masih kosong, kelima sub-menu yang selama ini tampil di situs ikut hilang.
 * Baris-baris ini mengembalikannya — dengan penanda (slug) yang sama persis
 * seperti tautan lamanya, sehingga alamat seperti /services#k9 yang mungkin
 * sudah tersebar tetap mengarah ke tempat yang benar.
 *
 * Isinya sengaja dibiarkan kosong: judul dan letak menu dipulihkan, sedangkan
 * teks layanannya diisi sendiri lewat dashboard.
 */
return new class extends Migration
{
    private const MENU = [
        ['security-guards', 'Security Guards',        1],
        ['technology',      'Technology Application', 2],
        ['training',        'Training & Education',   3],
        ['consultancy',     'Consultancy & Risk',     4],
        ['k9',              'K-9 Guard',              5],
    ];

    public function up(): void
    {
        foreach (self::MENU as [$slug, $judul, $urut]) {
            CmsService::firstOrCreate(
                ['slug' => $slug],
                ['title' => $judul, 'order' => $urut, 'parent_id' => null],
            );
        }
    }

    public function down(): void
    {
        // Hanya menu bawaan yang masih kosong yang dibuang, supaya isi yang
        // sudah ditulis orang tidak ikut hilang.
        foreach (self::MENU as [$slug, , ]) {
            CmsService::where('slug', $slug)
                ->whereNull('short_description')->whereNull('full_description')
                ->whereNull('image')->whereDoesntHave('children')
                ->delete();
        }
    }
};
