<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for {{ $career->title }} - Jaya Sakti Mandiri Unggul</title>
    <link href="{{ asset('assets/css/tailwind.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/fonts/inter-local.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Mobile-first improvements */
        @media (max-width: 640px) {
            .form-container { padding: 1rem; }
            .section-header { font-size: 1rem; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-6 sm:py-12 px-3 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-6 sm:mb-8">
                <img src="{{ asset('images/admin-logo.png') }}" alt="Jaya Sakti Mandiri Unggul" class="h-10 sm:h-12 mx-auto mb-3 sm:mb-4">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Form Lamaran Kerja</h1>
                <p class="mt-2 text-sm sm:text-base text-gray-600">Posisi: <span class="font-semibold text-blue-600">{{ $career->title }}</span></p>
            </div>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden" style="min-height: 500px;">
                <div class="px-4 sm:px-8 py-4 sm:py-6 bg-slate-900 text-white">
                    <h2 class="text-lg sm:text-xl font-semibold">Data Diri Pelamar</h2>
                    <p class="text-slate-300 text-xs sm:text-sm mt-1">Mohon isi data dengan lengkap dan benar.</p>
                </div>
                
                <form action="{{ route('career.apply') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-8 pb-8 space-y-5 sm:space-y-6 form-container">
                    @csrf
                    <input type="hidden" name="cms_career_id" value="{{ $career->id }}">
                    
                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative text-sm" role="alert">
                            <strong class="font-bold">Berhasil!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative text-sm" role="alert">
                            <strong class="font-bold">Validasi Error!</strong>
                            <ul class="list-disc pl-4 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Section: Data Utama --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap (Sesuai KTP) <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Data Pribadi --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Data Pribadi</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                                <input type="text" name="birth_city" value="{{ old('birth_city') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Usia</label>
                                <input type="number" name="age" value="{{ old('age') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="gender" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                                    <option value="">Pilih</option>
                                    <option value="Laki-laki" @selected(old('gender') == 'Laki-laki')>Laki-laki</option>
                                    <option value="Perempuan" @selected(old('gender') == 'Perempuan')>Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Agama</label>
                                <input type="text" name="religion" value="{{ old('religion') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Golongan Darah</label>
                                <input type="text" name="blood_type" value="{{ old('blood_type') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu Kandung</label>
                                <input type="text" name="mother_name" value="{{ old('mother_name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Pernikahan</label>
                                <select name="marital_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                                    <option value="">Pilih</option>
                                    <option value="Belum Menikah" @selected(old('marital_status') == 'Belum Menikah')>Belum Menikah</option>
                                    <option value="Menikah" @selected(old('marital_status') == 'Menikah')>Menikah</option>
                                    <option value="Duda/Janda" @selected(old('marital_status') == 'Duda/Janda')>Duda/Janda</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Identitas & Fisik --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Identitas & Data Fisik</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. KTP</label>
                                <input type="text" name="ktp_number" value="{{ old('ktp_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. KK</label>
                                <input type="text" name="kk_number" value="{{ old('kk_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tinggi Badan (cm)</label>
                                <input type="number" name="height_cm" value="{{ old('height_cm') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Berat Badan (kg)</label>
                                <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Alamat Domisili --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Alamat Domisili</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Provinsi</label>
                                    <select name="domicile_province" id="domicile_province" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm">
                                        <option value="">- Pilih Provinsi -</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                                    <select name="domicile_regency" id="domicile_regency" disabled class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm bg-gray-100">
                                        <option value="">- Pilih Kabupaten/Kota -</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kecamatan</label>
                                    <select name="domicile_district" id="domicile_district" disabled class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm bg-gray-100">
                                        <option value="">- Pilih Kecamatan -</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kelurahan/Desa</label>
                                    <select name="domicile_subdistrict" id="domicile_subdistrict" disabled class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm bg-gray-100">
                                        <option value="">- Pilih Kelurahan/Desa -</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap (Jalan)</label>
                                <textarea name="domicile_street" id="domicile_street" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">{{ old('domicile_street') }}</textarea>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">RT</label>
                                    <input type="text" name="domicile_rt" id="domicile_rt" value="{{ old('domicile_rt') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">RW</label>
                                    <input type="text" name="domicile_rw" id="domicile_rw" value="{{ old('domicile_rw') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kode Pos</label>
                                    <input type="text" name="domicile_postal_code" id="domicile_postal_code" value="{{ old('domicile_postal_code') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Alamat KTP --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Alamat Sesuai KTP</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Provinsi</label>
                                    <select name="address_province" id="address_province" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm">
                                        <option value="">- Pilih Provinsi -</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                                    <select name="address_regency" id="address_regency" disabled class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm bg-gray-100">
                                        <option value="">- Pilih Kabupaten/Kota -</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kecamatan</label>
                                    <select name="address_district" id="address_district" disabled class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm bg-gray-100">
                                        <option value="">- Pilih Kecamatan -</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kelurahan/Desa</label>
                                    <select name="address_subdistrict" id="address_subdistrict" disabled class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm bg-gray-100">
                                        <option value="">- Pilih Kelurahan/Desa -</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap (Jalan)</label>
                                <textarea name="address_street" id="address_street" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">{{ old('address_street') }}</textarea>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">RT</label>
                                    <input type="text" name="address_rt" id="address_rt" value="{{ old('address_rt') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">RW</label>
                                    <input type="text" name="address_rw" id="address_rw" value="{{ old('address_rw') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kode Pos</label>
                                    <input type="text" name="address_postal_code" id="address_postal_code" value="{{ old('address_postal_code') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Identitas Tambahan --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Identitas Tambahan</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NPWP</label>
                                <input type="text" name="npwp" value="{{ old('npwp') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SIM A</label>
                                <input type="text" name="sim_a_number" value="{{ old('sim_a_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SIM C</label>
                                <input type="text" name="sim_c_number" value="{{ old('sim_c_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">BPJS Ketenagakerjaan</label>
                                <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">BPJS Kesehatan</label>
                                <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm sm:max-w-xs">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Keluarga & Kontak Darurat --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Keluarga & Kontak Darurat</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Anak</label>
                                <input type="number" name="children_count" value="{{ old('children_count', 0) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div></div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kontak Darurat</label>
                                <input type="text" name="emergency_name" value="{{ old('emergency_name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hubungan</label>
                                <input type="text" name="emergency_relation" value="{{ old('emergency_relation') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon Darurat</label>
                                <input type="text" name="emergency_phone" value="{{ old('emergency_phone') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm sm:max-w-xs">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Ukuran Seragam --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Ukuran Seragam</h3>
                        <div class="grid grid-cols-3 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Baju</label>
                                <input type="text" name="uniform_shirt_size" value="{{ old('uniform_shirt_size') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 sm:p-2.5 text-sm" placeholder="S/M/L/XL">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Celana</label>
                                <input type="text" name="uniform_pants_size" value="{{ old('uniform_pants_size') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 sm:p-2.5 text-sm" placeholder="30/32/34">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Sepatu</label>
                                <input type="text" name="uniform_shoes_size" value="{{ old('uniform_shoes_size') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2 sm:p-2.5 text-sm" placeholder="40/41/42">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Pendidikan --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Pendidikan Terakhir</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenjang Pendidikan</label>
                                <select name="education_level" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                                    <option value="">Pilih</option>
                                    <option value="SD" @selected(old('education_level') == 'SD')>SD</option>
                                    <option value="SMP" @selected(old('education_level') == 'SMP')>SMP</option>
                                    <option value="SMA/SMK" @selected(old('education_level') == 'SMA/SMK')>SMA/SMK</option>
                                    <option value="D3" @selected(old('education_level') == 'D3')>D3</option>
                                    <option value="S1" @selected(old('education_level') == 'S1')>S1</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Lulus</label>
                                <input type="text" name="education_graduation_year" value="{{ old('education_graduation_year') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah/Universitas</label>
                                <input type="text" name="education_school_name" value="{{ old('education_school_name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                                <input type="text" name="education_major" value="{{ old('education_major') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                                <input type="text" name="education_city" value="{{ old('education_city') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Sertifikat Satpam --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Sertifikasi Satpam <span class="text-gray-500 font-normal text-sm">(Jika Ada)</span></h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kualifikasi</label>
                                <select name="satpam_qualification" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                                    <option value="">Pilih</option>
                                    <option value="Gada Pratama" @selected(old('satpam_qualification') == 'Gada Pratama')>Gada Pratama</option>
                                    <option value="Gada Madya" @selected(old('satpam_qualification') == 'Gada Madya')>Gada Madya</option>
                                    <option value="Gada Utama" @selected(old('satpam_qualification') == 'Gada Utama')>Gada Utama</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pelatihan</label>
                                <input type="date" name="satpam_training_date" value="{{ old('satpam_training_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. KTA Satpam</label>
                                <input type="text" name="satpam_kta_number" value="{{ old('satpam_kta_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Sertifikat</label>
                                <input type="text" name="satpam_certificate_number" value="{{ old('satpam_certificate_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Institusi Pelatihan</label>
                                <input type="text" name="satpam_training_institution" value="{{ old('satpam_training_institution') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Pelatihan</label>
                                <input type="text" name="satpam_training_location" value="{{ old('satpam_training_location') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Pengalaman Kerja --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Pengalaman Kerja <span class="text-gray-500 font-normal text-sm">(Terakhir)</span></h3>
                        <div class="space-y-5">
                            {{-- Exp 1 --}}
                            <div class="p-3 sm:p-4 bg-gray-50 rounded-lg">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Pengalaman 1</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="text" name="exp1_company" placeholder="Nama Perusahaan" value="{{ old('exp1_company') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="exp1_position" placeholder="Posisi / Jabatan" value="{{ old('exp1_position') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="exp1_year" placeholder="Tahun (2020-2022)" value="{{ old('exp1_year') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="exp1_city" placeholder="Kota" value="{{ old('exp1_city') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                </div>
                            </div>
                            {{-- Exp 2 --}}
                            <div class="p-3 sm:p-4 bg-gray-50 rounded-lg">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Pengalaman 2</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="text" name="exp2_company" placeholder="Nama Perusahaan" value="{{ old('exp2_company') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="exp2_position" placeholder="Posisi / Jabatan" value="{{ old('exp2_position') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="exp2_year" placeholder="Tahun" value="{{ old('exp2_year') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="exp2_city" placeholder="Kota" value="{{ old('exp2_city') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Sertifikasi Lain --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Sertifikasi / Pelatihan Lain</h3>
                        <div class="space-y-5">
                            {{-- Cert 1 --}}
                            <div class="p-3 sm:p-4 bg-gray-50 rounded-lg">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Sertifikasi 1</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="text" name="cert1_training" placeholder="Nama Pelatihan" value="{{ old('cert1_training') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="cert1_organizer" placeholder="Penyelenggara" value="{{ old('cert1_organizer') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="date" name="cert1_date" value="{{ old('cert1_date') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="cert1_city" placeholder="Kota" value="{{ old('cert1_city') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                </div>
                            </div>
                            {{-- Cert 2 --}}
                            <div class="p-3 sm:p-4 bg-gray-50 rounded-lg">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Sertifikasi 2</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="text" name="cert2_training" placeholder="Nama Pelatihan" value="{{ old('cert2_training') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="cert2_organizer" placeholder="Penyelenggara" value="{{ old('cert2_organizer') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="date" name="cert2_date" value="{{ old('cert2_date') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                    <input type="text" name="cert2_city" placeholder="Kota" value="{{ old('cert2_city') }}" class="w-full rounded-md border-gray-300 text-sm p-2 border">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Social Media --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Media Sosial</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
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
                    </div>

                    {{-- Section: Resume Upload --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 section-header">Dokumen</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resume / CV <span class="text-red-500">*</span></label>
                            <div class="flex justify-center px-4 sm:px-6 pt-4 pb-5 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition-colors">
                                <div class="space-y-2 text-center">
                                    <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex flex-col sm:flex-row text-sm text-gray-600 justify-center items-center gap-1">
                                        <label for="resume-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                            <span>Pilih File</span>
                                            <input id="resume-upload" name="resume" type="file" class="sr-only" accept=".pdf,.doc,.docx" required>
                                        </label>
                                        <p>atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF, DOC, DOCX (Maks. 2MB)</p>
                                    <p id="file-name" class="text-sm text-blue-600 font-medium hidden"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Cover Letter --}}
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan</label>
                        <textarea name="cover_letter" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 border p-2.5 text-sm" placeholder="Jelaskan mengapa Anda cocok untuk posisi ini...">{{ old('cover_letter') }}</textarea>
                    </div>

                    {{-- Section: Consent & Signature --}}
                    <div class="pt-6 border-t-4 border-blue-500">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Persetujuan & Tanda Tangan</h3>
                        
                        {{-- Consent Checkbox --}}
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox" id="data-consent-checkbox" name="data_consent" value="1" class="mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" {{ old('data_consent') ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">
                                    <strong class="text-gray-900">Persetujuan Pengumpulan Data</strong><br>
                                    Saya menyetujui bahwa data pribadi yang saya berikan akan dikelola oleh PT. JSMU SECURITY INDONESIA untuk keperluan proses rekrutmen dan administrasi karyawan sesuai dengan kebijakan privasi yang berlaku. Data yang saya berikan adalah benar dan dapat dipertanggungjawabkan.
                                </span>
                            </label>
                            @error('data_consent')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Signature Canvas --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan <span class="text-red-500">*</span></label>
                            <div class="border-2 border-gray-300 rounded-lg p-2 bg-white">
                                <canvas id="signature-canvas" class="w-full border border-gray-200 rounded cursor-crosshair" style="height: 150px; touch-action: none;"></canvas>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-xs text-gray-500">Gambar tanda tangan Anda menggunakan mouse atau jari (pada layar sentuh)</p>
                                <button type="button" id="clear-signature" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                    Hapus Tanda Tangan
                                </button>
                            </div>
                            <input type="hidden" id="signature-data" name="signature" value="{{ old('signature') }}">
                            @error('signature')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <a href="{{ route('career') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 order-2 sm:order-1">← Kembali ke Karir</a>
                        <button type="submit" id="submit-btn" disabled class="w-full sm:w-auto inline-flex justify-center py-4 px-12 text-lg font-bold rounded-lg text-white bg-gray-400 cursor-not-allowed shadow-2xl border-4 border-gray-300 order-1 sm:order-2 transition-all duration-200">
                            🚀 KIRIM LAMARAN SEKARANG
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-6 sm:mt-8 text-xs sm:text-sm text-gray-500">
                &copy; {{ date('Y') }} Jaya Sakti Mandiri Unggul. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        // ==================== FILE UPLOAD ====================
        const fileInput = document.getElementById('resume-upload');
        const fileNameDisplay = document.getElementById('file-name');

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileNameDisplay.textContent = 'File: ' + e.target.files[0].name;
                fileNameDisplay.classList.remove('hidden');
            } else {
                fileNameDisplay.classList.add('hidden');
            }
        });

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
                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed', 'border-gray-300');
                submitBtn.classList.add('bg-green-600', 'hover:bg-green-700', 'border-yellow-400');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'border-gray-300');
                submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700', 'border-yellow-400');
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

        // ==================== ADDRESS CASCADING (KTP) ====================
        const addressProvinceSelect = document.getElementById('address_province');
        const addressRegencySelect = document.getElementById('address_regency');
        const addressDistrictSelect = document.getElementById('address_district');
        const addressSubdistrictSelect = document.getElementById('address_subdistrict');

        addressProvinceSelect.addEventListener('change', async function() {
            const provinceId = getSelectedId(this);
            
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

        // ==================== LOAD PROVINCES ON PAGE LOAD ====================
        async function loadProvinces() {
            try {
                const provinces = await regionalApi.getProvinces();
                populateSelect(domicileProvinceSelect, provinces, 'Pilih Provinsi');
                populateSelect(addressProvinceSelect, provinces, 'Pilih Provinsi');
            } catch (error) {
                console.error('Error loading provinces:', error);
            }
        }

        // Load provinces when page loads
        loadProvinces();
    </script>
</body>
</html>
