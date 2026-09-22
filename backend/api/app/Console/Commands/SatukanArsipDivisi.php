<?php

namespace App\Console\Commands;

use App\Models\PortalItem;
use App\Models\User;
use App\Services\ArsipDivisi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Menyatukan folder arsip yang terlanjur kembar menjadi satu folder divisi.
 *
 * Selama folder arsip dititipkan ke salah satu anggota divisi, pergantian
 * penerima titipan melahirkan folder kedua dengan isi yang berbeda. Perintah
 * ini memindahkan seluruh dokumen arsip ke folder milik divisi, lalu membuang
 * folder lama yang sudah kosong.
 *
 * Hanya berkas yang jelas-jelas dokumen pengajuan yang dipindahkan — dikenali
 * dari awalan nomornya. Berkas pribadi yang kebetulan menumpang di folder
 * bernama sama sengaja ditinggalkan dan dilaporkan, supaya tidak ada dokumen
 * orang yang tiba-tiba terlihat satu divisi.
 */
class SatukanArsipDivisi extends Command
{
    protected $signature = 'portal:satukan-arsip {--terapkan : Jalankan sungguhan; tanpa ini hanya menampilkan rencana}';

    protected $description = 'Satukan folder arsip divisi yang kembar menjadi satu folder milik divisi';

    /** Awalan nomor dokumen per jenis arsip. */
    private const AWALAN = [
        'rptk'         => 'RPTK-',
        'pbg'          => 'PBG-',
        'kendaraan'    => 'PK-',
        'joc'          => 'JOC-',
        'sasaran_mutu' => 'SM-',
    ];

    public function handle(ArsipDivisi $arsip): int
    {
        $terapkan = (bool) $this->option('terapkan');
        $dipindah = 0;
        $ditinggal = [];

        foreach (ArsipDivisi::TUJUAN as $kunci => [$divisi, $namaFolder]) {
            $anggota = User::whereHas('profile', fn ($q) => $q->where('division', $divisi))->pluck('id');

            // Folder bernama sama, di akar, milik anggota divisi ini.
            $kembar = PortalItem::aktif()
                ->where('type', PortalItem::TYPE_FOLDER)
                ->whereNull('parent_id')
                ->where('name', $namaFolder)
                ->whereIn('user_id', $anggota)
                ->get();

            if ($kembar->isEmpty()) {
                continue;
            }

            $this->line(sprintf('%s / %s — %d folder perorangan ditemukan', $divisi, $namaFolder, $kembar->count()));

            $tujuan = $terapkan ? $arsip->folderDivisi($divisi, $namaFolder) : null;

            foreach ($kembar as $folderLama) {
                $pemilik = $folderLama->user?->name ?? '-';

                foreach ($folderLama->children()->whereNull('trashed_at')->get() as $berkas) {
                    if (! str_starts_with($berkas->name, self::AWALAN[$kunci])) {
                        $ditinggal[] = "{$berkas->name} (folder {$namaFolder} milik {$pemilik})";
                        continue;
                    }

                    $this->line("    pindah: {$berkas->name}  (dari {$pemilik})");
                    $dipindah++;

                    if ($terapkan) {
                        $this->pindahkan($berkas, $tujuan, $divisi, $arsip);
                    }
                }

                if ($terapkan) {
                    $folderLama->refresh();
                    if ($folderLama->children()->whereNull('trashed_at')->count() === 0) {
                        $folderLama->forceDelete();
                        $this->line("    folder kosong milik {$pemilik} dihapus");
                    } else {
                        $this->warn("    folder milik {$pemilik} masih berisi berkas lain, dibiarkan");
                    }
                }
            }
        }

        foreach ($ditinggal as $b) {
            $this->warn('  dibiarkan (bukan dokumen pengajuan): ' . $b);
        }

        $this->info($dipindah . ' dokumen ' . ($terapkan ? 'dipindahkan.' : 'akan dipindahkan. Jalankan ulang dengan --terapkan.'));

        return self::SUCCESS;
    }

    /**
     * Memindahkan satu berkas ke folder divisi, beserta berkas fisiknya.
     * Nama yang sudah ada di tujuan dianggap dokumen yang sama — yang lama
     * ditimpa agar tidak lahir "(2)" untuk dokumen yang identik.
     */
    private function pindahkan(PortalItem $berkas, PortalItem $tujuan, string $divisi, ArsipDivisi $arsip): void
    {
        $kembar = PortalItem::aktif()
            ->where('parent_id', $tujuan->id)
            ->where('name', $berkas->name)
            ->first();

        $tujuanDir = $arsip->direktoriDivisi($divisi);
        $baru      = $tujuanDir . '/' . basename((string) $berkas->path);

        if ($berkas->path && Storage::disk('local')->exists($berkas->path) && $berkas->path !== $baru) {
            Storage::disk('local')->makeDirectory($tujuanDir);
            Storage::disk('local')->move($berkas->path, $baru);
        }

        if ($kembar) {
            if ($kembar->path && Storage::disk('local')->exists($kembar->path)) {
                Storage::disk('local')->delete($kembar->path);
            }
            $kembar->forceDelete();
        }

        $berkas->update([
            'user_id'        => null,
            'divisi_pemilik' => $divisi,
            'parent_id'      => $tujuan->id,
            'path'           => $baru,
        ]);
    }
}
