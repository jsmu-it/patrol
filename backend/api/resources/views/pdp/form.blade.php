<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Data Pribadi Karyawan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex items-center justify-center py-8">
    <div class="w-full max-w-5xl bg-white shadow-md rounded-lg p-6 text-sm">
        <h1 class="text-xl font-semibold mb-1 text-gray-800">Form Data Pribadi Karyawan (PDP)</h1>
        <p class="mb-4 text-gray-600">Silakan isi data berikut dengan lengkap dan benar.</p>

        @if(session('status'))
            <div id="success-notification" class="fixed top-4 right-4 z-50 p-4 rounded-lg bg-green-100 text-green-800 text-sm shadow-lg max-w-md">
                <div class="flex items-center justify-between">
                    <span>{{ session('status') }}</span>
                    <button onclick="document.getElementById('success-notification').remove()" class="ml-4 text-green-600 hover:text-green-800 font-bold">&times;</button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div id="error-notification" class="fixed top-4 right-4 z-50 p-4 rounded-lg bg-red-100 text-red-800 text-sm shadow-lg max-w-md max-h-96 overflow-y-auto">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                        <ul class="list-disc pl-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button onclick="document.getElementById('error-notification').remove()" class="ml-4 text-red-600 hover:text-red-800 font-bold">&times;</button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('pdp.submit') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">NIP *</label>
                    <input type="text" name="nip" value="{{ old('nip') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Lokasi Tugas (Project) *</label>
                    @if(count($projects) > 0)
                        <select name="active_project_id" id="project_select" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                            <option value="">- Pilih Lokasi -</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('active_project_id') == $project->id)>{{ $project->name }}</option>
                            @endforeach
                            <option value="__new__">+ Tambah Lokasi Tugas Baru</option>
                        </select>
                    @else
                        <div class="space-y-2">
                            <div class="bg-yellow-50 border border-yellow-200 rounded px-3 py-2 text-xs text-yellow-800">
                                Belum ada lokasi tugas. Silakan tambah lokasi tugas baru.
                            </div>
                            <input type="text" name="new_project_name" id="new_project_name" placeholder="Nama Lokasi Tugas" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                            <input type="hidden" name="active_project_id" value="__new__">
                        </div>
                    @endif
                    
                    <!-- Hidden input for new project name when selected from dropdown -->
                    <div id="new_project_input_container" class="mt-2 hidden">
                        <input type="text" name="new_project_name_dropdown" id="new_project_name_dropdown" placeholder="Masukkan nama lokasi tugas baru" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Jabatan *</label>
                    <input type="text" name="position" value="{{ old('position') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Divisi *</label>
                    <select name="division" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <option value="">- Pilih Divisi -</option>
                        <option value="HR & GA" @selected(old('division') == 'HR & GA')>HR & GA</option>
                        <option value="IT" @selected(old('division') == 'IT')>IT</option>
                        <option value="FINANCE & ACCOUNTING" @selected(old('division') == 'FINANCE & ACCOUNTING')>FINANCE & ACCOUNTING</option>
                        <option value="DOC.CONTROL" @selected(old('division') == 'DOC.CONTROL')>DOC.CONTROL</option>
                        <option value="FACILITY & SERVICES" @selected(old('division') == 'FACILITY & SERVICES')>FACILITY & SERVICES</option>
                        <option value="DEVELOPMENT" @selected(old('division') == 'DEVELOPMENT')>DEVELOPMENT</option>
                        <option value="OPERASIONAL" @selected(old('division') == 'OPERASIONAL')>OPERASIONAL</option>
                        <option value="TRAINING" @selected(old('division') == 'TRAINING')>TRAINING</option>
                        <option value="HSE" @selected(old('division') == 'HSE')>HSE</option>
                        <option value="LEGAL" @selected(old('division') == 'LEGAL')>LEGAL</option>
                        <option value="DIREKSI" @selected(old('division') == 'DIREKSI')>DIREKSI</option>
                        <option value="PAJAK" @selected(old('division') == 'PAJAK')>PAJAK</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Foto Profil *</label>
                    <input type="file" name="profile_photo" accept="image/*" required class="w-full text-xs">
                </div>
            </div>

            <hr class="border-gray-200">

            <h2 class="text-sm font-semibold text-gray-800">Pendidikan Satpam</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kualifikasi</label>
                    <input type="text" name="satpam_qualification" value="{{ old('satpam_qualification') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Tanggal Pendidikan</label>
                    <input type="date" name="satpam_training_date" value="{{ old('satpam_training_date') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Instansi Penyelenggara</label>
                    <input type="text" name="satpam_training_institution" value="{{ old('satpam_training_institution') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Lokasi Diklat</label>
                    <input type="text" name="satpam_training_location" value="{{ old('satpam_training_location') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. KTA</label>
                    <input type="text" name="satpam_kta_number" value="{{ old('satpam_kta_number') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. Ijazah</label>
                    <input type="text" name="satpam_certificate_number" value="{{ old('satpam_certificate_number') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Pendidikan Akademis</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Tingkat *</label>
                    <input type="text" name="education_level" value="{{ old('education_level') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs" placeholder="SMA / D3 / S1">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Tahun Lulus *</label>
                    <input type="text" name="education_graduation_year" value="{{ old('education_graduation_year') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nama Sekolah / Univ *</label>
                    <input type="text" name="education_school_name" value="{{ old('education_school_name') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kota *</label>
                    <input type="text" name="education_city" value="{{ old('education_city') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Jurusan *</label>
                    <input type="text" name="education_major" value="{{ old('education_major') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Data Pribadi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kota Lahir *</label>
                    <input type="text" name="birth_city" value="{{ old('birth_city') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Tanggal Lahir *</label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Usia *</label>
                    <input type="number" name="age" id="age" value="{{ old('age') }}" readonly class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-gray-100">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Jenis Kelamin *</label>
                    <input type="text" name="gender" value="{{ old('gender') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs" placeholder="L / P">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nama Ibu Kandung *</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Agama *</label>
                    <input type="text" name="religion" value="{{ old('religion') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Golongan Darah *</label>
                    <input type="text" name="blood_type" value="{{ old('blood_type') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. Handphone *</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Email Pribadi *</label>
                    <input type="email" name="personal_email" value="{{ old('personal_email') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Postur & Seragam</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Tinggi Badan (cm) *</label>
                    <input type="number" name="height_cm" value="{{ old('height_cm') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Berat Badan (kg) *</label>
                    <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Ukuran Baju *</label>
                    <input type="text" name="uniform_shirt_size" value="{{ old('uniform_shirt_size') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Ukuran Celana *</label>
                    <input type="text" name="uniform_pants_size" value="{{ old('uniform_pants_size') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Ukuran Sepatu *</label>
                    <input type="text" name="uniform_shoes_size" value="{{ old('uniform_shoes_size') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Telp Darurat</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. Telp Darurat *</label>
                    <input type="text" name="emergency_phone" value="{{ old('emergency_phone') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nama Pemilik *</label>
                    <input type="text" name="emergency_name" value="{{ old('emergency_name') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Hubungan *</label>
                    <input type="text" name="emergency_relation" value="{{ old('emergency_relation') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Identitas</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nomor SIM C</label>
                    <input type="text" name="sim_c_number" value="{{ old('sim_c_number') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nomor SIM A</label>
                    <input type="text" name="sim_a_number" value="{{ old('sim_a_number') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. BPJS TK</label>
                    <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. BPJS KES</label>
                    <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. KK *</label>
                    <input type="text" name="kk_number" value="{{ old('kk_number') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Alamat KTP</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Provinsi *</label>
                    <select name="address_province" id="address_province" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <option value="">- Pilih Provinsi -</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kabupaten/Kota *</label>
                    <select name="address_regency" id="address_regency" required disabled class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-gray-100">
                        <option value="">- Pilih Kabupaten/Kota -</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kecamatan *</label>
                    <select name="address_district" id="address_district" required disabled class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-gray-100">
                        <option value="">- Pilih Kecamatan -</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kelurahan/Desa *</label>
                    <select name="address_subdistrict" id="address_subdistrict" required disabled class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-gray-100">
                        <option value="">- Pilih Kelurahan/Desa -</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-1 text-xs">Desa / Jalan *</label>
                    <input type="text" name="address_street" id="address_street" value="{{ old('address_street') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">RT *</label>
                    <input type="text" name="address_rt" id="address_rt" value="{{ old('address_rt') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">RW *</label>
                    <input type="text" name="address_rw" id="address_rw" value="{{ old('address_rw') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kode Pos *</label>
                    <input type="text" name="address_postal_code" id="address_postal_code" value="{{ old('address_postal_code') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Domisili</h2>
            <div id="domicile_fields" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Provinsi *</label>
                    <select name="domicile_province" id="domicile_province" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <option value="">- Pilih Provinsi -</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kabupaten/Kota *</label>
                    <select name="domicile_regency" id="domicile_regency" required disabled class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-gray-100">
                        <option value="">- Pilih Kabupaten/Kota -</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kecamatan *</label>
                    <select name="domicile_district" id="domicile_district" required disabled class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-gray-100">
                        <option value="">- Pilih Kecamatan -</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kelurahan/Desa *</label>
                    <select name="domicile_subdistrict" id="domicile_subdistrict" required disabled class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-gray-100">
                        <option value="">- Pilih Kelurahan/Desa -</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-1 text-xs">Desa / Jalan *</label>
                    <input type="text" name="domicile_street" id="domicile_street" value="{{ old('domicile_street') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">RT *</label>
                    <input type="text" name="domicile_rt" id="domicile_rt" value="{{ old('domicile_rt') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">RW *</label>
                    <input type="text" name="domicile_rw" id="domicile_rw" value="{{ old('domicile_rw') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kode Pos *</label>
                    <input type="text" name="domicile_postal_code" id="domicile_postal_code" value="{{ old('domicile_postal_code') }}" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800 mt-6">Data Keluarga</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Status Pernikahan</label>
                    <select name="marital_status" id="marital_status" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <option value="">- Pilih Status -</option>
                        <option value="Kawin" @selected(old('marital_status') == 'Kawin')>Kawin</option>
                        <option value="Lajang" @selected(old('marital_status') == 'Lajang')>Lajang</option>
                        <option value="Cerai Hidup" @selected(old('marital_status') == 'Cerai Hidup')>Cerai Hidup</option>
                        <option value="Cerai Mati" @selected(old('marital_status') == 'Cerai Mati')>Cerai Mati</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Jumlah Anak</label>
                    <input type="number" name="children_count" id="children_count" value="{{ old('children_count') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>


            <h2 class="text-sm font-semibold text-gray-800">Pengalaman Kerja</h2>
            <div class="space-y-4">
                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-xs mb-2">Pengalaman 1</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block text-gray-700 mb-1">Tahun</label>
                            <input type="text" name="exp1_year" value="{{ old('exp1_year') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Posisi</label>
                            <input type="text" name="exp1_position" value="{{ old('exp1_position') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Nama Perusahaan</label>
                            <input type="text" name="exp1_company" value="{{ old('exp1_company') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Kota</label>
                            <input type="text" name="exp1_city" value="{{ old('exp1_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-xs mb-2">Pengalaman 2</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block text-gray-700 mb-1">Tahun</label>
                            <input type="text" name="exp2_year" value="{{ old('exp2_year') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Posisi</label>
                            <input type="text" name="exp2_position" value="{{ old('exp2_position') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Nama Perusahaan</label>
                            <input type="text" name="exp2_company" value="{{ old('exp2_company') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Kota</label>
                            <input type="text" name="exp2_city" value="{{ old('exp2_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-xs mb-2">Pengalaman 3</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block text-gray-700 mb-1">Tahun</label>
                            <input type="text" name="exp3_year" value="{{ old('exp3_year') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Posisi</label>
                            <input type="text" name="exp3_position" value="{{ old('exp3_position') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Nama Perusahaan</label>
                            <input type="text" name="exp3_company" value="{{ old('exp3_company') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Kota</label>
                            <input type="text" name="exp3_city" value="{{ old('exp3_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Sertifikasi</h2>
            <div class="space-y-4">
                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-xs mb-2">Sertifikasi 1</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block text-gray-700 mb-1">Tanggal</label>
                            <input type="date" name="cert1_date" value="{{ old('cert1_date') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Pelatihan</label>
                            <input type="text" name="cert1_training" value="{{ old('cert1_training') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Penyelenggara</label>
                            <input type="text" name="cert1_organizer" value="{{ old('cert1_organizer') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Kota</label>
                            <input type="text" name="cert1_city" value="{{ old('cert1_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-xs mb-2">Sertifikasi 2</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block text-gray-700 mb-1">Tanggal</label>
                            <input type="date" name="cert2_date" value="{{ old('cert2_date') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Pelatihan</label>
                            <input type="text" name="cert2_training" value="{{ old('cert2_training') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Penyelenggara</label>
                            <input type="text" name="cert2_organizer" value="{{ old('cert2_organizer') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Kota</label>
                            <input type="text" name="cert2_city" value="{{ old('cert2_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-700 text-xs mb-2">Sertifikasi 3</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block text-gray-700 mb-1">Tanggal</label>
                            <input type="date" name="cert3_date" value="{{ old('cert3_date') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Pelatihan</label>
                            <input type="text" name="cert3_training" value="{{ old('cert3_training') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Penyelenggara</label>
                            <input type="text" name="cert3_organizer" value="{{ old('cert3_organizer') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Kota</label>
                            <input type="text" name="cert3_city" value="{{ old('cert3_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Media Sosial</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Instagram</label>
                    <input type="text" name="instagram" value="{{ old('instagram') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Facebook</label>
                    <input type="text" name="facebook" value="{{ old('facebook') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">X (Twitter)</label>
                    <input type="text" name="twitter" value="{{ old('twitter') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">TikTok</label>
                    <input type="text" name="tiktok" value="{{ old('tiktok') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">LinkedIn</label>
                    <input type="text" name="linkedin" value="{{ old('linkedin') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">YouTube</label>
                    <input type="text" name="youtube" value="{{ old('youtube') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            {{-- Persetujuan & Tanda Tangan --}}
            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Persetujuan & Tanda Tangan</h2>
                
                {{-- Consent Checkbox --}}
                <div class="mb-4">
                    <label class="inline-flex items-start cursor-pointer">
                        <input type="checkbox" id="data-consent-checkbox" name="data_consent" value="1" class="mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm text-gray-700">
                            <strong class="text-gray-900">Persetujuan Pengumpulan Data</strong><br>
                            Saya menyetujui bahwa data pribadi yang saya berikan akan dikelola oleh perusahaan untuk keperluan administrasi karyawan sesuai dengan kebijakan privasi yang berlaku.
                        </span>
                    </label>
                </div>

                {{-- Signature Canvas --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan Digital</label>
                    <div class="border border-gray-200 rounded-lg bg-white p-2">
                        <canvas id="signature-canvas" class="w-full border border-gray-200 rounded cursor-crosshair" style="height: 150px; touch-action: none;"></canvas>
                        <input type="hidden" name="signature" id="signature-data">
                        <div class="mt-2 flex justify-end">
                            <button type="button" id="clear-signature" class="text-xs text-red-600 hover:text-red-800 font-medium">
                                🗑️ Hapus Tanda Tangan
                            </button>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Gunakan mouse atau sentuhan untuk membuat tanda tangan Anda di area di atas.</p>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" id="submit-btn" disabled class="px-6 py-2 rounded bg-gray-400 text-white text-xs font-semibold cursor-not-allowed transition-all duration-200">Kirim Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ==================== SIGNATURE CANVAS ====================
    const canvas = document.getElementById('signature-canvas');
    const ctx = canvas.getContext('2d');
    const signatureData = document.getElementById('signature-data');
    const clearBtn = document.getElementById('clear-signature');
    
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;

    // Set canvas size properly
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.strokeStyle = '#1e40af';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function getCoordinates(e) {
        const rect = canvas.getBoundingClientRect();
        if (e.touches && e.touches.length > 0) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top
            };
        }
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    function startDrawing(e) {
        isDrawing = true;
        const coords = getCoordinates(e);
        lastX = coords.x;
        lastY = coords.y;
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        
        const coords = getCoordinates(e);
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(coords.x, coords.y);
        ctx.stroke();
        
        lastX = coords.x;
        lastY = coords.y;
    }

    function stopDrawing() {
        if (isDrawing) {
            isDrawing = false;
            saveSignature();
        }
    }

    function saveSignature() {
        signatureData.value = canvas.toDataURL('image/png');
        updateSubmitButton();
    }

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        signatureData.value = '';
        updateSubmitButton();
    }

    // Mouse events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseleave', stopDrawing);

    // Touch events
    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);

    // Clear button
    clearBtn.addEventListener('click', clearSignature);

    // ==================== FORM VALIDATION ====================
    const consentCheckbox = document.getElementById('data-consent-checkbox');
    const submitBtn = document.getElementById('submit-btn');

    function isSignatureEmpty() {
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        return !imageData.data.some((channel, index) => {
            // Check alpha channel (every 4th value starting from 3)
            return index % 4 === 3 && channel !== 0;
        });
    }

    function updateSubmitButton() {
        const consentChecked = consentCheckbox.checked;
        const hasSignature = !isSignatureEmpty();

        if (consentChecked && hasSignature) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        }
    }

    consentCheckbox.addEventListener('change', updateSubmitButton);

    // Initial check
    updateSubmitButton();

    // ==================== REGIONAL API FUNCTIONS ====================
    const regionalApi = {
        async getProvinces() {
            const response = await fetch('/api/regional/provinces');
            return response.json();
        },
        async getRegencies(provinceId) {
            const response = await fetch(`/api/regional/regencies/${provinceId}`);
            return response.json();
        },
        async getDistricts(regencyId) {
            const response = await fetch(`/api/regional/districts/${regencyId}`);
            return response.json();
        },
        async getVillages(districtId) {
            const response = await fetch(`/api/regional/villages/${districtId}`);
            return response.json();
        }
    };

    // ==================== DROPDOWN HELPER FUNCTIONS ====================
    function populateSelect(selectElement, data, defaultText) {
        selectElement.innerHTML = `<option value="">- ${defaultText} -</option>`;
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.name;
            option.dataset.id = item.id;
            option.textContent = item.name;
            selectElement.appendChild(option);
        });
    }

    function enableSelect(selectElement) {
        selectElement.disabled = false;
        selectElement.classList.remove('bg-gray-100');
    }

    function disableSelect(selectElement) {
        selectElement.disabled = true;
        selectElement.classList.add('bg-gray-100');
        selectElement.innerHTML = `<option value="">- Pilih -</option>`;
    }

    function getSelectedId(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        return selectedOption ? selectedOption.dataset.id : null;
    }

    // ==================== ADDRESS CASCADING (KTP) ====================
    const addressProvinceSelect = document.getElementById('address_province');
    const addressRegencySelect = document.getElementById('address_regency');
    const addressDistrictSelect = document.getElementById('address_district');
    const addressSubdistrictSelect = document.getElementById('address_subdistrict');

    // Load provinces on page load
    async function loadProvinces() {
        try {
            const provinces = await regionalApi.getProvinces();
            populateSelect(addressProvinceSelect, provinces, 'Pilih Provinsi');
            populateSelect(document.getElementById('domicile_province'), provinces, 'Pilih Provinsi');
        } catch (error) {
            console.error('Error loading provinces:', error);
        }
    }

    addressProvinceSelect.addEventListener('change', async function() {
        const provinceId = getSelectedId(this);
        
        // Reset dependent selects
        disableSelect(addressRegencySelect);
        disableSelect(addressDistrictSelect);
        disableSelect(addressSubdistrictSelect);
        
        if (provinceId) {
            try {
                const regencies = await regionalApi.getRegencies(provinceId);
                populateSelect(addressRegencySelect, regencies, 'Pilih Kabupaten/Kota');
                enableSelect(addressRegencySelect);
            } catch (error) {
                console.error('Error loading regencies:', error);
            }
        }
    });

    addressRegencySelect.addEventListener('change', async function() {
        const regencyId = getSelectedId(this);
        
        // Reset dependent selects
        disableSelect(addressDistrictSelect);
        disableSelect(addressSubdistrictSelect);
        
        if (regencyId) {
            try {
                const districts = await regionalApi.getDistricts(regencyId);
                populateSelect(addressDistrictSelect, districts, 'Pilih Kecamatan');
                enableSelect(addressDistrictSelect);
            } catch (error) {
                console.error('Error loading districts:', error);
            }
        }
    });

    addressDistrictSelect.addEventListener('change', async function() {
        const districtId = getSelectedId(this);
        
        // Reset dependent select
        disableSelect(addressSubdistrictSelect);
        
        if (districtId) {
            try {
                const villages = await regionalApi.getVillages(districtId);
                populateSelect(addressSubdistrictSelect, villages, 'Pilih Kelurahan/Desa');
                enableSelect(addressSubdistrictSelect);
            } catch (error) {
                console.error('Error loading villages:', error);
            }
        }
    });

    // ==================== ADDRESS CASCADING (DOMICILE) ====================
    const domicileProvinceSelect = document.getElementById('domicile_province');
    const domicileRegencySelect = document.getElementById('domicile_regency');
    const domicileDistrictSelect = document.getElementById('domicile_district');
    const domicileSubdistrictSelect = document.getElementById('domicile_subdistrict');

    domicileProvinceSelect.addEventListener('change', async function() {
        const provinceId = getSelectedId(this);
        
        disableSelect(domicileRegencySelect);
        disableSelect(domicileDistrictSelect);
        disableSelect(domicileSubdistrictSelect);
        
        if (provinceId) {
            try {
                const regencies = await regionalApi.getRegencies(provinceId);
                populateSelect(domicileRegencySelect, regencies, 'Pilih Kabupaten/Kota');
                enableSelect(domicileRegencySelect);
            } catch (error) {
                console.error('Error loading regencies:', error);
            }
        }
    });

    domicileRegencySelect.addEventListener('change', async function() {
        const regencyId = getSelectedId(this);
        
        disableSelect(domicileDistrictSelect);
        disableSelect(domicileSubdistrictSelect);
        
        if (regencyId) {
            try {
                const districts = await regionalApi.getDistricts(regencyId);
                populateSelect(domicileDistrictSelect, districts, 'Pilih Kecamatan');
                enableSelect(domicileDistrictSelect);
            } catch (error) {
                console.error('Error loading districts:', error);
            }
        }
    });

    domicileDistrictSelect.addEventListener('change', async function() {
        const districtId = getSelectedId(this);
        
        disableSelect(domicileSubdistrictSelect);
        
        if (districtId) {
            try {
                const villages = await regionalApi.getVillages(districtId);
                populateSelect(domicileSubdistrictSelect, villages, 'Pilih Kelurahan/Desa');
                enableSelect(domicileSubdistrictSelect);
            } catch (error) {
                console.error('Error loading villages:', error);
            }
        }
    });

    // ==================== AGE CALCULATION ====================
    const birthDateInput = document.getElementById('birth_date');
    const ageInput = document.getElementById('age');

    birthDateInput.addEventListener('change', function() {
        const birthDate = new Date(this.value);
        if (isNaN(birthDate.getTime())) {
            ageInput.value = '';
            return;
        }
        
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        // Adjust age if birthday hasn't occurred yet this year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        ageInput.value = age >= 0 ? age : 0;
    });

    // ==================== NEW PROJECT HANDLING ====================
    const projectSelect = document.getElementById('project_select');
    const newProjectInputContainer = document.getElementById('new_project_input_container');
    const newProjectNameDropdown = document.getElementById('new_project_name_dropdown');

    if (projectSelect) {
        projectSelect.addEventListener('change', function() {
            if (this.value === '__new__') {
                newProjectInputContainer.classList.remove('hidden');
                newProjectNameDropdown.required = true;
            } else {
                newProjectInputContainer.classList.add('hidden');
                newProjectNameDropdown.required = false;
                newProjectNameDropdown.value = '';
            }
        });
    }


    // ==================== INITIALIZE ON PAGE LOAD ====================
    document.addEventListener('DOMContentLoaded', function() {
        loadProvinces();
        
        // Calculate age if birth_date has value on load
        if (birthDateInput.value) {
            birthDateInput.dispatchEvent(new Event('change'));
        }
        
        // ==================== CSRF TOKEN AUTO-REFRESH ====================
        // Refresh CSRF token every 15 minutes to prevent 419 errors
        const CSRF_REFRESH_INTERVAL = 15 * 60 * 1000; // 15 minutes in milliseconds
        
        async function refreshCsrfToken() {
            try {
                const response = await fetch('{{ route("pdp.form") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                
                if (response.ok) {
                    const text = await response.text();
                    // Extract CSRF token from response
                    const match = text.match(/name="_token"\s+value="([^"]+)"/);
                    if (match && match[1]) {
                        // Update the hidden CSRF field in our form
                        const csrfInput = document.querySelector('input[name="_token"]');
                        if (csrfInput) {
                            csrfInput.value = match[1];
                        }
                        // Update meta tag
                        const metaCsrf = document.querySelector('meta[name="csrf-token"]');
                        if (metaCsrf) {
                            metaCsrf.content = match[1];
                        }
                        console.log('CSRF token refreshed successfully');
                    }
                }
            } catch (error) {
                console.error('Error refreshing CSRF token:', error);
            }
        }
        
        // Set interval to refresh CSRF token
        setInterval(refreshCsrfToken, CSRF_REFRESH_INTERVAL);
        
        // Also refresh when page becomes visible again (user returns to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                refreshCsrfToken();
            }
        });
    });
</script>
</body>
</html>

