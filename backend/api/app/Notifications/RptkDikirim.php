<?php

namespace App\Notifications;

use App\Models\RptkRequest;
use Illuminate\Notifications\Notification;

/** Pemberitahuan RPTK: pengajuan baru, butuh persetujuan, atau hasil akhir. */
class RptkDikirim extends Notification
{
    public function __construct(
        public RptkRequest $rptk,
        public string $peran = 'info',   // info | persetujuan | hasil
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        [$judul, $pesan] = match ($this->peran) {
            'persetujuan' => [
                'RPTK menunggu persetujuan Anda',
                $this->rptk->nomor . ' — ' . $this->rptk->jabatan . ' (' . $this->rptk->jumlah . ' orang) dari ' . $this->rptk->nama,
            ],
            'hasil' => [
                'RPTK ' . strtolower($this->rptk->labelStatus()),
                $this->rptk->nomor . ' — ' . $this->rptk->jabatan . '. Status: ' . $this->rptk->labelStatus() . '.',
            ],
            default => [
                'Permintaan RPTK baru',
                $this->rptk->nomor . ' — ' . $this->rptk->jabatan . ' (' . $this->rptk->jumlah . ' orang) diajukan oleh ' . $this->rptk->nama,
            ],
        };

        return [
            'jenis'  => 'rptk',
            'rptk_id'=> $this->rptk->id,
            'nomor'  => $this->rptk->nomor,
            'judul'  => $judul,
            'pesan'  => $pesan,
            'tautan' => route('portal.rptk.show', $this->rptk->id),
        ];
    }
}
