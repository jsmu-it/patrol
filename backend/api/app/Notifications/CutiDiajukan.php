<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Notifications\Notification;

/**
 * Pemberitahuan pengajuan cuti/izin di kotak masuk portal.
 *
 * Melengkapi notifikasi HP: selama ini pengajuan cuti hanya dikirim lewat
 * Firebase, sehingga ketika Firebase belum tersetel tidak ada jejak sama sekali
 * — atasan tidak tahu ada pengajuan, dan pengajunya tidak tahu hasilnya.
 * Kotak masuk portal berjalan tanpa layanan luar mana pun.
 */
class CutiDiajukan extends Notification
{
    public function __construct(
        public LeaveRequest $cuti,
        public string $peran = 'persetujuan',   // persetujuan | hasil | tanda_terima
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $jenis   = $this->cuti->leaveType?->name ?? ucfirst((string) $this->cuti->type);
        $periode = $this->cuti->date_from?->format('d M') . ' – ' . $this->cuti->date_to?->format('d M Y');

        [$judul, $pesan] = match ($this->peran) {
            'hasil' => [
                'Pengajuan ' . $jenis . ' Anda ' . $this->label(),
                $periode . '. Status: ' . $this->label() . '.',
            ],
            'tanda_terima' => [
                'Pengajuan ' . $jenis . ' terkirim',
                $periode . '. Menunggu persetujuan.',
            ],
            default => [
                'Pengajuan ' . $jenis . ' dari ' . $this->cuti->user?->name,
                $periode . '. Alasan: ' . \Illuminate\Support\Str::limit($this->cuti->reason, 80),
            ],
        };

        return [
            'jenis'   => 'cuti',
            'cuti_id' => $this->cuti->id,
            'judul'   => $judul,
            'pesan'   => $pesan,
            'tautan'  => route('admin.approvals.leave', ['status' => 'pending']),
        ];
    }

    private function label(): string
    {
        return match ($this->cuti->status) {
            'approved' => 'disetujui',
            'rejected' => 'ditolak',
            default    => 'menunggu',
        };
    }
}
