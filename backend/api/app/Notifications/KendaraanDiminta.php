<?php

namespace App\Notifications;

use App\Models\PermintaanKendaraan;
use Illuminate\Notifications\Notification;

/**
 * Pemberitahuan permintaan kendaraan: menunggu persetujuan atasan, menunggu
 * penetapan driver oleh GA, kabar hasil untuk pemohon, atau tanda terima.
 */
class KendaraanDiminta extends Notification
{
    public function __construct(
        public PermintaanKendaraan $permintaan,
        public string $peran = 'info',   // info | persetujuan | ga | hasil | tanda_terima
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $p       = $this->permintaan;
        $ringkas = $p->labelHariTanggal() . ' pukul ' . $p->labelJam() . ' — ' . \Illuminate\Support\Str::limit($p->keperluan, 70);

        [$judul, $pesan] = match ($this->peran) {
            'persetujuan' => [
                'Permintaan kendaraan menunggu persetujuan Anda',
                $p->nomor . ' dari ' . $p->nama . ' (' . $p->bagian . '). ' . $ringkas,
            ],
            'ga' => [
                'Permintaan kendaraan perlu driver & nomor polisi',
                $p->nomor . ' dari ' . $p->nama . ' sudah disetujui atasan. ' . $ringkas,
            ],
            'hasil' => [
                match ($p->status) {
                    'siap'    => 'Kendaraan Anda sudah disiapkan',
                    'ditolak' => 'Permintaan kendaraan ditolak',
                    default   => 'Permintaan kendaraan disetujui atasan',
                },
                $p->status === 'siap'
                    ? $p->nomor . ' — driver ' . ($p->driver ?: '-') . ', nopol ' . ($p->no_polisi ?: '-') . '.'
                    : $p->nomor . ' — status: ' . $p->labelStatus() . '.',
            ],
            'tanda_terima' => [
                'Permintaan kendaraan Anda terkirim',
                $p->nomor . ' menunggu persetujuan atasan langsung.',
            ],
            default => [
                'Permintaan kendaraan baru dari ' . $p->nama,
                $p->bagian . ' — ' . $ringkas,
            ],
        };

        return [
            'jenis'      => 'kendaraan',
            'kendaraan_id' => $p->id,
            'nomor'      => $p->nomor,
            'judul'      => $judul,
            'pesan'      => $pesan,
            'tautan'     => route('portal.kendaraan.show', $p->id),
        ];
    }
}
