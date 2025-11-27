# Satpam Attendance & Patrol Backend – Agent Notes

This file is for future AI/automation agents working on this repository.

## 1. Project Overview

- **Framework**: Laravel 10 (PHP), Sanctum for API auth.
- **Domain**: Security guard attendance & patrol management.
- **Clients**:
  - Mobile app (planned, e.g. Flutter) consuming REST API under `/api`.
  - Admin web dashboard under `/admin` using Blade + Tailwind (CDN).

## 2. Core Domain Model

- **User**: roles = `SUPERADMIN`, `ADMIN`, `PROJECT_ADMIN`, `GUARD`.
  - Login via `username` + `password` only.
  - `active_project_id` links guards/admins to a project.
  - Memiliki relasi `profile()` (hasOne ke `UserProfile`) untuk data biodata lengkap.
- **Project**: has geo location (lat/lng, radius) for geofence.
- **Shift**: name + time range; assigned to projects.
- **AttendanceLog**:
  - Fields: occurred_at, type (clock-in/out), mode (on-site/dinas), status_dinas, note, lat/lng, photo path.
  - Related to user, project, shift.
- **Checkpoint**:
  - Patrol points with lat/lng, title, post_name, description, and unique `code`.
  - QR payload: `satpamapp://checkpoint?code={code}` (server-side generated via `simple-qrcode`).
- **PatrolLog**: patrol events; references user, project, checkpoint, occurred_at, description, etc.
- **LeaveRequest**: leave/cuti workflow with status (pending/approved/rejected).

- **UserProfile** (biodata karyawan, tabel `user_profiles`):
  - Satu baris per user; dihapus cascade jika user dihapus.
  - Field utama (ringkasan, tidak lengkap):
    - Data kerja: `nip`, `position`, `division`, `join_date`, `contract_period`, `employment_status`, `ktp_number`.
    - Pendidikan satpam: kualifikasi, tanggal, instansi, lokasi, `satpam_kta_number`, `satpam_certificate_number`.
    - Pendidikan akademis: level, tahun lulus, nama sekolah/universitas, kota, jurusan.
    - Data pribadi: kota/tanggal lahir, usia, jenis kelamin, nama ibu, agama, golongan darah, no HP, email pribadi.
    - Postur & seragam: tinggi/berat badan, ukuran baju/celana/sepatu.
    - Kontak darurat: nomor, nama pemilik, hubungan.
    - Identitas: NPWP, SIM C/A, BPJS TK/KES, KK.
    - Alamat KTP & domisili: provinsi, kabupaten, kecamatan, kelurahan, jalan, RT/RW, kode pos.
    - Keluarga: status pernikahan, jumlah anak.
    - Pengalaman 1–3: tahun, posisi, perusahaan, kota.
    - Sertifikasi 1–3: tanggal, pelatihan, penyelenggara, kota.
    - Media sosial: Instagram, Facebook, Twitter/X, TikTok, LinkedIn, YouTube.
    - Foto profil: `profile_photo_path` (path file di disk `public`, biasanya `profile-photos/...`).

## 3. API Surface (for Mobile)

All routes in `routes/api.php`, protected with `auth:sanctum` except login.

- **Auth**
  - `POST /api/login` → returns Sanctum token.
  - `POST /api/logout`.
  - `GET /api/me` → current user info, including role & active project.
    - Resource `UserResource` mengembalikan field tambahan untuk mobile: `nip` (dari `user_profiles`), `profile_photo_url` (URL publik ke foto profil, jika ada), dan `active_project_name` (nama project dari relasi `activeProject`).

- **Shifts & Projects**
  - `GET /api/me/available-shifts` → shifts relevant to current guard.
  - `GET /api/shifts` → list shifts.
  - Admin-only (`SUPERADMIN,ADMIN,PROJECT_ADMIN`): CRUD for `projects`, `shifts`, and `checkpoints`.

- **Attendance** (`AttendanceController`)
  - `POST /api/attendance/clock-in`.
  - `POST /api/attendance/clock-out`.
  - Both expect GPS (lat/lng), optional photo, and project context; geofence validated with Haversine.
  - `GET /api/attendance/history` → list per-user attendance.

- **Patrol** (`PatrolController`)
  - `POST /api/patrol/logs` → body includes `checkpoint_code` (from QR) and optional note.
  - `GET /api/patrol/history` → patrol history for guard.

