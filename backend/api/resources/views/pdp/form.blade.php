<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Data Pribadi Karyawan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex items-center justify-center py-8">
    <div class="w-full max-w-5xl bg-white shadow-md rounded-lg p-6 text-sm">
        <h1 class="text-xl font-semibold mb-1 text-gray-800">Form Data Pribadi Karyawan (PDP)</h1>
        <p class="mb-4 text-gray-600">Silakan isi data berikut dengan lengkap dan benar.</p>

        @if(session('status'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-xs">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800 text-xs">
                <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                    <label class="block text-gray-700 mb-1 text-xs">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Lokasi Tugas (Project)</label>
                    <select name="active_project_id" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <option value="">- Pilih Lokasi -</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('active_project_id') == $project->id)>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Jabatan</label>
                    <input type="text" name="position" value="{{ old('position') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Divisi</label>
                    <input type="text" name="division" value="{{ old('division') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Foto Profil</label>
                    <input type="file" name="profile_photo" accept="image/*" class="w-full text-xs">
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
                    <label class="block text-gray-700 mb-1 text-xs">Tingkat</label>
                    <input type="text" name="education_level" value="{{ old('education_level') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs" placeholder="SMA / D3 / S1">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Tahun Lulus</label>
                    <input type="text" name="education_graduation_year" value="{{ old('education_graduation_year') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nama Sekolah / Univ</label>
                    <input type="text" name="education_school_name" value="{{ old('education_school_name') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kota</label>
                    <input type="text" name="education_city" value="{{ old('education_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Jurusan</label>
                    <input type="text" name="education_major" value="{{ old('education_major') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Data Pribadi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kota Lahir</label>
                    <input type="text" name="birth_city" value="{{ old('birth_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Usia</label>
                    <input type="number" name="age" value="{{ old('age') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Jenis Kelamin</label>
                    <input type="text" name="gender" value="{{ old('gender') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs" placeholder="L / P">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nama Ibu Kandung</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Agama</label>
                    <input type="text" name="religion" value="{{ old('religion') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Golongan Darah</label>
                    <input type="text" name="blood_type" value="{{ old('blood_type') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. Handphone</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Email Pribadi</label>
                    <input type="email" name="personal_email" value="{{ old('personal_email') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Postur & Seragam</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Tinggi Badan (cm)</label>
                    <input type="number" name="height_cm" value="{{ old('height_cm') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Berat Badan (kg)</label>
                    <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Ukuran Baju</label>
                    <input type="text" name="uniform_shirt_size" value="{{ old('uniform_shirt_size') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Ukuran Celana</label>
                    <input type="text" name="uniform_pants_size" value="{{ old('uniform_pants_size') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Ukuran Sepatu</label>
                    <input type="text" name="uniform_shoes_size" value="{{ old('uniform_shoes_size') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Telp Darurat</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">No. Telp Darurat</label>
                    <input type="text" name="emergency_phone" value="{{ old('emergency_phone') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Nama Pemilik</label>
                    <input type="text" name="emergency_name" value="{{ old('emergency_name') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Hubungan</label>
                    <input type="text" name="emergency_relation" value="{{ old('emergency_relation') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
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
                    <label class="block text-gray-700 mb-1 text-xs">No. KK</label>
                    <input type="text" name="kk_number" value="{{ old('kk_number') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Alamat KTP</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Provinsi</label>
                    <input type="text" name="address_province" value="{{ old('address_province') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kabupaten</label>
                    <input type="text" name="address_regency" value="{{ old('address_regency') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kecamatan</label>
                    <input type="text" name="address_district" value="{{ old('address_district') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-1 text-xs">Kelurahan</label>
                    <input type="text" name="address_subdistrict" value="{{ old('address_subdistrict') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-gray-700 mb-1 text-xs">Desa / Jalan</label>
                    <input type="text" name="address_street" value="{{ old('address_street') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">RT</label>
                    <input type="text" name="address_rt" value="{{ old('address_rt') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">RW</label>
                    <input type="text" name="address_rw" value="{{ old('address_rw') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kode Pos</label>
                    <input type="text" name="address_postal_code" value="{{ old('address_postal_code') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Domisili</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Provinsi</label>
                    <input type="text" name="domicile_province" value="{{ old('domicile_province') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kabupaten</label>
                    <input type="text" name="domicile_regency" value="{{ old('domicile_regency') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kecamatan</label>
                    <input type="text" name="domicile_district" value="{{ old('domicile_district') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-1 text-xs">Kelurahan</label>
                    <input type="text" name="domicile_subdistrict" value="{{ old('domicile_subdistrict') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-gray-700 mb-1 text-xs">Desa / Jalan</label>
                    <input type="text" name="domicile_street" value="{{ old('domicile_street') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">RT</label>
                    <input type="text" name="domicile_rt" value="{{ old('domicile_rt') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">RW</label>
                    <input type="text" name="domicile_rw" value="{{ old('domicile_rw') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Kode Pos</label>
                    <input type="text" name="domicile_postal_code" value="{{ old('domicile_postal_code') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Status Pernikahan</label>
                    <input type="text" name="marital_status" value="{{ old('marital_status') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1 text-xs">Jumlah Anak</label>
                    <input type="number" name="children_count" value="{{ old('children_count') }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Pengalaman Kerja</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                <div class="md:col-span-1 font-semibold text-gray-700">Pengalaman 1</div>
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
                <div class="md:col-start-2">
                    <label class="block text-gray-700 mb-1">Kota</label>
                    <input type="text" name="exp1_city" value="{{ old('exp1_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                </div>

                <div class="md:col-span-1 font-semibold text-gray-700 mt-2">Pengalaman 2</div>
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
                <div class="md:col-start-2">
                    <label class="block text-gray-700 mb-1">Kota</label>
                    <input type="text" name="exp2_city" value="{{ old('exp2_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                </div>

                <div class="md:col-span-1 font-semibold text-gray-700 mt-2">Pengalaman 3</div>
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
                <div class="md:col-start-2">
                    <label class="block text-gray-700 mb-1">Kota</label>
                    <input type="text" name="exp3_city" value="{{ old('exp3_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                </div>
            </div>

            <h2 class="text-sm font-semibold text-gray-800">Sertifikasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                <div class="md:col-span-1 font-semibold text-gray-700">Sertifikasi 1</div>
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
                <div class="md:col-start-2">
                    <label class="block text-gray-700 mb-1">Kota</label>
                    <input type="text" name="cert1_city" value="{{ old('cert1_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                </div>

                <div class="md:col-span-1 font-semibold text-gray-700 mt-2">Sertifikasi 2</div>
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
                <div class="md:col-start-2">
                    <label class="block text-gray-700 mb-1">Kota</label>
                    <input type="text" name="cert2_city" value="{{ old('cert2_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
                </div>

                <div class="md:col-span-1 font-semibold text-gray-700 mt-2">Sertifikasi 3</div>
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
                <div class="md:col-start-2">
                    <label class="block text-gray-700 mb-1">Kota</label>
                    <input type="text" name="cert3_city" value="{{ old('cert3_city') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
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

            <div class="flex justify-end mt-4">
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700">Kirim Data</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
