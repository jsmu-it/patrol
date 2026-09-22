@extends('layouts.portal')
@section('title', 'Buat RPTK')

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ alasan: '{{ old('alasan') }}', statusK: '{{ old('status_karyawan') }}' }">
    <div class="mb-5">
        <a href="{{ route('portal.rptk.index') }}" class="text-sm text-gray-500 hover:text-gray-900">&larr; Daftar RPTK</a>
        <h1 class="text-xl font-bold text-gray-900 mt-1">Rencana Permintaan Tenaga Kerja</h1>
        <p class="text-sm text-gray-600">Formulir HR/FM-04-01. Setelah dikirim, HR &amp; GA Manager langsung diberi tahu.</p>
    </div>

    <form action="{{ route('portal.rptk.store') }}" method="POST">
        @csrf

        {{-- A. Permintaan --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-4">A. Permintaan</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama pemohon</label>
                    <input type="text" value="{{ auth()->user()->name }}" disabled class="pt-input" style="background:#F3F5F8">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah kebutuhan karyawan <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah" min="1" max="999" required value="{{ old('jumlah', 1) }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Divisi / Dept <span class="text-red-500">*</span></label>
                    @php($divisiTerpilih = old('divisi', auth()->user()->profile->division ?? ''))
                    <select name="divisi" required class="pt-input">
                        <option value="">— pilih divisi —</option>
                        @foreach($daftarDivisi as $d)
                            <option value="{{ $d }}" {{ $divisiTerpilih === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ref. dokumen dari</label>
                    <input type="text" name="ref_dokumen" value="{{ old('ref_dokumen') }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Untuk lokasi kerja <span class="text-red-500">*</span></label>
                    <select name="lokasi_kerja" required class="pt-input">
                        <option value="">— pilih lokasi —</option>
                        @foreach($daftarLokasi as $l)
                            <option value="{{ $l }}" {{ old('lokasi_kerja') === $l ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. &amp; tanggal dokumen</label>
                    <input type="text" value="{{ \App\Models\RptkRequest::nomorDokumen() }}" disabled class="pt-input" style="background:#F3F5F8">
                    <p class="text-xs text-gray-500 mt-1">Terisi otomatis dari kode formulir.</p>
                </div>
            </div>
        </div>

        {{-- Alasan & status --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-3">Alasan Permintaan</div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                @foreach(\App\Models\RptkRequest::daftarAlasan() as $n => $l)
                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer text-sm"
                           :class="alasan === '{{ $n }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                        <input type="radio" name="alasan" value="{{ $n }}" x-model="alasan" required>
                        <span>{{ $l }}</span>
                    </label>
                @endforeach
            </div>
            <input type="text" name="alasan_lain" x-show="alasan === 'lain'" x-cloak placeholder="Sebutkan alasan lain"
                   value="{{ old('alasan_lain') }}" class="pt-input">

            <div class="pt-judul mt-6 mb-3">Status Karyawan</div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                @foreach(\App\Models\RptkRequest::daftarStatusKaryawan() as $n => $l)
                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer text-sm"
                           :class="statusK === '{{ $n }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                        <input type="radio" name="status_karyawan" value="{{ $n }}" x-model="statusK" required>
                        <span>{{ $l }}</span>
                    </label>
                @endforeach
            </div>
            <input type="text" name="status_karyawan_lain" x-show="statusK === 'lain'" x-cloak placeholder="Sebutkan status lain"
                   value="{{ old('status_karyawan_lain') }}" class="pt-input">
        </div>

        {{-- Spesifikasi jabatan --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-4">Spesifikasi Jabatan</div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="jabatan" required value="{{ old('jabatan') }}" placeholder="mis. Staff IT Support" class="pt-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Uraian tugas &amp; tanggung jawab <span class="text-red-500">*</span></label>
                <textarea name="uraian_tugas" rows="6" required class="pt-input"
                          placeholder="Tulis satu tugas per baris.">{{ old('uraian_tugas') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Satu baris satu tugas — nanti dicetak bernomor di dokumen.</p>
            </div>
        </div>

        {{-- B. Spesifikasi tenaga kerja --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-4">B. Spesifikasi Tenaga Kerja yang Diharapkan</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tinggi badan</label>
                    <input type="text" name="tinggi_badan" value="{{ old('tinggi_badan') }}" placeholder="mis. 165 s/d 180 cm" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berat badan</label>
                    <input type="text" name="berat_badan" value="{{ old('berat_badan') }}" placeholder="mis. 55 s/d 80 kg" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usia</label>
                    <input type="text" name="usia" value="{{ old('usia') }}" placeholder="mis. 20 s/d 35" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis kelamin</label>
                    <select name="jenis_kelamin" class="pt-input">
                        @foreach(\App\Models\RptkRequest::daftarKelamin() as $n => $l)
                            <option value="{{ $n }}" {{ old('jenis_kelamin') === $n ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan formal</label>
                    <input type="text" name="pendidikan_formal" value="{{ old('pendidikan_formal') }}" placeholder="mis. Diploma" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keahlian khusus</label>
                    <input type="text" name="keahlian_khusus" value="{{ old('keahlian_khusus') }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan non formal</label>
                    <input type="text" name="pendidikan_non_formal" value="{{ old('pendidikan_non_formal') }}" class="pt-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pengalaman</label>
                    <input type="text" name="pengalaman" value="{{ old('pengalaman') }}" placeholder="mis. 0-1 Tahun" class="pt-input">
                </div>
            </div>
        </div>

        {{-- Rekomendasi & catatan --}}
        <div class="pt-card pt-card-pad mb-4">
            <div class="pt-judul mb-3">Rekomendasi Sumber Rekrutmen</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                @foreach(\App\Models\RptkRequest::daftarSumber() as $n => $l)
                    <label class="flex items-center gap-2 p-3 border border-gray-200 rounded-lg cursor-pointer text-sm hover:bg-gray-50">
                        <input type="radio" name="sumber" value="{{ $n }}" {{ old('sumber', 'eksternal') === $n ? 'checked' : '' }} required>
                        <span>{{ $l }}</span>
                    </label>
                @endforeach
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / keterangan</label>
                <textarea name="catatan" rows="3" class="pt-input">{{ old('catatan') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal efektif bekerja</label>
                <input type="text" name="tanggal_efektif" value="{{ old('tanggal_efektif', 'ASAP') }}" class="pt-input">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="pt-btn pt-btn-utama">Kirim Permintaan</button>
            <a href="{{ route('portal.rptk.index') }}" class="pt-btn pt-btn-garis">Batal</a>
        </div>
    </form>
</div>
@endsection
