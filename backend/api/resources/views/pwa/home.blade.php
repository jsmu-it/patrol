@extends('pwa.layout')

@section('title', 'Home - JSMU Guard')

@section('content')
<!-- Header -->
<div class="header">
    <div class="header-top">
        <span class="header-date" id="current-date"></span>
        <a href="/app/profile" style="color:white">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </a>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <p class="greeting">Halo, Petugas</p>
            <h2 class="user-name" id="user-name">Memuat...</h2>
            <span class="user-project" id="user-project">-</span>
        </div>
        <img id="user-avatar" class="user-avatar" src="/images/default-avatar.png" alt="Avatar">
    </div>
</div>

<!-- Status Card -->
<div class="status-card" id="status-card">
    <div class="status-header">
        <div class="status-icon ready" id="status-icon">🔒</div>
        <div>
            <h3 class="status-title ready" id="status-title">SIAP BERTUGAS</h3>
            <p class="status-subtitle" id="status-subtitle">Silakan absen masuk untuk memulai shift.</p>
        </div>
    </div>
    <div class="clock-time" id="clock-time" style="display:none;">
        Masuk pukul <span id="clock-in-time">00:00</span>
    </div>
    <button class="btn btn-primary" id="attendance-btn" onclick="goToAttendance()">
        ABSEN MASUK
    </button>
</div>

<!-- Admin Approval Section (for admins only) -->
<div id="admin-approvals" style="display:none;">
    <h3 class="section-title">Persetujuan</h3>
    <div class="menu-grid">
        <a href="/app/approvals/leave" class="menu-item">
            <div class="menu-icon red">📋</div>
            <span class="menu-label">Persetujuan Cuti</span>
            <span id="leave-approval-badge" class="badge badge-pending" style="display:none;margin-top:4px;"></span>
        </a>
    </div>
</div>

<!-- Menu Grid -->
<h3 class="section-title">Menu Utama</h3>
<div class="menu-grid">
    <a href="/app/patrol" class="menu-item">
        <div class="menu-icon blue">🚶</div>
        <span class="menu-label">Patroli</span>
    </a>
    <a href="/app/leave" class="menu-item">
        <div class="menu-icon orange">📅</div>
        <span class="menu-label">Izin / Cuti</span>
    </a>
    <a href="/app/history" class="menu-item">
        <div class="menu-icon purple">📊</div>
        <span class="menu-label">Riwayat</span>
    </a>
    <a href="/app/profile" class="menu-item">
        <div class="menu-icon teal">👤</div>
        <span class="menu-label">Profil</span>
    </a>
</div>
@endsection

@push('scripts')
<script>
let isClockedIn = false;
let clockInTime = null;

// Helper functions
function getToken() { return localStorage.getItem('jsmu_token'); }
function getUser() { 
    try {
        const u = localStorage.getItem('jsmu_user'); 
        return u ? JSON.parse(u) : null;
    } catch (e) {
        console.error('Error parsing user:', e);
        return null;
    }
}
function setUser(user) {
    localStorage.setItem('jsmu_user', JSON.stringify(user));
}

function formatDate(date) {
    const d = new Date(date);
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

// Parse date from API format: dd-MM-yyyy HH:mm (e.g., "06-01-2026 08:30")
function parseApiDate(dateStr) {
    if (!dateStr) return null;
    // Handle ISO format
    if (dateStr.includes('T')) {
        return new Date(dateStr);
    }
    // Parse dd-MM-yyyy HH:mm
    const parts = dateStr.match(/(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2})/);
    if (parts) {
        // parts[1]=day, parts[2]=month, parts[3]=year, parts[4]=hour, parts[5]=min
        return new Date(parseInt(parts[3]), parseInt(parts[2]) - 1, parseInt(parts[1]), parseInt(parts[4]), parseInt(parts[5]));
    }
    // Try direct parse
    return new Date(dateStr);
}

