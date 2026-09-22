<?php

namespace App\Notifications;

use App\Models\JocCard;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Pemberitahuan kartu JOC baru.
 *
 * Dikirim ke seluruh karyawan berdivisi HSE, dan sebagai tanda terima ke
 * pengamat yang mengirimkannya. Memakai kanal database bawaan Laravel
 * (tabel `notifications`), jadi setiap akun punya kotak masuknya sendiri.
 */
class JocDikirim extends Notification
{
    use Queueable;

    public function __construct(
        public JocCard $kartu,
        public bool $untukPengirim = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'jenis'   => 'joc',
            'joc_id'  => $this->kartu->id,
            'nomor'   => $this->kartu->nomor,
            'judul'   => $this->untukPengirim
                ? 'Kartu JOC Anda terkirim'
                : 'Kartu JOC baru dari ' . $this->kartu->nama,
            'pesan'   => $this->untukPengirim
                ? 'Kartu ' . $this->kartu->nomor . ' sudah diteruskan ke divisi HSE.'
                : $this->kartu->labelJenis() . ' di ' . $this->kartu->lokasi . '. '
                  . \Illuminate\Support\Str::limit($this->kartu->rincian_temuan, 90),
            'tautan'  => route('portal.joc.show', $this->kartu->id),
        ];
    }
}
