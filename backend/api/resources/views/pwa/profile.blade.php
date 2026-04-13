@extends('pwa.layout')

@section('title', 'Profil - JSMU Guard')

@section('content')
<div class="header" style="padding-top:40px;padding-bottom:80px;text-align:center;">
    <img id="user-avatar" class="user-avatar" src="/images/default-avatar.png" alt="Avatar" style="width:80px;height:80px;margin:0 auto 12px;display:block;">
    <h2 id="user-name" style="font-size:20px;color:white;font-weight:700;">Memuat...</h2>
    <p id="user-email" style="font-size:14px;color:rgba(255,255,255,0.7);">-</p>
</div>

<div style="margin-top:-40px;padding:0 16px;">
    <div class="card" style="margin:0;">
        <div style="padding:8px 0;border-bottom:1px solid #e2e8f0;">
            <p style="font-size:12px;color:var(--text-light);">Lokasi Proyek</p>
            <p style="font-size:15px;font-weight:600;" id="user-project">-</p>
        </div>
        <div style="padding:8px 0;border-bottom:1px solid #e2e8f0;">
            <p style="font-size:12px;color:var(--text-light);">Posisi</p>
            <p style="font-size:15px;font-weight:600;" id="user-position">-</p>
        </div>
        <div style="padding:8px 0;">
            <p style="font-size:12px;color:var(--text-light);">Status</p>
            <p style="font-size:15px;font-weight:600;color:var(--success);" id="user-status">Aktif</p>
        </div>
    </div>
    
    <div style="margin-top:24px;">
        <a href="/app/history" class="btn btn-outline" style="margin-bottom:12px;">
            📊 Riwayat Absensi
        </a>
        <a href="/app/leave" class="btn btn-outline" style="margin-bottom:12px;">
            📅 Pengajuan Cuti
        </a>
        <button class="btn btn-danger" onclick="doLogout()">
            Keluar (Logout)
        </button>
    </div>
    
    <p style="text-align:center;font-size:12px;color:var(--text-light);margin-top:32px;">
        JSMU Guard PWA v1.0<br>
        © 2024 PT. Jaya Sakti Mandiri Unggul
    </p>
</div>
@endsection

@push('scripts')
<script>
function getToken() { return localStorage.getItem('jsmu_token'); }
function getUser() { 
    try {
        const u = localStorage.getItem('jsmu_user'); 
        return u ? JSON.parse(u) : null;
    } catch (e) {
        return null;
    }
}
function setUser(user) {
    localStorage.setItem('jsmu_user', JSON.stringify(user));
}
function doLogout() {
    localStorage.removeItem('jsmu_token');
    localStorage.removeItem('jsmu_user');
    window.location.href = '/app';
}

function updateUI(data) {
    console.log('Profile updateUI:', data);
    document.getElementById('user-name').textContent = data.name || 'User';
    document.getElementById('user-email').textContent = data.email || '-';
    document.getElementById('user-project').textContent = data.active_project_name || 'Belum Diset';
    document.getElementById('user-position').textContent = data.position || '-';
    
    if (data.profile_photo_url) {
        document.getElementById('user-avatar').src = data.profile_photo_url;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    console.log('Profile page loaded');
    const token = getToken();
    console.log('Token:', token ? 'present' : 'missing');
    
    if (!token) {
        window.location.href = '/app';
        return;
    }
    
    // Load from localStorage first
    const cachedUser = getUser();
    console.log('Cached user:', cachedUser);
    if (cachedUser && cachedUser.name) {
        updateUI(cachedUser);
    }
    
    // Always refresh from API to get latest data
    try {
        console.log('Fetching /api/me...');
        const res = await fetch('/api/me', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        console.log('/api/me status:', res.status);
        
        if (res.ok) {
            const responseData = await res.json();
            console.log('/api/me response:', responseData);
            // Handle both wrapped {data: {...}} and unwrapped {...} response
            const data = responseData.data || responseData;
            setUser(data);
            updateUI(data);
        } else if (res.status === 401) {
            console.log('Token expired');
            doLogout();
        }
    } catch (e) {
        console.error('Error refreshing user:', e);
    }
});
</script>
@endpush
