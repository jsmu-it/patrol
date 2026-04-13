@extends('pwa.layout')

@section('title', 'Absensi - JSMU Guard')

@section('content')
<div class="page-header">
    <button class="back-btn" onclick="goBack()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
    </button>
    <h1 class="page-title" id="page-title">Absen Masuk</h1>
</div>

<!-- Alert Messages -->
<div id="alert-box" class="alert" style="display:none;"></div>

<!-- Location Info -->
<div class="location-info">
    <p class="location-label">Lokasi Anda</p>
    <p class="location-value" id="location-text">Mencari lokasi...</p>
    <div class="location-status" id="location-status">
        <span class="spinner" style="width:16px;height:16px;border-width:2px;"></span>
        <span>Memvalidasi lokasi...</span>
    </div>
</div>

<!-- Shift Selection -->
<div class="location-info" style="margin-top:16px;">
    <p class="location-label">Pilih Shift</p>
    <select id="shift-select" class="shift-select" style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border);background:var(--card-bg);color:var(--text);font-size:14px;margin-top:8px;">
        <option value="">Memuat shift...</option>
    </select>
    <div class="location-status" id="shift-status" style="display:none;">
        <span style="color:var(--danger)">✗</span>
        <span>Pilih shift terlebih dahulu</span>
    </div>
</div>

<!-- Mode Selection -->
<div class="location-info" style="margin-top:16px;">
    <p class="location-label">Mode Kehadiran</p>
    <div style="display:flex;gap:12px;margin-top:8px;">
        <button id="mode-normal" class="mode-btn mode-btn-active" onclick="selectMode('normal')">
            WFO
        </button>
        <button id="mode-dinas" class="mode-btn" onclick="selectMode('dinas')">
            DINAS
        </button>
    </div>
    <div id="mode-warning" style="display:none;font-size:12px;color:#f59e0b;margin-top:8px;font-style:italic;">
        * WFO hanya tersedia saat berada di area kantor
    </div>
</div>

<!-- Note Field -->
<div class="location-info" style="margin-top:16px;">
    <p class="location-label" id="note-label">Keterangan (Opsional)</p>
    <textarea id="note-input" 
              style="width:100%;padding:12px;border-radius:12px;border:1px solid var(--border);background:var(--card-bg);color:var(--text);font-size:14px;font-family:inherit;min-height:80px;resize:vertical;margin-top:8px;"
              placeholder="Masukkan keterangan..."></textarea>
</div>

<!-- Camera View -->
<div style="padding:16px;">
    <div class="camera-container" id="camera-container">
        <video id="video" autoplay playsinline muted></video>
        <canvas id="canvas" style="display:none;"></canvas>
        <div class="camera-overlay"></div>
    </div>
    <p style="text-align:center;font-size:13px;color:var(--text-light);margin-top:12px;">
        Arahkan wajah ke dalam frame
    </p>
</div>

<!-- Captured Image Preview -->
<div id="preview-container" style="display:none;padding:16px;">
    <img id="preview-image" style="width:100%;max-width:300px;display:block;margin:0 auto;border-radius:20px;">
    <div style="text-align:center;margin-top:12px;">
        <button class="btn btn-outline" onclick="retakePhoto()" style="width:auto;padding:10px 24px;display:inline-block;">
            Foto Ulang
        </button>
    </div>
</div>

<!-- Submit Button -->
<div style="padding:16px;">
    <button class="btn btn-primary" id="capture-btn" onclick="capturePhoto()">
        📸 AMBIL FOTO
    </button>
    <button class="btn btn-primary" id="submit-btn" onclick="submitAttendance()" style="display:none;">
        ✓ KIRIM ABSENSI
    </button>
</div>
@endsection

@push('scripts')
<script>
let currentLocation = null;
let isValidLocation = false;
let capturedPhoto = null;
let attendanceType = 'in';
let cameraStream = null;
let selectedShiftId = null;
let availableShifts = [];
let selectedMode = 'normal';
let isWithinGeofence = false;
let currentDistance = 0;

function getToken() { return localStorage.getItem('jsmu_token'); }
function getUser() { 
    const u = localStorage.getItem('jsmu_user'); 
    return u ? JSON.parse(u) : null; 
}
function goBack() { window.location.href = '/app/home'; }

function selectMode(mode) {
    // If outside geofence, force dinas
    if (!isWithinGeofence && mode === 'normal') {
        showAlert('WFO hanya tersedia di area kantor', 'error');
        return;
    }
    
    selectedMode = mode;
    
    // Update UI
    document.getElementById('mode-normal').classList.toggle('mode-btn-active', mode === 'normal');
    document.getElementById('mode-dinas').classList.toggle('mode-btn-active', mode === 'dinas');
    
    // Update note label
    const noteLabel = document.getElementById('note-label');
    noteLabel.textContent = mode === 'dinas' ? 'Keterangan (Wajib)' : 'Keterangan (Opsional)';
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!getToken()) {
        window.location.href = '/app';
        return;
    }
    
    // Get type from URL
    const params = new URLSearchParams(window.location.search);
    attendanceType = params.get('type') || 'in';
    
    document.getElementById('page-title').textContent = 
        attendanceType === 'out' ? 'Absen Keluar' : 'Absen Masuk';
    
    // Load shifts first
    await loadShifts();
    
    // Start location
    await getLocation();
    
    // Start camera
    await startCamera();
});

