// JSMU Guard PWA App JavaScript
const API_BASE = '/api';

// Storage Keys
const TOKEN_KEY = 'jsmu_token';
const USER_KEY = 'jsmu_user';

// API Helper
const api = {
    getToken() {
        return localStorage.getItem(TOKEN_KEY);
    },

    setToken(token) {
        localStorage.setItem(TOKEN_KEY, token);
    },

    getUser() {
        const user = localStorage.getItem(USER_KEY);
        return user ? JSON.parse(user) : null;
    },

    setUser(user) {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    },

    clearAuth() {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
    },

    isAuthenticated() {
        return !!this.getToken();
    },

    async request(endpoint, options = {}) {
        const token = this.getToken();
        const headers = {
            'Accept': 'application/json',
            ...options.headers
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        if (!(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(`${API_BASE}${endpoint}`, {
            ...options,
            headers
        });

        if (response.status === 401) {
            this.clearAuth();
            window.location.href = '/app';
            throw new Error('Unauthorized');
        }

        return response;
    },

    async get(endpoint) {
        const res = await this.request(endpoint);
        return res.json();
    },

    async post(endpoint, data) {
        const body = data instanceof FormData ? data : JSON.stringify(data);
        const res = await this.request(endpoint, {
            method: 'POST',
            body
        });
        return res.json();
    }
};

// Location Helper
const location = {
    current: null,
    watchId: null,

    async getCurrentPosition() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation tidak didukung browser ini'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.current = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    };
                    resolve(this.current);
                },
                (err) => {
                    reject(new Error('Gagal mendapatkan lokasi: ' + err.message));
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        });
    },

    startWatch(callback) {
        if (!navigator.geolocation) return;

        this.watchId = navigator.geolocation.watchPosition(
            (pos) => {
                this.current = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                    accuracy: pos.coords.accuracy
                };
                if (callback) callback(this.current);
            },
            (err) => console.log('Watch error:', err),
            { enableHighAccuracy: true }
        );
    },

    stopWatch() {
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
    }
};

// Camera Helper
const camera = {
    stream: null,

    async start(videoElement) {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: 640, height: 480 },
                audio: false
            });
            videoElement.srcObject = this.stream;
            await videoElement.play();
            return true;
        } catch (err) {
            console.error('Camera error:', err);
            return false;
        }
    },

    stop() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
    },

    capture(videoElement, canvasElement) {
        const ctx = canvasElement.getContext('2d');
        canvasElement.width = videoElement.videoWidth;
        canvasElement.height = videoElement.videoHeight;
        ctx.drawImage(videoElement, 0, 0);
        return canvasElement.toDataURL('image/jpeg', 0.8);
    },

    dataURLtoBlob(dataURL) {
        const parts = dataURL.split(',');
        const mime = parts[0].match(/:(.*?);/)[1];
        const raw = atob(parts[1]);
        const arr = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; i++) {
            arr[i] = raw.charCodeAt(i);
        }
        return new Blob([arr], { type: mime });
    }
};

// Date Helper
const dateHelper = {
    formatDate(date) {
        const d = new Date(date);
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    },

    formatTime(date) {
        const d = new Date(date);
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    },

    formatDateInput(date) {
        const d = new Date(date);
        return d.toISOString().split('T')[0];
    }
};

// Auth check on page load
document.addEventListener('DOMContentLoaded', () => {
    const isLoginPage = window.location.pathname === '/app' || window.location.pathname === '/app/';

    if (!isLoginPage && !api.isAuthenticated()) {
        window.location.href = '/app';
    }

    if (isLoginPage && api.isAuthenticated()) {
        window.location.href = '/app/home';
    }
});

// Global functions
window.logout = function () {
    api.clearAuth();
    window.location.href = '/app';
};

window.goBack = function () {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/app/home';
    }
};
