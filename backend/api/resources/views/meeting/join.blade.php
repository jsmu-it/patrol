<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meeting->title }} - JSMUGuard Meeting</title>
    <meta name="description" content="Bergabung ke meeting: {{ $meeting->title }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #fff; overflow: hidden; height: 100vh; }

        /* === JOIN PAGE === */
        .join-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); padding: 20px; }
        .join-card { width: 100%; max-width: 420px; text-align: center; }
        .join-logo { width: 72px; height: 72px; background: rgba(59,130,246,0.15); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; animation: float 3s ease-in-out infinite; }
        .join-logo svg { width: 36px; height: 36px; color: #60a5fa; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .join-card h1 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .join-card .desc { font-size: 13px; color: #94a3b8; margin-bottom: 16px; }
        .join-meta { display: flex; justify-content: center; gap: 20px; font-size: 12px; color: #64748b; margin-bottom: 24px; }
        .join-meta span { display: flex; align-items: center; gap: 4px; }
        .join-meta svg { width: 14px; height: 14px; }
        .join-form { background: rgba(255,255,255,0.04); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 24px; }
        .join-form label { display: block; text-align: left; font-size: 13px; font-weight: 500; color: #cbd5e1; margin-bottom: 6px; }
        .join-form input { width: 100%; background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 12px 14px; color: white; font-size: 14px; outline: none; transition: border 0.2s; margin-bottom: 16px; }
        .join-form input:focus { border-color: #3b82f6; }
        .join-form input::placeholder { color: #475569; }
        .join-btn { width: 100%; padding: 13px; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .join-btn:hover { background: linear-gradient(135deg, #1d4ed8, #2563eb); transform: translateY(-1px); box-shadow: 0 8px 24px rgba(37,99,235,0.3); }
        .join-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
        .join-error { margin-top: 12px; padding: 10px 14px; background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.3); border-radius: 8px; color: #fca5a5; font-size: 13px; display: none; }
        .join-loading { margin-top: 12px; display: none; }
        .join-loading .spinner { display: inline-flex; align-items: center; gap: 8px; color: #60a5fa; font-size: 13px; }
        .join-loading svg { width: 20px; height: 20px; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .join-ended { margin-top: 20px; padding: 14px; background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.2); border-radius: 10px; }
        .join-ended p { color: #fca5a5; font-size: 14px; font-weight: 500; }
        .powered { margin-top: 32px; font-size: 11px; color: #334155; }

        /* === MEETING UI (same as admin) === */
        .meeting-ui { display: none; height: 100vh; flex-direction: column; }
        .meeting-ui.active { display: flex; }
        .meeting-header { height: 48px; background: #1a1a2e; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .meeting-header .left { display: flex; align-items: center; gap: 12px; }
        .meeting-header .shield { width: 28px; height: 28px; background: linear-gradient(135deg, #16a34a, #15803d); border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .meeting-header .shield svg { width: 16px; height: 16px; fill: white; }
        .meeting-header .info h1 { font-size: 13px; font-weight: 600; color: #e2e8f0; }
        .meeting-header .right { display: flex; align-items: center; gap: 12px; }
        .badge-live { padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 500; background: #dc2626; color: white; animation: blink 2s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.6} }
        .timer { font-size: 12px; color: #94a3b8; font-variant-numeric: tabular-nums; }

        .meeting-body { display: flex; flex: 1; overflow: hidden; }
        .video-area { flex: 1; position: relative; display: flex; align-items: center; justify-content: center; padding: 8px; }
        .video-grid { display: grid; gap: 6px; width: 100%; height: 100%; transition: all 0.3s; }
        .video-tile { position: relative; background: #16213e; border-radius: 10px; overflow: hidden; border: 2px solid transparent; transition: all 0.3s; }
        .video-tile.speaking { border-color: #22c55e; box-shadow: 0 0 16px rgba(34,197,94,0.25); }
        .video-tile video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .video-tile .avatar { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #16213e, #0f3460); }
        .video-tile .avatar-circle { width: clamp(48px,8vw,96px); height: clamp(48px,8vw,96px); border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #6366f1); display: flex; align-items: center; justify-content: center; font-size: clamp(20px,3vw,40px); font-weight: 700; text-transform: uppercase; }
        .tile-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 6px 10px; background: linear-gradient(transparent, rgba(0,0,0,0.7)); display: flex; align-items: center; justify-content: space-between; }
        .tile-name { font-size: 12px; font-weight: 500; text-shadow: 0 1px 3px rgba(0,0,0,0.5); }
        .tile-icons { display: flex; gap: 4px; }
        .tile-icon { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .tile-icon.muted { background: #dc2626; }
        .tile-icon svg { width: 12px; height: 12px; fill: white; }

        .view-toggle { position: absolute; top: 12px; right: 12px; display: flex; background: rgba(0,0,0,0.4); border-radius: 8px; overflow: hidden; z-index: 20; backdrop-filter: blur(8px); }
        .view-toggle button { background: none; border: none; color: #94a3b8; padding: 6px 14px; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px; }
        .view-toggle button.active { background: rgba(255,255,255,0.15); color: white; }
        .view-toggle button svg { width: 14px; height: 14px; }

        .video-grid.speaker-view .video-tile { display: none; }
        .video-grid.speaker-view .video-tile.active-speaker { display: block; }
        .filmstrip { display: none; position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); height: 120px; gap: 6px; z-index: 10; }
        .filmstrip .video-tile { width: 160px; min-width: 160px; height: 100%; border-radius: 8px; cursor: pointer; }

        /* Side panels */
        .side-panel { width: 320px; background: #16213e; border-left: 1px solid rgba(255,255,255,0.08); display: none; flex-direction: column; }
        .side-panel.open { display: flex; }
        .panel-header { height: 48px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .panel-header h2 { font-size: 14px; font-weight: 600; }
        .panel-close { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; border-radius: 4px; }
        .panel-close:hover { background: rgba(255,255,255,0.1); color: white; }
        .panel-close svg { width: 18px; height: 18px; }
        .panel-body { flex: 1; overflow-y: auto; padding: 12px 16px; }
        .panel-body::-webkit-scrollbar { width: 4px; }
        .panel-body::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }

        .participant-item { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; }
        .participant-item:hover { background: rgba(255,255,255,0.05); }
        .participant-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #6366f1); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
        .participant-info { flex: 1; min-width: 0; }
        .participant-name { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .participant-role { font-size: 11px; color: #64748b; }
        .participant-status { display: flex; gap: 4px; }
        .participant-status .si { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }
        .participant-status .si svg { width: 12px; height: 12px; }
        .participant-status .si.on { color: #22c55e; }
        .participant-status .si.off { color: #dc2626; background: rgba(220,38,38,0.15); border-radius: 50%; }

        .chat-messages { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding-bottom: 8px; }
        .chat-msg { max-width: 85%; }
        .chat-msg.self { align-self: flex-end; }
        .chat-msg .msg-sender { font-size: 11px; color: #60a5fa; margin-bottom: 2px; font-weight: 500; }
        .chat-msg.self .msg-sender { color: #a78bfa; text-align: right; }
        .chat-msg .msg-bubble { padding: 8px 12px; border-radius: 12px; font-size: 13px; line-height: 1.4; background: #1e293b; color: #e2e8f0; }
        .chat-msg.self .msg-bubble { background: #3b82f6; color: white; border-bottom-right-radius: 4px; }
        .chat-msg .msg-time { font-size: 10px; color: #475569; margin-top: 2px; }
        .chat-input-area { padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; gap: 8px; }
        .chat-input-area input { flex: 1; background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 13px; outline: none; }
        .chat-input-area input:focus { border-color: #3b82f6; }
        .chat-input-area button { background: #3b82f6; border: none; color: white; width: 36px; height: 36px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .chat-input-area button:hover { background: #2563eb; }
        .chat-input-area button svg { width: 18px; height: 18px; }

        /* Toolbar */
        .meeting-toolbar { height: 72px; background: #1a1a2e; display: flex; align-items: center; justify-content: center; gap: 4px; padding: 0 16px; border-top: 1px solid rgba(255,255,255,0.08); }
        .toolbar-divider { width: 1px; height: 32px; background: rgba(255,255,255,0.1); margin: 0 8px; }
        .tb-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 8px 14px; border-radius: 8px; cursor: pointer; border: none; color: #d1d5db; font-size: 11px; background: transparent; transition: all 0.15s; position: relative; min-width: 64px; }
        .tb-btn:hover { background: rgba(255,255,255,0.08); color: white; }
        .tb-btn.active { color: white; }
        .tb-btn.muted { color: #fca5a5; }
        .tb-btn.muted .tb-icon { background: #dc2626; }
        .tb-btn .tb-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #2d2d44; transition: all 0.15s; }
        .tb-btn:hover .tb-icon { background: #3d3d5c; }
        .tb-btn svg { width: 20px; height: 20px; }
        .tb-btn-end .tb-icon { background: #dc2626; }
        .tb-btn-end:hover .tb-icon { background: #b91c1c; }
        .tb-badge { position: absolute; top: 2px; right: 12px; background: #3b82f6; color: white; font-size: 10px; min-width: 18px; height: 18px; border-radius: 9px; display: none; align-items: center; justify-content: center; font-weight: 600; }
        .tb-badge.show { display: flex; }

        .reactions-popup { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #16213e; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 8px 12px; display: none; gap: 8px; margin-bottom: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
        .reactions-popup.open { display: flex; }
        .reaction-btn { font-size: 24px; cursor: pointer; padding: 4px; border-radius: 8px; border: none; background: transparent; }
        .reaction-btn:hover { background: rgba(255,255,255,0.1); transform: scale(1.2); }

        .floating-reaction { position: fixed; font-size: 36px; pointer-events: none; z-index: 100; animation: floatUp 3s ease-out forwards; }
        @keyframes floatUp { 0%{opacity:1;transform:translateY(0)} 100%{opacity:0;transform:translateY(-200px) scale(1.5)} }

        .toast { position: fixed; top: 60px; left: 50%; transform: translateX(-50%); background: #16213e; border: 1px solid rgba(255,255,255,0.1); color: white; padding: 8px 20px; border-radius: 8px; font-size: 13px; z-index: 200; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
        .toast.show { opacity: 1; }

        @media (max-width: 768px) {
            .side-panel { width: 100%; position: fixed; inset: 0; z-index: 100; }
            .tb-btn { min-width: 48px; padding: 8px 8px; font-size: 10px; }
        }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 300; display: none; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #1e293b; border: 1px solid rgba(255,255,255,0.12); border-radius: 16px; padding: 28px 32px; width: 340px; text-align: center; }
        .modal-box h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .modal-box p { font-size: 13px; color: #94a3b8; margin-bottom: 20px; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal-btn { padding: 9px 24px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; }
        .modal-btn-cancel { background: #334155; color: #cbd5e1; }
        .modal-btn-leave { background: #f59e0b; color: white; }
        .screen-badge { position: absolute; top: 8px; left: 8px; background: rgba(37,99,235,0.85); color: white; font-size: 10px; padding: 2px 8px; border-radius: 4px; z-index: 5; display: none; }
        .host-crown { color: #f59e0b; font-size: 12px; margin-left: 4px; }
    </style>
</head>
<body>
    <div class="toast" id="toast"></div>

    <!-- Leave Modal -->
    <div class="modal-overlay" id="modal-leave">
        <div class="modal-box">
            <h3>🚪 Keluar Meeting?</h3>
            <p>Anda akan keluar dari meeting ini.</p>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="document.getElementById('modal-leave').classList.remove('open')">Batal</button>
                <button class="modal-btn modal-btn-leave" onclick="room.disconnect()">Ya, Keluar</button>
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div class="modal-overlay" id="modal-transfer">
        <div class="modal-box">
            <h3>👑 Pindahkan Host</h3>
            <p>Jadikan <strong id="t-target-name"></strong> sebagai host baru?</p>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="document.getElementById('modal-transfer').classList.remove('open')">Batal</button>
                <button class="modal-btn modal-btn-leave" onclick="doTransferHost()">Ya, Pindahkan</button>
            </div>
        </div>
    </div>
    <div class="join-page" id="join-page">
        <div class="join-card">
            <div class="join-logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </div>
            <h1>{{ $meeting->title }}</h1>
            @if($meeting->description)
                <p class="desc">{{ $meeting->description }}</p>
            @endif
            <div class="join-meta">
                <span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ $meeting->host->name }}
                </span>
                <span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Maks. {{ $meeting->max_participants }}
                </span>
            </div>

            @if($meeting->status === 'ended')
                <div class="join-ended"><p>Meeting ini sudah berakhir.</p></div>
            @else
                <div class="join-form">
                    <form onsubmit="return joinMeeting(event)">
                        <label>Nama Anda</label>
                        <input type="text" id="participant-name" required placeholder="Masukkan nama Anda...">
                        @if($meeting->password)
                            <label>Password Meeting</label>
                            <input type="password" id="meeting-password" placeholder="Masukkan password...">
                        @endif
                        <button type="submit" class="join-btn" id="join-btn">🎥 Gabung Meeting</button>
                    </form>
                    <div class="join-error" id="join-error"></div>
                    <div class="join-loading" id="join-loading">
                        <div class="spinner">
                            <svg fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Menghubungkan ke meeting...
                        </div>
                    </div>
                </div>
            @endif
            <p class="powered">Powered by <strong style="color:#475569">JSMUGuard</strong></p>
        </div>
    </div>

    <!-- === MEETING UI === -->
    <div class="meeting-ui" id="meeting-ui">
        <div class="meeting-header">
            <div class="left">
                <div class="shield"><svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg></div>
                <div class="info"><h1>{{ $meeting->title }}</h1></div>
            </div>
            <div class="right">
                <span class="timer" id="meeting-timer">00:00:00</span>
                <span class="badge-live" style="animation-duration:3s">LIVE</span>
            </div>
        </div>

        <div class="meeting-body">
            <div class="video-area">
                <div class="view-toggle">
                    <button class="active" onclick="setView('gallery')" id="btn-gallery">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Gallery
                    </button>
                    <button onclick="setView('speaker')" id="btn-speaker">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Speaker
                    </button>
                </div>
                <div class="video-grid" id="video-grid"></div>
                <div class="filmstrip" id="filmstrip"></div>
            </div>

            <div class="side-panel" id="panel-participants">
                <div class="panel-header">
                    <h2>Peserta (<span id="p-count">0</span>)</h2>
                    <button class="panel-close" onclick="closePanel('participants')"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="panel-body" id="participants-list"></div>
            </div>

            <div class="side-panel" id="panel-chat">
                <div class="panel-header">
                    <h2>Chat</h2>
                    <button class="panel-close" onclick="closePanel('chat')"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="panel-body chat-messages" id="chat-messages"></div>
                <div class="chat-input-area">
                    <input type="text" id="chat-input" placeholder="Ketik pesan..." onkeydown="if(event.key==='Enter')sendChat()">
                    <button onclick="sendChat()"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></button>
                </div>
            </div>
        </div>

        <div class="meeting-toolbar">
            <button class="tb-btn active" id="btn-mic" onclick="toggleMic()">
                <div class="tb-icon"><svg id="ico-mic" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg><svg id="ico-mic-off" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4M5 3l14 14m-7-7V5a3 3 0 00-5.356-1.857"/></svg></div>
                <span id="lbl-mic">Mute</span>
            </button>
            <button class="tb-btn active" id="btn-cam" onclick="toggleCamera()">
                <div class="tb-icon"><svg id="ico-cam" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg><svg id="ico-cam-off" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
                <span id="lbl-cam">Video</span>
            </button>
            <div class="toolbar-divider"></div>
            <button class="tb-btn" id="btn-screen" onclick="toggleScreen()">
                <div class="tb-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                <span>Share</span>
            </button>
            <button class="tb-btn" onclick="togglePanel('participants')" id="btn-participants">
                <div class="tb-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                <span>Peserta</span>
                <span class="tb-badge" id="badge-participants"></span>
            </button>
            <button class="tb-btn" onclick="togglePanel('chat')" id="btn-chat">
                <div class="tb-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
                <span>Chat</span>
                <span class="tb-badge" id="badge-chat"></span>
            </button>
            <button class="tb-btn" onclick="toggleReactions()" id="btn-reactions" style="position:relative">
                <div class="reactions-popup" id="reactions-popup">
                    <button class="reaction-btn" onclick="sendReaction('👏');event.stopPropagation()">👏</button>
                    <button class="reaction-btn" onclick="sendReaction('👍');event.stopPropagation()">👍</button>
                    <button class="reaction-btn" onclick="sendReaction('❤️');event.stopPropagation()">❤️</button>
                    <button class="reaction-btn" onclick="sendReaction('😂');event.stopPropagation()">😂</button>
                    <button class="reaction-btn" onclick="sendReaction('😮');event.stopPropagation()">😮</button>
                    <button class="reaction-btn" onclick="sendReaction('🎉');event.stopPropagation()">🎉</button>
                </div>
                <div class="tb-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <span>Reaksi</span>
            </button>
            <button class="tb-btn" onclick="raiseHand()" id="btn-hand">
                <div class="tb-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/></svg></div>
                <span id="lbl-hand">✋</span>
            </button>
            <div class="toolbar-divider"></div>
            <button class="tb-btn tb-btn-end" onclick="document.getElementById('modal-leave').classList.add('open')">
                <div class="tb-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></div>
                <span>Keluar</span>
            </button>
        </div>
    </div>

    <script src="https://unpkg.com/livekit-client@2.9.1/dist/livekit-client.umd.js"></script>
    <script>
        const MEETING_ID = {{ $meeting->id }};
        const TOKEN_URL = '{{ route("meeting.token", $meeting->id) }}';
        let room, MY_NAME, micOn=true, camOn=true, screenOn=false, handRaised=false;
        let currentView='gallery', currentSpeaker=null, chatUnread=0, openPanel=null, startTime, currentHost = '{{ addslashes($meeting->host->name) }}';

        async function joinMeeting(e) {
            e.preventDefault();
            MY_NAME = document.getElementById('participant-name').value.trim();
            if (!MY_NAME) return false;
            const pwd = document.getElementById('meeting-password')?.value || '';
            document.getElementById('join-error').style.display = 'none';
            document.getElementById('join-loading').style.display = 'block';
            document.getElementById('join-btn').disabled = true;

            try {
                const resp = await fetch(TOKEN_URL, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json' },
                    body: JSON.stringify({ name: MY_NAME, password: pwd }),
                });
                const data = await resp.json();
                if (!resp.ok) throw new Error(data.error || 'Gagal bergabung.');

                room = new LivekitClient.Room({ adaptiveStream: true, dynacast: true, videoCaptureDefaults: { resolution: LivekitClient.VideoPresets.h720.resolution } });
                room.on(LivekitClient.RoomEvent.TrackSubscribed, (t,pu,p) => { 
                    if (t.source === LivekitClient.Track.Source.ScreenShare) handleScreenTrack(t, p);
                    else { attachTrack(t,p); updateGrid(); }
                });
                room.on(LivekitClient.RoomEvent.TrackUnsubscribed, (t,pu,p) => { 
                    if (t.source === LivekitClient.Track.Source.ScreenShare) {
                        const st = document.getElementById('tile-screen-' + p.identity);
                        if (st) st.remove();
                        t.detach();
                    } else detachTrack(t,p); 
                    updateGrid(); 
                });
                room.on(LivekitClient.RoomEvent.ParticipantConnected, p => { createTile(p,false); updateGrid(); updatePList(); showToast(p.identity+' bergabung'); });
                room.on(LivekitClient.RoomEvent.ParticipantDisconnected, p => { removeTile(p); updateGrid(); updatePList(); showToast(p.identity+' keluar'); });
                room.on(LivekitClient.RoomEvent.ActiveSpeakersChanged, handleSpeakers);
                room.on(LivekitClient.RoomEvent.Disconnected, () => { document.getElementById('meeting-ui').classList.remove('active'); document.getElementById('join-page').style.display='flex'; });
                room.on(LivekitClient.RoomEvent.DataReceived, handleData);
                room.on(LivekitClient.RoomEvent.TrackMuted, (_,p) => updateTileStatus(p));
                room.on(LivekitClient.RoomEvent.TrackUnmuted, (_,p) => updateTileStatus(p));

                await room.connect(data.url, data.token);
                document.getElementById('join-page').style.display = 'none';
                document.getElementById('meeting-ui').classList.add('active');

                startTime = Date.now();
                setInterval(() => {
                    const el = Math.floor((Date.now()-startTime)/1000);
                    document.getElementById('meeting-timer').textContent = String(Math.floor(el/3600)).padStart(2,'0')+':'+String(Math.floor((el%3600)/60)).padStart(2,'0')+':'+String(el%60).padStart(2,'0');
                }, 1000);

                // Try mic (soft fail)
                try {
                    await room.localParticipant.setMicrophoneEnabled(true);
                    micOn = true;
                } catch (err) {
                    console.warn('Microphone not available:', err.message);
                    micOn = false;
                    document.getElementById('btn-mic').classList.add('muted');
                    document.getElementById('ico-mic').style.display = 'none';
                    document.getElementById('ico-mic-off').style.display = '';
                    document.getElementById('lbl-mic').textContent = 'No Mic';
                    showToast('⚠️ Mikrofon tidak tersedia — bergabung tanpa audio');
                }

                // Try camera (soft fail)
                try {
                    await room.localParticipant.setCameraEnabled(true);
                    camOn = true;
                } catch (err) {
                    console.warn('Camera not available:', err.message);
                    camOn = false;
                    document.getElementById('btn-cam').classList.add('muted');
                    document.getElementById('ico-cam').style.display = 'none';
                    document.getElementById('ico-cam-off').style.display = '';
                    document.getElementById('lbl-cam').textContent = 'No Cam';
                    if (!micOn) {
                        showToast('⚠️ Kamera tidak tersedia — bergabung sebagai penonton');
                    }
                }

                createTile(room.localParticipant, true);
                room.remoteParticipants.forEach(p => createTile(p, false));
                updateGrid(); updatePList();
            } catch (err) {
                document.getElementById('join-error').textContent = err.message;
                document.getElementById('join-error').style.display = 'block';
                document.getElementById('join-loading').style.display = 'none';
                document.getElementById('join-btn').disabled = false;
            }
            return false;
        }

        function createTile(p, isLocal) {
            if (document.getElementById('tile-'+p.identity)) return;
            const tile = document.createElement('div');
            tile.id = 'tile-'+p.identity; tile.className = 'video-tile'; tile.dataset.identity = p.identity;
            tile.innerHTML = `<div class="avatar" id="avatar-${p.identity}"><div class="avatar-circle">${(p.identity||'?')[0].toUpperCase()}</div></div>
                <video id="video-${p.identity}" autoplay playsinline ${isLocal?'muted':''} style="display:none"></video>
                <div class="tile-overlay"><div class="tile-name">${p.identity}${isLocal?' (You)':''}</div><div class="tile-icons" id="icons-${p.identity}"></div></div>`;
            document.getElementById('video-grid').appendChild(tile);
            const fs = tile.cloneNode(true); fs.id='fs-'+p.identity; fs.onclick=()=>pinSpeaker(p.identity);
            document.getElementById('filmstrip').appendChild(fs);
            p.trackPublications.forEach(pub => { if(pub.track) attachTrack(pub.track, p); });
            updateTileStatus(p);
        }
        function removeTile(p) { ['tile-','fs-'].forEach(pf=>{const e=document.getElementById(pf+p.identity);if(e)e.remove();}); }

        function attachTrack(track, p) {
            if (track.kind==='video') {
                const v=document.getElementById('video-'+p.identity), a=document.getElementById('avatar-'+p.identity);
                if(v){track.attach(v);v.style.display='block';} if(a)a.style.display='none';
                const fs=document.getElementById('fs-'+p.identity);
                if(fs){const fv=fs.querySelector('video'),fa=fs.querySelector('.avatar');if(fv){track.attach(fv);fv.style.display='block';}if(fa)fa.style.display='none';}
            }
            if (track.kind==='audio') { const ae=track.attach(); ae.id='audio-'+p.identity; document.body.appendChild(ae); }
        }
        function detachTrack(track, p) {
            if(track.kind==='video'){const v=document.getElementById('video-'+p.identity),a=document.getElementById('avatar-'+p.identity);if(v){track.detach(v);v.style.display='none';}if(a)a.style.display='flex';
            const fs=document.getElementById('fs-'+p.identity);if(fs){const fv=fs.querySelector('video'),fa=fs.querySelector('.avatar');if(fv){track.detach(fv);fv.style.display='none';}if(fa)fa.style.display='flex';}}
            if(track.kind==='audio'){const a=document.getElementById('audio-'+p.identity);if(a)a.remove();track.detach();}
        }

        function handleScreenTrack(track, participant) {
            if (track.source !== LivekitClient.Track.Source.ScreenShare) return;
            const tileId = 'tile-screen-' + participant.identity;
            if (document.getElementById(tileId)) return;
            const tile = document.createElement('div');
            tile.id = tileId; tile.className = 'video-tile'; tile.dataset.identity = participant.identity + '-screen';
            tile.innerHTML = `
                <div class="screen-badge" style="display:block">🖥️ ${participant.identity} Screen</div>
                <video id="video-screen-${participant.identity}" autoplay playsinline style="width:100%;height:100%;object-fit:contain;background:#000"></video>
                <div class="tile-overlay"><div class="tile-name">${participant.identity} — Screen</div></div>`;
            document.getElementById('video-grid').appendChild(tile);
            const v = tile.querySelector('video');
            track.attach(v);
            
            // Auto focus screen share
            setTimeout(() => {
                setView('speaker');
                pinSpeaker(participant.identity + '-screen');
                showToast('🖥️ ' + participant.identity + ' sedang berbagi layar');
            }, 500);
            updateGrid();
        }
        function updateTileStatus(p) {
            const ic=document.getElementById('icons-'+p.identity); if(!ic)return;
            let h='';
            if(!p.isMicrophoneEnabled) h+='<div class="tile-icon muted"><svg viewBox="0 0 24 24"><path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4M5 3l14 14" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"/></svg></div>';
            ic.innerHTML=h;
        }

        function updateGrid() {
            const grid=document.getElementById('video-grid'), c=grid.children.length;
            const bp=document.getElementById('badge-participants'); bp.textContent=c; bp.classList.add('show');
            document.getElementById('p-count').textContent=c;
            if(currentView==='gallery'){
                grid.classList.remove('speaker-view'); document.getElementById('filmstrip').style.display='none';
                if(c<=1){grid.style.gridTemplateColumns='1fr';grid.style.gridTemplateRows='1fr';}
                else if(c<=2){grid.style.gridTemplateColumns='1fr 1fr';grid.style.gridTemplateRows='1fr';}
                else if(c<=4){grid.style.gridTemplateColumns='1fr 1fr';grid.style.gridTemplateRows='1fr 1fr';}
                else if(c<=6){grid.style.gridTemplateColumns='repeat(3,1fr)';grid.style.gridTemplateRows='1fr 1fr';}
                else if(c<=9){grid.style.gridTemplateColumns='repeat(3,1fr)';grid.style.gridTemplateRows='repeat(3,1fr)';}
                else{grid.style.gridTemplateColumns='repeat(4,1fr)';grid.style.gridTemplateRows=`repeat(${Math.ceil(c/4)},1fr)`;}
                grid.querySelectorAll('.video-tile').forEach(t=>{t.style.display='block';t.classList.remove('active-speaker');});
            } else {
                grid.classList.add('speaker-view'); document.getElementById('filmstrip').style.display='flex';
                const sid=currentSpeaker||room.localParticipant.identity;
                grid.querySelectorAll('.video-tile').forEach(t=>t.classList.toggle('active-speaker',t.dataset.identity===sid));
            }
        }
        function setView(v){currentView=v;document.getElementById('btn-gallery').classList.toggle('active',v==='gallery');document.getElementById('btn-speaker').classList.toggle('active',v==='speaker');updateGrid();}
        function pinSpeaker(id){currentSpeaker=id;updateGrid();}
        function handleSpeakers(speakers){
            document.querySelectorAll('#video-grid .video-tile').forEach(t=>t.classList.remove('speaking'));
            speakers.forEach(p=>{const t=document.getElementById('tile-'+p.identity);if(t)t.classList.add('speaking');if(currentView==='speaker'&&!currentSpeaker){document.querySelectorAll('.active-speaker').forEach(e=>e.classList.remove('active-speaker'));if(t)t.classList.add('active-speaker');}});
        }

        async function toggleMic(){const newState=!micOn;try{await room.localParticipant.setMicrophoneEnabled(newState);micOn=newState;}catch(err){console.warn('Mic toggle:',err.message);showToast('⚠️ Mikrofon tidak tersedia');micOn=false;}document.getElementById('btn-mic').classList.toggle('muted',!micOn);document.getElementById('ico-mic').style.display=micOn?'':'none';document.getElementById('ico-mic-off').style.display=micOn?'none':'';document.getElementById('lbl-mic').textContent=micOn?'Mute':'Unmute';updateTileStatus(room.localParticipant);}
        async function toggleCamera(){const newState=!camOn;try{await room.localParticipant.setCameraEnabled(newState);camOn=newState;}catch(err){console.warn('Cam toggle:',err.message);showToast('⚠️ Kamera tidak tersedia');camOn=false;}document.getElementById('btn-cam').classList.toggle('muted',!camOn);document.getElementById('ico-cam').style.display=camOn?'':'none';document.getElementById('ico-cam-off').style.display=camOn?'none':'';document.getElementById('lbl-cam').textContent=camOn?'Video':'Start Video';const av=document.getElementById('avatar-'+room.localParticipant.identity),vid=document.getElementById('video-'+room.localParticipant.identity);if(!camOn){if(vid)vid.style.display='none';if(av)av.style.display='flex';}else{if(vid)vid.style.display='block';if(av)av.style.display='none';}}
        async function toggleScreen(){try{screenOn=!screenOn;await room.localParticipant.setScreenShareEnabled(screenOn);document.getElementById('btn-screen').classList.toggle('active',screenOn);}catch{screenOn=false;document.getElementById('btn-screen').classList.remove('active');}}
        function leaveMeeting(){if(confirm('Keluar dari meeting?'))room.disconnect();}

        function togglePanel(n){if(openPanel===n){closePanel(n);return;}if(openPanel)closePanel(openPanel);document.getElementById('panel-'+n).classList.add('open');openPanel=n;if(n==='chat'){chatUnread=0;document.getElementById('badge-chat').classList.remove('show');}if(n==='participants')updatePList();}
        function closePanel(n){document.getElementById('panel-'+n).classList.remove('open');openPanel=null;}

        function updatePList(){
            if(!room)return;
            const amHost = (MY_NAME === currentHost);
            const all=[room.localParticipant,...room.remoteParticipants.values()];
            document.getElementById('participants-list').innerHTML=all.map(p=>{
                const isL=p===room.localParticipant;
                const isH=p.identity===currentHost;
                return `<div class="participant-item" style="flex-wrap:wrap">
                    <div class="participant-avatar" style="background:${isH?'linear-gradient(135deg,#f59e0b,#d97706)':'linear-gradient(135deg,#3b82f6,#6366f1)'}">${(p.identity||'?')[0].toUpperCase()}</div>
                    <div class="participant-info">
                        <div class="participant-name">${p.identity}${isL?' (You)':''}${isH?'<span class="host-crown">👑</span>':''}</div>
                        <div class="participant-role">${isH?'Host':'Participant'}</div>
                    </div>
                    <div class="participant-status">
                        <div class="si ${p.isMicrophoneEnabled?'on':'off'}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg></div>
                        <div class="si ${p.isCameraEnabled?'on':'off'}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></div>
                    </div>
                    ${amHost && !isL ? `<div class="participant-actions" style="width:100%;padding-left:42px;display:flex;gap:4px;margin-top:4px"><button class="btn-transfer" style="font-size:10px;padding:2px 8px;border-radius:4px;border:1px solid #3b82f6;color:#60a5fa;background:transparent;cursor:pointer" onclick="pTransferHost('${p.identity}')">👑 Jadikan Host</button></div>` : ''}
                </div>`;
            }).join('');
        }

        let tTarget = '';
        function pTransferHost(n){ tTarget=n; document.getElementById('t-target-name').textContent=n; document.getElementById('modal-transfer').classList.add('open'); }
        function doTransferHost(){
            document.getElementById('modal-transfer').classList.remove('open');
            currentHost = tTarget;
            const d = JSON.stringify({type:'host_changed',newHost:tTarget,sender:MY_NAME});
            room.localParticipant.publishData(new TextEncoder().encode(d), LivekitClient.DataPacket_Kind.RELIABLE);
            updatePList();
            showToast('👑 Host dipindahkan ke ' + tTarget);
        }

        function sendChat(){const inp=document.getElementById('chat-input'),msg=inp.value.trim();if(!msg||!room)return;const d=JSON.stringify({type:'chat',sender:MY_NAME,text:msg,time:new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})});room.localParticipant.publishData(new TextEncoder().encode(d),LivekitClient.DataPacket_Kind.RELIABLE);appendChat(MY_NAME,msg,new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}),true);inp.value='';}
        function appendChat(s,t,tm,self){const el=document.createElement('div');el.className='chat-msg'+(self?' self':'');el.innerHTML=`<div class="msg-sender">${s}</div><div class="msg-bubble">${t}</div><div class="msg-time">${tm}</div>`;document.getElementById('chat-messages').appendChild(el);el.scrollIntoView({behavior:'smooth'});}

        function toggleReactions(){document.getElementById('reactions-popup').classList.toggle('open');}
        function sendReaction(e){document.getElementById('reactions-popup').classList.remove('open');showFloat(e);if(room)room.localParticipant.publishData(new TextEncoder().encode(JSON.stringify({type:'reaction',sender:MY_NAME,emoji:e})),LivekitClient.DataPacket_Kind.RELIABLE);}
        function showFloat(e){const el=document.createElement('div');el.className='floating-reaction';el.textContent=e;el.style.left=(40+Math.random()*20)+'%';el.style.bottom='100px';document.body.appendChild(el);setTimeout(()=>el.remove(),3000);}
        function raiseHand(){handRaised=!handRaised;document.getElementById('btn-hand').classList.toggle('active',handRaised);document.getElementById('lbl-hand').textContent=handRaised?'🙌':'✋';if(room)room.localParticipant.publishData(new TextEncoder().encode(JSON.stringify({type:'hand',sender:MY_NAME,raised:handRaised})),LivekitClient.DataPacket_Kind.RELIABLE);showToast(handRaised?'✋ Hand raised':'Hand lowered');}

        function handleData(payload){
            try{const d=JSON.parse(new TextDecoder().decode(payload));
            if(d.type==='chat'){appendChat(d.sender,d.text,d.time,false);if(openPanel!=='chat'){chatUnread++;const b=document.getElementById('badge-chat');b.textContent=chatUnread;b.classList.add('show');}}
            if(d.type==='reaction')showFloat(d.emoji);
            if(d.type==='hand')showToast(d.raised?`✋ ${d.sender} raised hand`:`${d.sender} lowered hand`);
            if(d.type==='meeting_ended'){showToast('⛔ Meeting diakhiri oleh host');setTimeout(()=>{if(room)room.disconnect();document.getElementById('meeting-ui').classList.remove('active');document.getElementById('join-page').style.display='flex';},1500);}
            if(d.type==='host_changed'){
                currentHost = d.newHost;
                showToast(`👑 ${d.newHost} sekarang menjadi host`);
                updatePList();
            }
            }catch{}
        }

        function showToast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3000);}
    </script>
</body>
</html>