async function loadShifts() {
    const shiftSelect = document.getElementById('shift-select');
    try {
        // Get shifts for user's active project
        const res = await fetch('/api/me/available-shifts', {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        });
        
        if (!res.ok) {
            const errorData = await res.json();
            console.error('Failed to load project shifts:', errorData);
            
            // Show user-friendly error message
            if (res.status === 422) {
                shiftSelect.innerHTML = '<option value="">Tidak ada project aktif</option>';
                showAlert('Anda belum memiliki project aktif. Hubungi admin untuk assignment project.', 'error');
            } else {
                shiftSelect.innerHTML = '<option value="">Gagal memuat shift</option>';
                showAlert('Gagal memuat shift. Refresh halaman atau hubungi admin.', 'error');
            }
            return;
        }
        
        const data = await res.json();
        availableShifts = data.data || data;
        
        console.log('Loaded shifts for project:', availableShifts);
        
        if (!availableShifts || availableShifts.length === 0) {
            shiftSelect.innerHTML = '<option value="">Tidak ada shift untuk project ini</option>';
            showAlert('Project Anda belum memiliki shift. Hubungi admin untuk menambahkan shift ke project.', 'error');
            return;
        }
        
        shiftSelect.innerHTML = '<option value="">-- Pilih Shift --</option>';
        availableShifts.forEach(shift => {
            const option = document.createElement('option');
            option.value = shift.id;
            option.textContent = `${shift.name} (${shift.start_time} - ${shift.end_time})`;
            shiftSelect.appendChild(option);
        });
        
        shiftSelect.addEventListener('change', (e) => {
            selectedShiftId = e.target.value ? parseInt(e.target.value) : null;
            document.getElementById('shift-status').style.display = 'none';
        });
    } catch (err) {
        console.error('Failed to load shifts:', err);
        shiftSelect.innerHTML = '<option value="">Gagal memuat shift</option>';
        showAlert('Gagal memuat daftar shift. Refresh halaman.', 'error');
    }
}

async function getLocation() {
    const locText = document.getElementById('location-text');
    const locStatus = document.getElementById('location-status');
    
    // Check if geolocation is supported
    if (!navigator.geolocation) {
        locText.textContent = 'Browser tidak mendukung GPS';
        locStatus.innerHTML = '<span style="color:var(--danger)">✗</span> <span>GPS tidak tersedia</span>';
        showAlert('Browser Anda tidak mendukung fitur GPS', 'error');
        return;
    }
    
    try {
        const pos = await new Promise((resolve, reject) => {
            // Safari-specific: Show instruction first
            const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            if (isSafari) {
                locStatus.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;"></span> <span>Mengakses GPS... Izinkan akses lokasi jika diminta</span>';
            }
            
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            });
        });
        
        currentLocation = {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude
        };
        
        locText.textContent = `${currentLocation.lat.toFixed(6)}, ${currentLocation.lng.toFixed(6)}`;
        
        // Check geofence
        const user = getUser();
        if (user && user.project_lat && user.project_lng && user.project_radius) {
            const distance = calculateDistance(
                currentLocation.lat, currentLocation.lng,
                parseFloat(user.project_lat), parseFloat(user.project_lng)
            );
            
            currentDistance = distance;
            isWithinGeofence = distance <= user.project_radius;
            
            if (isWithinGeofence) {
                isValidLocation = true;
                locStatus.innerHTML = '<span style="color:var(--success)">✓</span> <span>Dalam area kerja</span>';
                document.getElementById('mode-normal').disabled = false;
                document.getElementById('mode-warning').style.display = 'none';
            } else {
                isValidLocation = true; // Allow attendance in dinas mode
                locStatus.innerHTML = '<span style="color:#f59e0b">⚠</span> <span>Di luar area kerja (' + Math.round(distance) + 'm)</span>';
                // Force dinas mode
                selectMode('dinas');
                document.getElementById('mode-normal').disabled = true;
                document.getElementById('mode-warning').style.display = 'block';
            }
        } else {
            isValidLocation = true;
            isWithinGeofence = true;
            locStatus.innerHTML = '<span style="color:var(--success)">✓</span> <span>Lokasi ditemukan</span>';
        }
    } catch (err) {
        console.error('Geolocation error:', err);
        locText.textContent = 'Gagal mendapatkan lokasi';
        
        // Better error messages for Safari and other browsers
        let errorMsg = 'Aktifkan GPS untuk melanjutkan';
        if (err.code === 1) { // PERMISSION_DENIED
            errorMsg = 'Izinkan akses lokasi di pengaturan browser Anda';
            locStatus.innerHTML = '<span style="color:var(--danger)">✗</span> <span>Akses lokasi ditolak</span>';
        } else if (err.code === 2) { // POSITION_UNAVAILABLE
            errorMsg = 'Lokasi tidak tersedia. Pastikan GPS aktif';
            locStatus.innerHTML = '<span style="color:var(--danger)">✗</span> <span>GPS tidak tersedia</span>';
        } else if (err.code === 3) { // TIMEOUT
            errorMsg = 'Waktu mencari lokasi habis. Coba lagi';
            locStatus.innerHTML = '<span style="color:var(--danger)">✗</span> <span>Timeout</span>';
        } else {
            locStatus.innerHTML = '<span style="color:var(--danger)">✗</span> <span>Aktifkan GPS</span>';
        }
        
        showAlert(errorMsg, 'error');
        
        // Add retry button for Safari users
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        if (isSafari && err.code === 1) {
            setTimeout(() => {
                const retry = confirm('Safari memerlukan izin akses lokasi. Klik OK untuk mencoba lagi dan izinkan akses lokasi.');
                if (retry) {
                    getLocation();
                }
            }, 1000);
        }
    }
}

