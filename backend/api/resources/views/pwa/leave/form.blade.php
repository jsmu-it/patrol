@extends('pwa.layout')

@section('title', 'Pengajuan Cuti - JSMU Guard')

@section('content')
<div class="page-header">
    <button class="back-btn" onclick="goBack()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
    </button>
    <h1 class="page-title">Pengajuan Izin / Cuti</h1>
</div>

<div id="alert-box" class="alert" style="display:none;"></div>

<div style="padding:16px;">
    <form id="leave-form">
        <!-- Type -->
        <div class="form-group">
            <label class="form-label">Jenis</label>
            <select id="leave-type" class="form-select" onchange="onTypeChange()">
                <option value="Cuti">Cuti</option>
                <option value="Izin">Izin</option>
                <option value="Sakit">Sakit</option>
            </select>
        </div>
        
        <!-- Leave Type (only for Cuti) -->
        <div class="form-group" id="leave-type-group" style="display:block;">
            <label class="form-label">Jenis Cuti</label>
            <select id="leave-type-id" class="form-select" onchange="onLeaveTypeChange()">
                <option value="">Pilih jenis cuti</option>
            </select>
        </div>
        
        <!-- Balance Display (only for Cuti) -->
        <div id="balance-display" style="display:none;margin-bottom:16px;padding:12px;border-radius:8px;"></div>
        
        <!-- Date From -->
        <div class="form-group">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" id="date-from" class="form-input" required>
        </div>
        
        <!-- Date To -->
        <div class="form-group">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" id="date-to" class="form-input" required>
        </div>
        
        <!-- Time Range (only for Izin) -->
        <div id="time-range-group" style="display:none;">
            <div class="form-group">
                <label class="form-label">Dari Jam</label>
                <input type="time" id="time-from" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Sampai Jam</label>
                <input type="time" id="time-to" class="form-input">
            </div>
        </div>
        
        <!-- Reason -->
        <div class="form-group">
            <label class="form-label">Alasan</label>
            <textarea id="reason" class="form-textarea" placeholder="Tulis alasan pengajuan..." required></textarea>
        </div>
        
        <!-- Doctor Note -->
        <div class="form-group">
            <label class="form-label">Catatan Dokter (opsional)</label>
            <textarea id="doctor-note" class="form-textarea" placeholder="Catatan dari dokter..." style="min-height:60px;"></textarea>
        </div>
        
        <!-- Sick Letter Photo (only for Sakit) -->
        <div class="form-group" id="sick-photo-group" style="display:none;">
            <label class="form-label">Foto Surat Sakit (opsional)</label>
            <div id="sick-photo-preview" style="display:none;margin-bottom:12px;">
                <img id="sick-preview-img" style="width:100%;max-width:200px;border-radius:12px;">
                <button type="button" class="btn btn-outline" onclick="removeSickPhoto()" style="margin-top:8px;width:auto;padding:8px 16px;">
                    Hapus
                </button>
            </div>
            <input type="file" id="sick-photo-input" accept="image/*" style="display:none;" onchange="handleSickPhoto(this)">
            <button type="button" class="btn btn-outline" id="sick-photo-btn" onclick="document.getElementById('sick-photo-input').click()">
                📷 Upload Foto
            </button>
        </div>
        
        <!-- Permit Photo (only for Izin) -->
        <div class="form-group" id="permit-photo-group" style="display:none;">
            <label class="form-label">Foto Pendukung (opsional)</label>
            <div id="permit-photo-preview" style="display:none;margin-bottom:12px;">
                <img id="permit-preview-img" style="width:100%;max-width:200px;border-radius:12px;">
                <button type="button" class="btn btn-outline" onclick="removePermitPhoto()" style="margin-top:8px;width:auto;padding:8px 16px;">
                    Hapus
                </button>
            </div>
            <input type="file" id="permit-photo-input" accept="image/*" style="display:none;" onchange="handlePermitPhoto(this)">
            <button type="button" class="btn btn-outline" id="permit-photo-btn" onclick="document.getElementById('permit-photo-input').click()">
                📷 Upload Foto
            </button>
        </div>
        
        <!-- Submit -->
        <button type="submit" class="btn btn-primary" id="submit-btn">
            KIRIM PENGAJUAN
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
let sickPhotoFile = null;
let permitPhotoFile = null;
let leaveTypes = [];
let userProfile = null;

