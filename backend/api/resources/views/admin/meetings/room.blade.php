<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meeting->title }} - JSMUGuard Meeting</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #1a1a2e; color: #fff; overflow: hidden; height: 100vh; }

        /* === HEADER === */
        .meeting-header { height: 48px; background: #1a1a2e; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid rgba(255,255,255,0.08); z-index: 50; position: relative; }
        .meeting-header .left { display: flex; align-items: center; gap: 12px; }
        .meeting-header .shield { width: 28px; height: 28px; background: linear-gradient(135deg, #16a34a, #15803d); border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .meeting-header .shield svg { width: 16px; height: 16px; fill: white; }
        .meeting-header .info h1 { font-size: 13px; font-weight: 600; color: #e2e8f0; }
        .meeting-header .info p { font-size: 11px; color: #64748b; }
        .meeting-header .right { display: flex; align-items: center; gap: 12px; }
        .meeting-header .badge { padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 500; }
        .badge-live { background: #dc2626; color: white; animation: blink 2s infinite; }
        .badge-recording { background: #ea580c; color: white; display: none; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.6} }
        .timer { font-size: 12px; color: #94a3b8; font-variant-numeric: tabular-nums; }
        .header-btn { background: #2d2d44; border: none; color: #94a3b8; padding: 6px 12px; border-radius: 6px; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.15s; }
        .header-btn:hover { background: #3d3d5c; color: white; }
        .header-btn svg { width: 14px; height: 14px; }

        /* === MAIN LAYOUT === */
        .meeting-body { display: flex; height: calc(100vh - 48px - 72px); }
        .video-area { flex: 1; position: relative; display: flex; align-items: center; justify-content: center; padding: 8px; overflow: hidden; }

        /* === VIDEO GRID (Gallery View) === */
        .video-grid { display: grid; gap: 6px; width: 100%; height: 100%; transition: all 0.3s ease; }
        .video-tile { position: relative; background: #16213e; border-radius: 10px; overflow: hidden; transition: all 0.3s ease; border: 2px solid transparent; }
        .video-tile.speaking { border-color: #22c55e; box-shadow: 0 0 16px rgba(34,197,94,0.25); }
        .video-tile video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .video-tile .avatar { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #16213e, #0f3460); }
        .video-tile .avatar-circle { width: clamp(48px,8vw,96px); height: clamp(48px,8vw,96px); border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #6366f1); display: flex; align-items: center; justify-content: center; font-size: clamp(20px,3vw,40px); font-weight: 700; color: white; text-transform: uppercase; }
        .tile-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 6px 10px; background: linear-gradient(transparent, rgba(0,0,0,0.7)); display: flex; align-items: center; justify-content: space-between; }
        .tile-name { font-size: 12px; color: white; font-weight: 500; text-shadow: 0 1px 3px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 6px; }
        .tile-name .host-badge { font-size: 9px; background: #2563eb; padding: 1px 6px; border-radius: 3px; }
        .tile-icons { display: flex; gap: 4px; }
        .tile-icon { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .tile-icon.muted { background: #dc2626; }
        .tile-icon.cam-off { background: #dc2626; }
        .tile-icon svg { width: 12px; height: 12px; fill: white; }

        /* === SPEAKER VIEW === */
        .video-grid.speaker-view { grid-template-columns: 1fr; grid-template-rows: 1fr; position: relative; }
        .video-grid.speaker-view .video-tile { display: none; }
        .video-grid.speaker-view .video-tile.active-speaker { display: block; grid-column: 1; grid-row: 1; }
        .filmstrip { display: none; position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); height: 120px; gap: 6px; z-index: 10; overflow-x: auto; max-width: calc(100% - 16px); padding: 0 8px; }
        .video-grid.speaker-view ~ .filmstrip { display: flex; }
        .filmstrip .video-tile { width: 160px; min-width: 160px; height: 100%; border-radius: 8px; cursor: pointer; }
        .filmstrip .video-tile:hover { border-color: rgba(255,255,255,0.3); }

        /* === SIDE PANEL === */
        .side-panel { width: 320px; background: #16213e; border-left: 1px solid rgba(255,255,255,0.08); display: none; flex-direction: column; z-index: 30; }
        .side-panel.open { display: flex; }
        .panel-header { height: 48px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .panel-header h2 { font-size: 14px; font-weight: 600; }
        .panel-close { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; border-radius: 4px; }
        .panel-close:hover { background: rgba(255,255,255,0.1); color: white; }
        .panel-close svg { width: 18px; height: 18px; }
        .panel-body { flex: 1; overflow-y: auto; padding: 12px 16px; }
        .panel-body::-webkit-scrollbar { width: 4px; }
        .panel-body::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }

        /* Participants Panel */
        .participant-item { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; transition: background 0.15s; }
        .participant-item:hover { background: rgba(255,255,255,0.05); }
        .participant-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #6366f1); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
        .participant-info { flex: 1; min-width: 0; }
        .participant-name { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .participant-role { font-size: 11px; color: #64748b; }
        .participant-status { display: flex; gap: 4px; }
        .participant-status .status-icon { width: 20px; height: 20px; border-radius: 4px; display: flex; align-items: center; justify-content: center; }
        .participant-status .status-icon svg { width: 12px; height: 12px; }
        .participant-status .status-icon.active { color: #22c55e; }
        .participant-status .status-icon.inactive { color: #dc2626; background: rgba(220,38,38,0.15); border-radius: 50%; }

        /* Chat Panel */
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
        .chat-input-area button { background: #3b82f6; border: none; color: white; width: 36px; height: 36px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s; }
        .chat-input-area button:hover { background: #2563eb; }
        .chat-input-area button svg { width: 18px; height: 18px; }

        /* === BOTTOM TOOLBAR === */
        .meeting-toolbar { height: 72px; background: #1a1a2e; display: flex; align-items: center; justify-content: center; gap: 4px; padding: 0 16px; border-top: 1px solid rgba(255,255,255,0.08); position: relative; z-index: 40; }
        .toolbar-group { display: flex; align-items: center; gap: 4px; }
        .toolbar-divider { width: 1px; height: 32px; background: rgba(255,255,255,0.1); margin: 0 8px; }
        .tb-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 8px 14px; border-radius: 8px; cursor: pointer; border: none; color: #d1d5db; font-size: 11px; background: transparent; transition: all 0.15s; position: relative; min-width: 64px; }
        .tb-btn:hover { background: rgba(255,255,255,0.08); color: white; }
        .tb-btn.active { color: white; }
        .tb-btn.muted { color: #fca5a5; }
        .tb-btn.muted .tb-icon { background: #dc2626; }
        .tb-btn .tb-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #2d2d44; transition: all 0.15s; }
        .tb-btn:hover .tb-icon { background: #3d3d5c; }
        .tb-btn.muted .tb-icon { background: #dc2626; }
        .tb-btn svg { width: 20px; height: 20px; }
        .tb-btn .arrow { position: absolute; top: 6px; right: 6px; width: 14px; height: 14px; background: #2d2d44; border-radius: 3px; display: flex; align-items: center; justify-content: center; }
        .tb-btn .arrow svg { width: 10px; height: 10px; }
        .tb-btn-end { padding: 8px 24px; }
        .tb-btn-end .tb-icon { background: #dc2626; }
        .tb-btn-end:hover .tb-icon { background: #b91c1c; }
        .tb-badge { position: absolute; top: 2px; right: 12px; background: #3b82f6; color: white; font-size: 10px; min-width: 18px; height: 18px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-weight: 600; display: none; }
        .tb-badge.show { display: flex; }

        /* Reactions popup */
        .reactions-popup { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #16213e; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 8px 12px; display: none; gap: 8px; margin-bottom: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
        .reactions-popup.open { display: flex; }
        .reaction-btn { font-size: 24px; cursor: pointer; padding: 4px; border-radius: 8px; border: none; background: transparent; transition: all 0.15s; }
        .reaction-btn:hover { background: rgba(255,255,255,0.1); transform: scale(1.2); }

        /* Floating reaction */
        .floating-reaction { position: fixed; font-size: 36px; pointer-events: none; z-index: 100; animation: floatUp 3s ease-out forwards; }
        @keyframes floatUp { 0%{opacity:1;transform:translateY(0) scale(1)} 100%{opacity:0;transform:translateY(-200px) scale(1.5)} }

        /* View toggle */
        .view-toggle { position: absolute; top: 12px; right: 12px; display: flex; background: rgba(0,0,0,0.4); border-radius: 8px; overflow: hidden; z-index: 20; backdrop-filter: blur(8px); }
        .view-toggle button { background: none; border: none; color: #94a3b8; padding: 6px 14px; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.15s; }
        .view-toggle button.active { background: rgba(255,255,255,0.15); color: white; }
        .view-toggle button svg { width: 14px; height: 14px; }

        /* Notification toast */
        .toast { position: fixed; top: 60px; left: 50%; transform: translateX(-50%); background: #16213e; border: 1px solid rgba(255,255,255,0.1); color: white; padding: 8px 20px; border-radius: 8px; font-size: 13px; z-index: 200; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
        .toast.show { opacity: 1; }

        /* Fullscreen adjustments */
        :fullscreen .meeting-header { display: none; }
        :fullscreen .meeting-body { height: calc(100vh - 72px); }

        @media (max-width: 768px) {
            .side-panel { width: 100%; position: fixed; inset: 0; z-index: 100; }
            .tb-btn { min-width: 48px; padding: 8px 8px; font-size: 10px; }
            .tb-btn .tb-icon { width: 32px; height: 32px; }
        }
        /* Leave / End buttons */
        .tb-btn-leave .tb-icon { background: #f59e0b; }
        .tb-btn-leave:hover .tb-icon { background: #d97706; }
        .tb-btn-end .tb-icon { background: #dc2626; }
        .tb-btn-end:hover .tb-icon { background: #b91c1c; }
        /* Transfer host */
        .participant-actions { display: flex; gap: 4px; margin-top: 6px; }
        .btn-transfer { font-size: 10px; padding: 2px 8px; border-radius: 4px; border: 1px solid #3b82f6; color: #60a5fa; background: transparent; cursor: pointer; transition: all 0.15s; }
        .btn-transfer:hover { background: #3b82f6; color: white; }
        .host-crown { color: #f59e0b; font-size: 12px; margin-left: 4px; }
        /* Screen share indicator */
        .screen-badge { position: absolute; top: 8px; left: 8px; background: rgba(37,99,235,0.85); color: white; font-size: 10px; padding: 2px 8px; border-radius: 4px; z-index: 5; display: none; }
        /* Confirm Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 300; display: none; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #1e293b; border: 1px solid rgba(255,255,255,0.12); border-radius: 16px; padding: 28px 32px; width: 360px; text-align: center; }
        .modal-box h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .modal-box p { font-size: 13px; color: #94a3b8; margin-bottom: 20px; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal-btn { padding: 9px 24px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; }
        .modal-btn-cancel { background: #334155; color: #cbd5e1; }
        .modal-btn-cancel:hover { background: #475569; }
        .modal-btn-danger { background: #dc2626; color: white; }
        .modal-btn-danger:hover { background: #b91c1c; }
        .modal-btn-warn { background: #f59e0b; color: white; }
        .modal-btn-warn:hover { background: #d97706; }
    </style>
</head>
<body>
    <!-- Toast notification -->
    <div class="toast" id="toast"></div>

    <!-- End Meeting Modal -->
    <div class="modal-overlay" id="modal-end">
        <div class="modal-box">
            <h3>⛔ Akhiri Meeting?</h3>
            <p>Semua peserta akan dikeluarkan dan meeting akan ditutup permanen.</p>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('end')">Batal</button>
                <button class="modal-btn modal-btn-danger" onclick="doEndMeeting()">Ya, Akhiri</button>
            </div>
        </div>
    </div>

    <!-- Leave Meeting Modal -->
    <div class="modal-overlay" id="modal-leave">
        <div class="modal-box">
            <h3>🚪 Keluar Meeting?</h3>
            <p>Anda akan keluar, tapi meeting tetap berlanjut untuk peserta lain.</p>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('leave')">Batal</button>
                <button class="modal-btn modal-btn-warn" onclick="doLeaveMeeting()">Ya, Keluar</button>
            </div>
        </div>
    </div>

    <!-- Transfer Host Modal -->
    <div class="modal-overlay" id="modal-transfer">
        <div class="modal-box">
            <h3>👑 Pindahkan Host</h3>
            <p>Jadikan <strong id="transfer-target-name"></strong> sebagai host baru? Anda akan menjadi peserta biasa.</p>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('transfer')">Batal</button>
                <button class="modal-btn modal-btn-danger" onclick="doTransferHost()">Ya, Pindahkan</button>
            </div>
        </div>
    </div>

    <!-- === HEADER === -->
    <div class="meeting-header">
        <div class="left">
            <div class="shield">
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
            </div>
            <div class="info">
                <h1>{{ $meeting->title }}</h1>
                <p id="meeting-id">Meeting ID: {{ $meeting->room_name }}</p>
            </div>
        </div>
        <div class="right">
            <span class="badge badge-live">● REC</span>
            <span class="timer" id="meeting-timer">00:00:00</span>
            <span class="badge badge-live" style="animation-duration:3s">LIVE</span>
            <button class="header-btn" onclick="copyInviteLink()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Invite Link
            </button>
            <button class="header-btn" onclick="toggleFullscreen()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/></svg>
            </button>
        </div>
    </div>

    <!-- === MAIN === -->
    <div class="meeting-body">
        <!-- Video Area -->
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

        <!-- Side Panel: Participants -->
        <div class="side-panel" id="panel-participants">
            <div class="panel-header">
                <h2>Peserta (<span id="p-count">0</span>)</h2>
                <button class="panel-close" onclick="closePanel('participants')"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="panel-body" id="participants-list"></div>
        </div>

        <!-- Side Panel: Chat -->
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

    <!-- === TOOLBAR === -->
    <div class="meeting-toolbar">
        <div class="toolbar-group">
            <button class="tb-btn active" id="btn-mic" onclick="toggleMic()">
                <div class="tb-icon">
                    <svg id="ico-mic" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                    <svg id="ico-mic-off" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4M5 3l14 14m-7-7V5a3 3 0 00-5.356-1.857"/></svg>
                </div>
                <span id="lbl-mic">Mute</span>
            </button>
            <button class="tb-btn active" id="btn-cam" onclick="toggleCamera()">
                <div class="tb-icon">
                    <svg id="ico-cam" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <svg id="ico-cam-off" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <span id="lbl-cam">Video</span>
            </button>
        </div>
        <div class="toolbar-divider"></div>
        <div class="toolbar-group">
            <button class="tb-btn" id="btn-screen" onclick="toggleScreen()">
                <div class="tb-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <span>Share Screen</span>
            </button>
            <button class="tb-btn" onclick="togglePanel('participants')" id="btn-participants">
                <div class="tb-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span>Participants</span>
                <span class="tb-badge" id="badge-participants"></span>
            </button>
            <button class="tb-btn" onclick="togglePanel('chat')" id="btn-chat">
                <div class="tb-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
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
                <div class="tb-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span>Reactions</span>
            </button>
            <button class="tb-btn" onclick="raiseHand()" id="btn-hand">
                <div class="tb-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/></svg>
                </div>
                <span id="lbl-hand">Raise Hand</span>
            </button>
        </div>
        <div class="toolbar-divider"></div>
        <button class="tb-btn tb-btn-leave" onclick="leaveMeeting()">
            <div class="tb-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>
            <span>Keluar</span>
        </button>
        @if($canEnd ?? true)
        <button class="tb-btn tb-btn-end" onclick="endMeeting()">
            <div class="tb-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/></svg>
            </div>
            <span>End</span>
        </button>
        @endif
    </div>

    <script src="https://unpkg.com/livekit-client@2.9.1/dist/livekit-client.umd.js"></script>
    <script>
        const LIVEKIT_URL = '{{ $livekitUrl }}';
        const TOKEN = '{{ $token }}';
        const JOIN_URL = '{{ $meeting->join_url }}';
        const MY_NAME = '{{ addslashes($userName) }}';
        const END_MEETING_URL = '{{ $endUrl ?? route("admin.meetings.end", $meeting->id) }}';
        // Ruangan yang sama dipakai dari dashboard admin maupun Portal Kerja,
        // jadi ke mana peserta kembali setelah keluar ditentukan pemanggilnya.
        const BACK_URL = '{{ $backUrl ?? route("admin.meetings.index") }}';
        const CAN_END = {{ ($canEnd ?? true) ? 'true' : 'false' }};
        const CSRF_TOKEN = '{{ csrf_token() }}';
        let currentHost = MY_NAME; // track who is host
        let transferTargetName = '';

        let room, micOn = true, camOn = true, screenOn = false, handRaised = false;
        let currentView = 'gallery';
        let currentSpeaker = null;
        let chatUnread = 0, openPanel = null;
        const startTime = Date.now();

        // Timer
        setInterval(() => {
            const e = Math.floor((Date.now() - startTime) / 1000);
            document.getElementById('meeting-timer').textContent =
                String(Math.floor(e/3600)).padStart(2,'0') + ':' +
                String(Math.floor((e%3600)/60)).padStart(2,'0') + ':' +
                String(e%60).padStart(2,'0');
        }, 1000);

        // === LIVEKIT CONNECTION ===
        async function init() {
            room = new LivekitClient.Room({ adaptiveStream: true, dynacast: true, videoCaptureDefaults: { resolution: LivekitClient.VideoPresets.h720.resolution } });

            room.on(LivekitClient.RoomEvent.TrackSubscribed, (track, pub, p) => {
                if (track.source === LivekitClient.Track.Source.ScreenShare) { handleScreenTrack(track, p); }
                else { attachTrack(track, p); }
                updateGrid();
            });
            room.on(LivekitClient.RoomEvent.TrackUnsubscribed, (track, pub, p) => {
                if (track.source === LivekitClient.Track.Source.ScreenShare) {
                    const st = document.getElementById('tile-screen-' + p.identity);
                    if (st) st.remove();
                    track.detach();
                } else { detachTrack(track, p); }
                updateGrid();
            });
            room.on(LivekitClient.RoomEvent.ParticipantConnected, (p) => { createTile(p, false); updateGrid(); updateParticipantsList(); showToast(p.identity + ' bergabung'); });
            room.on(LivekitClient.RoomEvent.ParticipantDisconnected, (p) => { removeTile(p); updateGrid(); updateParticipantsList(); showToast(p.identity + ' keluar'); });
            room.on(LivekitClient.RoomEvent.ActiveSpeakersChanged, handleSpeakers);
            room.on(LivekitClient.RoomEvent.Disconnected, () => { window.location.href = BACK_URL; });
            room.on(LivekitClient.RoomEvent.DataReceived, handleData);
            room.on(LivekitClient.RoomEvent.TrackMuted, (pub, p) => updateTileStatus(p));
            room.on(LivekitClient.RoomEvent.TrackUnmuted, (pub, p) => updateTileStatus(p));

            try {
                await room.connect(LIVEKIT_URL, TOKEN);
            } catch (err) {
                console.error('Connection error:', err);
                showToast('Gagal terhubung ke server: ' + err.message);
                return;
            }

            // Try to enable microphone (soft fail if no device)
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

            // Try to enable camera (soft fail if no device)
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
            updateGrid();
            updateParticipantsList();
        }

        // === TILE MANAGEMENT ===
        function createTile(participant, isLocal) {
            if (document.getElementById('tile-' + participant.identity)) return;
            const tile = document.createElement('div');
            tile.id = 'tile-' + participant.identity;
            tile.className = 'video-tile';
            tile.dataset.identity = participant.identity;
            const initial = (participant.identity || '?')[0].toUpperCase();
            tile.innerHTML = `
                <div class="avatar" id="avatar-${participant.identity}"><div class="avatar-circle">${initial}</div></div>
                <video id="video-${participant.identity}" autoplay playsinline ${isLocal ? 'muted' : ''} style="display:none"></video>
                <div class="tile-overlay">
                    <div class="tile-name">
                        ${participant.identity}${isLocal ? ' (You)' : ''}
                        ${isLocal ? '<span class="host-badge">Host</span>' : ''}
                    </div>
                    <div class="tile-icons" id="icons-${participant.identity}"></div>
                </div>
            `;
            document.getElementById('video-grid').appendChild(tile);

            // Clone for filmstrip
            const fsTile = tile.cloneNode(true);
            fsTile.id = 'fs-' + participant.identity;
            fsTile.onclick = () => pinSpeaker(participant.identity);
            document.getElementById('filmstrip').appendChild(fsTile);

            // Attach existing tracks
            participant.trackPublications.forEach(pub => { if (pub.track) attachTrack(pub.track, participant); });
            updateTileStatus(participant);
        }

        function removeTile(participant) {
            ['tile-', 'fs-'].forEach(prefix => {
                const el = document.getElementById(prefix + participant.identity);
                if (el) el.remove();
            });
        }

        function attachTrack(track, participant) {
            ['', 'fs-'].forEach(prefix => {
                const id = prefix ? 'fs-' + participant.identity : participant.identity;
                if (track.kind === 'video') {
                    const v = document.getElementById('video-' + (prefix ? '' : '') + participant.identity);
                    const a = document.getElementById('avatar-' + participant.identity);
                    // Only operate on the main grid video
                    if (!prefix) {
                        if (v) { track.attach(v); v.style.display = 'block'; }
                        if (a) a.style.display = 'none';
                    }
                    // Filmstrip
                    const fsTile = document.getElementById('fs-' + participant.identity);
                    if (fsTile) {
                        const fsVideo = fsTile.querySelector('video');
                        const fsAvatar = fsTile.querySelector('.avatar');
                        if (fsVideo) { track.attach(fsVideo); fsVideo.style.display = 'block'; }
                        if (fsAvatar) fsAvatar.style.display = 'none';
                    }
                }
                if (track.kind === 'audio' && !prefix) {
                    const audioEl = track.attach();
                    audioEl.id = 'audio-' + participant.identity;
                    document.body.appendChild(audioEl);
                }
            });
        }

        function detachTrack(track, participant) {
            if (track.kind === 'video') {
                const v = document.getElementById('video-' + participant.identity);
                const a = document.getElementById('avatar-' + participant.identity);
                if (v) { track.detach(v); v.style.display = 'none'; }
                if (a) a.style.display = 'flex';
                const fsTile = document.getElementById('fs-' + participant.identity);
                if (fsTile) {
                    const fsV = fsTile.querySelector('video');
                    const fsA = fsTile.querySelector('.avatar');
                    if (fsV) { track.detach(fsV); fsV.style.display = 'none'; }
                    if (fsA) fsA.style.display = 'flex';
                }
            }
            if (track.kind === 'audio') {
                const a = document.getElementById('audio-' + participant.identity);
                if (a) a.remove();
                track.detach();
            }
        }

        function updateTileStatus(participant) {
            const icons = document.getElementById('icons-' + participant.identity);
            if (!icons) return;
            let html = '';
            const audioMuted = !participant.isMicrophoneEnabled;
            const videoMuted = !participant.isCameraEnabled;
            if (audioMuted) html += '<div class="tile-icon muted"><svg viewBox="0 0 24 24"><path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6M5 3l14 14" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"/></svg></div>';
            if (videoMuted) html += '<div class="tile-icon cam-off"><svg viewBox="0 0 24 24"><path d="M18.364 18.364A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"/></svg></div>';
            icons.innerHTML = html;
        }

        // === LAYOUT ===
        function updateGrid() {
            const grid = document.getElementById('video-grid');
            const count = grid.children.length;
            const badge = document.getElementById('badge-participants');
            badge.textContent = count;
            badge.classList.add('show');
            document.getElementById('p-count').textContent = count;

            if (currentView === 'gallery') {
                grid.classList.remove('speaker-view');
                document.getElementById('filmstrip').style.display = 'none';
                if (count <= 1) { grid.style.gridTemplateColumns = '1fr'; grid.style.gridTemplateRows = '1fr'; }
                else if (count <= 2) { grid.style.gridTemplateColumns = '1fr 1fr'; grid.style.gridTemplateRows = '1fr'; }
                else if (count <= 4) { grid.style.gridTemplateColumns = '1fr 1fr'; grid.style.gridTemplateRows = '1fr 1fr'; }
                else if (count <= 6) { grid.style.gridTemplateColumns = 'repeat(3,1fr)'; grid.style.gridTemplateRows = '1fr 1fr'; }
                else if (count <= 9) { grid.style.gridTemplateColumns = 'repeat(3,1fr)'; grid.style.gridTemplateRows = 'repeat(3,1fr)'; }
                else if (count <= 16) { grid.style.gridTemplateColumns = 'repeat(4,1fr)'; grid.style.gridTemplateRows = `repeat(${Math.ceil(count/4)},1fr)`; }
                else { grid.style.gridTemplateColumns = 'repeat(5,1fr)'; grid.style.gridTemplateRows = `repeat(${Math.ceil(count/5)},1fr)`; }
                grid.querySelectorAll('.video-tile').forEach(t => { t.style.display = 'block'; t.classList.remove('active-speaker'); });
            } else {
                grid.classList.add('speaker-view');
                document.getElementById('filmstrip').style.display = 'flex';
                const speakerId = currentSpeaker || room.localParticipant.identity;
                grid.querySelectorAll('.video-tile').forEach(t => {
                    t.classList.toggle('active-speaker', t.dataset.identity === speakerId);
                });
            }
        }

        function setView(view) {
            currentView = view;
            document.getElementById('btn-gallery').classList.toggle('active', view === 'gallery');
            document.getElementById('btn-speaker').classList.toggle('active', view === 'speaker');
            updateGrid();
        }

        function pinSpeaker(identity) { currentSpeaker = identity; updateGrid(); }

        function handleSpeakers(speakers) {
            document.querySelectorAll('#video-grid .video-tile').forEach(t => t.classList.remove('speaking'));
            speakers.forEach(p => {
                const t = document.getElementById('tile-' + p.identity);
                if (t) t.classList.add('speaking');
                if (currentView === 'speaker' && !currentSpeaker) {
                    const mainTile = document.getElementById('tile-' + p.identity);
                    if (mainTile) { document.querySelectorAll('.active-speaker').forEach(e=>e.classList.remove('active-speaker')); mainTile.classList.add('active-speaker'); }
                }
            });
        }

        // === CONTROLS ===
        async function toggleMic() {
            const newState = !micOn;
            try {
                await room.localParticipant.setMicrophoneEnabled(newState);
                micOn = newState;
            } catch (err) {
                console.warn('Microphone toggle error:', err.message);
                showToast('⚠️ Mikrofon tidak tersedia');
                micOn = false;
            }
            document.getElementById('btn-mic').classList.toggle('muted', !micOn);
            document.getElementById('ico-mic').style.display = micOn ? '' : 'none';
            document.getElementById('ico-mic-off').style.display = micOn ? 'none' : '';
            document.getElementById('lbl-mic').textContent = micOn ? 'Mute' : 'Unmute';
            updateTileStatus(room.localParticipant);
        }

        async function toggleCamera() {
            const newState = !camOn;
            try {
                await room.localParticipant.setCameraEnabled(newState);
                camOn = newState;
            } catch (err) {
                console.warn('Camera toggle error:', err.message);
                showToast('⚠️ Kamera tidak tersedia');
                camOn = false;
            }
            document.getElementById('btn-cam').classList.toggle('muted', !camOn);
            document.getElementById('ico-cam').style.display = camOn ? '' : 'none';
            document.getElementById('ico-cam-off').style.display = camOn ? 'none' : '';
            document.getElementById('lbl-cam').textContent = camOn ? 'Video' : 'Start Video';
            const av = document.getElementById('avatar-' + room.localParticipant.identity);
            const vid = document.getElementById('video-' + room.localParticipant.identity);
            if (!camOn) { if(vid)vid.style.display='none'; if(av)av.style.display='flex'; }
            else { if(vid)vid.style.display='block'; if(av)av.style.display='none'; }
        }

        async function toggleScreen() {
            if (screenOn) {
                // Stop screen share
                try { await room.localParticipant.setScreenShareEnabled(false); } catch {}
                screenOn = false;
                document.getElementById('btn-screen').classList.remove('active');
                // Remove screen share tile
                const st = document.getElementById('tile-screen-' + room.localParticipant.identity);
                if (st) st.remove();
                const fst = document.getElementById('fs-screen-' + room.localParticipant.identity);
                if (fst) fst.remove();
                showToast('🖥️ Screen share dihentikan');
            } else {
                try {
                    await room.localParticipant.setScreenShareEnabled(true);
                    screenOn = true;
                    document.getElementById('btn-screen').classList.add('active');
                    showToast('🖥️ Screen share aktif');
                    // Listen for user stopping share from browser UI
                    room.localParticipant.getTrackPublication(LivekitClient.Track.Source.ScreenShare)?.track?.mediaStreamTrack?.addEventListener('ended', () => {
                        if (screenOn) toggleScreen();
                    });
                } catch (err) {
                    screenOn = false;
                    document.getElementById('btn-screen').classList.remove('active');
                    if (!err.message.includes('denied') && !err.message.includes('cancel')) {
                        showToast('⚠️ Gagal membagi layar: ' + err.message);
                    }
                }
            }
        }

        // Handle remote screen share tracks
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

        // Modal helpers
        function openModal(id) { document.getElementById('modal-' + id).classList.add('open'); }
        function closeModal(id) { document.getElementById('modal-' + id).classList.remove('open'); }

        function leaveMeeting() { openModal('leave'); }
        function doLeaveMeeting() { closeModal('leave'); room.disconnect(); window.location.href = BACK_URL; }

        function endMeeting() { openModal('end'); }
        async function doEndMeeting() {
            closeModal('end');
            // Broadcast end signal to all participants
            if (room) {
                const data = JSON.stringify({ type: 'meeting_ended', sender: MY_NAME });
                room.localParticipant.publishData(new TextEncoder().encode(data), LivekitClient.DataPacket_Kind.RELIABLE);
                await new Promise(r => setTimeout(r, 300));
            }
            // Update DB
            try {
                await fetch(END_MEETING_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' }
                });
            } catch {}
            if (room) room.disconnect();
            window.location.href = BACK_URL;
        }

        // Transfer Host
        function promptTransferHost(targetName) {
            transferTargetName = targetName;
            document.getElementById('transfer-target-name').textContent = targetName;
            openModal('transfer');
        }
        function doTransferHost() {
            closeModal('transfer');
            currentHost = transferTargetName;
            // Broadcast host change
            const data = JSON.stringify({ type: 'host_changed', newHost: transferTargetName, sender: MY_NAME });
            room.localParticipant.publishData(new TextEncoder().encode(data), LivekitClient.DataPacket_Kind.RELIABLE);
            updateParticipantsList();
            showToast('👑 Host dipindahkan ke ' + transferTargetName);
        }

        // === SIDE PANELS ===
        function togglePanel(name) {
            if (openPanel === name) { closePanel(name); return; }
            if (openPanel) closePanel(openPanel);
            document.getElementById('panel-' + name).classList.add('open');
            openPanel = name;
            if (name === 'chat') { chatUnread = 0; document.getElementById('badge-chat').classList.remove('show'); }
            if (name === 'participants') updateParticipantsList();
        }
        function closePanel(name) {
            document.getElementById('panel-' + name).classList.remove('open');
            openPanel = null;
        }

        function updateParticipantsList() {
            const list = document.getElementById('participants-list');
            let html = '';
            const all = [room.localParticipant, ...room.remoteParticipants.values()];
            const amHost = (MY_NAME === currentHost);
            all.forEach(p => {
                const isLocal = p === room.localParticipant;
                const isHost = p.identity === currentHost;
                const micActive = p.isMicrophoneEnabled;
                const camActive = p.isCameraEnabled;
                html += `<div class="participant-item" style="flex-wrap:wrap">
                    <div class="participant-avatar" style="background:${isHost?'linear-gradient(135deg,#f59e0b,#d97706)':'linear-gradient(135deg,#3b82f6,#6366f1)'}">${(p.identity||'?')[0].toUpperCase()}</div>
                    <div class="participant-info">
                        <div class="participant-name">${p.identity}${isLocal?' (You)':''}${isHost?'<span class="host-crown">👑</span>':''}</div>
                        <div class="participant-role">${isHost?'Host':'Participant'}</div>
                    </div>
                    <div class="participant-status">
                        <div class="status-icon ${micActive?'active':'inactive'}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg></div>
                        <div class="status-icon ${camActive?'active':'inactive'}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></div>
                    </div>
                    ${amHost && !isLocal ? `<div class="participant-actions" style="width:100%;padding-left:42px"><button class="btn-transfer" onclick="promptTransferHost('${p.identity}')">👑 Jadikan Host</button></div>` : ''}
                </div>`;
            });
            list.innerHTML = html;
        }

        // === CHAT ===
        function sendChat() {
            const input = document.getElementById('chat-input');
            const msg = input.value.trim();
            if (!msg) return;
            const data = JSON.stringify({ type: 'chat', sender: MY_NAME, text: msg, time: new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}) });
            room.localParticipant.publishData(new TextEncoder().encode(data), LivekitClient.DataPacket_Kind.RELIABLE);
            appendChat(MY_NAME, msg, new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}), true);
            input.value = '';
        }

        function appendChat(sender, text, time, self) {
            const el = document.createElement('div');
            el.className = 'chat-msg' + (self ? ' self' : '');
            el.innerHTML = `<div class="msg-sender">${sender}</div><div class="msg-bubble">${text}</div><div class="msg-time">${time}</div>`;
            document.getElementById('chat-messages').appendChild(el);
            el.scrollIntoView({ behavior: 'smooth' });
        }

        // === REACTIONS ===
        function toggleReactions() {
            document.getElementById('reactions-popup').classList.toggle('open');
        }

        function sendReaction(emoji) {
            document.getElementById('reactions-popup').classList.remove('open');
            showFloatingReaction(emoji);
            const data = JSON.stringify({ type: 'reaction', sender: MY_NAME, emoji });
            room.localParticipant.publishData(new TextEncoder().encode(data), LivekitClient.DataPacket_Kind.RELIABLE);
        }

        function showFloatingReaction(emoji) {
            const el = document.createElement('div');
            el.className = 'floating-reaction';
            el.textContent = emoji;
            el.style.left = (40 + Math.random() * 20) + '%';
            el.style.bottom = '100px';
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }

        function raiseHand() {
            handRaised = !handRaised;
            document.getElementById('btn-hand').classList.toggle('active', handRaised);
            document.getElementById('lbl-hand').textContent = handRaised ? 'Lower Hand' : 'Raise Hand';
            const data = JSON.stringify({ type: 'hand', sender: MY_NAME, raised: handRaised });
            room.localParticipant.publishData(new TextEncoder().encode(data), LivekitClient.DataPacket_Kind.RELIABLE);
            showToast(handRaised ? '✋ Hand raised' : 'Hand lowered');
        }

        // === DATA HANDLER ===
        function handleData(payload, participant) {
            try {
                const data = JSON.parse(new TextDecoder().decode(payload));
                if (data.type === 'chat') {
                    appendChat(data.sender, data.text, data.time, false);
                    if (openPanel !== 'chat') {
                        chatUnread++;
                        const badge = document.getElementById('badge-chat');
                        badge.textContent = chatUnread;
                        badge.classList.add('show');
                    }
                }
                if (data.type === 'reaction') showFloatingReaction(data.emoji);
                if (data.type === 'hand') showToast(data.raised ? `✋ ${data.sender} raised hand` : `${data.sender} lowered hand`);
                if (data.type === 'meeting_ended') {
                    showToast('⛔ Meeting telah diakhiri oleh host');
                    setTimeout(() => { if(room) room.disconnect(); window.location.href = BACK_URL; }, 1500);
                }
                if (data.type === 'host_changed') {
                    currentHost = data.newHost;
                    showToast(`👑 ${data.newHost} sekarang menjadi host`);
                    updateParticipantsList();
                }
            } catch {}
        }

        // === UTILS ===
        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg; t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        function copyInviteLink() {
            navigator.clipboard.writeText(JOIN_URL).then(() => showToast('✅ Invite link copied!'));
        }

        function toggleFullscreen() {
            if (document.fullscreenElement) document.exitFullscreen();
            else document.documentElement.requestFullscreen();
        }

        // Start
        init();
    </script>
</body>
</html>
