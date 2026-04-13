@extends('pwa.layout')

@section('title', 'Riwayat Absensi - JSMU Guard')

@section('content')
<div class="page-header">
    <button class="back-btn" onclick="goBack()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
    </button>
    <h1 class="page-title">Riwayat Absensi</h1>
</div>

<!-- Date Filter -->
<div style="padding:16px;display:flex;gap:12px;">
    <div style="flex:1;">
        <label class="form-label">Dari</label>
        <input type="date" id="date-from" class="form-input" onchange="loadHistory()">
    </div>
    <div style="flex:1;">
        <label class="form-label">Sampai</label>
        <input type="date" id="date-to" class="form-input" onchange="loadHistory()">
    </div>
</div>

<div id="loading" class="loading">
    <div class="spinner"></div>
</div>

<div id="history-list"></div>

<div id="empty-state" class="empty-state" style="display:none;">
    <div class="empty-state-icon">📊</div>
    <p>Belum ada riwayat absensi</p>
</div>
@endsection

@push('scripts')
<script>
function getToken() { return localStorage.getItem('jsmu_token'); }

// Parse date from API format: dd-MM-yyyy HH:mm
function parseApiDate(dateStr) {
    if (!dateStr) return null;
    // Handle ISO format
    if (dateStr.includes('T')) {
        return new Date(dateStr);
    }
    // Parse dd-MM-yyyy HH:mm
    const parts = dateStr.match(/(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2})/);
    if (parts) {
        return new Date(parts[3], parts[2] - 1, parts[1], parts[4], parts[5]);
    }
    return new Date(dateStr);
}

function formatDate(date) {
    if (!date || isNaN(date.getTime())) return 'Tanggal tidak valid';
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]}`;
}

function formatTime(date) {
    if (!date || isNaN(date.getTime())) return '--:--';
    return date.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
}

function goBack() {
    window.location.href = '/app/home';
}

document.addEventListener('DOMContentLoaded', () => {
    if (!getToken()) {
        window.location.href = '/app';
        return;
    }
    
    // Set default dates (last 30 days)
    const today = new Date();
    const from = new Date();
    from.setDate(from.getDate() - 30);
    
    document.getElementById('date-from').value = from.toISOString().split('T')[0];
    document.getElementById('date-to').value = today.toISOString().split('T')[0];
    
    loadHistory();
});

async function loadHistory() {
    const loading = document.getElementById('loading');
    const listEl = document.getElementById('history-list');
    const emptyEl = document.getElementById('empty-state');
    
    const from = document.getElementById('date-from').value;
    const to = document.getElementById('date-to').value;
    
    loading.style.display = 'flex';
    listEl.innerHTML = '';
    emptyEl.style.display = 'none';
    
    try {
        const token = getToken();
        const res = await fetch(`/api/attendance/history?from=${from}&to=${to}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        loading.style.display = 'none';
        
        if (!res.ok) {
            listEl.innerHTML = '<div class="alert alert-error">Gagal memuat data</div>';
            return;
        }
        
        const data = await res.json();
        const records = data.data || data;
        
        if (!records || records.length === 0) {
            emptyEl.style.display = 'block';
            return;
        }
        
        // Group by date
        const byDate = {};
        records.forEach(record => {
            const date = parseApiDate(record.occurred_at);
            const dateKey = date.toISOString().split('T')[0]; // YYYY-MM-DD
            
            if (!byDate[dateKey]) {
                byDate[dateKey] = { date: date, clockIn: null, clockOut: null };
            }
            
            if (record.type === 'clock_in') {
                byDate[dateKey].clockIn = record;
            } else {
                byDate[dateKey].clockOut = record;
            }
        });
        
        // Convert to array and sort by date desc
        const grouped = Object.values(byDate).sort((a, b) => b.date - a.date);
        
        let html = '';
        grouped.forEach(group => {
            const dateStr = formatDate(group.date);
            
            // Get times
            const clockInTime = group.clockIn ? formatTime(parseApiDate(group.clockIn.occurred_at)) : '--:--';
            const clockOutTime = group.clockOut ? formatTime(parseApiDate(group.clockOut.occurred_at)) : '--:--';
            
            // Get status from clock-in (or clock-out if no clock-in)
            const record = group.clockIn || group.clockOut;
            let status = record.mode === 'dinas' ? 'Dinas' : 'Normal';
            if (record.mode === 'dinas') {
                if (record.status_dinas === 'approved') status = 'Dinas ✓';
                else if (record.status_dinas === 'rejected') status = 'Dinas ✗';
                else status = 'Dinas ⏳';
            }
            
            html += `
                <div style="background:var(--card-bg);border-radius:16px;padding:16px;margin-bottom:12px;">
                    <div style="font-weight:600;font-size:15px;color:var(--text);margin-bottom:12px;">${dateStr}</div>
                    <div style="display:flex;gap:16px;margin-bottom:8px;">
                        <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                            <span style="font-size:11px;color:var(--text-light);text-transform:uppercase;">Masuk</span>
                            <span style="font-size:18px;font-weight:600;color:var(--text);">${clockInTime}</span>
                        </div>
                        <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                            <span style="font-size:11px;color:var(--text-light);text-transform:uppercase;">Keluar</span>
                            <span style="font-size:18px;font-weight:600;color:var(--text);">${clockOutTime}</span>
                        </div>
                    </div>
                    <div style="font-size:12px;color:var(--text-light);">${status}</div>
                </div>
            `;
        });
        
        listEl.innerHTML = html;
    } catch (err) {
        console.error('History error:', err);
        loading.style.display = 'none';
        listEl.innerHTML = '<div class="alert alert-error">Gagal memuat data</div>';
    }
}
</script>
@endpush
