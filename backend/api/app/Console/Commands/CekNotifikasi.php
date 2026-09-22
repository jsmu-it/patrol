<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;

/**
 * Memeriksa kesiapan notifikasi HP, dan bila diminta mengirim satu uji coba.
 *
 * Dibuat karena kegagalannya selama ini tidak terlihat di mana pun: tanpa
 * kredensial Firebase, setiap pengiriman berhenti diam-diam dan pengaju cuti
 * maupun atasannya sama-sama tidak tahu ada yang tidak beres.
 */
class CekNotifikasi extends Command
{
    protected $signature = 'notifikasi:cek {--kirim= : Kirim uji coba ke username ini}';

    protected $description = 'Periksa setelan notifikasi HP (Firebase) dan kirim uji coba';

    public function handle(PushNotificationService $push): int
    {
        $projectId   = config('services.fcm.project_id');
        $kredensial  = config('services.fcm.credentials');
        $adaBerkas   = $kredensial && is_readable($kredensial);

        $this->line('Setelan server:');
        $this->line('  FIREBASE_PROJECT_ID  : ' . ($projectId ?: '<kosong>'));
        $this->line('  FIREBASE_CREDENTIALS : ' . ($kredensial ?: '<kosong>')
            . ($kredensial ? ($adaBerkas ? '  (terbaca)' : '  (TIDAK TERBACA)') : ''));

        $punyaToken = User::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->count();
        $this->line('  Perangkat terdaftar  : ' . $punyaToken . ' dari ' . User::count() . ' akun');

        if (! $projectId || ! $adaBerkas) {
            $this->newLine();
            $this->error('Notifikasi HP MATI: berkas kredensial Firebase belum terpasang.');
            $this->line('Langkahnya: unduh service account JSON dari Firebase project "jsmuguard",');
            $this->line('taruh di storage/app/firebase.json, lalu isi FIREBASE_CREDENTIALS dan');
            $this->line('FIREBASE_PROJECT_ID di berkas .env.');

            return self::FAILURE;
        }

        $this->info('Setelan lengkap.');

        if ($username = $this->option('kirim')) {
            $user = User::where('username', $username)->first();

            if (! $user) {
                $this->error('Akun "' . $username . '" tidak ditemukan.');

                return self::FAILURE;
            }

            if (! $user->fcm_token) {
                $this->error($user->name . ' belum pernah membuka aplikasi di HP, jadi belum punya alamat kirim.');

                return self::FAILURE;
            }

            $push->notifyUser($user, 'Uji notifikasi', 'Kalau pesan ini muncul, notifikasi HP sudah jalan.', ['jenis' => 'uji']);
            $this->info('Uji coba dikirim ke ' . $user->name . '. Cek HP-nya; bila tidak muncul, lihat storage/logs/laravel.log.');
        }

        return self::SUCCESS;
    }
}
