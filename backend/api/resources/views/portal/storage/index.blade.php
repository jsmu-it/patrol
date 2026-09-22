@extends('layouts.portal')
@section('title', 'Penyimpanan Data')

@section('content')
@php $persen = $kuota > 0 ? min(100, round($terpakai / $kuota * 100, 1)) : 0; @endphp

<div x-data="{ ubah: null, pindah: null, bagi: null, buatFolder: false }"
     class="lg:grid lg:grid-cols-4 lg:gap-6">

    {{-- ================= Sidebar: folder divisi yang dibagikan ================= --}}
    <aside class="lg:col-span-1 mb-6 lg:mb-0">
        <div class="pt-card pt-card-pad lg:sticky lg:top-20">
            <div class="text-sm font-semibold text-gray-900 mb-1">Folder Divisi</div>
            <p class="text-xs text-gray-500 mb-3">Folder yang dibagikan rekan kerja.</p>

            @forelse($folderDivisi as $divisi => $daftar)
                <div class="mb-3">
                    <div class="pt-side-label">{{ $divisi }}</div>
                    <ul class="space-y-0.5">
                        @foreach($daftar as $f)
                            <li>
                                <a href="{{ $f->user_id === auth()->id() ? route('portal.storage.index', ['folder' => $f->id]) : route('portal.storage.divisi', $f) }}"
                                   class="pt-side-item">
                                    <svg class="w-4 h-4 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    <span class="truncate">{{ $f->name }}</span>
                                    @if($f->pakaiSandi())
                                        <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="text-xs text-gray-500 py-2">Belum ada folder yang dibagikan.</p>
            @endforelse

            <a href="{{ route('portal.storage.sampah') }}" class="mt-3 pt-3 border-t border-gray-100 block text-sm text-gray-600 hover:text-gray-900">Tempat Sampah</a>
        </div>
    </aside>

    <div class="lg:col-span-3">

    {{-- Kepala: remah roti + aksi --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-gray-900">Penyimpanan Data</h1>
            <nav class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-1">
                <a href="{{ route('portal.storage.index') }}" class="hover:text-gray-900">Semua berkas</a>
                @foreach($jejak as $j)
                    <span>/</span>
                    <a href="{{ route('portal.storage.index', ['folder' => $j->id]) }}" class="hover:text-gray-900 truncate">{{ $j->name }}</a>
                @endforeach
            </nav>
        </div>

        <div class="flex items-center gap-2">
            {{-- Berkas lepas maupun satu folder utuh; keduanya lewat jalur yang sama --}}
            <input type="file" id="input-berkas" multiple class="hidden">
            <input type="file" id="input-folder" webkitdirectory directory multiple class="hidden">

            <button type="button" onclick="document.getElementById('input-berkas').click()"
                    class="pt-btn pt-btn-utama">Unggah Berkas</button>
            <button type="button" onclick="document.getElementById('input-folder').click()"
                    class="pt-btn pt-btn-garis whitespace-nowrap">Unggah Folder</button>

            <button type="button" @click="buatFolder = !buatFolder"
                    class="pt-btn pt-btn-garis whitespace-nowrap">Buat Folder</button>
        </div>
    </div>

    {{-- Panel buat folder --}}
    <div x-show="buatFolder" x-cloak class="bg-white border border-gray-200 rounded-xl p-4 mb-4"
         x-data="{ mode: 'privat' }">
        <form action="{{ route('portal.storage.folder') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $folder?->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama folder</label>
                <input type="text" name="name" required placeholder="mis. Laporan Bulanan"
                       class="pt-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Siapa yang bisa membuka</label>
                <select name="share_mode" x-model="mode"
                        class="pt-input">
                    @foreach(\App\Models\PortalItem::modeBerbagi() as $nilai => $label)
                        <option value="{{ $nilai }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="mode === 'divisi'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">Divisi yang boleh melihat</label>
                <select name="share_division" class="pt-input">
                    <option value="">— Pilih divisi —</option>
                    @foreach($daftarDivisi as $d)
                        <option value="{{ $d }}" {{ (auth()->user()->profile->division ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Hanya anggota divisi ini yang melihat folder di sidebar dan bisa membukanya.</p>
            </div>
            <div x-show="mode === 'tautan'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata sandi folder <span class="text-gray-400 font-normal">(opsional)</span></label>
                <input type="text" name="share_password" minlength="4" maxlength="64" placeholder="Kosongkan bila tanpa sandi"
                       class="pt-input">
                <p class="mt-1 text-xs text-gray-500">Siapa pun yang punya tautannya bisa membuka folder ini. Beri sandi bila isinya sensitif.</p>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="pt-btn pt-btn-utama">Buat</button>
                <button type="button" @click="buatFolder = false" class="pt-btn pt-btn-garis">Batal</button>
            </div>
        </form>
    </div>

    {{-- Pencarian + kuota --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <form action="{{ route('portal.storage.index') }}" method="GET" class="flex-1 min-w-0">
            <input type="text" name="q" value="{{ $cari }}" placeholder="Cari berkas atau folder..."
                   class="pt-input">
        </form>
        <div class="text-xs text-gray-600 whitespace-nowrap">
            {{ \App\Models\PortalItem::formatUkuran($terpakai) }} / {{ \App\Models\PortalItem::formatUkuran($kuota) }}
            <div class="w-32 bg-gray-100 rounded-full h-1.5 mt-1">
                <div class="bg-blue-700 h-1.5 rounded-full" style="width: {{ $persen }}%"></div>
            </div>
        </div>
    </div>

    @if($cari !== '')
        <p class="text-sm text-gray-600 mb-3">{{ $items->count() }} hasil untuk "<span class="font-medium">{{ $cari }}</span>". <a href="{{ route('portal.storage.index') }}" class="text-blue-700 hover:underline">Bersihkan</a></p>
    @endif

    {{-- Daftar --}}
    <div class="pt-card pt-card-list">
        @forelse($items as $item)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
                @php
                    $jenis = $item->jenisRingkas();
                    $warna = [
                        'folder' => 'bg-yellow-50 text-yellow-700', 'gambar' => 'bg-pink-50 text-pink-700',
                        'video' => 'bg-purple-50 text-purple-700', 'audio' => 'bg-indigo-50 text-indigo-700',
                        'pdf' => 'bg-red-50 text-red-700', 'dokumen' => 'bg-blue-50 text-blue-700',
                        'lembar' => 'bg-green-50 text-green-700', 'presentasi' => 'bg-yellow-50 text-yellow-700',
                        'arsip' => 'bg-gray-100 text-gray-700', 'aplikasi' => 'bg-green-50 text-green-700',
                    ][$jenis] ?? 'bg-gray-100 text-gray-600';
                @endphp

                <span class="w-9 h-9 rounded-lg {{ $warna }} flex items-center justify-center flex-shrink-0">
                    @if($item->isFolder())
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    @if($item->isFolder())
                        <a href="{{ route('portal.storage.index', ['folder' => $item->id]) }}" class="text-sm font-medium text-gray-900 hover:text-blue-800 truncate block">{{ $item->name }}</a>
                    @else
                        <a href="{{ \App\Http\Controllers\Portal\SuntingDokumenController::jenis($item) ? route('portal.storage.sunting', $item) : route('portal.storage.unduh', $item) }}"
                           class="text-sm font-medium text-gray-900 hover:text-blue-800 truncate block">{{ $item->name }}</a>
                    @endif
                    <div class="text-xs text-gray-500 flex items-center gap-2 flex-wrap">
                        <span>{{ $item->isFolder() ? 'Folder' : $item->ukuranTerbaca() }} &middot; {{ $item->updated_at->diffForHumans() }}</span>
                        @if($item->isFolder() && $item->dibagikan())
                            <span class="px-1.5 py-0.5 rounded bg-green-50 text-green-700 border border-green-200">{{ $item->labelBerbagi() }}</span>
                        @endif
                    </div>
                </div>

                <div class="relative flex items-center gap-1 flex-shrink-0" x-data="{ menu: false }">
                    @if(! $item->isFolder())
                        @if(\App\Http\Controllers\Portal\SuntingDokumenController::jenis($item))
                            {{-- Word, Excel, PowerPoint, dan PDF bisa dibuka langsung tanpa diunduh --}}
                            <a href="{{ route('portal.storage.sunting', $item) }}" class="px-2 py-1 text-xs text-blue-700 hover:underline">Buka</a>
                        @endif
                        <a href="{{ route('portal.storage.unduh', $item) }}" class="px-2 py-1 text-xs text-gray-500 hover:underline">Unduh</a>
                    @endif
                    <button @click="menu = !menu" class="px-2 py-1 text-gray-500 hover:text-gray-900 rounded" aria-label="Aksi lain">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
                    </button>
                    <div x-show="menu" x-cloak @click.away="menu = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 transform -translate-y-1"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="absolute right-0 top-full mt-1 w-44 pt-card py-1 z-30">
                        <button type="button" @click="ubah = {{ $item->id }}; menu = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50">Ubah nama</button>
                        <button type="button" @click="pindah = {{ $item->id }}; menu = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50">Pindahkan</button>
                        @if($item->isFolder())
                            <button type="button" @click="bagi = {{ $item->id }}; menu = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50">Bagikan</button>
                        @endif
                        <form action="{{ route('portal.storage.hapus', $item) }}" method="POST"
                              onsubmit="return confirm('Pindahkan {{ addslashes($item->name) }} ke tempat sampah?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Ubah nama --}}
            <div x-show="ubah === {{ $item->id }}" x-cloak class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                <form action="{{ route('portal.storage.nama', $item) }}" method="POST" class="flex flex-wrap items-center gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $item->name }}" required
                           class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button type="submit" class="pt-btn pt-btn-utama pt-btn-kecil">Simpan</button>
                    <button type="button" @click="ubah = null" class="pt-btn pt-btn-garis pt-btn-kecil">Batal</button>
                </form>
            </div>

            {{-- Bagikan --}}
            @if($item->isFolder())
            <div x-show="bagi === {{ $item->id }}" x-cloak class="px-4 py-3 bg-gray-50 border-b border-gray-100"
                 x-data="{ mode: '{{ $item->share_mode }}' }">
                <form action="{{ route('portal.storage.berbagi', $item) }}" method="POST" class="space-y-3">
                    @csrf @method('PUT')
                    <div class="flex flex-wrap items-center gap-2">
                        <select name="share_mode" x-model="mode" class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @foreach(\App\Models\PortalItem::modeBerbagi() as $nilai => $label)
                                <option value="{{ $nilai }}" {{ $item->share_mode === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="pt-btn pt-btn-utama pt-btn-kecil">Simpan</button>
                        <button type="button" @click="bagi = null" class="pt-btn pt-btn-garis pt-btn-kecil">Tutup</button>
                    </div>

                    <div x-show="mode === 'divisi'" x-cloak>
                        <select name="share_division" class="pt-input">
                            <option value="">— Pilih divisi —</option>
                            @foreach($daftarDivisi as $d)
                                <option value="{{ $d }}" {{ $item->share_division === $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Hanya anggota divisi ini yang bisa membuka folder.</p>
                    </div>

                    <div x-show="mode === 'tautan'" x-cloak class="space-y-2">
                        <input type="text" name="share_password" minlength="4" maxlength="64"
                               placeholder="{{ $item->pakaiSandi() ? 'Isi untuk mengganti sandi' : 'Kata sandi (opsional)' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @if($item->pakaiSandi())
                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                <input type="checkbox" name="hapus_sandi" value="1" class="rounded border-gray-300">
                                Hapus kata sandi folder ini
                            </label>
                        @endif
                    </div>
                </form>

                @if($item->share_mode === \App\Models\PortalItem::BAGI_TAUTAN && $item->share_token)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tautan berbagi</label>
                        <div class="flex gap-2">
                            <input type="text" readonly value="{{ route('portal.share.show', $item->share_token) }}"
                                   id="tautan-{{ $item->id }}"
                                   class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white">
                            <button type="button"
                                    onclick="const t=document.getElementById('tautan-{{ $item->id }}'); t.select(); document.execCommand('copy'); this.textContent='Tersalin';"
                                    class="px-3 py-2 rounded-lg border border-gray-300 text-xs whitespace-nowrap hover:bg-white">Salin</button>
                        </div>
                        @if($item->pakaiSandi())
                            <p class="mt-1 text-xs text-gray-500">Folder ini bersandi — penerima tautan harus memasukkannya.</p>
                        @endif
                    </div>
                @endif
            </div>
            @endif

            {{-- Pindahkan --}}
            <div x-show="pindah === {{ $item->id }}" x-cloak class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                <form action="{{ route('portal.storage.pindah', $item) }}" method="POST" class="flex flex-wrap items-center gap-2">
                    @csrf @method('PUT')
                    <select name="parent_id" class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Semua berkas (akar)</option>
                        @foreach($semuaFolder as $f)
                            @if($f->id !== $item->id)
                                <option value="{{ $f->id }}" {{ $item->parent_id == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="submit" class="pt-btn pt-btn-utama pt-btn-kecil">Pindahkan</button>
                    <button type="button" @click="pindah = null" class="pt-btn pt-btn-garis pt-btn-kecil">Batal</button>
                </form>
            </div>
        @empty
            <div class="px-4 py-16 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                <p class="text-sm text-gray-600">{{ $cari !== '' ? 'Tidak ada yang cocok dengan pencarian.' : 'Folder ini masih kosong.' }}</p>
                @if($cari === '')
                    <p class="text-xs text-gray-500 mt-1">Unggah berkas atau buat folder lewat tombol di atas.</p>
                @endif
            </div>
        @endforelse
    </div>

    <p class="mt-3 text-xs text-gray-500">
        Berkas yang dihapus masuk <a href="{{ route('portal.storage.sampah') }}" class="text-blue-700 hover:underline">tempat sampah</a> dan bisa dipulihkan.
        Ukuran maksimal per berkas 100 MB.
    </p>
    </div>{{-- /kolom isi --}}
</div>

{{-- Lapisan yang muncul saat berkas diseret ke halaman --}}
<div id="tirai-seret" class="fixed inset-0 z-50 hidden items-center justify-center"
     style="background:rgba(15,37,68,.55); backdrop-filter:blur(2px);">
    <div class="bg-white rounded-xl px-8 py-10 text-center shadow-2xl">
        <svg class="w-12 h-12 mx-auto text-blue-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.9A5 5 0 1115.9 6H16a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        <div class="text-base font-semibold text-gray-900">Lepaskan di sini</div>
        <div class="text-sm text-gray-500 mt-1">Berkas maupun folder, isinya ikut tersusun</div>
    </div>
</div>

{{-- Bilah kemajuan unggahan --}}
<div id="bilah-unggah" class="fixed inset-x-0 bottom-0 z-50 hidden border-t border-gray-200 bg-white px-4 py-3 shadow-lg">
    <div class="mx-auto max-w-3xl">
        <div class="flex items-center justify-between text-sm text-gray-700 mb-2">
            <span id="unggah-teks">Menyiapkan…</span>
            <span id="unggah-angka" class="text-gray-500"></span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-gray-200 overflow-hidden">
            <div id="unggah-garis" class="h-full bg-blue-600" style="width:0%; transition:width .2s"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const URL_UNGGAH = @json(route('portal.storage.unggah'));
    const CSRF       = @json(csrf_token());
    const INDUK      = @json($folder?->id);

    // Batas satu kiriman: PHP menerima 20 berkas dan 100 MB sekali jalan, jadi
    // folder besar dipecah menjadi beberapa kiriman berurutan.
    const MAKS_BERKAS_SEKALI = 20;
    const MAKS_BYTE_SEKALI   = 80 * 1024 * 1024;

    const tirai  = document.getElementById('tirai-seret');
    const bilah  = document.getElementById('bilah-unggah');
    const teks   = document.getElementById('unggah-teks');
    const angka  = document.getElementById('unggah-angka');
    const garis  = document.getElementById('unggah-garis');

    // --- pemilihan lewat tombol ---
    document.getElementById('input-berkas')?.addEventListener('change', (e) => {
        kirim(Array.from(e.target.files).map(f => ({ file: f, jalur: f.name })));
    });
    document.getElementById('input-folder')?.addEventListener('change', (e) => {
        kirim(Array.from(e.target.files).map(f => ({ file: f, jalur: f.webkitRelativePath || f.name })));
    });

    // --- seret & lepas ---
    let hitungSeret = 0;
    window.addEventListener('dragenter', (e) => {
        if (! Array.from(e.dataTransfer?.types || []).includes('Files')) return;
        hitungSeret++; tirai.classList.remove('hidden'); tirai.classList.add('flex');
    });
    window.addEventListener('dragover', (e) => e.preventDefault());
    window.addEventListener('dragleave', () => {
        if (--hitungSeret <= 0) { hitungSeret = 0; tutupTirai(); }
    });
    window.addEventListener('drop', async (e) => {
        e.preventDefault(); hitungSeret = 0; tutupTirai();
        const isi = await bacaSeretan(e.dataTransfer);
        if (isi.length) kirim(isi);
    });

    function tutupTirai() { tirai.classList.add('hidden'); tirai.classList.remove('flex'); }

    /**
     * Membaca isi seretan. Folder yang dilepas ditelusuri sampai ke dalam
     * memakai antarmuka entri peramban, supaya susunannya ikut terbawa.
     */
    async function bacaSeretan(dt) {
        const entri = Array.from(dt.items || [])
            .map(i => i.webkitGetAsEntry?.())
            .filter(Boolean);

        if (! entri.length) {
            return Array.from(dt.files || []).map(f => ({ file: f, jalur: f.name }));
        }

        const hasil = [];
        for (const e of entri) await telusuri(e, '', hasil);
        return hasil;
    }

    function telusuri(entri, awalan, hasil) {
        return new Promise((selesai) => {
            if (entri.isFile) {
                entri.file((f) => { hasil.push({ file: f, jalur: awalan + f.name }); selesai(); }, selesai);
                return;
            }
            if (! entri.isDirectory) return selesai();

            const pembaca = entri.createReader();
            const kumpul = [];
            const baca = () => pembaca.readEntries(async (bagian) => {
                // readEntries hanya mengembalikan sebagian isi tiap panggilan.
                if (bagian.length) { kumpul.push(...bagian); baca(); return; }
                for (const anak of kumpul) await telusuri(anak, awalan + entri.name + '/', hasil);
                selesai();
            }, selesai);
            baca();
        });
    }

    // --- pengiriman bertahap ---
    async function kirim(daftar) {
        if (! daftar.length) return;

        const kelompok = [];
        let sekarang = [], byte = 0;
        for (const b of daftar) {
            if (sekarang.length >= MAKS_BERKAS_SEKALI || (byte + b.file.size) > MAKS_BYTE_SEKALI) {
                if (sekarang.length) kelompok.push(sekarang);
                sekarang = []; byte = 0;
            }
            sekarang.push(b); byte += b.file.size;
        }
        if (sekarang.length) kelompok.push(sekarang);

        bilah.classList.remove('hidden');
        let terkirim = 0;

        for (const [nomor, kel] of kelompok.entries()) {
            teks.textContent = 'Mengunggah ' + daftar.length + ' berkas…';
            angka.textContent = terkirim + '/' + daftar.length;
            garis.style.width = Math.round((terkirim / daftar.length) * 100) + '%';

            const data = new FormData();
            data.append('_token', CSRF);
            data.append('async', '1');
            if (INDUK) data.append('parent_id', INDUK);
            kel.forEach(b => { data.append('files[]', b.file); data.append('paths[]', b.jalur); });

            try {
                const res = await fetch(URL_UNGGAH, {
                    method: 'POST', body: data,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (! res.ok) {
                    const j = await res.json().catch(() => ({}));
                    throw new Error(j.message || ('Gagal pada kiriman ke-' + (nomor + 1)));
                }
            } catch (err) {
                teks.textContent = 'Gagal: ' + err.message;
                garis.style.background = '#dc2626';
                return;
            }

            terkirim += kel.length;
        }

        garis.style.width = '100%';
        teks.textContent = 'Selesai, memuat ulang daftar…';
        angka.textContent = daftar.length + '/' + daftar.length;
        setTimeout(() => window.location.reload(), 600);
    }
})();
</script>
@endpush

@endsection
