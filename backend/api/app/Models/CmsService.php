<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CmsService extends Model
{
    protected $fillable = [
        'parent_id', 'title', 'slug', 'short_description', 'full_description', 'icon', 'image', 'order',
    ];

    /**
     * Menu "Our Services" di situs disusun dari tabel ini dan disimpan sementara
     * di cache. Setiap perubahan langsung membuang cache itu supaya layanan baru
     * tampil di menu saat itu juga, bukan menunggu cache kedaluwarsa.
     */
    protected static function booted(): void
    {
        $bersihkan = fn () => Cache::forget('menu_layanan');

        static::saved($bersihkan);
        static::deleted($bersihkan);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order')->orderBy('title');
    }

    /** Layanan yang berdiri sendiri sebagai menu, bukan anak menu. */
    public function scopeUtama($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    public function adalahAnak(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Calon induk untuk sebuah layanan: seluruh layanan utama, kecuali dirinya
     * sendiri. Anak menu tidak boleh jadi induk agar menunya berhenti di dua
     * tingkat — lebih dalam dari itu tidak bisa ditampilkan dengan rapi.
     */
    public static function calonInduk(?self $kecuali = null)
    {
        return static::utama()->urut()
            ->when($kecuali?->exists, fn ($q) => $q->whereKeyNot($kecuali->getKey()))
            ->get();
    }
}
