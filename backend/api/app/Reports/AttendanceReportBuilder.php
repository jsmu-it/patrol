<?php

namespace App\Reports;

use App\Models\AttendanceLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AttendanceReportBuilder
{
    /**
     * @param  array{from: \Carbon\CarbonImmutable, to: \Carbon\CarbonImmutable, project_id: int|null, user_id: int|null, sort_by_project?: bool}  $filters
     */
    public function buildCollection(array $filters): Collection
    {
        $query = AttendanceLog::query()
            ->with(['user', 'project', 'shift'])
            ->whereBetween('occurred_at', [$filters['from'], $filters['to']->addDay()]) // Extend to fetch potential outs
            ->orderBy('occurred_at');

        if ($filters['project_id']) {
            $query->where('project_id', $filters['project_id']);
        }

        if ($filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        $allLogs = $query->get();

        // Group by User to process sessions
        $groupedByUser = $allLogs->groupBy('user_id');
        $sessions = collect();

        foreach ($groupedByUser as $userId => $userLogs) {
            // We stream through logs to pair IN and OUT
            $currentSession = null;

            foreach ($userLogs as $log) {
                if ($log->type === AttendanceLog::TYPE_CLOCK_IN) {
                    // If we encounter an IN, we start a new session.
                    // If there was an existing open session (IN without OUT), we force close it as incomplete.
                    if ($currentSession) {
                        $sessions->push($this->finalizeSession($currentSession));
                    }

                    $currentSession = [
                        'date' => $log->occurred_at->toDateString(),
                        'user' => $log->user,
                        'project' => $log->project,
                        'shift' => $log->shift,
                        'in_log' => $log,
                        'out_log' => null,
                    ];
                } elseif ($log->type === AttendanceLog::TYPE_CLOCK_OUT) {
                    if ($currentSession) {
                        // Check if this OUT belongs to the current session
                        // Logic: Must be same Shift.
                        // Ideally, we should also check time constraints, but assuming basic flow:
                        
                        $currentSession['out_log'] = $log;
                        $sessions->push($this->finalizeSession($currentSession));
                        $currentSession = null;
                    } else {
                        // Orphan OUT log (Out without In). Typically shouldn't happen with strict app logic.
                        // We can ignore or log as 'Unknown IN'.
                        // For this report, we ignore orphan OUTs as they don't represent a full shift.
                    }
                }
            }

            // If loop finishes and session is still open
            if ($currentSession) {
                $sessions->push($this->finalizeSession($currentSession));
            }
        }

        // Filter sessions to strict date range requested
        // Because we grabbed +1 day to find potential outs, we need to filter IN dates back
        $sessions = $sessions->filter(function ($session) use ($filters) {
            $date = CarbonImmutable::parse($session['date']);
            return $date->greaterThanOrEqualTo($filters['from']) && $date->lessThanOrEqualTo($filters['to']);
        });

        // Sort
        $sessions = $sessions->sortBy(function ($session) {
            return $session['date'] . '|' . $session['user_name'];
        })->values();

        return $sessions;
    }

    private function finalizeSession(array $session): array
    {
        $inLog = $session['in_log'];
        $outLog = $session['out_log'];
        $shift = $session['shift'];

        // "Jika absen keluar tidak di tekan sampai pergantian hari maka di hitung tidak melakukan absen keluar"
        // "Kecuali shift malam baru boleh lintas hari"
        
        $isNightShift = false;
        if ($shift) {
            $start = CarbonImmutable::parse($shift->start_time);
            $end = CarbonImmutable::parse($shift->end_time);
            if ($end->lessThan($start)) {
                $isNightShift = true;
            }
        }

        // Check if out_log is valid based on "Day Change" rule
        if ($outLog) {
            if (!$isNightShift) {
                if ($outLog->occurred_at->toDateString() !== $inLog->occurred_at->toDateString()) {
                    // Invalid Out for Day Shift
                    $outLog = null; 
                }
            }
        }

        // Calculate Status
        $status = 'Tanpa Keluar';
        if ($outLog) {
            if ($shift) {
                $inTime = $inLog->occurred_at;
                $outTime = $outLog->occurred_at;
                $durationMinutes = $inTime->diffInMinutes($outTime);

                // Calculate Shift Duration
                $sStart = CarbonImmutable::parse($shift->start_time);
                $sEnd = CarbonImmutable::parse($shift->end_time);
                if ($sEnd->lessThan($sStart)) {
                    $sEnd = $sEnd->addDay();
                }
                $shiftDuration = $sStart->diffInMinutes($sEnd);

                // Tolerance 15 minutes
                if ($durationMinutes < $shiftDuration - 15) {
                    $status = 'Kurang Jam Kerja';
                } elseif ($durationMinutes > $shiftDuration + 15) {
                    $status = 'Lebih Jam Kerja';
                } else {
                    $status = 'Sesuai Jam Kerja';
                }
            } else {
                $status = 'Sesuai Jam Kerja'; // Default if no shift
            }
        }

        // Format for View/Export
        return [
            'date' => $inLog->occurred_at->toDateString(),
            'user_name' => $session['user']?->name ?? '-',
            'nip' => $session['user']?->profile?->nip ?? '-', 
            'project_name' => $session['project']?->name ?? '-',
            'shift_name' => $shift?->name . ' (' . $shift?->start_time . '-' . $shift?->end_time . ')',
            'clock_in_time' => $inLog->occurred_at->format('H:i:s'),
            'clock_out_time' => $outLog ? $outLog->occurred_at->format('H:i:s') : '-',
            'clock_in_photo' => $inLog->selfie_photo_path ? asset('storage/' . $inLog->selfie_photo_path) : null,
            'clock_out_photo' => $outLog && $outLog->selfie_photo_path ? asset('storage/' . $outLog->selfie_photo_path) : null,
            'clock_in_note' => $inLog->note,
            'clock_out_note' => $outLog?->note,
            'clock_in_location' => $inLog->latitude . ',' . $inLog->longitude,
            'clock_out_location' => $outLog ? $outLog->latitude . ',' . $outLog->longitude : '-',
            'status' => $status,
        ];
    }
}
