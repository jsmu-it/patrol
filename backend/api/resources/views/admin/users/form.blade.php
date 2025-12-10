@php($isEdit = isset($user))
@php($profile = isset($user) ? $user->profile : null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs mb-6">
    <div>
        <label class="block text-gray-600 mb-1">Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5" required>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Username</label>
        <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5" required>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Role</label>
        <select name="role" class="w-full border border-gray-300 rounded px-2 py-1.5" required>
            <option value="SUPERADMIN" @selected(old('role', $user->role ?? '') === 'SUPERADMIN')>SUPERADMIN</option>
            <option value="ADMIN" @selected(old('role', $user->role ?? '') === 'ADMIN')>ADMIN</option>
            <option value="PROJECT_ADMIN" @selected(old('role', $user->role ?? '') === 'PROJECT_ADMIN')>PROJECT ADMIN</option>
            <option value="HRD" @selected(old('role', $user->role ?? '') === 'HRD')>HRD</option>
            <option value="PAYROLL" @selected(old('role', $user->role ?? '') === 'PAYROLL')>PAYROLL</option>
            <option value="CMS" @selected(old('role', $user->role ?? '') === 'CMS')>CMS</option>
            <option value="GUARD" @selected(old('role', $user->role ?? '') === 'GUARD')>GUARD</option>
        </select>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Password @if($isEdit)<span class="text-gray-400">(kosongkan jika tidak diubah)</span>@endif</label>
        <input type="password" name="password" class="w-full border border-gray-300 rounded px-2 py-1.5" @if(! $isEdit) required @endif>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Lokasi Tugas (Project Aktif)</label>
        <div class="flex gap-2 items-center">
            <select name="active_project_id" class="w-full border border-gray-300 rounded px-2 py-1.5">
                <option value="">- Tidak ada -</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string)old('active_project_id', $user->active_project_id ?? '') === (string)$project->id)>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
            <a href="{{ route('admin.projects.create') }}" target="_blank" class="text-[10px] text-blue-600 underline whitespace-nowrap">+ Tambah lokasi</a>
        </div>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Foto Profil</label>
        <input type="file" name="profile_photo" accept="image/*" class="w-full text-xs">
        @if($isEdit && $profile && $profile->profile_photo_path)
            <div class="mt-2">
                <img src="{{ asset('storage/'.$profile->profile_photo_path) }}" alt="Foto profil" class="h-16 w-16 rounded-full object-cover border border-gray-300">
            </div>
        @endif
    </div>
</div>