function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

async function startCamera() {
    const video = document.getElementById('video');
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: 640, height: 480 },
            audio: false
        });
        video.srcObject = cameraStream;
        await video.play();
    } catch (err) {
        showAlert('Gagal mengakses kamera. Izinkan akses kamera.', 'error');
    }
}

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
}

function capturePhoto() {
    // Validate shift selection first
    if (!selectedShiftId) {
        document.getElementById('shift-status').style.display = 'flex';
        showAlert('Pilih shift terlebih dahulu', 'error');
        const shiftSelect = document.getElementById('shift-select');
        shiftSelect.focus();
        shiftSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);
    capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
    
    document.getElementById('camera-container').style.display = 'none';
    document.getElementById('preview-container').style.display = 'block';
    document.getElementById('preview-image').src = capturedPhoto;
    document.getElementById('capture-btn').style.display = 'none';
    document.getElementById('submit-btn').style.display = 'block';
    
    stopCamera();
}

function retakePhoto() {
    capturedPhoto = null;
    document.getElementById('camera-container').style.display = 'block';
    document.getElementById('preview-container').style.display = 'none';
    document.getElementById('capture-btn').style.display = 'block';
    document.getElementById('submit-btn').style.display = 'none';
    startCamera();
}

function dataURLtoBlob(dataURL) {
    const parts = dataURL.split(',');
    const mime = parts[0].match(/:(.*?);/)[1];
    const raw = atob(parts[1]);
    const arr = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; i++) {
        arr[i] = raw.charCodeAt(i);
    }
    return new Blob([arr], { type: mime });
}

async function submitAttendance() {
    if (!selectedShiftId) {
        showAlert('Pilih shift terlebih dahulu.', 'error');
        document.getElementById('shift-select').focus();
        return;
    }
    
    if (!currentLocation) {
        showAlert('Lokasi belum ditemukan. Tunggu sebentar.', 'error');
        return;
    }
    
    if (!capturedPhoto) {
        showAlert('Ambil foto selfie terlebih dahulu.', 'error');
        return;
    }
    
    // Validate note for dinas mode
    const noteInput = document.getElementById('note-input');
    const note = noteInput.value.trim();
    
    if (selectedMode === 'dinas' && !note) {
        showAlert('Harap isi keterangan untuk mode DINAS', 'error');
        noteInput.focus();
        return;
    }
    
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
    
    try {
        const token = getToken();
        const user = getUser();
        
        // Use correct endpoint based on type
        const endpoint = attendanceType === 'out' ? '/api/attendance/clock-out' : '/api/attendance/clock-in';
        
        const formData = new FormData();
        formData.append('latitude', currentLocation.lat);
        formData.append('longitude', currentLocation.lng);
        formData.append('mode', selectedMode);
        formData.append('shift_id', selectedShiftId);
        
        if (note) {
            formData.append('note', note);
        }
        
        // Convert base64 to blob and append as file
        const photoBlob = dataURLtoBlob(capturedPhoto);
        formData.append('selfie', photoBlob, 'selfie.jpg');
        
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await res.json();
        
        if (res.ok) {
            showAlert(data.message || 'Absensi berhasil!', 'success');
            setTimeout(() => {
                window.location.href = '/app/home';
            }, 1500);
        } else {
            showAlert(data.message || data.error || 'Gagal mengirim absensi', 'error');
            btn.disabled = false;
            btn.textContent = '✓ KIRIM ABSENSI';
        }
    } catch (err) {
        console.error('Attendance error:', err);
        showAlert('Terjadi kesalahan. Coba lagi.', 'error');
        btn.disabled = false;
        btn.textContent = '✓ KIRIM ABSENSI';
    }
}

function showAlert(message, type) {
    const alertBox = document.getElementById('alert-box');
    alertBox.textContent = message;
    alertBox.className = `alert alert-${type}`;
    alertBox.style.display = 'block';
}
</script>
@endpush
