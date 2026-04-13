@extends('layouts.admin')

@section('title', 'Import Karyawan')
@section('page_title', 'Import Karyawan')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-xs max-w-5xl">
        <p class="mb-3 text-gray-600">1. Download template Excel berikut, lalu isi data karyawan sesuai kolom yang tersedia.</p>
        <a href="{{ route('admin.users.import.template') }}" class="inline-flex items-center px-3 py-1.5 mb-4 rounded bg-slate-900 text-white hover:bg-slate-800">Download template Excel</a>

        <p class="mb-2 text-gray-600">2. Atau jika ingin membuat manual, pastikan header berisi kolom-kolom berikut:</p>

        <div class="space-y-4 mb-4">
            {{-- Data Akun Dasar --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Data Akun Dasar <span class="text-red-500">*wajib</span></h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">name *</code>
                    <code class="bg-white px-2 py-1 rounded border">username *</code>
                    <code class="bg-white px-2 py-1 rounded border">email *</code>
                    <code class="bg-white px-2 py-1 rounded border">role * (ADMIN/GUARD)</code>
                    <code class="bg-white px-2 py-1 rounded border">project_name *</code>
                    <code class="bg-white px-2 py-1 rounded border">password</code>
                </div>
            </div>

            {{-- Data Karyawan --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Data Karyawan</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">nip</code>
                    <code class="bg-white px-2 py-1 rounded border">position</code>
                    <code class="bg-white px-2 py-1 rounded border">division</code>
                    <code class="bg-white px-2 py-1 rounded border">join_date</code>
                    <code class="bg-white px-2 py-1 rounded border">contract_period</code>
                    <code class="bg-white px-2 py-1 rounded border">employment_status</code>
                    <code class="bg-white px-2 py-1 rounded border">ktp_number</code>
                </div>
            </div>

            {{-- Pendidikan Satpam --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Pendidikan Satpam</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">satpam_qualification</code>
                    <code class="bg-white px-2 py-1 rounded border">satpam_training_date</code>
                    <code class="bg-white px-2 py-1 rounded border">satpam_training_institution</code>
                    <code class="bg-white px-2 py-1 rounded border">satpam_training_location</code>
                    <code class="bg-white px-2 py-1 rounded border">satpam_kta_number</code>
                    <code class="bg-white px-2 py-1 rounded border">satpam_certificate_number</code>
                </div>
            </div>

            {{-- Pendidikan Akademis --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Pendidikan Akademis</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">education_level</code>
                    <code class="bg-white px-2 py-1 rounded border">education_graduation_year</code>
                    <code class="bg-white px-2 py-1 rounded border">education_school_name</code>
                    <code class="bg-white px-2 py-1 rounded border">education_city</code>
                    <code class="bg-white px-2 py-1 rounded border">education_major</code>
                </div>
            </div>

            {{-- Data Pribadi --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Data Pribadi</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">birth_city</code>
                    <code class="bg-white px-2 py-1 rounded border">birth_date</code>
                    <code class="bg-white px-2 py-1 rounded border">age</code>
                    <code class="bg-white px-2 py-1 rounded border">gender</code>
                    <code class="bg-white px-2 py-1 rounded border">mother_name</code>
                    <code class="bg-white px-2 py-1 rounded border">religion</code>
                    <code class="bg-white px-2 py-1 rounded border">blood_type</code>
                    <code class="bg-white px-2 py-1 rounded border">phone_number</code>
                    <code class="bg-white px-2 py-1 rounded border">personal_email</code>
                </div>
            </div>

            {{-- Postur & Seragam --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Postur & Seragam</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">height_cm</code>
                    <code class="bg-white px-2 py-1 rounded border">weight_kg</code>
                    <code class="bg-white px-2 py-1 rounded border">uniform_shirt_size</code>
                    <code class="bg-white px-2 py-1 rounded border">uniform_pants_size</code>
                    <code class="bg-white px-2 py-1 rounded border">uniform_shoes_size</code>
                </div>
            </div>

            {{-- Kontak Darurat --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Telp Darurat</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">emergency_phone</code>
                    <code class="bg-white px-2 py-1 rounded border">emergency_name</code>
                    <code class="bg-white px-2 py-1 rounded border">emergency_relation</code>
                </div>
            </div>

            {{-- Identitas --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Identitas</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">npwp</code>
                    <code class="bg-white px-2 py-1 rounded border">sim_c_number</code>
                    <code class="bg-white px-2 py-1 rounded border">sim_a_number</code>
                    <code class="bg-white px-2 py-1 rounded border">bpjs_tk_number</code>
                    <code class="bg-white px-2 py-1 rounded border">bpjs_kes_number</code>
                    <code class="bg-white px-2 py-1 rounded border">kk_number</code>
                </div>
            </div>

            {{-- Alamat KTP --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Alamat KTP</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">address_province</code>
                    <code class="bg-white px-2 py-1 rounded border">address_regency</code>
                    <code class="bg-white px-2 py-1 rounded border">address_district</code>
                    <code class="bg-white px-2 py-1 rounded border">address_subdistrict</code>
                    <code class="bg-white px-2 py-1 rounded border">address_street</code>
                    <code class="bg-white px-2 py-1 rounded border">address_rt</code>
                    <code class="bg-white px-2 py-1 rounded border">address_rw</code>
                    <code class="bg-white px-2 py-1 rounded border">address_postal_code</code>
                </div>
            </div>

            {{-- Domisili --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Domisili</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">domicile_province</code>
                    <code class="bg-white px-2 py-1 rounded border">domicile_regency</code>
                    <code class="bg-white px-2 py-1 rounded border">domicile_district</code>
                    <code class="bg-white px-2 py-1 rounded border">domicile_subdistrict</code>
                    <code class="bg-white px-2 py-1 rounded border">domicile_street</code>
                    <code class="bg-white px-2 py-1 rounded border">domicile_rt</code>
                    <code class="bg-white px-2 py-1 rounded border">domicile_rw</code>
                    <code class="bg-white px-2 py-1 rounded border">domicile_postal_code</code>
                </div>
            </div>

            {{-- Data Keluarga --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Data Keluarga</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">marital_status</code>
                    <code class="bg-white px-2 py-1 rounded border">children_count</code>
                </div>
            </div>

            {{-- Pengalaman Kerja --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Pengalaman Kerja</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">exp1_year</code>
                    <code class="bg-white px-2 py-1 rounded border">exp1_position</code>
                    <code class="bg-white px-2 py-1 rounded border">exp1_company</code>
                    <code class="bg-white px-2 py-1 rounded border">exp1_city</code>
                    <code class="bg-white px-2 py-1 rounded border">exp2_year</code>
                    <code class="bg-white px-2 py-1 rounded border">exp2_position</code>
                    <code class="bg-white px-2 py-1 rounded border">exp2_company</code>
                    <code class="bg-white px-2 py-1 rounded border">exp2_city</code>
                    <code class="bg-white px-2 py-1 rounded border">exp3_year</code>
                    <code class="bg-white px-2 py-1 rounded border">exp3_position</code>
                    <code class="bg-white px-2 py-1 rounded border">exp3_company</code>
                    <code class="bg-white px-2 py-1 rounded border">exp3_city</code>
                </div>
            </div>

            {{-- Sertifikasi --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Sertifikasi</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">cert1_date</code>
                    <code class="bg-white px-2 py-1 rounded border">cert1_training</code>
                    <code class="bg-white px-2 py-1 rounded border">cert1_organizer</code>
                    <code class="bg-white px-2 py-1 rounded border">cert1_city</code>
                    <code class="bg-white px-2 py-1 rounded border">cert2_date</code>
                    <code class="bg-white px-2 py-1 rounded border">cert2_training</code>
                    <code class="bg-white px-2 py-1 rounded border">cert2_organizer</code>
                    <code class="bg-white px-2 py-1 rounded border">cert2_city</code>
                    <code class="bg-white px-2 py-1 rounded border">cert3_date</code>
                    <code class="bg-white px-2 py-1 rounded border">cert3_training</code>
                    <code class="bg-white px-2 py-1 rounded border">cert3_organizer</code>
                    <code class="bg-white px-2 py-1 rounded border">cert3_city</code>
                </div>
            </div>

            {{-- Media Sosial --}}
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h3 class="font-semibold text-gray-700 text-xs mb-2">Media Sosial</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                    <code class="bg-white px-2 py-1 rounded border">instagram</code>
                    <code class="bg-white px-2 py-1 rounded border">facebook</code>
                    <code class="bg-white px-2 py-1 rounded border">twitter</code>
                    <code class="bg-white px-2 py-1 rounded border">tiktok</code>
                    <code class="bg-white px-2 py-1 rounded border">linkedin</code>
                    <code class="bg-white px-2 py-1 rounded border">youtube</code>
                    <code class="bg-white px-2 py-1 rounded border">profile_photo_url</code>
                </div>
            </div>
        </div>

        <p class="mb-4 text-gray-500">Kolom dengan tanda <span class="text-red-500">*</span> bersifat wajib. Kolom lain bersifat opsional. Jika password kosong, akan diisi default <code>password</code>.</p>

        <form method="POST" action="{{ route('admin.users.import.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-gray-600 mb-1">File</label>
                <input type="file" name="file" class="w-full text-xs" required>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700">Batal</a>
                <button type="submit" class="px-3 py-1.5 rounded bg-slate-900 text-white">Import</button>
            </div>
        </form>
    </div>
@endsection
