<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'divisi_pemilik',
        'parent_id',
        'type',
        'name',
        'path',
        'mime_type',
        'size',
        'trashed_at',
        'share_mode',
        'share_division',
        'share_token',
        'share_password',
    ];

    protected $casts = [
        'size'       => 'integer',
        'trashed_at' => 'datetime',
    ];

    protected $hidden = ['share_password'];

    public const TYPE_FOLDER = 'folder';
    public const TYPE_FILE   = 'file';

    public const BAGI_PRIVAT   = 'privat';
    public const BAGI_INTERNAL = 'internal';
    public const BAGI_DIVISI   = 'divisi';
    public const BAGI_TAUTAN   = 'tautan';

    public static function modeBerbagi(): array
    {
        return [
            self::BAGI_PRIVAT   => 'Privat — hanya saya',
            self::BAGI_INTERNAL => 'Internal — semua karyawan yang login',
            self::BAGI_DIVISI   => 'Divisi tertentu — hanya satu divisi',
            self::BAGI_TAUTAN   => 'Tautan — siapa pun yang punya tautannya',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isFolder(): bool
    {
        return $this->type === self::TYPE_FOLDER;
    }

    /** Item yang belum dibuang ke tempat sampah. */
    public function scopeAktif($query)
    {
        return $query->whereNull('trashed_at');
    }

    public function scopeDiSampah($query)
    {
        return $query->whereNotNull('trashed_at');
    }

    public function scopeMilik($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /** Arsip yang dimiliki divisi, bukan perorangan. */
    public function scopeMilikDivisi($query, string $divisi)
    {
        return $query->whereNull('user_id')->where('divisi_pemilik', $divisi);
    }

    public function adalahArsipDivisi(): bool
    {
        return $this->user_id === null && $this->divisi_pemilik !== null;
    }

    /** Divisi yang menaungi item ini, baik lewat pemilik maupun penanda divisi. */
    public function divisi(): ?string
    {
        return $this->divisi_pemilik ?? ($this->user?->profile->division ?? null);
    }

    /** Folder yang bisa dilihat karyawan lain lewat sidebar. */
    public function scopeDibagikanInternal($query)
    {
        return $query->where('type', self::TYPE_FOLDER)
            ->whereIn('share_mode', [self::BAGI_INTERNAL, self::BAGI_DIVISI, self::BAGI_TAUTAN])
            ->whereNull('trashed_at');
    }

    public function dibagikan(): bool
    {
        return $this->share_mode !== self::BAGI_PRIVAT;
    }

    public function pakaiSandi(): bool
    {
        return $this->share_mode === self::BAGI_TAUTAN && ! empty($this->share_password);
    }

    public function labelBerbagi(): string
    {
        return match ($this->share_mode) {
            self::BAGI_INTERNAL => 'Internal',
            self::BAGI_DIVISI   => 'Divisi ' . ($this->share_division ?: '—'),
            self::BAGI_TAUTAN   => $this->pakaiSandi() ? 'Tautan + sandi' : 'Tautan',
            default             => 'Privat',
        };
    }

    /**
     * Folder terdekat ke atas (termasuk diri sendiri) yang dibagikan lewat
     * tautan. Dipakai untuk memeriksa apakah sebuah berkas boleh diunduh
     * pengunjung yang datang dari tautan berbagi.
     */
    /** Bolehkah pengguna ini membuka folder yang dibagikan? */
    public function bolehDibukaOleh(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        if ($this->user_id !== null && $this->user_id === $user->id) {
            return true;
        }

        // Arsip divisi terbuka bagi seluruh anggota divisi itu.
        if ($this->adalahArsipDivisi()) {
            return ($user->profile->division ?? null) === $this->divisi_pemilik;
        }

        return match ($this->share_mode) {
            self::BAGI_INTERNAL, self::BAGI_TAUTAN => true,
            self::BAGI_DIVISI => $this->share_division
                && ($user->profile->division ?? null) === $this->share_division,
            default => false,
        };
    }

    public function akarBerbagi(): ?self
    {
        foreach (array_reverse($this->jejak()) as $node) {
            if ($node->share_mode === self::BAGI_TAUTAN && ! $node->trashed_at) {
                return $node;
            }
        }

        return null;
    }

    /** Jejak folder dari akar sampai item ini, untuk remah roti. */
    public function jejak(): array
    {
        $jejak = [];
        $node  = $this;

        // Batas 50 tingkat sebagai pengaman bila ada data melingkar.
        for ($i = 0; $i < 50 && $node; $i++) {
            array_unshift($jejak, $node);
            $node = $node->parent;
        }

        return $jejak;
    }

    public function ukuranTerbaca(): string
    {
        return static::formatUkuran($this->size);
    }

    public static function formatUkuran(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $satuan = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($satuan) - 1);

        return round($bytes / (1024 ** $i), $i === 0 ? 0 : 1) . ' ' . $satuan[$i];
    }

    /**
     * Ikon dan warna berdasarkan jenis berkas. Dipakai tampilan agar
     * daftar berkas bisa dipindai sekilas seperti pengelola berkas biasa.
     */
    public function jenisRingkas(): string
    {
        if ($this->isFolder()) {
            return 'folder';
        }

        $mime = (string) $this->mime_type;
        $ext  = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));

        return match (true) {
            str_starts_with($mime, 'image/')                                => 'gambar',
            str_starts_with($mime, 'video/')                                => 'video',
            str_starts_with($mime, 'audio/')                                => 'audio',
            $mime === 'application/pdf'                                     => 'pdf',
            in_array($ext, ['doc', 'docx', 'odt'], true)                    => 'dokumen',
            in_array($ext, ['xls', 'xlsx', 'csv', 'ods'], true)             => 'lembar',
            in_array($ext, ['ppt', 'pptx', 'odp'], true)                    => 'presentasi',
            in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'], true)         => 'arsip',
            in_array($ext, ['apk', 'aab'], true)                            => 'aplikasi',
            default                                                          => 'berkas',
        };
    }
}
