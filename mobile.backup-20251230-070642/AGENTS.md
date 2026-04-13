# JSMUGuard Mobile – Agent Notes

File ini ditujukan untuk AI/automation agent yang akan bekerja di **aplikasi Flutter JSMUGuard**.

## 1. Ringkasan Proyek

- **Framework**: Flutter 3, Dart 3.
- **Platform**: Android (aktif), iOS disiapkan oleh Flutter secara default.
- **State Management**: `flutter_riverpod` (StateNotifier + Provider).
- **HTTP Client**: `dio` dibungkus di `ApiClient` dengan error handling terpusat.
- **Fitur utama**:
  - Login dengan username/password (Laravel Sanctum, Bearer token).
  - Absensi (clock-in/out) dengan GPS, selfie, shift, mode normal/dinas.
  - Riwayat absensi.
  - Patroli QR + form, termasuk SOS dan Insiden (tanpa scan barcode).
  - Pengajuan izin/cuti/sakit.
  - Profil + logout (menampilkan foto profil, NIP, nama, dan lokasi tugas).
  - **Offline queue** untuk absensi, patroli, dan izin (auto-sync saat online).
  - Push notification via Firebase Cloud Messaging (FCM HTTP v1 di backend).

## 2. Struktur Direktori Utama

- `lib/main.dart`
  - Entry point aplikasi, setup `ProviderScope`, routing, dan init Firebase/FCM.
- `lib/config/app_config.dart`
  - Base URL API. Default emulator: `http://10.0.2.2:8000/api`, untuk device fisik gunakan IP LAN PC, misalnya `http://192.168.0.75:8000/api`.
- `lib/routes/app_router.dart`
  - Konstanta route (splash, login, home, attendance history, patrol, leave, profile).
- `lib/services/`
  - `api_client.dart` – wrapper Dio + `ApiException` (jangan ubah kontrak sembarangan).
  - `auth_service.dart` – login, `/me`, `/logout`.
  - `attendance_service.dart` – clock-in/out + integrasi offline queue.
  - `patrol_service.dart` – kirim log patroli (normal/SOS/Insiden) + offline queue.
  - `leave_service.dart` – list & create leave-requests + offline queue.
  - `shift_service.dart` – shift untuk guard aktif.
  - `location_service.dart` – GPS + permission.
  - `secure_storage_service.dart` – token + raw JSON (offline queue) via `flutter_secure_storage`.
  - `offline_queue_service.dart` – menyimpan dan sync antrian offline.
  - `notification_service.dart` – inisialisasi Firebase dan kirim FCM token ke backend.
- `lib/state/`
  - `auth/` – `AuthState`, `AuthNotifier`, providers.
  - `attendance/` – state untuk absensi dan riwayat.
  - `patrol/` – state submit patroli.
  - `leave/` – list & form izin/cuti.
- `lib/ui/screens/`
  - `splash/` – cek token & redirect login/home.
  - `auth/login_screen.dart` – form login + logo.
  - `home/home_screen.dart` – dashboard dengan header biru (menu drawer, logo), foto profil lingkaran besar + nama user, tombol MASUK/KELUAR (selfie → bottom sheet shift+mode+note), tombol PATROL (bottom sheet Patroli/Incident/SOS), indikator offline queue, dan **Riwayat absen 1 bulan** dalam 2 kolom (MASUK/KELUAR) dengan thumbnail selfie.
  - `attendance/attendance_history_screen.dart` – riwayat absensi dengan filter tanggal.
  - `patrol/patrol_scan_screen.dart` – scan QR + tombol SOS/Insiden.
  - `patrol/patrol_form_screen.dart` – form patroli (mode normal/SOS/Insiden).
  - `leave/leave_list_screen.dart`, `leave/leave_form_screen.dart` – izin/cuti/sakit.
  - `profile/profile_screen.dart` – informasi user (foto profil, NIP, nama, lokasi tugas) + logout, indikator & tombol sync offline.

## 3. Integrasi Backend & Kontrak API (high level)

Semua endpoint berada di backend Laravel (lihat `backend/api/AGENTS.md` untuk detail):

- Auth:
  - `POST /api/login` → `{ access_token, user }`.
  - `POST /api/logout`.
  - `GET /api/me`.
    - `user` berisi field tambahan yang dipakai mobile: `nip`, `profile_photo_url`, `active_project_name`.
  - `POST /api/me/device-token` – menerima `{ fcm_token }` dari app.
- Absensi:
  - `POST /api/attendance/clock-in` – FormData (shift_id, lat/lng, mode, note?, selfie).
  - `POST /api/attendance/clock-out` – FormData (shift_id, lat/lng, note?, selfie?).
  - `GET /api/attendance/history` – query `from`, `to`, `project_id?`.
