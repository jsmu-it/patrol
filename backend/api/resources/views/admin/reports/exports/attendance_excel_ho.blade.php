<!DOCTYPE html>
<html>
<head>
</head>
<body>
    @php
        use Carbon\Carbon;
        use Carbon\CarbonPeriod;
        
        // Generate ALL dates in the range
        $startDate = $filters['from'];
        $endDate = $filters['to'];
        $period = CarbonPeriod::create($startDate, $endDate);
        $allDates = [];
        foreach ($period as $date) {
            $allDates[] = $date->format('Y-m-d');
        }
        
        // Transform row-based data to pivot format
        $userAttendance = [];
        $userIds = [];
        
        foreach ($rows as $row) {
            $userName = $row['user_name'];
            $date = Carbon::parse($row['date'])->format('Y-m-d');
            
            // Track user IDs for leave query
            if (!isset($userIds[$userName])) {
                $user = \App\Models\User::where('name', $userName)->first();
                if ($user) {
                    $userIds[$userName] = $user->id;
                }
            }
            
            // Group attendance by user
            if (!isset($userAttendance[$userName])) {
                $userAttendance[$userName] = [];
            }
            
            $userAttendance[$userName][$date] = [
                'clock_in' => $row['clock_in_time'] ?? '-',
                'clock_out' => $row['clock_out_time'] ?? '-',
                'type' => 'attendance'
            ];
        }
        
        // Fetch approved leave requests for the period
        $leaveRequests = \App\Models\LeaveRequest::with('leaveType')
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('date_from', [$startDate, $endDate])
                      ->orWhereBetween('date_to', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('date_from', '<=', $startDate)
                            ->where('date_to', '>=', $endDate);
                      });
            })
            ->get();
        
        // Add leave data to userAttendance
        foreach ($leaveRequests as $leave) {
            $user = \App\Models\User::find($leave->user_id);
            if (!$user) continue;
            
            $userName = $user->name;
            if (!isset($userAttendance[$userName])) {
                $userAttendance[$userName] = [];
            }
            
            $leavePeriod = CarbonPeriod::create($leave->date_from, $leave->date_to);
            foreach ($leavePeriod as $leaveDate) {
                $dateKey = $leaveDate->format('Y-m-d');
                if (in_array($dateKey, $allDates)) {
                    // Only add leave if no attendance exists for that date
                    if (!isset($userAttendance[$userName][$dateKey])) {
                        $leaveTypeName = $leave->leaveType ? $leave->leaveType->name : 'Cuti';
                        $userAttendance[$userName][$dateKey] = [
                            'clock_in' => $leaveTypeName,
                            'clock_out' => '',
                            'type' => 'leave'
                        ];
                    }
                }
            }
        }
        
        // Calculate total working days for each user
        $userTotalDays = [];
        foreach ($userAttendance as $userName => $dates) {
            $totalDays = 0;
            foreach ($dates as $dateKey => $attendance) {
                // Only count if:
                // 1. It's an attendance (not leave)
                // 2. Clock in time is not '-' and is <= 08:15:00
                if ($attendance['type'] === 'attendance' && $attendance['clock_in'] !== '-') {
                    // Parse time and check if <= 08:15:00
                    try {
                        $clockInTime = Carbon::parse($attendance['clock_in']);
                        $cutoffTime = Carbon::parse('08:15:00');
                        
                        if ($clockInTime->format('H:i:s') <= $cutoffTime->format('H:i:s')) {
                            $totalDays++;
                        }
                    } catch (\Exception $e) {
                        // Skip if time parsing fails
                    }
                }
            }
            $userTotalDays[$userName] = $totalDays;
        }
    @endphp
    
    <table>
        <thead>
            <tr>
                <th colspan="{{ 2 + (count($allDates) * 2) }}" style="font-weight: bold; text-align: center; font-size: 14pt;">LAPORAN ABSENSI - FORMAT HO</th>
            </tr>
            <tr>
                <td colspan="{{ 2 + (count($allDates) * 2) }}" style="text-align: center;">Periode: {{ $filters['from']->format('d M Y') }} - {{ $filters['to']->format('d M Y') }}</td>
            </tr>
            <tr>
                <td colspan="{{ 2 + (count($allDates) * 2) }}" style="text-align: center;">Project: {{ $projectName }}</td>
            </tr>
            @if($userName)
            <tr>
                <td colspan="{{ 2 + (count($allDates) * 2) }}" style="text-align: center; font-weight: bold;">Karyawan: {{ $userName }}</td>
            </tr>
            @endif
            <tr>
                <th style="font-weight: bold; vertical-align: middle;" rowspan="2">Nama</th>
                @foreach($allDates as $date)
                    <th colspan="2" style="font-weight: bold; text-align: center;">{{ Carbon::parse($date)->format('d/m/Y') }}</th>
                @endforeach
                <th style="font-weight: bold; vertical-align: middle; background-color: #ffc107;" rowspan="2">Total Hari Masuk</th>
            </tr>
            <tr>
                @foreach($allDates as $date)
                    <th style="font-weight: bold; text-align: center; background-color: #d4edda;">in</th>
                    <th style="font-weight: bold; text-align: center;">out</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($userAttendance as $name => $dates)
            <tr>
                <td style="font-weight: bold;">{{ $name }}</td>
                @foreach($allDates as $date)
                    @php
                        $attendance = $dates[$date] ?? null;
                        $isLeave = $attendance && ($attendance['type'] ?? '') === 'leave';
                        $bgColorIn = $isLeave ? '#fff3cd' : '#d4edda'; // Yellow for leave, green for attendance
                    @endphp
                    <td style="text-align: center; background-color: {{ $bgColorIn }};">
                        {{ $attendance ? $attendance['clock_in'] : '-' }}
                    </td>
                    <td style="text-align: center;">
                        {{ $attendance ? $attendance['clock_out'] : '-' }}
                    </td>
                @endforeach
                <td style="text-align: center; font-weight: bold; background-color: #fff9c4;">
                    {{ $userTotalDays[$name] ?? 0 }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