- **Leave** (`LeaveRequestController`)
  - `GET /api/leave-requests` (current user's requests).
  - `POST /api/leave-requests`.
  - `GET /api/leave-requests/{id}`.

Agents must preserve these API contracts; coordinate with mobile client before making breaking changes.

### Employee PDP (Public Biodata Form)

- Controller: `App\Http\Controllers\EmployeeProfileController`.
- Routes publik (non-/admin) di `routes/web.php`:
  - `GET /pdp` → tampilkan form biodata karyawan yang akan diisi sendiri oleh karyawan.
  - `POST /pdp` → simpan/update data ke `users` dan `user_profiles`.
- Aturan utama:
  - Field wajib minimal: `nip`, `name`; optional: `email`, `active_project_id`, dan biodata lainnya.
  - `username` user selalu diset = `nip`.
  - `role` user selalu `GUARD` untuk input dari sini.
  - Jika user baru atau belum punya password → password di-set ke NIP (di-hash).
  - Semua field biodata lain dari form PDP disimpan ke `user_profiles` (termasuk `profile_photo_path` jika upload foto profil).
  - Foto profil PDP disimpan di disk `public` folder `profile-photos/` dan direferensikan lewat `user_profiles.profile_photo_path`.

## 4. Admin Web Dashboard

Routes in `routes/web.php` under `/admin`, middleware `auth` + `role:SUPERADMIN,ADMIN,PROJECT_ADMIN`.

- **Login**: `AdminAuthController` uses `username`/`password` and role checks.
- **Modules**:
  - Dashboard with basic stats + Leaflet map of checkpoints.
  - User management with Excel/CSV import (only `SUPERADMIN`).
  - Project management with map-based location picker + shift assignment.
  - Patrol checkpoints management with map picker, server-side QR codes, single & bulk print.
  - Attendance and patrol reports with filters, sorting, and Excel/PDF export.
  - Approval flows for dinas attendance and leave requests.

### Role Scoping Rules

- `SUPERADMIN`: Full access to all data.
- `ADMIN`: Full access by default unless assigned to a specific project (`active_project_id` not null).
- `PROJECT_ADMIN` (or `ADMIN` with `active_project_id`):
  - **Strictly Scoped** to `active_project_id` for:
    - Dashboard stats & map.
    - Users (Employee list & CRUD).
    - Projects (View only own project, no Create/Delete).
    - Patrol Checkpoints (CRUD own checkpoints only).
    - Reports (Attendance & Patrol).
    - Approvals (Attendance Dinas & Leave Requests).
  - Cannot import users.
  - Cannot view other projects' data.
- `GUARD`: Mobile app access only.

## 5. QR Code & Patrol Logic

- **QR Format**: `satpamapp://checkpoint?code={code}`.
- **Validation**:
  - Mobile app sends `checkpoint_code` (optional for Incident/SOS).
  - Backend automatically strips URL prefixes (`satpamapp://...`) to extract the code.
  - **Geofence**: Backend checks if guard is within **50 meters** of the checkpoint (if checkpoint has coords). Returns 422 if too far.
  - **Project Check**: If a checkpoint exists but belongs to another project, backend returns a specific 422 error.
- **Auto-fill**: Mobile calls `GET /api/patrol/checkpoint?code=...` to pre-fill title/post name.

## 6. Reports & Exports

- **Formats**: PDF (DomPDF) and Excel (Maatwebsite).
- **Features**:
  - Custom headers (Logo, Title, Project Name, Period).
  - PDF displays actual photos (using local disk path).
  - Excel includes clickable links to photos.
- **Controllers**: `AttendanceReportController`, `PatrolReportController`.
- **Exports Classes**: `App\Exports\AttendanceReportExport`, `App\Exports\PatrolReportExport` (using `FromView`).

## 7. Testing & Validation

- Default PHPUnit tests are present and passing (`php artisan test`).
- When modifying code, especially controllers, routes, or models, always run:
  - `php artisan test`
  - Optionally applicable linters/formatters if added later.

## 8. Guidelines for Future Changes

- **Do not** silently change API request/response formats; coordinate with the mobile app.
- Preserve role-based access rules, especially restrictions for `PROJECT_ADMIN`.
- Maintain server-side QR generation; avoid reintroducing client-side QR libraries or external CDNs for core features.
- Keep map and geocoding behavior consistent (Leaflet + Nominatim) unless there's a clear migration path.
- Before adding new dependencies, check `composer.json` and prefer libraries already in use.

## 9. User Import & Template (Excel)

- Import class: `App\Imports\UserImport`.
  - Membaca file dengan header (minimal): `name`, `username`, `email`, `role`, `project_name`, `password`.
  - `project_name` akan dibuat sebagai `Project` baru jika belum ada (alamat default, lat/lng 0, radius 500m).
  - `role` hanya `ADMIN`/`GUARD`; selain itu otomatis dibatasi ke `GUARD`.
  - Jika kolom password kosong, default `password` akan dipakai.
  - Selain membuat/memperbarui `User`, import juga mengisi `UserProfile` jika kolom biodata tersedia:
    - `nip`, `position`, `division`, `join_date`, `contract_period`, `employment_status`, `ktp_number`, dst (seluruh field profil yang sama dengan form admin/PDP).
    - Kolom khusus `profile_photo_url` akan disalin apa adanya ke `user_profiles.profile_photo_path` (anggap berisi path/URL yang valid); file fisik **tidak** diunduh otomatis.
- Template Excel:
  - Export class: `App\Exports\UserImportTemplateExport`.
  - Route admin: `GET /admin/users-import-template` (`Admin\UserController@downloadImportTemplate`).
  - Menghasilkan file `user_import_template.xlsx` tanpa data, hanya header lengkap untuk seluruh kolom `users` + `user_profiles` (termasuk `profile_photo_url`).
- Halaman import:
  - `GET /admin/users-import` → form upload, berisi tombol "Download template Excel" dan petunjuk header minimal.
  - `POST /admin/users-import` → memproses file; hanya role `ADMIN`/`SUPERADMIN` yang boleh mengakses.

> Penting: untuk foto profil dan foto lain yang disajikan via URL publik, jalankan `php artisan storage:link` sekali sehingga file di disk `public` dapat diakses melalui `/storage/...`.
