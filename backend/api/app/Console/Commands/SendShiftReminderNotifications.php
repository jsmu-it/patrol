<?php

namespace App\Console\Commands;

use App\Models\Shift;
use App\Models\User;
use App\Services\PushNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendShiftReminderNotifications extends Command
{
    protected $signature = 'notifications:shift-reminders {--debug : Show debug information}';

    protected $description = 'Kirim notifikasi absen 5 menit sebelum jam masuk shift dan 5 menit sesudah jam keluar shift.';

    public function handle(PushNotificationService $notifications): int
    {
        $now = CarbonImmutable::now(config('app.timezone'));
        $debug = $this->option('debug');

        if ($debug) {
            $this->info("Current time: {$now->format('Y-m-d H:i:s')}");
        }

        $guards = User::query()
            ->where('role', User::ROLE_GUARD)
            ->whereNotNull('active_project_id')
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            // Sisa dari masa shift masih berupa relasi many-to-many: dulu
            // penyaringnya kolom pivot `is_active`. Sejak shift menjadi milik
            // satu project, seluruh shift project memang berlaku — dan
            // wherePivot() pada relasi hasMany menghasilkan query rusak yang
            // menggagalkan pengingat shift setiap menit.
            ->with('activeProject.shifts')
            ->get();

        if ($debug) {
            $this->info("Found {$guards->count()} guards with FCM token");
        }

        $clockInSent = 0;
        $clockOutSent = 0;

        foreach ($guards as $guard) {
            $project = $guard->activeProject;
            if (! $project) {
                continue;
            }

            foreach ($project->shifts as $shift) {
                if (! $shift->start_time) {
                    continue;
                }

                // Parse shift start time
                $shiftStart = $now->setTimeFromTimeString($shift->start_time);
                
                // Calculate minutes until shift starts (positive = shift in future)
                $minutesUntilStart = $now->diffInMinutes($shiftStart, false);

                if ($debug) {
                    $this->line("  Guard: {$guard->name}, Shift: {$shift->name}, Start: {$shift->start_time}, Minutes until start: {$minutesUntilStart}");
                }

                // Clock-in reminder: 4-5 minutes before shift starts (inclusive range)
                // minutesUntilStart will be positive when shift is in future
                if ($minutesUntilStart >= 4 && $minutesUntilStart <= 5) {
                    $title = 'Pengingat Absen Masuk';
                    $body = sprintf(
                        'Shift %s akan mulai pukul %s. Jangan lupa absen masuk.',
                        $shift->name,
                        $shiftStart->format('H:i')
                    );

                    $notifications->notifyUser($guard, $title, $body, [
                        'type' => 'attendance_reminder',
                        'shift_id' => (string) $shift->id,
                        'action' => 'clock_in',
                    ]);

                    $clockInSent++;

                    if ($debug) {
                        $this->info("    -> Sent clock-in reminder to {$guard->name}");
                    }

                    Log::info('Shift clock-in reminder sent', [
                        'user_id' => $guard->id,
                        'user_name' => $guard->name,
                        'shift' => $shift->name,
                        'shift_start' => $shift->start_time,
                    ]);
                }

                // Clock-out reminder: 4-5 minutes after shift ends
                if ($shift->end_time) {
                    $shiftEnd = $now->setTimeFromTimeString($shift->end_time);
                    
                    // Handle overnight shifts
                    // Need to determine if shift end is today or tomorrow/yesterday
                    if ($shiftEnd->lt($shiftStart)) {
                        // Shift ends on a different day than it starts
                        // If current time is before shift start (in the early morning)
                        // then we're likely in the "end" portion of an overnight shift
                        if ($now->hour < 12 && $shiftStart->hour >= 12) {
                            // We're in early morning (e.g., 06:00), shift started last night (e.g., 22:00)
                            // Shift end is TODAY (already set correctly)
                        } else {
                            // We're in afternoon/evening, shift hasn't started yet
                            // Shift end is TOMORROW
                            $shiftEnd = $shiftEnd->addDay();
                        }
                    }
                    
                    // Minutes since shift ended (positive = shift ended in past)
                    $minutesSinceEnd = $now->diffInMinutes($shiftEnd, false) * -1;

                    if ($debug) {
                        $this->line("    End: {$shift->end_time}, Minutes since end: {$minutesSinceEnd}");
                    }

                    if ($minutesSinceEnd >= 4 && $minutesSinceEnd <= 5) {
                        $title = 'Pengingat Absen Keluar';
                        $body = sprintf(
                            'Shift %s telah berakhir pukul %s. Jangan lupa absen keluar.',
                            $shift->name,
                            $shiftEnd->format('H:i')
                        );

                        $notifications->notifyUser($guard, $title, $body, [
                            'type' => 'attendance_reminder',
                            'shift_id' => (string) $shift->id,
                            'action' => 'clock_out',
                        ]);

                        $clockOutSent++;

                        if ($debug) {
                            $this->info("    -> Sent clock-out reminder to {$guard->name}");
                        }

                        Log::info('Shift clock-out reminder sent', [
                            'user_id' => $guard->id,
                            'user_name' => $guard->name,
                            'shift' => $shift->name,
                            'shift_end' => $shift->end_time,
                        ]);
                    }
                }

            }
        }

        if ($debug || $clockInSent > 0 || $clockOutSent > 0) {
            $this->info("Sent {$clockInSent} clock-in reminders and {$clockOutSent} clock-out reminders");
        }

        return self::SUCCESS;
    }
}

