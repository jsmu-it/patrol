<?php

namespace App\Notifications;

use App\Models\PbgRequest;
use Illuminate\Notifications\Notification;

/**
 * Pemberitahuan PBG: pengajuan masuk ke gudang, menunggu persetujuan kepala
 * bagian, kabar hasil untuk pemohon, atau tanda terima saat baru dikirim.
 */
class PbgDiajukan extends Notification
{
    public function __construct(
        public PbgRequest $pbg,
        public string $peran = 'info',   // info | persetujuan | gudang | hasil | tanda_terima
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $p       = $this->pbg;
        $ringkas = $p->jumlahJenis() . ' jenis barang untuk ' . $p->departemen
            . ($p->unit_kerja ? ' (' . $p->unit_kerja . ')' : '');

        [$judul, $pesan] = match ($this->peran) {
            'persetujuan' => [
                'PBG menunggu persetujuan Anda',
                $p->nomor . ' dari ' . $p->nama . ' — ' . $ringkas . '.',
            ],
            'gudang' => [
                'PBG siap diproses gudang',
                $p->nomor . ' dari ' . $p->nama . ' sudah disetujui kepala bagian — ' . $ringkas . '.',
            ],
            'hasil' => [
                match ($p->status) {
                    'selesai' => 'Barang PBG Anda sudah diserahkan',
                    'ditolak' => 'PBG Anda ditolak',
                    default   => 'PBG Anda disetujui kepala bagian',
                },
                $p->nomor . ' — status: ' . $p->labelStatus() . '.',
            ],
            'tanda_terima' => [
                'Permintaan barang Anda terkirim',
                $p->nomor . ' menunggu persetujuan kepala bagian.',
            ],
            default => [
                'Permintaan barang gudang baru dari ' . $p->nama,
                $ringkas . '.',
            ],
        };

        return [
            'jenis'  => 'pbg',
            'pbg_id' => $p->id,
            'nomor'  => $p->nomor,
            'judul'  => $judul,
            'pesan'  => $pesan,
            'tautan' => route('portal.pbg.show', $p->id),
        ];
    }
}
