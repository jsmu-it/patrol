@extends('layouts.company_profile')

@section('title', 'Privacy Policy - JSMU Guard')
@section('description', 'Kebijakan Privasi JSMU Guard')

@section('content')
<section class="bg-blue-900 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white">Privacy Policy</h1>
        <p class="mt-4 text-blue-200">Kebijakan Privasi Aplikasi JSMUGuard</p>
    </div>
</section>

<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8 space-y-8 text-gray-700 leading-relaxed">
            
            <p class="text-sm text-gray-500">Terakhir diperbarui: {{ date('d F Y') }}</p>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Pendahuluan</h2>
                <p>
                    PT. Jaya Sakti Mandiri Unggul ("kami", "perusahaan") menghargai privasi Anda dan berkomitmen untuk melindungi data pribadi Anda. 
                    Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi 
                    Anda saat menggunakan aplikasi mobile JSMUGuard untuk absensi dan patroli ("Aplikasi").
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Informasi yang Kami Kumpulkan</h2>
                <p class="mb-3">Kami mengumpulkan informasi berikut melalui Aplikasi:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Data Identitas:</strong> Nama lengkap, NIP (Nomor Induk Pegawai), email, nomor telepon, dan foto profil.</li>
                    <li><strong>Data Lokasi:</strong> Koordinat GPS saat melakukan absensi (clock-in/clock-out) dan patroli untuk memverifikasi kehadiran di lokasi tugas.</li>
                    <li><strong>Foto Selfie:</strong> Foto wajah saat melakukan absensi untuk verifikasi identitas.</li>
                    <li><strong>Data Patroli:</strong> Waktu, lokasi, dan dokumentasi foto saat melakukan pemindaian checkpoint patroli.</li>
                    <li><strong>Data Perangkat:</strong> Token notifikasi (FCM token) untuk mengirimkan pemberitahuan terkait tugas.</li>
                    <li><strong>Data Izin/Cuti:</strong> Informasi pengajuan izin, cuti, atau sakit termasuk tanggal dan alasan.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Tujuan Penggunaan Data</h2>
                <p class="mb-3">Data yang dikumpulkan digunakan untuk:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Mencatat dan memverifikasi kehadiran karyawan di lokasi tugas.</li>
                    <li>Memantau aktivitas patroli keamanan secara real-time.</li>
                    <li>Mengelola pengajuan izin, cuti, dan sakit karyawan.</li>
                    <li>Mengirimkan notifikasi terkait jadwal, tugas, dan informasi penting lainnya.</li>
                    <li>Menyusun laporan kehadiran dan patroli untuk keperluan administrasi.</li>
                    <li>Meningkatkan keamanan dan kualitas layanan kami.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Penyimpanan dan Keamanan Data</h2>
                <p class="mb-3">Kami menerapkan langkah-langkah keamanan untuk melindungi data Anda:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Data disimpan di server yang aman dengan enkripsi.</li>
                    <li>Akses ke data dibatasi hanya untuk personel yang berwenang.</li>
                    <li>Token autentikasi digunakan untuk mengamankan sesi pengguna.</li>
                    <li>Data offline disimpan dengan aman di perangkat menggunakan penyimpanan terenkripsi.</li>
                </ul>
                <p class="mt-3">Data akan disimpan selama diperlukan untuk tujuan yang dijelaskan atau sesuai dengan kewajiban hukum yang berlaku.</p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Berbagi Data dengan Pihak Ketiga</h2>
                <p class="mb-3">Kami tidak menjual data pribadi Anda. Data hanya dapat dibagikan kepada:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Klien/Proyek:</strong> Data kehadiran dan patroli dapat dibagikan kepada klien tempat Anda bertugas sesuai kontrak kerja.</li>
                    <li><strong>Penyedia Layanan:</strong> Pihak ketiga yang membantu operasional kami (misalnya, layanan cloud dan notifikasi) dengan perjanjian kerahasiaan.</li>
                    <li><strong>Otoritas Hukum:</strong> Jika diwajibkan oleh hukum atau proses hukum yang berlaku.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Izin Aplikasi</h2>
                <p class="mb-3">Aplikasi memerlukan izin berikut:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Kamera:</strong> Untuk mengambil foto selfie saat absensi dan dokumentasi patroli.</li>
                    <li><strong>Lokasi:</strong> Untuk memverifikasi kehadiran di lokasi tugas yang ditentukan.</li>
                    <li><strong>Notifikasi:</strong> Untuk menerima pemberitahuan terkait tugas dan informasi penting.</li>
                    <li><strong>Penyimpanan:</strong> Untuk menyimpan data offline sementara saat tidak ada koneksi internet.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Hak Pengguna</h2>
                <p class="mb-3">Anda memiliki hak untuk:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Mengakses data pribadi Anda yang kami simpan.</li>
                    <li>Meminta koreksi atas data yang tidak akurat.</li>
                    <li>Meminta penghapusan data sesuai dengan ketentuan yang berlaku.</li>
                    <li>Menarik persetujuan penggunaan data (dengan konsekuensi terbatasnya fungsi Aplikasi).</li>
                </ul>
                <p class="mt-3">Untuk menggunakan hak-hak ini, silakan hubungi kami melalui informasi kontak di bawah.</p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Perubahan Kebijakan Privasi</h2>
                <p>
                    Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan akan diberitahukan melalui 
                    Aplikasi atau website kami. Penggunaan Aplikasi setelah perubahan dianggap sebagai persetujuan Anda 
                    terhadap kebijakan yang diperbarui.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Hubungi Kami</h2>
                <p class="mb-3">Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini atau ingin menggunakan hak Anda, silakan hubungi:</p>
                @php
                    $contactEmail = \App\Models\Setting::get('footer_email', 'info@jsmuguard.com');
                    $contactPhone = \App\Models\Setting::get('footer_phone', '');
                    $contactAddress = \App\Models\Setting::get('footer_address', 'Jakarta, Indonesia');
                @endphp
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p><strong>PT. Jaya Sakti Mandiri Unggul</strong></p>
                    <p class="mt-1">{!! nl2br(e($contactAddress)) !!}</p>
                    <p class="mt-1">Email: <a href="mailto:{{ $contactEmail }}" class="text-blue-600 hover:underline">{{ $contactEmail }}</a></p>
                    @if($contactPhone)
                    <p class="mt-1">Telepon: <a href="tel:{{ $contactPhone }}" class="text-blue-600 hover:underline">{{ $contactPhone }}</a></p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