- Patroli:
  - `POST /api/patrol/logs` – Body: `project_id`, `checkpoint_code?`, `title`, `post_name`, `description?`, `lat/lng`, `photo?`, `type? (patrol/sos/incident)`.
- Izin/Cuti/Sakit:
  - `GET /api/leave-requests`.
  - `POST /api/leave-requests` – `type`, `date_from`, `date_to`, `reason`, `doctor_note?`.

**Penting**: Jangan mengubah path/format ini tanpa sinkronisasi dengan backend.

## 4. Offline Queue

- Implementasi di `OfflineQueueService` + perubahan di `AttendanceService`, `PatrolService`, `LeaveService`.
- Deteksi offline: `ApiException` dengan `statusCode == null` dan `data == null` → item disimpan ke queue.
- Queue disimpan sebagai JSON di `flutter_secure_storage` key `offline_queue`.
- `HomeScreen` memanggil `offlineQueueService.sync()` saat init; berbagai service juga memanggil `sync()` setelah request yang sukses.

### Jenis item queue

- `attendance_clock_in`, `attendance_clock_out` – payload termasuk path file selfie.
- `patrol_log` – payload termasuk path foto.
- `leave_request` – payload murni JSON.

## 5. Push Notification (Client Side)

- Menggunakan `firebase_core` dan `firebase_messaging`.
- Inisialisasi di `main.dart` dan `notification_service.dart`.
- Flow:
  1. App start → `Firebase.initializeApp()` + `FirebaseMessaging.onBackgroundMessage`.
  2. Setelah login / auto-login (`AuthNotifier.loadFromStorage()` dan `login()`), dipanggil:
     `notificationService.initAndRegisterDeviceToken()`.
  3. Service mengambil FCM token dan kirim ke backend via `POST /api/me/device-token`.
  4. Backend memakai HTTP v1 FCM dengan service account JSON untuk kirim notifikasi.

## 6. Ikon & Branding

- Logo dalam aplikasi: `assets/images/logo.png` (dipakai di layar login; gunakan `Image.asset`).
- Ikon launcher: `assets/icons/app_icon.png` di-generate ke Android/iOS via `flutter_launcher_icons`.
- Jangan mengganti manual file di `android/app/src/main/res/mipmap-*`; gunakan konfigurasi `flutter_launcher_icons` lalu jalankan:

```bash
flutter pub run flutter_launcher_icons:main
```

## 7. Pedoman Untuk Agent di Masa Depan

- **Selalu** jalankan sebelum menyelesaikan perubahan:
  - `flutter analyze`
  - `flutter test`
- Ikuti pola yang sudah ada: gunakan Riverpod (`StateNotifier`) untuk state global.
- Saat menambah endpoint baru:
  1. Tambah method di service terkait (`lib/services/...`).
  2. Tambah state/provider jika butuh status loading/error.
  3. Hubungkan di UI, pastikan error ditampilkan via `SnackBar` atau teks di layar.
- Untuk fitur offline baru, integrasikan dengan `OfflineQueueService` alih‑alih membuat mekanisme lain.
- Untuk push notification baru, backend sudah siap kirim `data` JSON; di sisi Flutter gunakan `FirebaseMessaging.onMessage` dan `onMessageOpenedApp` jika ingin deep‑link ke screen tertentu.

## 8. Model User & Attendance (Client Side)

- `lib/models/user.dart`:
  - Field inti: `id`, `name`, `username`, `email?`, `role?`, `activeProjectId?`.
  - Tambahan untuk profil: `nip?`, `profilePhotoPath?`, `profilePhotoUrl?` (URL dari backend), `activeProjectName?`.
  - Koordinat project: `projectLat`, `projectLng`, `projectRadius`.
- `lib/models/attendance_record.dart`:
  - Field utama: `id`, `userId`, `projectId`, `shiftId`, `type` (`clock_in`/`clock_out`), `occurredAt`, `latitude?`, `longitude?`, `note?`, `mode` (`normal`/`dinas`), `statusDinas?`.
  - `selfiePhotoPath?` – path/URL foto selfie absen; `HomeScreen` menggunakan ini untuk menampilkan thumbnail di riwayat.

**Penting:**
- `User.fromJson` menggunakan helper `_parseDouble` dan `_parseInt` untuk parsing angka yang aman (menangani string/num).
- `HomeScreen` menggunakan `.toLocal()` saat menampilkan waktu agar sesuai timezone perangkat (WIB).
- URL foto profil diproses dengan prioritas `profilePhotoPath` + base URL, baru fallback ke `profilePhotoUrl`.