<div class="border-t border-gray-200 pt-4 mt-4 text-xs space-y-4">
    <h2 class="text-sm font-semibold text-gray-800">Data Karyawan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">NIP</label>
            <input type="text" name="nip" value="{{ old('nip', $profile->nip ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Jabatan</label>
            <input type="text" name="position" value="{{ old('position', $profile->position ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Divisi</label>
            <input type="text" name="division" value="{{ old('division', $profile->division ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Tanggal Masuk</label>
            <input type="date" name="join_date" value="{{ old('join_date', optional(optional($profile)->join_date)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Masa Kontrak</label>
            <input type="text" name="contract_period" value="{{ old('contract_period', $profile->contract_period ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5" placeholder="mis. 1 tahun">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Status</label>
            <input type="text" name="employment_status" value="{{ old('employment_status', $profile->employment_status ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5" placeholder="mis. Aktif / Non Aktif">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">No. KTP</label>
            <input type="text" name="ktp_number" value="{{ old('ktp_number', $profile->ktp_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Pendidikan Satpam</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">Kualifikasi</label>
            <input type="text" name="satpam_qualification" value="{{ old('satpam_qualification', $profile->satpam_qualification ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Tanggal Pendidikan</label>
            <input type="date" name="satpam_training_date" value="{{ old('satpam_training_date', optional(optional($profile)->satpam_training_date)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Instansi Penyelenggara</label>
            <input type="text" name="satpam_training_institution" value="{{ old('satpam_training_institution', $profile->satpam_training_institution ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Lokasi Diklat</label>
            <input type="text" name="satpam_training_location" value="{{ old('satpam_training_location', $profile->satpam_training_location ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">No. KTA</label>
            <input type="text" name="satpam_kta_number" value="{{ old('satpam_kta_number', $profile->satpam_kta_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">No. Ijazah</label>
            <input type="text" name="satpam_certificate_number" value="{{ old('satpam_certificate_number', $profile->satpam_certificate_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Pendidikan Akademis</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">Tingkat</label>
            <input type="text" name="education_level" value="{{ old('education_level', $profile->education_level ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5" placeholder="mis. SMA / D3 / S1">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Tahun Lulus</label>
            <input type="text" name="education_graduation_year" value="{{ old('education_graduation_year', $profile->education_graduation_year ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nama Sekolah / Univ</label>
            <input type="text" name="education_school_name" value="{{ old('education_school_name', $profile->education_school_name ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Kota</label>
            <input type="text" name="education_city" value="{{ old('education_city', $profile->education_city ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Jurusan</label>
            <input type="text" name="education_major" value="{{ old('education_major', $profile->education_major ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Data Pribadi (BOD)</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">Kota Lahir</label>
            <input type="text" name="birth_city" value="{{ old('birth_city', $profile->birth_city ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Tanggal Lahir</label>
            <input type="date" name="birth_date" value="{{ old('birth_date', optional(optional($profile)->birth_date)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Usia</label>
            <input type="number" name="age" value="{{ old('age', $profile->age ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Jenis Kelamin</label>
            <input type="text" name="gender" value="{{ old('gender', $profile->gender ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5" placeholder="L / P">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nama Ibu Kandung</label>
            <input type="text" name="mother_name" value="{{ old('mother_name', $profile->mother_name ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Agama</label>
            <input type="text" name="religion" value="{{ old('religion', $profile->religion ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Golongan Darah</label>
            <input type="text" name="blood_type" value="{{ old('blood_type', $profile->blood_type ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">No. Handphone</label>
            <input type="text" name="phone_number" value="{{ old('phone_number', $profile->phone_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Email Pribadi</label>
            <input type="email" name="personal_email" value="{{ old('personal_email', $profile->personal_email ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Postur & Seragam</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">Tinggi Badan (cm)</label>
            <input type="number" name="height_cm" value="{{ old('height_cm', $profile->height_cm ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Berat Badan (kg)</label>
            <input type="number" name="weight_kg" value="{{ old('weight_kg', $profile->weight_kg ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Ukuran Baju</label>
            <input type="text" name="uniform_shirt_size" value="{{ old('uniform_shirt_size', $profile->uniform_shirt_size ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Ukuran Celana</label>
            <input type="text" name="uniform_pants_size" value="{{ old('uniform_pants_size', $profile->uniform_pants_size ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Ukuran Sepatu</label>
            <input type="text" name="uniform_shoes_size" value="{{ old('uniform_shoes_size', $profile->uniform_shoes_size ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Telp Darurat</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">No. Telp Darurat</label>
            <input type="text" name="emergency_phone" value="{{ old('emergency_phone', $profile->emergency_phone ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nama Pemilik</label>
            <input type="text" name="emergency_name" value="{{ old('emergency_name', $profile->emergency_name ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Hubungan</label>
            <input type="text" name="emergency_relation" value="{{ old('emergency_relation', $profile->emergency_relation ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Identitas</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">NPWP</label>
            <input type="text" name="npwp" value="{{ old('npwp', $profile->npwp ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nomor SIM C</label>
            <input type="text" name="sim_c_number" value="{{ old('sim_c_number', $profile->sim_c_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nomor SIM A</label>
            <input type="text" name="sim_a_number" value="{{ old('sim_a_number', $profile->sim_a_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">No. BPJS TK</label>
            <input type="text" name="bpjs_tk_number" value="{{ old('bpjs_tk_number', $profile->bpjs_tk_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">No. BPJS KES</label>
            <input type="text" name="bpjs_kes_number" value="{{ old('bpjs_kes_number', $profile->bpjs_kes_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">No. KK</label>
            <input type="text" name="kk_number" value="{{ old('kk_number', $profile->kk_number ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Alamat KTP</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">Provinsi</label>
            <input type="text" name="address_province" value="{{ old('address_province', $profile->address_province ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Kabupaten</label>
            <input type="text" name="address_regency" value="{{ old('address_regency', $profile->address_regency ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Kecamatan</label>
            <input type="text" name="address_district" value="{{ old('address_district', $profile->address_district ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-600 mb-1">Kelurahan</label>
            <input type="text" name="address_subdistrict" value="{{ old('address_subdistrict', $profile->address_subdistrict ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-span-3">
            <label class="block text-gray-600 mb-1">Desa / Jalan</label>
            <input type="text" name="address_street" value="{{ old('address_street', $profile->address_street ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">RT</label>
            <input type="text" name="address_rt" value="{{ old('address_rt', $profile->address_rt ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">RW</label>
            <input type="text" name="address_rw" value="{{ old('address_rw', $profile->address_rw ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Kode Pos</label>
            <input type="text" name="address_postal_code" value="{{ old('address_postal_code', $profile->address_postal_code ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Domisili</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">Provinsi</label>
            <input type="text" name="domicile_province" value="{{ old('domicile_province', $profile->domicile_province ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Kabupaten</label>
            <input type="text" name="domicile_regency" value="{{ old('domicile_regency', $profile->domicile_regency ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Kecamatan</label>
            <input type="text" name="domicile_district" value="{{ old('domicile_district', $profile->domicile_district ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-600 mb-1">Kelurahan</label>
            <input type="text" name="domicile_subdistrict" value="{{ old('domicile_subdistrict', $profile->domicile_subdistrict ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-span-3">
            <label class="block text-gray-600 mb-1">Desa / Jalan</label>
            <input type="text" name="domicile_street" value="{{ old('domicile_street', $profile->domicile_street ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">RT</label>
            <input type="text" name="domicile_rt" value="{{ old('domicile_rt', $profile->domicile_rt ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">RW</label>
            <input type="text" name="domicile_rw" value="{{ old('domicile_rw', $profile->domicile_rw ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Kode Pos</label>
            <input type="text" name="domicile_postal_code" value="{{ old('domicile_postal_code', $profile->domicile_postal_code ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Status Pernikahan</label>
            <input type="text" name="marital_status" value="{{ old('marital_status', $profile->marital_status ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Jumlah Anak</label>
            <input type="number" name="children_count" value="{{ old('children_count', $profile->children_count ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Pengalaman Kerja</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-1 font-semibold text-gray-700">Pengalaman 1</div>
        <div>
            <label class="block text-gray-600 mb-1">Tahun</label>
            <input type="text" name="exp1_year" value="{{ old('exp1_year', $profile->exp1_year ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Posisi</label>
            <input type="text" name="exp1_position" value="{{ old('exp1_position', $profile->exp1_position ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nama Perusahaan</label>
            <input type="text" name="exp1_company" value="{{ old('exp1_company', $profile->exp1_company ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-start-2">
            <label class="block text-gray-600 mb-1">Kota</label>
            <input type="text" name="exp1_city" value="{{ old('exp1_city', $profile->exp1_city ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>

        <div class="md:col-span-1 font-semibold text-gray-700 mt-2">Pengalaman 2</div>
        <div>
            <label class="block text-gray-600 mb-1">Tahun</label>
            <input type="text" name="exp2_year" value="{{ old('exp2_year', $profile->exp2_year ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Posisi</label>
            <input type="text" name="exp2_position" value="{{ old('exp2_position', $profile->exp2_position ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nama Perusahaan</label>
            <input type="text" name="exp2_company" value="{{ old('exp2_company', $profile->exp2_company ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-start-2">
            <label class="block text-gray-600 mb-1">Kota</label>
            <input type="text" name="exp2_city" value="{{ old('exp2_city', $profile->exp2_city ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>

        <div class="md:col-span-1 font-semibold text-gray-700 mt-2">Pengalaman 3</div>
        <div>
            <label class="block text-gray-600 mb-1">Tahun</label>
            <input type="text" name="exp3_year" value="{{ old('exp3_year', $profile->exp3_year ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Posisi</label>
            <input type="text" name="exp3_position" value="{{ old('exp3_position', $profile->exp3_position ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Nama Perusahaan</label>
            <input type="text" name="exp3_company" value="{{ old('exp3_company', $profile->exp3_company ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-start-2">
            <label class="block text-gray-600 mb-1">Kota</label>
            <input type="text" name="exp3_city" value="{{ old('exp3_city', $profile->exp3_city ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Sertifikasi</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-1 font-semibold text-gray-700">Sertifikasi 1</div>
        <div>
            <label class="block text-gray-600 mb-1">Tanggal</label>
            <input type="date" name="cert1_date" value="{{ old('cert1_date', optional(optional($profile)->cert1_date)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Pelatihan</label>
            <input type="text" name="cert1_training" value="{{ old('cert1_training', $profile->cert1_training ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Penyelenggara</label>
            <input type="text" name="cert1_organizer" value="{{ old('cert1_organizer', $profile->cert1_organizer ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-start-2">
            <label class="block text-gray-600 mb-1">Kota</label>
            <input type="text" name="cert1_city" value="{{ old('cert1_city', $profile->cert1_city ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>

        <div class="md:col-span-1 font-semibold text-gray-700 mt-2">Sertifikasi 2</div>
        <div>
            <label class="block text-gray-600 mb-1">Tanggal</label>
            <input type="date" name="cert2_date" value="{{ old('cert2_date', optional(optional($profile)->cert2_date)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Pelatihan</label>
            <input type="text" name="cert2_training" value="{{ old('cert2_training', $profile->cert2_training ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Penyelenggara</label>
            <input type="text" name="cert2_organizer" value="{{ old('cert2_organizer', $profile->cert2_organizer ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-start-2">
            <label class="block text-gray-600 mb-1">Kota</label>
            <input type="text" name="cert2_city" value="{{ old('cert2_city', $profile->cert2_city ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>

        <div class="md:col-span-1 font-semibold text-gray-700 mt-2">Sertifikasi 3</div>
        <div>
            <label class="block text-gray-600 mb-1">Tanggal</label>
            <input type="date" name="cert3_date" value="{{ old('cert3_date', optional(optional($profile)->cert3_date)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Pelatihan</label>
            <input type="text" name="cert3_training" value="{{ old('cert3_training', $profile->cert3_training ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Penyelenggara</label>
            <input type="text" name="cert3_organizer" value="{{ old('cert3_organizer', $profile->cert3_organizer ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div class="md:col-start-2">
            <label class="block text-gray-600 mb-1">Kota</label>
            <input type="text" name="cert3_city" value="{{ old('cert3_city', $profile->cert3_city ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>

    <h2 class="text-sm font-semibold text-gray-800 mt-4">Media Sosial</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-gray-600 mb-1">Instagram</label>
            <input type="text" name="instagram" value="{{ old('instagram', $profile->instagram ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">Facebook</label>
            <input type="text" name="facebook" value="{{ old('facebook', $profile->facebook ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">X (Twitter)</label>
            <input type="text" name="twitter" value="{{ old('twitter', $profile->twitter ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">TikTok</label>
            <input type="text" name="tiktok" value="{{ old('tiktok', $profile->tiktok ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">LinkedIn</label>
            <input type="text" name="linkedin" value="{{ old('linkedin', $profile->linkedin ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
        <div>
            <label class="block text-gray-600 mb-1">YouTube</label>
            <input type="text" name="youtube" value="{{ old('youtube', $profile->youtube ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </div>
    </div>
</div>