function updateUserUI(user) {
    console.log('Updating UI with user:', user);
    document.getElementById('user-name').textContent = user.name || 'User';
    document.getElementById('user-project').textContent = user.active_project_name || 'Lokasi Belum Diset';
    if (user.profile_photo_url) {
        document.getElementById('user-avatar').src = user.profile_photo_url;
    }
    
    // Show admin approvals section if user is admin
    if (user.role && ['ADMIN', 'SUPERADMIN', 'PROJECT_ADMIN'].includes(user.role)) {
        document.getElementById('admin-approvals').style.display = 'block';
        loadPendingApprovalsCount();
    }
}

async function loadPendingApprovalsCount() {
    try {
        const token = getToken();
        const res = await fetch('/api/admin/leave-requests', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (res.ok) {
            const data = await res.json();
            const pending = (data.data || data).filter(r => r.status === 'pending');
            if (pending.length > 0) {
                const badge = document.getElementById('leave-approval-badge');
                badge.textContent = pending.length;
                badge.style.display = 'inline-block';
            }
        }
    } catch (err) {
        console.error('Error loading approvals:', err);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    console.log('Home page loaded');
    
    // Check auth
    const token = getToken();
    console.log('Token:', token ? 'present' : 'missing');
    if (!token) {
        window.location.href = '/app';
        return;
    }
    
    // Set current date
    document.getElementById('current-date').textContent = formatDate(new Date());
    
    // Load user data from localStorage first
    const cachedUser = getUser();
    console.log('Cached user:', cachedUser);
    if (cachedUser && cachedUser.name) {
        updateUserUI(cachedUser);
    }
    
    // Always refresh user from API
    await refreshUser();
    
    // Load attendance status
    await loadAttendanceStatus();
});

async function refreshUser() {
    try {
        const token = getToken();
        console.log('Fetching /api/me...');
        const res = await fetch('/api/me', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        console.log('/api/me status:', res.status);
        
        if (res.ok) {
            const data = await res.json();
            console.log('/api/me response:', data);
            // Handle both wrapped {data: {...}} and unwrapped {...} response
            const user = data.data || data;
            setUser(user);
            updateUserUI(user);
        } else if (res.status === 401) {
            console.log('Token expired, redirecting to login');
            localStorage.removeItem('jsmu_token');
            localStorage.removeItem('jsmu_user');
            window.location.href = '/app';
        }
    } catch (e) {
        console.error('Error refreshing user:', e);
    }
}

/**
 * Check if a shift is an overnight shift (crosses midnight).
 * Overnight: end time (in minutes) <= start time (in minutes).
 * e.g., 22:00 - 06:00 = overnight (360 <= 1320)
 * e.g., 08:00 - 17:00 = NOT overnight (1020 > 480)
 */
function isOvernightShift(shift) {
    if (!shift || !shift.start_time || !shift.end_time) return false;
    
    const startParts = shift.start_time.split(':');
    const endParts = shift.end_time.split(':');
    
    if (startParts.length < 2 || endParts.length < 2) return false;
    
    const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
    const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
    
    return endMinutes <= startMinutes;
}

async function loadAttendanceStatus() {
    try {
        const token = getToken();
        console.log('Fetching attendance history and shifts...');
        
        // Fetch attendance history and available shifts in parallel
        const today = new Date();
        const from = new Date();
        from.setDate(from.getDate() - 2);
        
        const fromStr = from.toISOString().split('T')[0];
        const toStr = today.toISOString().split('T')[0];
        
        const [historyRes, shiftsRes] = await Promise.all([
            fetch(`/api/attendance/history?from=${fromStr}&to=${toStr}`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            }),
            fetch('/api/me/available-shifts', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            })
        ]);
        
        if (!historyRes.ok) {
            console.log('Attendance history failed');
            return;
        }
        
        // Parse shifts data for overnight detection
        let shifts = [];
        if (shiftsRes.ok) {
            const shiftsData = await shiftsRes.json();
            shifts = shiftsData.data || shiftsData || [];
        }
        
        const responseData = await historyRes.json();
        const records = responseData.data || responseData;
        
        if (records && records.length > 0) {
            console.log('Found', records.length, 'attendance records');
            // Sort by occurred_at desc, then by id desc
            const sorted = records.sort((a, b) => {
                const dateA = parseApiDate(a.occurred_at);
                const dateB = parseApiDate(b.occurred_at);
                const timeDiff = dateB - dateA;
                if (timeDiff !== 0) return timeDiff;
                return (b.id || 0) - (a.id || 0);
            });
            
            const last = sorted[0];
            console.log('Last record:', last);
            
            if (last.type === 'clock_in') {
                const clockInDate = parseApiDate(last.occurred_at);
                const now = new Date();
                
                console.log('Clock in date:', clockInDate?.toDateString());
                console.log('Today:', now.toDateString());
                
                if (clockInDate) {
                    // Find the shift for this attendance record
                    const recordShift = shifts.find(s => s.id === last.shift_id);
                    const overnight = recordShift ? isOvernightShift(recordShift) : false;
                    
                    console.log('Shift:', recordShift?.name, 'Overnight:', overnight);
                    
                    if (overnight) {
                        // For overnight shifts, keep clocked-in within 24-hour window
                        const hoursSinceClockIn = (now - clockInDate) / (1000 * 60 * 60);
                        console.log('Hours since clock-in:', hoursSinceClockIn.toFixed(1));
                        
                        if (hoursSinceClockIn < 24) {
                            console.log('User is in overnight shift (within 24h window)');
                            isClockedIn = true;
                            clockInTime = last.occurred_at;
                            updateStatusUI(true, clockInTime);
                        } else {
                            console.log('Overnight shift expired (>24h)');
                        }
                    } else {
                        // For regular shifts, only keep clocked in if same calendar day
                        if (clockInDate.toDateString() === now.toDateString()) {
                            console.log('User is clocked in today!');
                            isClockedIn = true;
                            clockInTime = last.occurred_at;
                            updateStatusUI(true, clockInTime);
                        } else {
                            console.log('Clock in was on different day (regular shift)');
                        }
                    }
                }
            } else {
                console.log('Last record was clock_out');
            }
        } else {
            console.log('No attendance records found');
        }
    } catch (err) {
        console.error('Error loading attendance status:', err);
    }
}

