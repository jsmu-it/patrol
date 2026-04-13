@extends('pwa.layout')

@section('title', 'Login - JSMU Guard')

@section('content')
<div class="login-container">
    <div class="login-logo">
        <img src="/images/admin-logo.png" alt="JSMU Guard">
        <h1>JSMU Guard</h1>
    </div>
    
    <div class="login-card">
        <h2 class="login-title">Masuk ke Akun</h2>
        
        <div id="error-msg" class="alert alert-error" style="display:none;margin:0 0 16px"></div>
        
        <form id="login-form">
            <div class="form-group">
                <label class="form-label">Email / Username</label>
                <input type="text" id="username" class="form-input" placeholder="Masukkan username" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" id="password" class="form-input" placeholder="Masukkan password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" id="login-btn">
                MASUK
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const btn = document.getElementById('login-btn');
    const errorDiv = document.getElementById('error-msg');
    
    btn.disabled = true;
    btn.textContent = 'Memproses...';
    errorDiv.style.display = 'none';
    
    try {
        console.log('Attempting login...');
        const res = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });
        
        console.log('Response status:', res.status);
        const data = await res.json();
        console.log('Response data:', data);
        
        if (res.ok && data.access_token) {
            // Store directly in localStorage
            localStorage.setItem('jsmu_token', data.access_token);
            localStorage.setItem('jsmu_user', JSON.stringify(data.user));
            console.log('Login successful, redirecting...');
            window.location.href = '/app/home';
        } else {
            const msg = data.message || data.error || 'Username atau password salah';
            errorDiv.textContent = msg;
            errorDiv.style.display = 'block';
            console.log('Login failed:', msg);
        }
    } catch (err) {
        console.error('Login error:', err);
        errorDiv.textContent = 'Terjadi kesalahan jaringan. Periksa koneksi internet.';
        errorDiv.style.display = 'block';
    }
    
    btn.disabled = false;
    btn.textContent = 'MASUK';
});
</script>
@endpush
