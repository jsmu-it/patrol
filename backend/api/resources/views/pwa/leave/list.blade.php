@extends('pwa.layout')

@section('title', 'Izin / Cuti - JSMU Guard')

@section('content')
<div class="page-header">
    <button class="back-btn" onclick="goBack()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
    </button>
    <h1 class="page-title">Izin / Cuti</h1>
</div>

<!-- Leave Balance Card -->
<div id="balance-card" style="display:none; margin:16px; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:16px;">
    <div style="display:flex; align-items:center; margin-bottom:12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="color:#4CAF50; margin-right:8px;">
            <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5z"/>
        </svg>
        <h3 style="margin:0; font-size:16px; font-weight:600; color:#333;">Saldo Cuti Anda</h3>
    </div>
    <hr style="border:none; border-top:1px solid #e0e0e0; margin:12px 0;">
    <div id="balance-list" style="display:flex; flex-wrap:wrap; gap:12px;">
        <!-- Balance items will be inserted here -->
    </div>
</div>

<div style="padding:16px;">
    <a href="/app/leave/create" class="btn btn-primary" style="margin-bottom:20px;">
        + Ajukan Cuti Baru
    </a>
</div>

<div id="loading" class="loading">
    <div class="spinner"></div>
</div>

<div id="leave-list"></div>

<div id="empty-state" class="empty-state" style="display:none;">
    <div class="empty-state-icon">📅</div>
    <p>Belum ada pengajuan cuti</p>
</div>
@endsection

@push('scripts')
<script>
function getToken() { return localStorage.getItem('jsmu_token'); }
function goBack() { window.location.href = '/app/home'; }

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadLeaveBalance();
    await loadLeaveList();
});

async function loadLeaveBalance() {
    if (!getToken()) {
        return;
    }
    
    try {
        const token = getToken();
        const res = await fetch('/api/me', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (!res.ok) return;
        
        const user = await res.json();
        const balances = user.leave_balances;
        
        if (!balances || balances.length === 0) {
            return; // Don't show card if no balances
        }
        
        // Show the balance card
        const balanceCard = document.getElementById('balance-card');
        const balanceList = document.getElementById('balance-list');
        
        let html = '';
        balances.forEach(balance => {
            const hasRemaining = balance.remaining > 0;
            const bgColor = hasRemaining ? '#E8F5E9' : '#F5F5F5';
            const textColor = hasRemaining ? '#2E7D32' : '#757575';
            
            html += `
                <div style="padding:8px 12px; background:${bgColor}; border-radius:8px; text-align:center; min-width:80px;">
                    <div style="font-size:12px; font-weight:500; color:#333; margin-bottom:2px;">
                        ${balance.leave_type_name}
                    </div>
                    <div style="font-size:16px; font-weight:700; color:${textColor};">
                        ${balance.remaining}/${balance.quota}
                    </div>
                </div>
            `;
        });
        
        balanceList.innerHTML = html;
        balanceCard.style.display = 'block';
    } catch (err) {
        console.error('Balance error:', err);
    }
}

async function loadLeaveList() {
    if (!getToken()) {
        window.location.href = '/app';
        return;
    }
    
    const loading = document.getElementById('loading');
    const listEl = document.getElementById('leave-list');
    const emptyEl = document.getElementById('empty-state');
    
    try {
        const token = getToken();
        const res = await fetch('/api/leave-requests', {
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
        const leaves = data.data || data;
        
        if (!leaves || leaves.length === 0) {
            emptyEl.style.display = 'block';
            return;
        }
        
        let html = '';
        leaves.forEach(leave => {
            const statusClass = {
                'pending': 'badge-pending',
                'approved': 'badge-approved',
                'rejected': 'badge-rejected'
            }[leave.status] || 'badge-pending';
            
            const statusText = {
                'pending': 'Menunggu',
                'approved': 'Disetujui',
                'rejected': 'Ditolak'
            }[leave.status] || leave.status;
            
            html += `
                <div class="list-item">
                    <div>
                        <div class="list-item-title">${leave.type}</div>
                        <div class="list-item-subtitle">
                            ${formatDate(leave.date_from)} - ${formatDate(leave.date_to)}
                        </div>
                    </div>
                    <span class="badge ${statusClass}">${statusText}</span>
                </div>
            `;
        });
        
        listEl.innerHTML = html;
    } catch (err) {
        console.error('Leave error:', err);
        loading.style.display = 'none';
        listEl.innerHTML = '<div class="alert alert-error">Gagal memuat data</div>';
    }
}
</script>
@endpush
