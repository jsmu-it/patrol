<?php

namespace App\Services;

use App\Models\PortalItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Mengarsipkan hasil pengajuan ke folder divisi di Penyimpanan Data portal.
 *
 * Setiap jenis pengajuan punya satu folder tetap:
 *   RPTK, PBG, Permintaan Kendaraan  -> divisi HR & GA
 *   JOC                              -> divisi HSE
 *   Sasaran Mutu                     -> divisi DOC.CONTROL
 *
 * Foldernya milik divisi, bukan milik salah satu anggotanya: `user_id` kosong
 * dan `divisi_pemilik` diisi nama divisi. Dulu folder dititipkan ke satu akun
 * penanggung jawab, tetapi pilihannya bergeser begitu anggota divisi bertambah
 * atau jabatannya berubah — hasilnya folder kembar dengan isi terbelah. Dengan
 * dimiliki divisi, hanya mungkin ada satu dan seluruh anggota divisi melihat
 * isi yang sama.
 */
class ArsipDivisi
{
    /** kunci => [nama divisi, nama folder] */
    public const TUJUAN = [
        'rptk'         => ['HR & GA',     'RPTK'],
        'pbg'          => ['HR & GA',     'PBG'],
        'kendaraan'    => ['HR & GA',     'Permintaan Kendaraan'],
        'joc'          => ['HSE',         'JOC'],
        'sasaran_mutu' => ['DOC.CONTROL', 'Sasaran Mutu'],
    ];

    /**
     * Simpan sebuah dokumen PDF ke folder divisi.
     *
     * @return PortalItem|null null bila jenis pengajuannya tidak dikenal.
     */
    public function simpanPdf(string $kunci, string $namaBerkas, string $isiPdf): ?PortalItem
    {
        [$divisi, $namaFolder] = self::TUJUAN[$kunci] ?? [null, null];
        if (! $divisi) {
            return null;
        }

        $folder = $this->folderDivisi($divisi, $namaFolder);

        // Dokumen yang sama diperbarui di tempat, bukan digandakan. Tanpa ini
        // setiap perubahan status akan meninggalkan salinan baru di folder.
        $lama = PortalItem::aktif()
            ->where('parent_id', $folder->id)
            ->where('name', $namaBerkas)
            ->first();

        if ($lama) {
            Storage::disk('local')->put($lama->path, $isiPdf);
            $lama->update(['size' => strlen($isiPdf)]);

            return $lama;
        }

        $relatif = $this->direktoriDivisi($divisi);
        $diDisk  = uniqid('', true) . '.pdf';
        Storage::disk('local')->put($relatif . '/' . $diDisk, $isiPdf);

        return PortalItem::create([
            'user_id'        => null,
            'divisi_pemilik' => $divisi,
            'parent_id'      => $folder->id,
            'type'           => PortalItem::TYPE_FILE,
            'name'           => $namaBerkas,
            'path'           => $relatif . '/' . $diDisk,
            'mime_type'      => 'application/pdf',
            'size'           => strlen($isiPdf),
        ]);
    }

    /** Folder arsip milik divisi; dibuat sekali, dipakai seterusnya. */
    public function folderDivisi(string $divisi, string $nama): PortalItem
    {
        $folder = PortalItem::milikDivisi($divisi)->aktif()
            ->where('type', PortalItem::TYPE_FOLDER)
            ->whereNull('parent_id')
            ->where('name', $nama)
            ->first();

        if ($folder) {
            return $folder;
        }

        return PortalItem::create([
            'user_id'        => null,
            'divisi_pemilik' => $divisi,
            'parent_id'      => null,
            'type'           => PortalItem::TYPE_FOLDER,
            'name'           => $nama,
            'share_mode'     => PortalItem::BAGI_DIVISI,
            'share_division' => $divisi,
        ]);
    }

    /** Direktori fisik arsip divisi di disk privat. */
    public function direktoriDivisi(string $divisi): string
    {
        return 'portal/divisi/' . Str::slug($divisi);
    }
}
