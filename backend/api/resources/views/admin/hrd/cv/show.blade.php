@extends('layouts.admin')

@section('title', 'CV - ' . $user->name)
@section('page_title', 'CV Karyawan')

@push('styles')
<style>
    @media print {
        body * { visibility: hidden; }
        #cv-content, #cv-content * { visibility: visible; }
        #cv-content { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
@php $profile = $user->profile; @endphp

<div class="mb-4 flex gap-2 no-print">
    <a href="{{ route('admin.hrd.cv.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">&larr; Kembali</a>
    <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Print</button>
    <a href="{{ route('admin.hrd.cv.pdf', $user) }}" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">Download PDF</a>
</div>

<div id="cv-content" class="bg-white rounded-lg shadow p-8 max-w-4xl mx-auto">
    <div class="flex items-start gap-6 mb-6 border-b pb-6">
        <div class="flex-shrink-0">
            @if($profile && $profile->profile_photo_path)
                <img src="{{ asset('storage/' . $profile->profile_photo_path) }}" alt="Foto Profil" class="w-32 h-40 object-cover rounded border">
            @else
                <div class="w-32 h-40 bg-gray-200 rounded border flex items-center justify-center text-gray-400 text-sm">No Photo</div>
            @endif
        </div>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
            <p class="text-gray-600">{{ $profile->position ?? '-' }} - {{ $profile->division ?? '-' }}</p>
            <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div><span class="text-gray-500">NIP:</span> {{ $profile->nip ?? '-' }}</div>
                <div><span class="text-gray-500">Project:</span> {{ $user->activeProject->name ?? '-' }}</div>
                <div><span class="text-gray-500">Email:</span> {{ $user->email ?? $profile->personal_email ?? '-' }}</div>
                <div><span class="text-gray-500">No. HP:</span> {{ $profile->phone_number ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Data Pribadi</h2>
            <table class="w-full">
                <tr><td class="py-1 text-gray-500 w-1/3">Tempat, Tgl Lahir</td><td class="py-1">{{ $profile->birth_city ?? '-' }}, {{ $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->format('d M Y') : '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">Usia</td><td class="py-1">{{ $profile->age ?? '-' }} tahun</td></tr>
                <tr><td class="py-1 text-gray-500">Jenis Kelamin</td><td class="py-1">{{ $profile->gender == 'L' ? 'Laki-laki' : ($profile->gender == 'P' ? 'Perempuan' : ($profile->gender ?? '-')) }}</td></tr>
                <tr><td class="py-1 text-gray-500">Agama</td><td class="py-1">{{ $profile->religion ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">Golongan Darah</td><td class="py-1">{{ $profile->blood_type ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">Status</td><td class="py-1">{{ $profile->marital_status ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">Jumlah Anak</td><td class="py-1">{{ $profile->children_count ?? '0' }}</td></tr>
                <tr><td class="py-1 text-gray-500">Nama Ibu</td><td class="py-1">{{ $profile->mother_name ?? '-' }}</td></tr>
            </table>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Postur & Seragam</h2>
            <table class="w-full">
                <tr><td class="py-1 text-gray-500 w-1/3">Tinggi Badan</td><td class="py-1">{{ $profile->height_cm ?? '-' }} cm</td></tr>
                <tr><td class="py-1 text-gray-500">Berat Badan</td><td class="py-1">{{ $profile->weight_kg ?? '-' }} kg</td></tr>
                <tr><td class="py-1 text-gray-500">Ukuran Baju</td><td class="py-1">{{ $profile->uniform_shirt_size ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">Ukuran Celana</td><td class="py-1">{{ $profile->uniform_pants_size ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">Ukuran Sepatu</td><td class="py-1">{{ $profile->uniform_shoes_size ?? '-' }}</td></tr>
            </table>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Alamat KTP</h2>
            <p class="text-gray-700">
                {{ $profile->address_street ?? '' }}
                @if($profile->address_rt || $profile->address_rw) RT {{ $profile->address_rt ?? '-' }} / RW {{ $profile->address_rw ?? '-' }} @endif
            </p>
            <p class="text-gray-600 text-xs mt-1">
                {{ $profile->address_subdistrict ?? '' }}, {{ $profile->address_district ?? '' }}, {{ $profile->address_regency ?? '' }}, {{ $profile->address_province ?? '' }} {{ $profile->address_postal_code ?? '' }}
            </p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Alamat Domisili</h2>
            <p class="text-gray-700">
                {{ $profile->domicile_street ?? '' }}
                @if($profile->domicile_rt || $profile->domicile_rw) RT {{ $profile->domicile_rt ?? '-' }} / RW {{ $profile->domicile_rw ?? '-' }} @endif
            </p>
            <p class="text-gray-600 text-xs mt-1">
                {{ $profile->domicile_subdistrict ?? '' }}, {{ $profile->domicile_district ?? '' }}, {{ $profile->domicile_regency ?? '' }}, {{ $profile->domicile_province ?? '' }} {{ $profile->domicile_postal_code ?? '' }}
            </p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Identitas</h2>
            <table class="w-full">
                <tr><td class="py-1 text-gray-500 w-1/3">No. KTP</td><td class="py-1">{{ $profile->ktp_number ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">No. KK</td><td class="py-1">{{ $profile->kk_number ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">NPWP</td><td class="py-1">{{ $profile->npwp ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">SIM A</td><td class="py-1">{{ $profile->sim_a_number ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">SIM C</td><td class="py-1">{{ $profile->sim_c_number ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">BPJS TK</td><td class="py-1">{{ $profile->bpjs_tk_number ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">BPJS KES</td><td class="py-1">{{ $profile->bpjs_kes_number ?? '-' }}</td></tr>
            </table>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Kontak Darurat</h2>
            <table class="w-full">
                <tr><td class="py-1 text-gray-500 w-1/3">Nama</td><td class="py-1">{{ $profile->emergency_name ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">No. Telp</td><td class="py-1">{{ $profile->emergency_phone ?? '-' }}</td></tr>
                <tr><td class="py-1 text-gray-500">Hubungan</td><td class="py-1">{{ $profile->emergency_relation ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Pendidikan Akademis</h2>
        <table class="w-full text-sm">
            <tr><td class="py-1 text-gray-500 w-1/4">Tingkat</td><td class="py-1">{{ $profile->education_level ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">Sekolah/Universitas</td><td class="py-1">{{ $profile->education_school_name ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">Jurusan</td><td class="py-1">{{ $profile->education_major ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">Kota</td><td class="py-1">{{ $profile->education_city ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">Tahun Lulus</td><td class="py-1">{{ $profile->education_graduation_year ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Pendidikan Satpam</h2>
        <table class="w-full text-sm">
            <tr><td class="py-1 text-gray-500 w-1/4">Kualifikasi</td><td class="py-1">{{ $profile->satpam_qualification ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">Instansi</td><td class="py-1">{{ $profile->satpam_training_institution ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">Lokasi Diklat</td><td class="py-1">{{ $profile->satpam_training_location ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">Tanggal</td><td class="py-1">{{ $profile->satpam_training_date ? \Carbon\Carbon::parse($profile->satpam_training_date)->format('d M Y') : '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">No. KTA</td><td class="py-1">{{ $profile->satpam_kta_number ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">No. Ijazah</td><td class="py-1">{{ $profile->satpam_certificate_number ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Pengalaman Kerja</h2>
        <div class="space-y-3 text-sm">
            @if($profile->exp1_company)
            <div class="border-l-2 border-blue-500 pl-3">
                <div class="font-medium">{{ $profile->exp1_position ?? '-' }}</div>
                <div class="text-gray-600">{{ $profile->exp1_company ?? '-' }}, {{ $profile->exp1_city ?? '-' }} ({{ $profile->exp1_year ?? '-' }})</div>
            </div>
            @endif
            @if($profile->exp2_company)
            <div class="border-l-2 border-blue-500 pl-3">
                <div class="font-medium">{{ $profile->exp2_position ?? '-' }}</div>
                <div class="text-gray-600">{{ $profile->exp2_company ?? '-' }}, {{ $profile->exp2_city ?? '-' }} ({{ $profile->exp2_year ?? '-' }})</div>
            </div>
            @endif
            @if($profile->exp3_company)
            <div class="border-l-2 border-blue-500 pl-3">
                <div class="font-medium">{{ $profile->exp3_position ?? '-' }}</div>
                <div class="text-gray-600">{{ $profile->exp3_company ?? '-' }}, {{ $profile->exp3_city ?? '-' }} ({{ $profile->exp3_year ?? '-' }})</div>
            </div>
            @endif
            @if(!$profile->exp1_company && !$profile->exp2_company && !$profile->exp3_company)
            <p class="text-gray-500">Tidak ada data pengalaman kerja.</p>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Sertifikasi</h2>
        <div class="space-y-3 text-sm">
            @if($profile->cert1_training)
            <div class="border-l-2 border-green-500 pl-3">
                <div class="font-medium">{{ $profile->cert1_training ?? '-' }}</div>
                <div class="text-gray-600">{{ $profile->cert1_organizer ?? '-' }}, {{ $profile->cert1_city ?? '-' }} ({{ $profile->cert1_date ? \Carbon\Carbon::parse($profile->cert1_date)->format('d M Y') : '-' }})</div>
            </div>
            @endif
            @if($profile->cert2_training)
            <div class="border-l-2 border-green-500 pl-3">
                <div class="font-medium">{{ $profile->cert2_training ?? '-' }}</div>
                <div class="text-gray-600">{{ $profile->cert2_organizer ?? '-' }}, {{ $profile->cert2_city ?? '-' }} ({{ $profile->cert2_date ? \Carbon\Carbon::parse($profile->cert2_date)->format('d M Y') : '-' }})</div>
            </div>
            @endif
            @if($profile->cert3_training)
            <div class="border-l-2 border-green-500 pl-3">
                <div class="font-medium">{{ $profile->cert3_training ?? '-' }}</div>
                <div class="text-gray-600">{{ $profile->cert3_organizer ?? '-' }}, {{ $profile->cert3_city ?? '-' }} ({{ $profile->cert3_date ? \Carbon\Carbon::parse($profile->cert3_date)->format('d M Y') : '-' }})</div>
            </div>
            @endif
            @if(!$profile->cert1_training && !$profile->cert2_training && !$profile->cert3_training)
            <p class="text-gray-500">Tidak ada data sertifikasi.</p>
            @endif
        </div>
    </div>

    @if($profile->instagram || $profile->facebook || $profile->twitter || $profile->tiktok || $profile->linkedin || $profile->youtube)
    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1">Media Sosial</h2>
        <div class="flex flex-wrap gap-4 text-sm">
            @if($profile->instagram)<span class="text-gray-600">IG: {{ $profile->instagram }}</span>@endif
            @if($profile->facebook)<span class="text-gray-600">FB: {{ $profile->facebook }}</span>@endif
            @if($profile->twitter)<span class="text-gray-600">X: {{ $profile->twitter }}</span>@endif
            @if($profile->tiktok)<span class="text-gray-600">TikTok: {{ $profile->tiktok }}</span>@endif
            @if($profile->linkedin)<span class="text-gray-600">LinkedIn: {{ $profile->linkedin }}</span>@endif
            @if($profile->youtube)<span class="text-gray-600">YouTube: {{ $profile->youtube }}</span>@endif
        </div>
    </div>
    @endif
</div>
@endsection