function updateStatusUI(clockedIn, time) {
    console.log('updateStatusUI:', clockedIn, time);
    const icon = document.getElementById('status-icon');
    const title = document.getElementById('status-title');
    const subtitle = document.getElementById('status-subtitle');
    const btn = document.getElementById('attendance-btn');
    const clockTimeDiv = document.getElementById('clock-time');
    
    if (clockedIn) {
        icon.textContent = '🛡️';
        icon.className = 'status-icon active';
        title.textContent = 'SEDANG BERTUGAS';
        title.className = 'status-title active';
        subtitle.textContent = 'Anda sudah melakukan absen masuk.';
        btn.textContent = 'ABSEN KELUAR';
        btn.className = 'btn btn-danger';
        
        if (time) {
            clockTimeDiv.style.display = 'block';
            const t = parseApiDate(time);
            if (t) {
                document.getElementById('clock-in-time').textContent = t.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
            }
        }
    } else {
        icon.textContent = '🔒';
        icon.className = 'status-icon ready';
        title.textContent = 'SIAP BERTUGAS';
        title.className = 'status-title ready';
        subtitle.textContent = 'Silakan absen masuk untuk memulai shift.';
        btn.textContent = 'ABSEN MASUK';
        btn.className = 'btn btn-primary';
        clockTimeDiv.style.display = 'none';
    }
    
    isClockedIn = clockedIn;
}

function goToAttendance() {
    const type = isClockedIn ? 'out' : 'in';
    window.location.href = `/app/attendance?type=${type}`;
}
</script>
@endpush
