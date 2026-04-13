@extends('pwa.layout')

@section('title', 'Form Patroli - JSMU Guard')

@section('content')
<div class="page-header">
    <button class="back-btn" onclick="goBack()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
    </button>
    <h1 class="page-title" id="page-title">Laporan Patroli</h1>
</div>

<div id="alert-box" class="alert" style="display:none;"></div>

<div style="padding:16px;">
    <!-- Checkpoint Info -->
    <div id="checkpoint-info" class="card" style="display:none;">
        <h4 style="font-size:14px;color:var(--text-light);margin-bottom:4px;">Checkpoint</h4>
        <p style="font-size:18px;font-weight:600;" id="checkpoint-name">-</p>
        <p style="font-size:13px;color:var(--text-light);" id="checkpoint-post">-</p>
    </div>
    
    <!-- Title -->
    <div class="form-group">
        <label class="form-label">Judul Laporan</label>
        <input type="text" id="title" class="form-input" placeholder="Contoh: Patroli Malam Rutin">
    </div>
    
    <!-- Photo -->
    <div class="form-group">
        <label class="form-label">Foto Lokasi (Opsional)</label>
        <div id="photo-preview" style="display:none;margin-bottom:12px;">
            <img id="preview-img" style="width:100%;max-width:300px;border-radius:12px;">
            <button type="button" class="btn btn-outline" onclick="removePhoto()" style="margin-top:8px;width:auto;padding:8px 16px;">
                Hapus Foto
            </button>
        </div>
        <input type="file" id="photo-input" accept="image/*" capture="environment" style="display:none;" onchange="handlePhoto(this)">
        <button type="button" class="btn btn-outline" id="photo-btn" onclick="document.getElementById('photo-input').click()">
            📷 Ambil Foto
        </button>
    </div>
    
    <!-- Notes -->
    <div class="form-group">
        <label class="form-label">Catatan</label>
        <textarea id="notes" class="form-textarea" placeholder="Tulis catatan patroli..."></textarea>
    </div>
    
    <!-- Submit -->
    <button class="btn btn-primary" id="submit-btn" onclick="submitPatrol()">
        KIRIM LAPORAN
    </button>
</div>
@endsection

@push('scripts')
<script>
let patrolMode = 'normal';
let patrolType = 'patrol';
let checkpointCode = null;
let checkpointData = null;
let photoFile = null;
let currentLocation = null;

function getToken() { return localStorage.getItem('jsmu_token'); }
function getUser() { 
    const u = localStorage.getItem('jsmu_user'); 
    return u ? JSON.parse(u) : null; 
}
function goBack() { window.location.href = '/app/patrol'; }

document.addEventListener('DOMContentLoaded', async () => {
    if (!getToken()) {
        window.location.href = '/app';
        return;
    }
    
    const params = new URLSearchParams(window.location.search);
    patrolMode = params.get('mode') || 'normal';
    checkpointCode = params.get('checkpoint');
    
    // Update title and type based on mode
    const titles = {
        'normal': 'Laporan Patroli',
        'sos': '🚨 Laporan Darurat (SOS)',
        'incident': '⚠️ Laporan Insiden'
    };
    document.getElementById('page-title').textContent = titles[patrolMode] || titles.normal;
    
    if (patrolMode === 'sos') patrolType = 'sos';
    else if (patrolMode === 'incident') patrolType = 'incident';
    
    // Load checkpoint data if available
    if (checkpointCode) {
        await loadCheckpoint(checkpointCode);
    }
    
    // Get location
    try {
        const pos = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 15000
            });
        });
        currentLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude };
    } catch (e) {
        console.log('Location error:', e);
    }
});

async function loadCheckpoint(code) {
    try {
        const token = getToken();
        const res = await fetch(`/api/patrol/checkpoint?code=${encodeURIComponent(code)}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (res.ok) {
            checkpointData = await res.json();
            if (checkpointData && checkpointData.name) {
                document.getElementById('checkpoint-info').style.display = 'block';
                document.getElementById('checkpoint-name').textContent = checkpointData.name;
                document.getElementById('checkpoint-post').textContent = checkpointData.post_name || '';
            }
        }
    } catch (e) {
        console.log('Checkpoint load error:', e);
    }
}

function handlePhoto(input) {
    if (input.files && input.files[0]) {
        photoFile = input.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('photo-preview').style.display = 'block';
            document.getElementById('photo-btn').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removePhoto() {
    photoFile = null;
    document.getElementById('photo-input').value = '';
    document.getElementById('photo-preview').style.display = 'none';
    document.getElementById('photo-btn').style.display = 'block';
}

async function submitPatrol() {
    const title = document.getElementById('title').value || 'Laporan Patroli';
    const notes = document.getElementById('notes').value;
    const btn = document.getElementById('submit-btn');
    
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
    
    try {
        const token = getToken();
        const user = getUser();
        
        const formData = new FormData();
        formData.append('project_id', user?.active_project_id || '');
        formData.append('title', title);
        formData.append('post_name', checkpointData?.post_name || 'Unknown');
        formData.append('description', notes);
        formData.append('type', patrolType);
        
        if (checkpointCode) {
            formData.append('checkpoint_code', checkpointCode);
        }
        
        if (currentLocation) {
            formData.append('latitude', currentLocation.lat);
            formData.append('longitude', currentLocation.lng);
        }
        
        if (photoFile) {
            formData.append('photo', photoFile);
        }
        
        // Correct endpoint: /patrol/logs
        const res = await fetch('/api/patrol/logs', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await res.json();
        
        if (res.ok) {
            showAlert(data.message || 'Laporan berhasil dikirim', 'success');
            setTimeout(() => {
                window.location.href = '/app/home';
            }, 1500);
        } else {
            showAlert(data.message || data.error || 'Gagal mengirim laporan', 'error');
            btn.disabled = false;
            btn.textContent = 'KIRIM LAPORAN';
        }
    } catch (err) {
        console.error('Patrol error:', err);
        showAlert('Terjadi kesalahan', 'error');
        btn.disabled = false;
        btn.textContent = 'KIRIM LAPORAN';
    }
}

function showAlert(msg, type) {
    const el = document.getElementById('alert-box');
    el.textContent = msg;
    el.className = `alert alert-${type}`;
    el.style.display = 'block';
}
</script>
@endpush
