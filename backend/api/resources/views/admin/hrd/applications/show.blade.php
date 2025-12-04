@extends('layouts.admin')

@section('title', 'Detail Pelamar')
@section('page_title', 'Detail Pelamar - ' . $application->name)

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <!-- Personal Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block text-gray-500 text-xs uppercase mb-1">Nama Lengkap</label>
                        <p class="font-medium text-gray-900">{{ $application->name }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs uppercase mb-1">Posisi Dilamar</label>
                        <p class="font-medium text-gray-900">{{ $application->career ? $application->career->title : 'Lamaran Umum' }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs uppercase mb-1">Email</label>
                        <p class="font-medium text-gray-900">{{ $application->email }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs uppercase mb-1">Nomor Telepon</label>
                        <p class="font-medium text-gray-900">{{ $application->phone }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs uppercase mb-1">Tanggal Melamar</label>
                        <p class="font-medium text-gray-900">{{ $application->created_at->format('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
            </div>

            <!-- Data Lengkap Pelamar (Additional PDP Fields) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Biodata Lengkap</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    
                    <!-- Data Pribadi -->
                    <div class="col-span-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Data Pribadi</h4>
                    </div>
                    <div><label class="text-gray-500 text-xs block">Tempat, Tanggal Lahir</label> <span class="font-medium">{{ $application->birth_city }}, {{ $application->birth_date }} ({{ $application->age }} tahun)</span></div>
                    <div><label class="text-gray-500 text-xs block">Jenis Kelamin</label> <span class="font-medium">{{ $application->gender }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Agama</label> <span class="font-medium">{{ $application->religion }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Golongan Darah</label> <span class="font-medium">{{ $application->blood_type }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Nama Ibu Kandung</label> <span class="font-medium">{{ $application->mother_name }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Status Pernikahan</label> <span class="font-medium">{{ $application->marital_status }}</span></div>

                    <!-- Identitas & Fisik -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Identitas & Fisik</h4>
                    </div>
                    <div><label class="text-gray-500 text-xs block">No. KTP</label> <span class="font-medium">{{ $application->ktp_number }}</span></div>
                    <div><label class="text-gray-500 text-xs block">No. KK</label> <span class="font-medium">{{ $application->kk_number }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Tinggi Badan</label> <span class="font-medium">{{ $application->height_cm }} cm</span></div>
                    <div><label class="text-gray-500 text-xs block">Berat Badan</label> <span class="font-medium">{{ $application->weight_kg }} kg</span></div>

                    <!-- Alamat Domisili -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Alamat Domisili</h4>
                    </div>
                    <div class="col-span-2">
                        <span class="font-medium">
                            {{ $application->domicile_street }}
                            RT {{ $application->domicile_rt }} / RW {{ $application->domicile_rw }}<br>
                            Kel. {{ $application->domicile_subdistrict }}, Kec. {{ $application->domicile_district }}<br>
                            {{ $application->domicile_regency }}, {{ $application->domicile_province }}
                        </span>
                    </div>

                    <!-- Pendidikan -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Pendidikan Terakhir</h4>
                    </div>
                    <div><label class="text-gray-500 text-xs block">Jenjang</label> <span class="font-medium">{{ $application->education_level }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Institusi</label> <span class="font-medium">{{ $application->education_school_name }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Jurusan</label> <span class="font-medium">{{ $application->education_major }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Tahun Lulus</label> <span class="font-medium">{{ $application->education_graduation_year }}</span></div>

                    <!-- Satpam -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Kualifikasi Satpam</h4>
                    </div>
                    <div><label class="text-gray-500 text-xs block">Kualifikasi</label> <span class="font-medium">{{ $application->satpam_qualification ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">No. KTA</label> <span class="font-medium">{{ $application->satpam_kta_number ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">No. Sertifikat</label> <span class="font-medium">{{ $application->satpam_certificate_number ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Tanggal Pelatihan</label> <span class="font-medium">{{ $application->satpam_training_date ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Institusi</label> <span class="font-medium">{{ $application->satpam_training_institution ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Lokasi</label> <span class="font-medium">{{ $application->satpam_training_location ?? '-' }}</span></div>

                    <!-- Identitas Tambahan -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Identitas Tambahan</h4>
                    </div>
                    <div><label class="text-gray-500 text-xs block">NPWP</label> <span class="font-medium">{{ $application->npwp ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">SIM A</label> <span class="font-medium">{{ $application->sim_a_number ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">SIM C</label> <span class="font-medium">{{ $application->sim_c_number ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">BPJS Ketenagakerjaan</label> <span class="font-medium">{{ $application->bpjs_tk_number ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">BPJS Kesehatan</label> <span class="font-medium">{{ $application->bpjs_kes_number ?? '-' }}</span></div>

                    <!-- Keluarga & Kontak Darurat -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Keluarga & Kontak Darurat</h4>
                    </div>
                    <div><label class="text-gray-500 text-xs block">Jumlah Anak</label> <span class="font-medium">{{ $application->children_count ?? '0' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Nama Kontak Darurat</label> <span class="font-medium">{{ $application->emergency_name ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">No. Kontak Darurat</label> <span class="font-medium">{{ $application->emergency_phone ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Hubungan</label> <span class="font-medium">{{ $application->emergency_relation ?? '-' }}</span></div>

                    <!-- Ukuran Seragam -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Ukuran Seragam</h4>
                    </div>
                    <div><label class="text-gray-500 text-xs block">Baju</label> <span class="font-medium">{{ $application->uniform_shirt_size ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Celana</label> <span class="font-medium">{{ $application->uniform_pants_size ?? '-' }}</span></div>
                    <div><label class="text-gray-500 text-xs block">Sepatu</label> <span class="font-medium">{{ $application->uniform_shoes_size ?? '-' }}</span></div>

                    <!-- Pengalaman Kerja -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Pengalaman Kerja</h4>
                    </div>
                    <div class="col-span-2 space-y-2">
                        @if($application->exp1_company)
                        <div class="p-2 bg-gray-50 rounded">
                            <p class="font-medium text-sm">{{ $application->exp1_position }} at {{ $application->exp1_company }}</p>
                            <p class="text-xs text-gray-500">{{ $application->exp1_city }} ({{ $application->exp1_year }})</p>
                        </div>
                        @endif
                        @if($application->exp2_company)
                        <div class="p-2 bg-gray-50 rounded">
                            <p class="font-medium text-sm">{{ $application->exp2_position }} at {{ $application->exp2_company }}</p>
                            <p class="text-xs text-gray-500">{{ $application->exp2_city }} ({{ $application->exp2_year }})</p>
                        </div>
                        @endif
                    </div>

                    <!-- Sertifikasi Lain -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Sertifikasi Lain</h4>
                    </div>
                    <div class="col-span-2 space-y-2">
                        @if($application->cert1_training)
                        <div class="p-2 bg-gray-50 rounded">
                            <p class="font-medium text-sm">{{ $application->cert1_training }}</p>
                            <p class="text-xs text-gray-500">Oleh {{ $application->cert1_organizer }} di {{ $application->cert1_city }} ({{ $application->cert1_date }})</p>
                        </div>
                        @endif
                        @if($application->cert2_training)
                        <div class="p-2 bg-gray-50 rounded">
                            <p class="font-medium text-sm">{{ $application->cert2_training }}</p>
                            <p class="text-xs text-gray-500">Oleh {{ $application->cert2_organizer }} di {{ $application->cert2_city }} ({{ $application->cert2_date }})</p>
                        </div>
                        @endif
                    </div>

                    <!-- Media Sosial -->
                    <div class="col-span-2 mt-2">
                        <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3">Media Sosial</h4>
                    </div>
                    <div class="col-span-2 grid grid-cols-2 gap-2">
                        @if($application->instagram) <div><label class="text-gray-500 text-xs">Instagram</label> <div class="text-sm">{{ $application->instagram }}</div></div> @endif
                        @if($application->facebook) <div><label class="text-gray-500 text-xs">Facebook</label> <div class="text-sm">{{ $application->facebook }}</div></div> @endif
                        @if($application->twitter) <div><label class="text-gray-500 text-xs">Twitter</label> <div class="text-sm">{{ $application->twitter }}</div></div> @endif
                        @if($application->tiktok) <div><label class="text-gray-500 text-xs">TikTok</label> <div class="text-sm">{{ $application->tiktok }}</div></div> @endif
                        @if($application->linkedin) <div><label class="text-gray-500 text-xs">LinkedIn</label> <div class="text-sm">{{ $application->linkedin }}</div></div> @endif
                    </div>

                </div>
            </div>

            <!-- Cover Letter -->
            @if($application->cover_letter)
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cover Letter</h3>
                <div class="prose prose-sm text-gray-600">
                    {!! nl2br(e($application->cover_letter)) !!}
                </div>
            </div>
            @endif

            <!-- Resume Viewer -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Resume / CV</h3>
                    <a href="{{ Storage::url($application->resume_path) }}" target="_blank" class="text-sm text-blue-600 hover:underline">Download File</a>
                </div>
                <div class="aspect-[4/3] w-full bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                   <iframe src="{{ Storage::url($application->resume_path) }}" class="w-full h-full rounded"></iframe>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Status Management -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Lamaran</h3>
                <form action="{{ route('admin.hrd.applications.status', $application->id) }}" method="POST" class="space-y-4" id="statusForm">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Update Status</label>
                        <select name="status" id="statusSelect" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-slate-900">
                            <option value="pending" @selected($application->status === 'pending')>Pending</option>
                            <option value="interview" @selected($application->status === 'interview')>Interview</option>
                            <option value="accepted" @selected($application->status === 'accepted')>Diterima</option>
                            <option value="rejected" @selected($application->status === 'rejected')>Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Internal</label>
                        <textarea name="notes" rows="4" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-slate-900" placeholder="Tambahkan catatan untuk pelamar ini...">{{ $application->notes }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-2 rounded text-sm hover:bg-slate-800">Simpan Perubahan</button>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    <a href="mailto:{{ $application->email }}" class="block w-full text-center px-4 py-2 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                        Kirim Email
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $application->phone)) }}" target="_blank" class="block w-full text-center px-4 py-2 border border-green-500 text-green-600 rounded text-sm hover:bg-green-50">
                        Hubungi via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Acceptance Modal -->
    <div id="acceptanceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Terima Karyawan</h3>
            <p class="text-sm text-gray-600 mb-4">Silakan lengkapi data berikut untuk memproses penerimaan karyawan.</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Penempatan</label>
                    <select form="statusForm" name="project_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="">Pilih Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP (Nomor Induk Pegawai)</label>
                    <input form="statusForm" type="text" name="nip" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Masukkan NIP">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Pokok</label>
                    <input form="statusForm" type="number" name="salary" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Contoh: 4500000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bergabung</label>
                    <input form="statusForm" type="date" name="join_date" value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="cancelAcceptance" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="button" id="confirmAcceptance" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">Konfirmasi & Simpan</button>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusSelect = document.getElementById('statusSelect');
            const statusForm = document.getElementById('statusForm');
            const acceptanceModal = document.getElementById('acceptanceModal');
            const cancelBtn = document.getElementById('cancelAcceptance');
            const confirmBtn = document.getElementById('confirmAcceptance');
            
            let isModalConfirmed = false;

            if (statusForm) {
                statusForm.addEventListener('submit', (e) => {
                    if (statusSelect.value === 'accepted' && !isModalConfirmed) {
                        e.preventDefault();
                        acceptanceModal.classList.remove('hidden');
                        acceptanceModal.classList.add('flex');
                    }
                });
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    acceptanceModal.classList.add('hidden');
                    acceptanceModal.classList.remove('flex');
                    statusSelect.value = 'pending'; 
                });
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    // Basic validation
                    const projectId = statusForm.querySelector('[name="project_id"]').value;
                    const nip = statusForm.querySelector('[name="nip"]').value;
                    const salary = statusForm.querySelector('[name="salary"]').value;

                    if (!projectId || !nip || !salary) {
                        alert('Mohon lengkapi semua field (Project, NIP, Gaji).');
                        return;
                    }

                    isModalConfirmed = true;
                    statusForm.submit();
                });
            }
        });
    </script>
@endpush
@endsection
