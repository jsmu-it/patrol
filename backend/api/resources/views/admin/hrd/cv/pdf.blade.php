<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CV - {{ $user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; line-height: 1.4; color: #333; }
        .container { padding: 20px; }
        .header { display: table; width: 100%; margin-bottom: 15px; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .photo-col { display: table-cell; width: 100px; vertical-align: top; }
        .photo { width: 90px; height: 120px; object-fit: cover; border: 1px solid #ddd; }
        .photo-placeholder { width: 90px; height: 120px; background: #f3f4f6; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; color: #999; font-size: 8px; }
        .info-col { display: table-cell; vertical-align: top; padding-left: 15px; }
        .name { font-size: 18px; font-weight: bold; color: #1e40af; margin-bottom: 5px; }
        .position { font-size: 12px; color: #4b5563; margin-bottom: 10px; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 80px; color: #6b7280; padding: 2px 0; }
        .info-value { display: table-cell; padding: 2px 0; }
        
        .section { margin-bottom: 12px; }
        .section-title { font-size: 12px; font-weight: bold; color: #1e40af; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; margin-bottom: 8px; }
        
        .two-col { display: table; width: 100%; }
        .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
        .col:last-child { padding-right: 0; padding-left: 10px; }
        
        table.data { width: 100%; border-collapse: collapse; }
        table.data td { padding: 2px 0; vertical-align: top; }
        table.data td.label { width: 35%; color: #6b7280; }
        
        .exp-item { border-left: 2px solid #2563eb; padding-left: 8px; margin-bottom: 6px; }
        .exp-title { font-weight: bold; }
        .exp-detail { color: #6b7280; }
        
        .cert-item { border-left: 2px solid #10b981; padding-left: 8px; margin-bottom: 6px; }
        
        .social { color: #6b7280; }
        
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
@php $profile = $user->profile; @endphp

<div class="container">
    <div class="header">
        <div class="photo-col">
            @if($profile && $profile->profile_photo_path)
                <img src="{{ public_path('storage/' . $profile->profile_photo_path) }}" class="photo" alt="Foto">
            @else
                <div class="photo-placeholder">No Photo</div>
            @endif
        </div>
        <div class="info-col">
            <div class="name">{{ $user->name }}</div>
            <div class="position">{{ $profile->position ?? '-' }} - {{ $profile->division ?? '-' }}</div>
            <div class="info-grid">
                <div class="info-row"><span class="info-label">NIP</span><span class="info-value">: {{ $profile->nip ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">Project</span><span class="info-value">: {{ $user->activeProject->name ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">Email</span><span class="info-value">: {{ $user->email ?? $profile->personal_email ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">No. HP</span><span class="info-value">: {{ $profile->phone_number ?? '-' }}</span></div>
            </div>
        </div>
    </div>

    <div class="two-col">
        <div class="col">
            <div class="section">
                <div class="section-title">Data Pribadi</div>
                <table class="data">
                    <tr><td class="label">Tempat, Tgl Lahir</td><td>{{ $profile->birth_city ?? '-' }}, {{ $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->format('d M Y') : '-' }}</td></tr>
                    <tr><td class="label">Usia</td><td>{{ $profile->age ?? '-' }} tahun</td></tr>
                    <tr><td class="label">Jenis Kelamin</td><td>{{ $profile->gender == 'L' ? 'Laki-laki' : ($profile->gender == 'P' ? 'Perempuan' : ($profile->gender ?? '-')) }}</td></tr>
                    <tr><td class="label">Agama</td><td>{{ $profile->religion ?? '-' }}</td></tr>
                    <tr><td class="label">Gol. Darah</td><td>{{ $profile->blood_type ?? '-' }}</td></tr>
                    <tr><td class="label">Status</td><td>{{ $profile->marital_status ?? '-' }}</td></tr>
                    <tr><td class="label">Jumlah Anak</td><td>{{ $profile->children_count ?? '0' }}</td></tr>
                    <tr><td class="label">Nama Ibu</td><td>{{ $profile->mother_name ?? '-' }}</td></tr>
                </table>
            </div>
            
            <div class="section">
                <div class="section-title">Alamat KTP</div>
                <p>{{ $profile->address_street ?? '' }} @if($profile->address_rt || $profile->address_rw) RT {{ $profile->address_rt ?? '-' }} / RW {{ $profile->address_rw ?? '-' }} @endif</p>
                <p style="color: #6b7280;">{{ $profile->address_subdistrict ?? '' }}, {{ $profile->address_district ?? '' }}, {{ $profile->address_regency ?? '' }}, {{ $profile->address_province ?? '' }} {{ $profile->address_postal_code ?? '' }}</p>
            </div>
            
            <div class="section">
                <div class="section-title">Alamat Domisili</div>
                <p>{{ $profile->domicile_street ?? '' }} @if($profile->domicile_rt || $profile->domicile_rw) RT {{ $profile->domicile_rt ?? '-' }} / RW {{ $profile->domicile_rw ?? '-' }} @endif</p>
                <p style="color: #6b7280;">{{ $profile->domicile_subdistrict ?? '' }}, {{ $profile->domicile_district ?? '' }}, {{ $profile->domicile_regency ?? '' }}, {{ $profile->domicile_province ?? '' }} {{ $profile->domicile_postal_code ?? '' }}</p>
            </div>
        </div>
        
        <div class="col">
            <div class="section">
                <div class="section-title">Postur & Seragam</div>
                <table class="data">
                    <tr><td class="label">Tinggi Badan</td><td>{{ $profile->height_cm ?? '-' }} cm</td></tr>
                    <tr><td class="label">Berat Badan</td><td>{{ $profile->weight_kg ?? '-' }} kg</td></tr>
                    <tr><td class="label">Ukuran Baju</td><td>{{ $profile->uniform_shirt_size ?? '-' }}</td></tr>
                    <tr><td class="label">Ukuran Celana</td><td>{{ $profile->uniform_pants_size ?? '-' }}</td></tr>
                    <tr><td class="label">Ukuran Sepatu</td><td>{{ $profile->uniform_shoes_size ?? '-' }}</td></tr>
                </table>
            </div>
            
            <div class="section">
                <div class="section-title">Identitas</div>
                <table class="data">
                    <tr><td class="label">No. KTP</td><td>{{ $profile->ktp_number ?? '-' }}</td></tr>
                    <tr><td class="label">No. KK</td><td>{{ $profile->kk_number ?? '-' }}</td></tr>
                    <tr><td class="label">NPWP</td><td>{{ $profile->npwp ?? '-' }}</td></tr>
                    <tr><td class="label">SIM A</td><td>{{ $profile->sim_a_number ?? '-' }}</td></tr>
                    <tr><td class="label">SIM C</td><td>{{ $profile->sim_c_number ?? '-' }}</td></tr>
                    <tr><td class="label">BPJS TK</td><td>{{ $profile->bpjs_tk_number ?? '-' }}</td></tr>
                    <tr><td class="label">BPJS KES</td><td>{{ $profile->bpjs_kes_number ?? '-' }}</td></tr>
                </table>
            </div>
            
            <div class="section">
                <div class="section-title">Kontak Darurat</div>
                <table class="data">
                    <tr><td class="label">Nama</td><td>{{ $profile->emergency_name ?? '-' }}</td></tr>
                    <tr><td class="label">No. Telp</td><td>{{ $profile->emergency_phone ?? '-' }}</td></tr>
                    <tr><td class="label">Hubungan</td><td>{{ $profile->emergency_relation ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Pendidikan Akademis</div>
        <table class="data">
            <tr><td class="label" style="width:20%">Tingkat</td><td>{{ $profile->education_level ?? '-' }}</td></tr>
            <tr><td class="label">Sekolah/Universitas</td><td>{{ $profile->education_school_name ?? '-' }}</td></tr>
            <tr><td class="label">Jurusan</td><td>{{ $profile->education_major ?? '-' }}</td></tr>
            <tr><td class="label">Kota</td><td>{{ $profile->education_city ?? '-' }}</td></tr>
            <tr><td class="label">Tahun Lulus</td><td>{{ $profile->education_graduation_year ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Pendidikan Satpam</div>
        <table class="data">
            <tr><td class="label" style="width:20%">Kualifikasi</td><td>{{ $profile->satpam_qualification ?? '-' }}</td></tr>
            <tr><td class="label">Instansi</td><td>{{ $profile->satpam_training_institution ?? '-' }}</td></tr>
            <tr><td class="label">Lokasi Diklat</td><td>{{ $profile->satpam_training_location ?? '-' }}</td></tr>
            <tr><td class="label">Tanggal</td><td>{{ $profile->satpam_training_date ? \Carbon\Carbon::parse($profile->satpam_training_date)->format('d M Y') : '-' }}</td></tr>
            <tr><td class="label">No. KTA</td><td>{{ $profile->satpam_kta_number ?? '-' }}</td></tr>
            <tr><td class="label">No. Ijazah</td><td>{{ $profile->satpam_certificate_number ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="two-col">
        <div class="col">
            <div class="section">
                <div class="section-title">Pengalaman Kerja</div>
                @if($profile->exp1_company)
                <div class="exp-item">
                    <div class="exp-title">{{ $profile->exp1_position ?? '-' }}</div>
                    <div class="exp-detail">{{ $profile->exp1_company ?? '-' }}, {{ $profile->exp1_city ?? '-' }} ({{ $profile->exp1_year ?? '-' }})</div>
                </div>
                @endif
                @if($profile->exp2_company)
                <div class="exp-item">
                    <div class="exp-title">{{ $profile->exp2_position ?? '-' }}</div>
                    <div class="exp-detail">{{ $profile->exp2_company ?? '-' }}, {{ $profile->exp2_city ?? '-' }} ({{ $profile->exp2_year ?? '-' }})</div>
                </div>
                @endif
                @if($profile->exp3_company)
                <div class="exp-item">
                    <div class="exp-title">{{ $profile->exp3_position ?? '-' }}</div>
                    <div class="exp-detail">{{ $profile->exp3_company ?? '-' }}, {{ $profile->exp3_city ?? '-' }} ({{ $profile->exp3_year ?? '-' }})</div>
                </div>
                @endif
                @if(!$profile->exp1_company && !$profile->exp2_company && !$profile->exp3_company)
                <p style="color: #9ca3af;">Tidak ada data.</p>
                @endif
            </div>
        </div>
        
        <div class="col">
            <div class="section">
                <div class="section-title">Sertifikasi</div>
                @if($profile->cert1_training)
                <div class="cert-item">
                    <div class="exp-title">{{ $profile->cert1_training ?? '-' }}</div>
                    <div class="exp-detail">{{ $profile->cert1_organizer ?? '-' }}, {{ $profile->cert1_city ?? '-' }} ({{ $profile->cert1_date ? \Carbon\Carbon::parse($profile->cert1_date)->format('d M Y') : '-' }})</div>
                </div>
                @endif
                @if($profile->cert2_training)
                <div class="cert-item">
                    <div class="exp-title">{{ $profile->cert2_training ?? '-' }}</div>
                    <div class="exp-detail">{{ $profile->cert2_organizer ?? '-' }}, {{ $profile->cert2_city ?? '-' }} ({{ $profile->cert2_date ? \Carbon\Carbon::parse($profile->cert2_date)->format('d M Y') : '-' }})</div>
                </div>
                @endif
                @if($profile->cert3_training)
                <div class="cert-item">
                    <div class="exp-title">{{ $profile->cert3_training ?? '-' }}</div>
                    <div class="exp-detail">{{ $profile->cert3_organizer ?? '-' }}, {{ $profile->cert3_city ?? '-' }} ({{ $profile->cert3_date ? \Carbon\Carbon::parse($profile->cert3_date)->format('d M Y') : '-' }})</div>
                </div>
                @endif
                @if(!$profile->cert1_training && !$profile->cert2_training && !$profile->cert3_training)
                <p style="color: #9ca3af;">Tidak ada data.</p>
                @endif
            </div>
        </div>
    </div>

    @if($profile->instagram || $profile->facebook || $profile->twitter || $profile->tiktok || $profile->linkedin || $profile->youtube)
    <div class="section">
        <div class="section-title">Media Sosial</div>
        <p class="social">
            @if($profile->instagram) IG: {{ $profile->instagram }} @endif
            @if($profile->facebook) | FB: {{ $profile->facebook }} @endif
            @if($profile->twitter) | X: {{ $profile->twitter }} @endif
            @if($profile->tiktok) | TikTok: {{ $profile->tiktok }} @endif
            @if($profile->linkedin) | LinkedIn: {{ $profile->linkedin }} @endif
            @if($profile->youtube) | YouTube: {{ $profile->youtube }} @endif
        </p>
    </div>
    @endif

    <div class="footer">
        CV di-generate pada {{ now()->format('d M Y H:i') }} | JSMUGuard System
    </div>
</div>
</body>
</html>