function getToken() { return localStorage.getItem('jsmu_token'); }
function goBack() { window.location.href = '/app/leave'; }

document.addEventListener('DOMContentLoaded', async () => {
    if (!getToken()) {
        window.location.href = '/app';
        return;
    }
    
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date-from').value = today;
    document.getElementById('date-to').value = today;
    
    // Load leave types and user profile
    await loadLeaveTypes();
    await loadUserProfile();
    
    // Initial state
    onTypeChange();
});


async function loadLeaveTypes() {
    try {
        const token = getToken();
        const res = await fetch('/api/leave-types', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (res.ok) {
            leaveTypes = await res.json();
            
            // Populate leave type dropdown
            const select = document.getElementById('leave-type-id');
            select.innerHTML = '<option value="">Pilih jenis cuti</option>';
            
            leaveTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.id;
                option.textContent = type.name;
                select.appendChild(option);
            });
        }
    } catch (err) {
        console.error('Error loading leave types:', err);
    }
}

async function loadUserProfile() {
    try {
        const token = getToken();
        const res = await fetch('/api/me', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (res.ok) {
            userProfile = await res.json();
        }
    } catch (err) {
        console.error('Error loading profile:', err);
    }
}

function onTypeChange() {
    const type = document.getElementById('leave-type').value;
    
    // Show/hide leave type group
    document.getElementById('leave-type-group').style.display = type === 'Cuti' ? 'block' : 'none';
    document.getElementById('balance-display').style.display = 'none';
    
    // Show/hide time range
    document.getElementById('time-range-group').style.display = type === 'Izin' ? 'block' : 'none';
    
    // Show/hide photo groups
    document.getElementById('sick-photo-group').style.display = type === 'Sakit' ? 'block' : 'none';
    document.getElementById('permit-photo-group').style.display = type === 'Izin' ? 'block' : 'none';
    
    // Reset selections
    if (type !== 'Cuti') {
        document.getElementById('leave-type-id').value = '';
    }
    if (type !== 'Izin') {
        document.getElementById('time-from').value = '';
        document.getElementById('time-to').value = '';
        removePermitPhoto();
    }
    if (type !== 'Sakit') {
        removeSickPhoto();
    }
}

function onLeaveTypeChange() {
    const leaveTypeId = parseInt(document.getElementById('leave-type-id').value);
    const balanceEl = document.getElementById('balance-display');
    
    if (!leaveTypeId || !userProfile || !userProfile.leave_balances) {
        balanceEl.style.display = 'none';
        return;
    }
    
    const balance = userProfile.leave_balances.find(b => b.leave_type_id === leaveTypeId);
    
    if (!balance) {
        balanceEl.style.backgroundColor = '#FFF3E0';
        balanceEl.style.border = '1px solid #FFB74D';
        balanceEl.style.color = '#E65100';
        balanceEl.innerHTML = '⚠️ Tidak ada saldo untuk jenis cuti ini';
        balanceEl.style.display = 'block';
        return;
    }
    
    const isLow = balance.remaining < balance.quota * 0.3;
    
    if (isLow) {
        balanceEl.style.backgroundColor = '#FFEBEE';
        balanceEl.style.border = '1px solid #EF5350';
        balanceEl.style.color = '#C62828';
        balanceEl.innerHTML = `⚠️ Saldo: ${balance.remaining} hari tersisa dari ${balance.quota}`;
    } else {
        balanceEl.style.backgroundColor = '#E8F5E9';
        balanceEl.style.border = '1px solid #66BB6A';
        balanceEl.style.color = '#2E7D32';
        balanceEl.innerHTML = `✅ Saldo: ${balance.remaining} hari tersisa dari ${balance.quota}`;
    }
    
    balanceEl.style.display = 'block';
}

function handleSickPhoto(input) {
    if (input.files && input.files[0]) {
        sickPhotoFile = input.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('sick-preview-img').src = e.target.result;
            document.getElementById('sick-photo-preview').style.display = 'block';
            document.getElementById('sick-photo-btn').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeSickPhoto() {
    sickPhotoFile = null;
    document.getElementById('sick-photo-input').value = '';
    document.getElementById('sick-photo-preview').style.display = 'none';
    document.getElementById('sick-photo-btn').style.display = 'block';
}

function handlePermitPhoto(input) {
    if (input.files && input.files[0]) {
        permitPhotoFile = input.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('permit-preview-img').src = e.target.result;
            document.getElementById('permit-photo-preview').style.display = 'block';
            document.getElementById('permit-photo-btn').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removePermitPhoto() {
    permitPhotoFile = null;
    document.getElementById('permit-photo-input').value = '';
    document.getElementById('permit-photo-preview').style.display = 'none';
    document.getElementById('permit-photo-btn').style.display = 'block';
}

document.getElementById('leave-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const type = document.getElementById('leave-type').value;
    
    // Validation for Izin
    if (type === 'Izin') {
        const timeFrom = document.getElementById('time-from').value;
        const timeTo = document.getElementById('time-to').value;
        
        if (!timeFrom || !timeTo) {
            showAlert('Waktu izin wajib diisi', 'error');
            return;
        }
    }
    
    // Validation for Cuti
    if (type === 'Cuti') {
        const leaveTypeId = document.getElementById('leave-type-id').value;
        if (!leaveTypeId) {
            showAlert('Pilih jenis cuti', 'error');
            return;
        }
    }
    
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
    
    try {
        const token = getToken();
        
        const formData = new FormData();
        formData.append('type', type);
        formData.append('date_from', document.getElementById('date-from').value);
        formData.append('date_to', document.getElementById('date-to').value);
        formData.append('reason', document.getElementById('reason').value);
        
        const doctorNote = document.getElementById('doctor-note').value;
        if (doctorNote) {
            formData.append('doctor_note', doctorNote);
        }
        
        // Leave type ID for Cuti
        if (type === 'Cuti') {
            const leaveTypeId = document.getElementById('leave-type-id').value;
            if (leaveTypeId) {
                formData.append('leave_type_id', leaveTypeId);
            }
        }
        
        // Time fields for Izin
        if (type === 'Izin') {
            const timeFrom = document.getElementById('time-from').value;
            const timeTo = document.getElementById('time-to').value;
            if (timeFrom) formData.append('time_from', timeFrom);
            if (timeTo) formData.append('time_to', timeTo);
        }
        
        // Photo files
        if (sickPhotoFile) {
            formData.append('sick_letter', sickPhotoFile);
        }
        if (permitPhotoFile) {
            formData.append('permit_photo', permitPhotoFile);
        }
        
        const res = await fetch('/api/leave-requests', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await res.json();
        
        if (res.ok) {
            showAlert(data.message || 'Pengajuan berhasil dikirim', 'success');
            setTimeout(() => {
                window.location.href = '/app/leave';
            }, 1500);
        } else {
            showAlert(data.message || data.error || 'Gagal mengirim pengajuan', 'error');
            btn.disabled = false;
            btn.textContent = 'KIRIM PENGAJUAN';
        }
    } catch (err) {
        console.error('Leave error:', err);
        showAlert('Terjadi kesalahan', 'error');
        btn.disabled = false;
        btn.textContent = 'KIRIM PENGAJUAN';
    }
});

function showAlert(msg, type) {
    const el = document.getElementById('alert-box');
    el.textContent = msg;
    el.className = `alert alert-${type}`;
    el.style.display = 'block';
}
</script>
@endpush
