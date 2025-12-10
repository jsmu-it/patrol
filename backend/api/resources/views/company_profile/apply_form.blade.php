<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for {{ $career->title }} - JSMU Guard</title>
    <link href="{{ asset('assets/css/tailwind.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/fonts/inter-local.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-8">
                <img src="{{ asset('images/admin-logo.png') }}" alt="JSMU Guard" class="h-12 mx-auto mb-4">
                <h1 class="text-3xl font-bold text-gray-900">Application Form</h1>
                <p class="mt-2 text-gray-600">Applying for <span class="font-semibold text-blue-600">{{ $career->title }}</span></p>
            </div>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-8 py-6 bg-slate-900 text-white">
                    <h2 class="text-xl font-semibold">Personal Information</h2>
                    <p class="text-slate-300 text-sm mt-1">Please fill in your details accurately.</p>
                </div>
                
                <form action="{{ route('career.apply') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                    @csrf
                    <input type="hidden" name="cms_career_id" value="{{ $career->id }}">
                    
                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Validation Error!</strong>
                            <ul class="list-disc pl-4 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name (Sesuai KTP)</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number (WhatsApp)</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Additional PDP Fields -->
                        <!-- Data Pribadi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                            <input type="text" name="birth_city" value="{{ old('birth_city') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usia</label>
                            <input type="number" name="age" value="{{ old('age') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="gender" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                                <option value="">Pilih</option>
                                <option value="Laki-laki" @selected(old('gender') == 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected(old('gender') == 'Perempuan')>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Agama</label>
                            <input type="text" name="religion" value="{{ old('religion') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Golongan Darah</label>
                            <input type="text" name="blood_type" value="{{ old('blood_type') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu Kandung</label>
                            <input type="text" name="mother_name" value="{{ old('mother_name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Pernikahan</label>
                            <input type="text" name="marital_status" value="{{ old('marital_status') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Identitas & Fisik -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. KTP</label>
                            <input type="text" name="ktp_number" value="{{ old('ktp_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. KK</label>
                            <input type="text" name="kk_number" value="{{ old('kk_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tinggi Badan (cm)</label>
                            <input type="number" name="height_cm" value="{{ old('height_cm') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Berat Badan (kg)</label>
                            <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Alamat Domisili -->
                        <div class="col-span-2">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Alamat Domisili</h3>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap (Jalan)</label>
                            <textarea name="domicile_street" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">{{ old('domicile_street') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RT / RW</label>
                            <div class="flex gap-2">
                                <input type="text" name="domicile_rt" placeholder="RT" value="{{ old('domicile_rt') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                                <input type="text" name="domicile_rw" placeholder="RW" value="{{ old('domicile_rw') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelurahan</label>
                            <input type="text" name="domicile_subdistrict" value="{{ old('domicile_subdistrict') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                            <input type="text" name="domicile_district" value="{{ old('domicile_district') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                            <input type="text" name="domicile_regency" value="{{ old('domicile_regency') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                            <input type="text" name="domicile_province" value="{{ old('domicile_province') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                            <input type="text" name="domicile_postal_code" value="{{ old('domicile_postal_code') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Alamat KTP -->
                        <div class="col-span-2">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Alamat Sesuai KTP</h3>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap (Jalan)</label>
                            <textarea name="address_street" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">{{ old('address_street') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RT / RW</label>
                            <div class="flex gap-2">
                                <input type="text" name="address_rt" placeholder="RT" value="{{ old('address_rt') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                                <input type="text" name="address_rw" placeholder="RW" value="{{ old('address_rw') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelurahan</label>
                            <input type="text" name="address_subdistrict" value="{{ old('address_subdistrict') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                            <input type="text" name="address_district" value="{{ old('address_district') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                            <input type="text" name="address_regency" value="{{ old('address_regency') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                            <input type="text" name="address_province" value="{{ old('address_province') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                            <input type="text" name="address_postal_code" value="{{ old('address_postal_code') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Identitas Tambahan -->
                        <div class="col-span-2">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Identitas Tambahan</h3>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SIM A</label>
                            <input type="text" name="sim_a_number" value="{{ old('sim_a_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SIM C</label>
                            <input type="text" name="sim_c_number" value="{{ old('sim_c_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">BPJS Ketenagakerjaan</label>
                            <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">BPJS Kesehatan</label>
                            <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Kontak Darurat & Keluarga -->
                        <div class="col-span-2">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Keluarga & Kontak Darurat</h3>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Anak</label>
                            <input type="number" name="children_count" value="{{ old('children_count', 0) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div class="col-span-2 md:col-span-1"></div> <!-- Spacer -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kontak Darurat</label>
                            <input type="text" name="emergency_name" value="{{ old('emergency_name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon Darurat</label>
                            <input type="text" name="emergency_phone" value="{{ old('emergency_phone') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hubungan</label>
                            <input type="text" name="emergency_relation" value="{{ old('emergency_relation') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Ukuran Seragam -->
                        <div class="col-span-2">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Ukuran Seragam</h3>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran Baju</label>
                            <input type="text" name="uniform_shirt_size" value="{{ old('uniform_shirt_size') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5" placeholder="S/M/L/XL/XXL">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran Celana</label>
                            <input type="text" name="uniform_pants_size" value="{{ old('uniform_pants_size') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5" placeholder="28/30/32/34...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran Sepatu</label>
                            <input type="text" name="uniform_shoes_size" value="{{ old('uniform_shoes_size') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5" placeholder="40/41/42...">
                        </div>

                        <!-- Pendidikan Terakhir -->
                        <div class="col-span-2">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Pendidikan Terakhir</h3>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenjang Pendidikan</label>
                            <select name="education_level" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                                <option value="">Pilih</option>
                                <option value="SD" @selected(old('education_level') == 'SD')>SD</option>
                                <option value="SMP" @selected(old('education_level') == 'SMP')>SMP</option>
                                <option value="SMA/SMK" @selected(old('education_level') == 'SMA/SMK')>SMA/SMK</option>
                                <option value="D3" @selected(old('education_level') == 'D3')>D3</option>
                                <option value="S1" @selected(old('education_level') == 'S1')>S1</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah/Universitas</label>
                            <input type="text" name="education_school_name" value="{{ old('education_school_name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kota Sekolah/Univ</label>
                            <input type="text" name="education_city" value="{{ old('education_city') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                            <input type="text" name="education_major" value="{{ old('education_major') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Lulus</label>
                            <input type="text" name="education_graduation_year" value="{{ old('education_graduation_year') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Sertifikat Satpam -->
                        <div class="col-span-2">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Sertifikasi Satpam (Jika Ada)</h3>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kualifikasi (Gada Pratama/Madya/Utama)</label>
                            <input type="text" name="satpam_qualification" value="{{ old('satpam_qualification') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. KTA Satpam</label>
                            <input type="text" name="satpam_kta_number" value="{{ old('satpam_kta_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Sertifikat Satpam</label>
                            <input type="text" name="satpam_certificate_number" value="{{ old('satpam_certificate_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pelatihan</label>
                            <input type="date" name="satpam_training_date" value="{{ old('satpam_training_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Institusi Pelatihan</label>
                            <input type="text" name="satpam_training_institution" value="{{ old('satpam_training_institution') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Pelatihan</label>
                            <input type="text" name="satpam_training_location" value="{{ old('satpam_training_location') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5">
                        </div>

                        <!-- Pengalaman Kerja -->
                        <div class="col-span-2">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Pengalaman Kerja (Terakhir)</h3>
                        </div>
                        
                        <!-- Exp 1 -->
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pengalaman 1</label>
                            <div class="space-y-2">
                                <input type="text" name="exp1_company" placeholder="Nama Perusahaan" value="{{ old('exp1_company') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                <input type="text" name="exp1_position" placeholder="Posisi / Jabatan" value="{{ old('exp1_position') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="exp1_year" placeholder="Tahun (Contoh: 2020-2022)" value="{{ old('exp1_year') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="exp1_city" placeholder="Kota" value="{{ old('exp1_city') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                </div>
                            </div>
                        </div>

                        <!-- Exp 2 -->
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pengalaman 2</label>
                            <div class="space-y-2">
                                <input type="text" name="exp2_company" placeholder="Nama Perusahaan" value="{{ old('exp2_company') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                <input type="text" name="exp2_position" placeholder="Posisi / Jabatan" value="{{ old('exp2_position') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="exp2_year" placeholder="Tahun" value="{{ old('exp2_year') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="exp2_city" placeholder="Kota" value="{{ old('exp2_city') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                </div>
                            </div>
                        </div>

                        <!-- Sertifikasi Lain -->
                        <div class="col-span-2 mt-4">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Sertifikasi / Pelatihan Lain</h3>
                        </div>
                        
                        <!-- Cert 1 -->
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Sertifikasi 1</label>
                            <div class="space-y-2">
                                <input type="text" name="cert1_training" placeholder="Nama Pelatihan" value="{{ old('cert1_training') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                <input type="text" name="cert1_organizer" placeholder="Penyelenggara" value="{{ old('cert1_organizer') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" name="cert1_date" value="{{ old('cert1_date') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="cert1_city" placeholder="Kota" value="{{ old('cert1_city') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                </div>
                            </div>
                        </div>

                        <!-- Cert 2 -->
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Sertifikasi 2</label>
                            <div class="space-y-2">
                                <input type="text" name="cert2_training" placeholder="Nama Pelatihan" value="{{ old('cert2_training') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                <input type="text" name="cert2_organizer" placeholder="Penyelenggara" value="{{ old('cert2_organizer') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" name="cert2_date" value="{{ old('cert2_date') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="cert2_city" placeholder="Kota" value="{{ old('cert2_city') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="col-span-2 mt-4">
                            <h3 class="font-semibold text-gray-800 border-b pb-2 mb-4">Media Sosial (Username/Link)</h3>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 col-span-2">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Instagram</label>
                                <input type="text" name="instagram" value="{{ old('instagram') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border" placeholder="@username">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Facebook</label>
                                <input type="text" name="facebook" value="{{ old('facebook') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border" placeholder="Nama / Link">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">TikTok</label>
                                <input type="text" name="tiktok" value="{{ old('tiktok') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border" placeholder="@username">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Twitter/X</label>
                                <input type="text" name="twitter" value="{{ old('twitter') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border" placeholder="@username">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">LinkedIn</label>
                                <input type="text" name="linkedin" value="{{ old('linkedin') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border" placeholder="Link Profile">
                            </div>
                        </div>

                        <!-- Resume Upload -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Resume / CV (PDF/DOCX)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-500 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="resume-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload a file</span>
                                            <input id="resume-upload" name="resume" type="file" class="sr-only" accept=".pdf,.doc,.docx" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 2MB</p>
                                    <p id="file-name" class="text-sm text-blue-600 mt-2 hidden font-medium"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Cover Letter -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cover Letter / Catatan Tambahan</label>
                            <textarea name="cover_letter" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5" placeholder="Jelaskan mengapa Anda cocok untuk posisi ini...">{{ old('cover_letter') }}</textarea>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-4 border-t border-gray-100">
                        <a href="{{ route('career') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Back to Careers</a>
                        <button type="submit" class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all">
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-8 text-sm text-gray-500">
                &copy; {{ date('Y') }} JSMU Guard. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('resume-upload');
        const fileNameDisplay = document.getElementById('file-name');

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileNameDisplay.textContent = 'Selected: ' + e.target.files[0].name;
                fileNameDisplay.classList.remove('hidden');
            } else {
                fileNameDisplay.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
