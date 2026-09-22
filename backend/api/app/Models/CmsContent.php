<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsContent extends Model
{
    protected $fillable = ['key', 'title', 'subtitle', 'body', 'image'];

    /**
     * Blok konten yang dipakai situs, beserta letaknya.
     *
     * Daftar ini jadi satu-satunya sumber kebenaran: halaman daftar di dashboard
     * membuat barisnya dari sini, dan halaman edit menolak kunci di luar daftar
     * supaya tidak lahir baris sampah dari URL yang salah ketik.
     *
     * kunci => [nama yang dimengerti orang, letaknya di situs]
     */
    public const BLOK = [
        'about_us'    => ['Tentang Kami', 'Halaman Profil Perusahaan — bagian atas'],
        'visi'        => ['Visi', 'Halaman Profil Perusahaan — bagian Visi'],
        'misi'        => ['Misi', 'Halaman Profil Perusahaan — bagian Misi'],
        'hsse'        => ['HSSE', 'Halaman Profil Perusahaan — bagian HSSE (menu HSSE)'],
        'archipelago' => ['Archipelago Coverage', 'Halaman Profil Perusahaan — bagian Archipelago (menu Archipelago)'],
    ];

    public function label(): string
    {
        return self::BLOK[$this->key][0] ?? ucwords(str_replace('_', ' ', (string) $this->key));
    }

    public function letak(): string
    {
        return self::BLOK[$this->key][1] ?? '-';
    }

    public function sudahDiisi(): bool
    {
        return filled($this->body) || filled($this->image);
    }
}
