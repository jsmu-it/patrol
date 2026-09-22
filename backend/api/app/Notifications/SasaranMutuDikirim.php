<?php

namespace App\Notifications;

use App\Models\SasaranMutu;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Pemberitahuan laporan Sasaran Mutu baru.
 *
 * Dipakai untuk empat keperluan: memberi tahu pemegang dokumen mutu
 * (DOC.CONTROL), meminta persetujuan pada tahap yang sedang berjalan,
 * mengabarkan hasil ke pembuat, dan tanda terima saat laporan terkirim.
 * Kanalnya database, sama seperti pemberitahuan JOC dan RPTK.
 */
class SasaranMutuDikirim extends Notification
{
    use Queueable;

    public function __construct(
        public SasaranMutu $laporan,
        public string $peran = 'info',   // info | persetujuan | hasil | tanda_terima
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $ringkas = $this->laporan->labelDepartemen() . ' — periode ' . $this->laporan->labelBulan();

        [$judul, $pesan] = match ($this->peran) {
            'persetujuan' => [
                'Sasaran Mutu menunggu persetujuan Anda',
                $this->laporan->nomor . ' — ' . $ringkas . ', dibuat oleh ' . $this->laporan->nama . '.',
            ],
            'hasil' => [
                'Sasaran Mutu ' . strtolower($this->laporan->labelStatus()),
                $this->laporan->nomor . ' — ' . $ringkas . '. Status: ' . $this->laporan->labelStatus() . '.',
            ],
            'tanda_terima' => [
                'Laporan Sasaran Mutu Anda terkirim',
                'Laporan ' . $this->laporan->nomor . ' sudah diteruskan ke divisi DOC.CONTROL.',
            ],
            default => [
                'Laporan Sasaran Mutu baru dari ' . $this->laporan->nama,
                $ringkas . ', ' . count($this->laporan->barisSasaran()) . ' sasaran.',
            ],
        };

        return [
            'jenis'  => 'sasaran_mutu',
            'sasaran_mutu_id' => $this->laporan->id,
            'nomor'  => $this->laporan->nomor,
            'judul'  => $judul,
            'pesan'  => $pesan,
            'tautan' => route('portal.sasaran-mutu.show', $this->laporan->id),
        ];
    }
}
