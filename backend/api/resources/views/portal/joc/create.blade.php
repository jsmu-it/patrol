@extends('layouts.portal')
@section('title', 'Buat Kartu JOC')

@section('content')
<div class="max-w-3xl mx-auto" x-data="kartuJoc()">
    <div class="mb-5">
        <a href="{{ route('portal.joc.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar kartu</a>
        <h1 class="text-xl font-bold text-gray-900 mt-1">JSMU Observation Card</h1>
        <p class="text-sm text-gray-600">Kartu pengamatan keselamatan. Setelah dikirim, divisi HSE langsung diberi tahu.</p>
    </div>

    <form action="{{ route('portal.joc.store') }}" method="POST" enctype="multipart/form-data" @submit="siapkanTtd">
        @csrf

        {{-- Data pengamatan --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-4">Data Pengamatan</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required value="{{ old('tanggal', now()->format('Y-m-d')) }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam <span class="text-red-500">*</span></label>
                    <input type="time" name="jam" required value="{{ old('jam', now()->format('H:i')) }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="lokasi" required value="{{ old('lokasi') }}" placeholder="mis. Head Office lantai 2" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                    <select name="project_id" class="pt-input">
                        <option value="">— Tidak terkait project —</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ old('project_id', auth()->user()->active_project_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama pengamat</label>
                    <input type="text" value="{{ auth()->user()->name }}" disabled class="pt-input" style="background:#F3F5F8">
                </div>
            </div>
        </div>

        {{-- Temuan observasi --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Temuan Observasi</div>
            <p class="text-xs text-gray-500 mb-3">Pilih salah satu.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($jenis as $nilai => $label)
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer"
                           :class="jenis === '{{ $nilai }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                        <input type="radio" name="jenis_temuan" value="{{ $nilai }}" x-model="jenis" required>
                        <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Kategori --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Kategori Temuan dari Aktifitas Observasi</div>
            <p class="text-xs text-gray-500 mb-3">Boleh lebih dari satu.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($kategori as $nilai => [$label, $tanya])
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="kategori[]" value="{{ $nilai }}" class="mt-0.5"
                               @checked(in_array($nilai, old('kategori', [])))
                               @if($nilai === 'lain_lain') x-model="lainLain" @endif>
                        <span>
                            <span class="block text-sm font-medium text-gray-900">{{ $label }}</span>
                            <span class="block text-xs text-gray-500 mt-0.5">{{ $tanya }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <div x-show="lainLain" x-cloak class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sebutkan kategori lain</label>
                <input type="text" name="kategori_lain" value="{{ old('kategori_lain') }}" class="pt-input">
            </div>
        </div>

        {{-- Rincian --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-4">Rincian</div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rincian Temuan Pengamatan <span class="text-red-500">*</span></label>
                <textarea name="rincian_temuan" rows="4" required class="pt-input"
                          placeholder="Apa yang Anda lihat di lokasi?">{{ old('rincian_temuan') }}</textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rincian Tindakan / Rekomendasi <span class="text-red-500">*</span></label>
                <textarea name="rincian_tindakan" rows="4" required class="pt-input"
                          placeholder="Apa yang sudah dilakukan atau disarankan?">{{ old('rincian_tindakan') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan tambahan</label>
                <textarea name="catatan" rows="2" class="pt-input">{{ old('catatan') }}</textarea>
            </div>
        </div>

        {{-- Foto bukti --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Foto Bukti</div>
            <p class="text-xs text-gray-500 mb-3">Maksimal 2 foto, tiap foto paling besar 8 MB. Boleh dikosongkan.</p>

            <input type="file" name="foto[]" id="input-foto" accept="image/*" multiple class="hidden"
                   @change="pilihFoto($event)">
            <button type="button" @click="document.getElementById('input-foto').click()"
                    class="pt-btn pt-btn-garis" x-show="foto.length < 2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-text="foto.length === 0 ? 'Pilih Foto' : 'Tambah Foto'"></span>
            </button>

            <div class="grid grid-cols-2 gap-3 mt-3" x-show="foto.length > 0" x-cloak>
                <template x-for="(f, i) in foto" :key="i">
                    <div class="relative">
                        <img :src="f.url" class="w-full rounded-lg border border-gray-200" style="height:150px;object-fit:cover">
                        <div class="text-xs text-gray-500 mt-1 truncate" x-text="f.nama"></div>
                    </div>
                </template>
            </div>
            <p class="text-xs text-gray-500 mt-2" x-show="foto.length >= 2" x-cloak>
                Sudah 2 foto. Pilih ulang bila ingin menggantinya.
            </p>
        </div>

        {{-- Tanda tangan --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-1">Tanda Tangan Pengamat</div>
            <p class="text-xs text-gray-500 mb-3">Tanda tangani dengan jari atau tetikus. Boleh dikosongkan.</p>
            <canvas id="kanvas-ttd" class="border border-gray-300 rounded-lg bg-white w-full" height="150" style="touch-action:none"></canvas>
            <input type="hidden" name="ttd" id="data-ttd" value="{{ old('ttd') }}">
            <button type="button" @click="bersihkanTtd" class="pt-btn pt-btn-garis pt-btn-kecil mt-2">Hapus tanda tangan</button>
        </div>

        <div class="px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-900 text-xs mb-4">
            Jika temuan bersifat <strong>emergency</strong> dan berpotensi menyebabkan kecelakaan saat pengamatan berlangsung,
            pengamat <strong>wajib menghentikan</strong> pekerjaan tersebut.
        </div>

        <div class="flex gap-2">
            <button type="submit" class="pt-btn pt-btn-utama">Kirim Kartu</button>
            <a href="{{ route('portal.joc.index') }}" class="pt-btn pt-btn-garis">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function kartuJoc() {
    return {
        jenis: '{{ old('jenis_temuan') }}',
        foto: [],

        // Dibatasi dua berkas; kelebihannya dibuang dengan pemberitahuan.
        pilihFoto(e) {
            const dipilih = Array.from(e.target.files);
            if (dipilih.length > 2) {
                alert('Maksimal 2 foto. Hanya dua pertama yang dipakai.');
                const dt = new DataTransfer();
                dipilih.slice(0, 2).forEach(f => dt.items.add(f));
                e.target.files = dt.files;
            }
            this.foto = Array.from(e.target.files).map(f => ({
                nama: f.name,
                url: URL.createObjectURL(f),
            }));
        },
        lainLain: {{ in_array('lain_lain', old('kategori', [])) ? 'true' : 'false' }},
        bersihkanTtd() {
            const c = document.getElementById('kanvas-ttd');
            c.getContext('2d').clearRect(0, 0, c.width, c.height);
            document.getElementById('data-ttd').value = '';
        },
        siapkanTtd() {
            const c = document.getElementById('kanvas-ttd');
            if (window.__adaCoretan) document.getElementById('data-ttd').value = c.toDataURL('image/png');
        },
    }
}

// Kanvas tanda tangan sederhana: mendukung tetikus dan sentuh.
document.addEventListener('DOMContentLoaded', function () {
    const c = document.getElementById('kanvas-ttd');
    if (!c) return;
    c.width = c.offsetWidth;
    const ctx = c.getContext('2d');
    ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#111827';
    let gambar = false;

    const titik = (e) => {
        const r = c.getBoundingClientRect();
        const t = e.touches ? e.touches[0] : e;
        return { x: t.clientX - r.left, y: t.clientY - r.top };
    };
    const mulai = (e) => { e.preventDefault(); gambar = true; const p = titik(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); };
    const garis = (e) => { if (!gambar) return; e.preventDefault(); const p = titik(e); ctx.lineTo(p.x, p.y); ctx.stroke(); window.__adaCoretan = true; };
    const henti = () => { gambar = false; };

    c.addEventListener('mousedown', mulai);  c.addEventListener('mousemove', garis);
    c.addEventListener('touchstart', mulai); c.addEventListener('touchmove', garis);
    window.addEventListener('mouseup', henti); c.addEventListener('touchend', henti);
});
</script>
@endpush
