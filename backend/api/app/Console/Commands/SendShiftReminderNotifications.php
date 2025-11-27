<?php

namespace App\Console\Commands;

use App\Models\Shift;
use App\Models\User;
use App\Services\PushNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendShiftReminderNotifications extends Command
{
    protected $signature = 'notifications:shift-reminders';

    protected $description = 'Kirim notifikasi absen 5 menit sebelum jam masuk shift berdasarkan project aktif.';

    public function handle(PushNotificationService $notifications): int
    {
        $now = CarbonImmutable::now(config('app.timezone'));

        $guards = User::query()
            ->where('role', User::ROLE_GUARD)
            ->whereNotNull('active_project_id')
            ->whereNotNull('fcm_token')
            ->with(['activeProject.shifts' => function ($query) {
                $query->wherePivot('is_active', true);
            }])
            ->get();

        foreach ($guards as $guard) {
            $project = $guard->activeProject;
            if (! $project) {
                continue;
            }

            foreach ($project->shifts as $shift) {
                if (! $shift->start_time) {
                    continue;
                }

                $shiftStart = $now->setTimeFromTimeString($shift->start_time);
                $diffMinutes = $shiftStart->diffInMinutes($now, false);

                if ($diffMinutes === 5) {
                    $title = 'Pengingat Absen';
                    $body = sprintf(
                        'Shift %s (%s) akan mulai pukul %s. Jangan lupa absen masuk.',
                        $shift->name,
                        $shift->code,
                        $shiftStart->format('H:i')
                    );

                    $notifications->notifyUser($guard, $title, $body, [
                        'type' => 'attendance_reminder',
                        'shift_id' => $shift->id,
                    ]);
                }
            }
        }

        return self::SUCCESS;
    }
}
