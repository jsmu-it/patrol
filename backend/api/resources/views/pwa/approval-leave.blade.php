@extends('pwa.layout')

@section('title', 'Persetujuan Cuti - JSMU Guard')

@section('content')
<div class="page-header">
    <button class="back-btn" onclick="goBack()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
    </button>
    <h1 class="page-title">Persetujuan Cuti</h1>
</div>

<div id="loading" class="loading">
    <div class="spinner"></div>
</div>

<div id="approval-list"></div>

<div id="empty-state" class="empty-state" style="display:none;">
    <div class="empty-state-icon">✅</div>
    <p>Tidak ada pengajuan cuti yang menunggu persetujuan</p>
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

document.addEventListener('DOMContentLoaded', loadApprovals);

async function loadApprovals() {
    if (!getToken()) {
        window.location.href = '/app';
        return;
    }
    
    const loading = document.getElementById('loading');
    const listEl = document.getElementById('approval-list');
    const emptyEl = document.getElementById('empty-state');
    
    try {
        const token = getToken();
        const res = await fetch('/api/admin/leave-requests', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        loading.style.display = 'none';
        
        if (!res.ok) {
            if (res.status === 403) {
                listEl.innerHTML = '<div class="alert alert-error">Anda tidak memiliki akses ke halaman ini</div>';
            } else {
                listEl.innerHTML = '<div class="alert alert-error">Gagal memuat data</div>';
            }
            return;
        }
        
        const data = await res.json();
        const requests = (data.data || data).filter(r => r.status === 'pending');
        
        if (!requests || requests.length === 0) {
            emptyEl.style.display = 'block';
            return;
        }
        
        let html = '';
        requests.forEach(request => {
            html += `
                <div class="approval-card" id="request-${request.id}">
                    <div class="approval-header">
                        <div>
                            <div class="approval-user">${request.user ? request.user.name : 'User'}</div>
                            <div class="approval-type">${request.type}${request.leave_type ? ' - ' + request.leave_type.name : ''}</div>
                        </div>
                        <span class="badge badge-pending">Menunggu</span>
                    </div>
                    <div class="approval-details">
                        <div class="approval-row">
                            <span>📅</span>
                            <span>${formatDate(request.date_from)} — ${formatDate(request.date_to)}</span>
                        </div>
                        ${request.time_from && request.time_to ? `
                        <div class="approval-row">
                            <span>🕐</span>
                            <span>${request.time_from} — ${request.time_to}</span>
                        </div>
                        ` : ''}
                        <div class="approval-row">
                            <span>📝</span>
                            <span>${request.reason}</span>
                        </div>
                        ${request.doctor_note ? `
                        <div class="approval-row">
                            <span>🩺</span>
                            <span>${request.doctor_note}</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="approval-actions">
                        <button class="btn btn-outline" onclick="rejectLeave(${request.id})" id="reject-btn-${request.id}">
                            Tolak
                        </button>
                        <button class="btn btn-primary" onclick="approveLeave(${request.id})" id="approve-btn-${request.id}">
                            Setujui
                        </button>
                    </div>
                </div>
            `;
        });
        
        listEl.innerHTML = html;
    } catch (err) {
        console.error('Approval error:', err);
        loading.style.display = 'none';
        listEl.innerHTML = '<div class="alert alert-error">Gagal memuat data</div>';
    }
}

async function approveLeave(id) {
    if (!confirm('Setujui pengajuan cuti ini?')) return;
    
    const approveBtn = document.getElementById(`approve-btn-${id}`);
    const rejectBtn = document.getElementById(`reject-btn-${id}`);
    
    approveBtn.disabled = true;
    rejectBtn.disabled = true;
    approveBtn.textContent = 'Memproses...';
    
    try {
        const token = getToken();
        const res = await fetch(`/api/admin/leave-requests/${id}/approve`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (res.ok) {
            document.getElementById(`request-${id}`).remove();
            
            // Check if any requests left
            if (document.querySelectorAll('.approval-card').length === 0) {
                document.getElementById('empty-state').style.display = 'block';
            }
        } else {
            const data = await res.json();
            alert(data.message || 'Gagal menyetujui');
            approveBtn.disabled = false;
            rejectBtn.disabled = false;
            approveBtn.textContent = 'Setujui';
        }
    } catch (err) {
        console.error('Approve error:', err);
        alert('Terjadi kesalahan');
        approveBtn.disabled = false;
        rejectBtn.disabled = false;
        approveBtn.textContent = 'Setujui';
    }
}

async function rejectLeave(id) {
    if (!confirm('Tolak pengajuan cuti ini?')) return;
    
    const approveBtn = document.getElementById(`approve-btn-${id}`);
    const rejectBtn = document.getElementById(`reject-btn-${id}`);
    
    approveBtn.disabled = true;
    rejectBtn.disabled = true;
    rejectBtn.textContent = 'Memproses...';
    
    try {
        const token = getToken();
        const res = await fetch(`/api/admin/leave-requests/${id}/reject`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (res.ok) {
            document.getElementById(`request-${id}`).remove();
            
            // Check if any requests left
            if (document.querySelectorAll('.approval-card').length === 0) {
                document.getElementById('empty-state').style.display = 'block';
            }
        } else {
            const data = await res.json();
            alert(data.message || 'Gagal menolak');
            approveBtn.disabled = false;
            rejectBtn.disabled = false;
            rejectBtn.textContent = 'Tolak';
        }
    } catch (err) {
        console.error('Reject error:', err);
        alert('Terjadi kesalahan');
        approveBtn.disabled = false;
        rejectBtn.disabled = false;
        rejectBtn.textContent = 'Tolak';
    }
}
</script>
@endpush
