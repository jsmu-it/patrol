<?php

namespace App\Reports;

use App\Models\AttendanceLog;
use App\Models\LeaveRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AttendanceReportBuilder
{
    /**
     * @param  array{from: \Carbon\CarbonImmutable, to: \Carbon\CarbonImmutable, project_id: int|null, user_id: int|null, sort_by_project?: bool}  
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

        // If filtering by a specific user, generate records for ALL dates in range
        if ($filters['user_id']) {
            $user = \App\Models\User::with(['activeProject', 'profile'])->find($filters['user_id']);
            if ($user) {
                $sessions = $this->fillMissingDates($sessions, $filters, $user);
            }
        }

        // Sort
        $sessions = $sessions->sortBy(function ($session) {
            return $session['date'] . '|' . $session['user_name'];
        })->values();

        return $sessions;
    }

    /**
     * Fill missing dates with "Tidak Masuk" or "Cuti" records
     */
    private function fillMissingDates(Collection $sessions, array $filters, $user): Collection
    {
        $from = $filters['from'];
        $to = $filters['to'];
        
        // Query approved leave requests for this user in the date range
        $approvedLeaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->where(function($query) use ($from, $to) {
                $query->whereBetween('date_from', [$from, $to])
                      ->orWhereBetween('date_to', [$from, $to])
                      ->orWhere(function($q) use ($from, $to) {
                          $q->where('date_from', '<=', $from)
                            ->where('date_to', '>=', $to);
                      });
            })
            ->with('leaveType')
            ->get();
        
        // Create a map of existing attendance dates
        $existingDates = $sessions->pluck('date')->unique()->toArray();
        
        // Generate all dates in range
        $allRecords = collect();
        $currentDate = $from->copy();
        
        while ($currentDate->lessThanOrEqualTo($to)) {
            $dateString = $currentDate->toDateString();
            
            // Check if attendance exists for this date (can be multiple sessions)
            $daySessions = $sessions->where('date', $dateString);
            
            if ($daySessions->isNotEmpty()) {
                foreach ($daySessions as $session) {
                    $allRecords->push($session);
                }
            } else {
                // Check if there's an approved leave for this date
                $leaveOnThisDate = $approvedLeaves->first(function($leave) use ($dateString) {
                    $leaveFrom = $leave->date_from->toDateString();
                    $leaveTo = $leave->date_to->toDateString();
                    return $dateString >= $leaveFrom && $dateString <= $leaveTo;
                });
                
                if ($leaveOnThisDate) {
                    // Create "Cuti" record
                    $leaveTypeName = $leaveOnThisDate->leaveType?->name ?? 'Cuti';
                    $allRecords->push([
                        'date' => $dateString,
                        'user_name' => $user->name,
                        'nip' => $user->profile?->nip ?? '-',
                        'project_name' => $user->activeProject?->name ?? '-',
                        'shift_name' => '-',
                        'clock_in_time' => '-',
                        'clock_out_time' => '-',
                        'clock_in_photo' => null,
                        'clock_out_photo' => null,
                        'clock_in_note' => $leaveOnThisDate->reason,
                        'clock_out_note' => null,
                        'clock_in_location' => '-',
                        'clock_out_location' => '-',
                        'status' => 'Cuti: ' . $leaveTypeName,
                        'jam_lebih' => '-',
                    ]);
                } else {
                    // Create "Tidak Masuk" record
                    $allRecords->push([
                        'date' => $dateString,
                        'user_name' => $user->name,
                        'nip' => $user->profile?->nip ?? '-',
                        'project_name' => $user->activeProject?->name ?? '-',
                        'shift_name' => '-',
                        'clock_in_time' => '-',
                        'clock_out_time' => '-',
                        'clock_in_photo' => null,
                        'clock_out_photo' => null,
                        'clock_in_note' => null,
                        'clock_out_note' => null,
                        'clock_in_location' => '-',
                        'clock_out_location' => '-',
                        'status' => 'Tidak Masuk',
                        'jam_lebih' => '-',
                    ]);
                }
            }
            
            $currentDate = $currentDate->addDay();
        }
        
        return $allRecords;
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

        // Calculate Jam Lebih (Overtime)
        $jamLebih = '-';
        if ($outLog && $shift) {
            $shiftStartBase = CarbonImmutable::parse($inLog->occurred_at->format('Y-m-d') . ' ' . $shift->start_time);
            $shiftEndBase = CarbonImmutable::parse($inLog->occurred_at->format('Y-m-d') . ' ' . $shift->end_time);

            // Handle night shift / crossing midnight
            if ($shiftEndBase->lessThan($shiftStartBase)) {
                $shiftEndBase = $shiftEndBase->addDay();
            }
            
            if ($outLog->occurred_at->greaterThan($shiftEndBase)) {
                 $diffMinutes = $outLog->occurred_at->diffInMinutes($shiftEndBase);
                 
                 if ($diffMinutes > 0) {
                     $hours = floor($diffMinutes / 60);
                     $minutes = $diffMinutes % 60;
                     
                     $parts = [];
                     if ($hours > 0) $parts[] = "{$hours} jam";
                     if ($minutes > 0) $parts[] = "{$minutes} menit";
                     
                     $jamLebih = implode(' ', $parts);
                 }
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
            'clock_in_photo_path' => $inLog->selfie_photo_path,
            'clock_out_photo_path' => $outLog?->selfie_photo_path,
            'clock_in_note' => $inLog->note,
            'clock_out_note' => $outLog?->note,
            'clock_in_location' => $inLog->latitude . ',' . $inLog->longitude,
            'clock_out_location' => $outLog ? $outLog->latitude . ',' . $outLog->longitude : '-',
            'status' => $status,
            'jam_lebih' => $jamLebih,
        ];
    }
}
