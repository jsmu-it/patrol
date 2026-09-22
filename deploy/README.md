# Deploy JSMUGuard ke absen.jsmu.co.id

Stack: Laravel 10 + MySQL 8 + Redis + Nginx + Let's Encrypt, semua lewat Docker Compose.

## Kondisi domain saat ini (hasil pengecekan)

| Item | Nilai |
|---|---|
| `jsmu.co.id` | 45.130.231.112 (LiteSpeed / Hostinger shared hosting) |
| `absen.jsmu.co.id` | **belum ada record** |
| Nameserver | `ns1.niagahoster.com`, `ns2.niagahoster.com` |
| MX | `srv181.niagahoster.com` |

DNS dikelola di **Niagahoster**, bukan hPanel Hostinger. Website utama dan email
tetap di shared hosting — yang ditambahkan hanya satu A record baru untuk
subdomain `absen`. **Jangan ganti nameserver**, nanti email jsmu.co.id mati.

## Langkah

### 1. Siapkan VPS
Minimal 2 vCPU / 4 GB RAM / 50 GB SSD (Ubuntu 24.04). Stack ini menjalankan
MySQL (limit 2G), app, queue worker, scheduler, dan Redis — 2 GB RAM akan sesak.

```
scp -r deploy/ root@IP_VPS:/tmp/
ssh root@IP_VPS 'bash /tmp/deploy/01-provision-vps.sh'
```

Script ini: install Docker, buka firewall 22/80/443, bikin swap 2 GB, set
timezone **Asia/Jakarta** (krusial — jam absensi & shift ikut timezone server).

### 2. Tambah DNS di Niagahoster
Member Area → Kelola Domain `jsmu.co.id` → DNS / Nameserver → Tambah record:

| Type | Name | Points to | TTL |
|---|---|---|---|
| A | `absen` | `IP_VPS` | 3600 |

Tunggu propagasi, cek dari mana saja:
```
dig +short absen.jsmu.co.id
```
Harus keluar IP VPS. Jangan lanjut ke SSL sebelum ini benar — Let's Encrypt akan
menolak kalau domain belum mengarah ke server.

### 3. Clone repo + isi .env
```
ssh root@IP_VPS
git clone https://github.com/jsmu-it/patrol.git /opt/patrol
cd /opt/patrol
cp deploy/env.root.example .env
cp deploy/env.production.example backend/api/.env
nano .env                 # isi DB_PASSWORD & DB_ROOT_PASSWORD
nano backend/api/.env     # isi DB_PASSWORD & DB_ROOT_PASSWORD yang SAMA
```

Password DB di dua file itu harus identik, kalau tidak container app gagal
connect ke MySQL.

### 4. Deploy
```
cd /opt/patrol
bash deploy/02-deploy.sh
```

Yang dikerjakan: build container → `composer install --no-dev` → `key:generate`
→ `storage:link` → migrasi → seed → cache config/route/view → terbitkan SSL →
switch nginx ke HTTPS.

### 5. Setelah live
```
# ganti password akun seeder SEGERA
docker exec -it jsmuguard-app php artisan tinker
>>> \App\Models\User::where('username','itjsmu')->update(['password'=>bcrypt('PASSWORD_BARU')]);

# backup harian
crontab -e
0 2 * * * cd /opt/patrol && bash deploy/04-backup-db.sh >> /var/log/jsmu-backup.log 2>&1
```

Update kode berikutnya: `bash deploy/05-update.sh`.

## Catatan penting

**Bug urutan migration.** Empat migration bertanggal `2024_01_01_*` (termasuk
`create_job_applications_table`) urutan namanya lebih awal daripada tabel yang
mereka rujuk (`projects`, `cms_careers`, `user_profiles`) yang baru dibuat di
migration `2025_11_*`. Akibatnya `php artisan migrate` di database kosong
**selalu gagal**:

```
SQLSTATE[HY000]: 1005 Can't create table `job_applications`
(errno: 150 "Foreign key constraint is incorrectly formed")
```

`deploy/migrate-ordered.sh` mengakalinya dengan menjalankan migration satu per
satu lewat `--path` dalam urutan yang benar, tanpa mengubah file repo. Ini sudah
diuji dan berhasil (56/56 migration jalan). Dipanggil otomatis oleh `02-deploy.sh`
hanya saat database masih kosong.

Perbaikan permanennya adalah me-rename keempat file itu ke timestamp setelah
`2025_12_31_000005`. Itu sengaja **tidak** dilakukan di sini karena akan membuat
Laravel menganggapnya migration baru di database yang sudah jalan (server lama,
laptop developer lain) lalu gagal dengan "table already exists".

**Scheduler.** `app/Console/Kernel.php` menjalankan `notifications:shift-reminders`
setiap menit, tapi `docker-compose.yml` di repo tidak punya container untuk itu —
jadi di setup lama reminder shift tidak pernah terkirim. Sudah ditambahkan
service `scheduler` di `docker-compose.prod.yml`.

**PhpMyAdmin.** Di `docker-compose.yml` repo, port 8081 terbuka ke internet.
Di config produksi ini di-bind ke `127.0.0.1` saja. Akses lewat tunnel:
```
ssh -L 8081:127.0.0.1:8081 root@IP_VPS
```
lalu buka http://localhost:8081.

**Menjalankan compose manual.** Selalu sertakan `--env-file .env` dan jalankan
dari root repo, karena compose membaca `.env` dari folder file compose
(`deploy/`), bukan dari root:
```
cd /opt/patrol
docker compose --env-file .env -f deploy/docker-compose.prod.yml ps
```
Tanpa itu, variabel `DB_*` kosong dan container MySQL gagal start.

**Foto absensi tidak ada di database.** File upload ada di
`backend/api/storage/app/public`. `04-backup-db.sh` sudah ikut membackup folder itu.

**App mobile.** Setelah live, base URL API di app Flutter harus diarahkan ke
`https://absen.jsmu.co.id/api`.

## Isi folder

| File | Fungsi |
|---|---|
| `01-provision-vps.sh` | Setup VPS kosong (Docker, firewall, swap, timezone) |
| `02-deploy.sh` | Deploy pertama kali |
| `03-issue-ssl.sh` | Terbitkan/perbaiki sertifikat SSL (idempoten) |
| `04-backup-db.sh` | Backup database + folder storage, retensi 14 hari |
| `05-update.sh` | Deploy update dari commit baru |
| `migrate-ordered.sh` | Bootstrap migrasi database kosong (workaround bug urutan) |
| `docker-compose.prod.yml` | Stack produksi (+ scheduler, + SSL, PMA di localhost) |
| `nginx/absen-http.conf` | Vhost tahap 1 (sebelum SSL) |
| `nginx/absen-ssl.conf` | Vhost tahap 2 (HTTPS + redirect + security header) |
| `nginx/absen.conf` | File yang aktif di-mount; di-swap oleh script |
| `env.production.example` | Template `backend/api/.env` |
| `env.root.example` | Template `.env` root (dipakai docker-compose untuk MySQL) |

## Situs publik (perancangan ulang, 28 Agustus 2026)

Sistem desain ada di `backend/api/public/assets/css/jsmu.css` (token + komponen,
prefiks `jsm-`), dimuat setelah `tailwind.min.css`. Tailwind v2.2.19 statis
menangani tata letak; berkas ini menangani identitas. Tidak ada build step.

Font Open Sans (400/600/700) + IBM Plex Mono 400 di-host lokal di
`public/assets/fonts/` (160 KB) beserta `plex-local.css`. Tidak ada panggilan
ke CDN eksternal.

Arah visual mengikuti rujukan kompetitor nawakara.com: navy nyaris hitam,
aksen emas, hero penuh layar, bagian berselang gelap-terang. Selama foto belum
diunggah, setiap tempat gambar memakai `--photo-fallback` (gradien navy) yang
otomatis tergantikan foto asli begitu diisi lewat panel admin.

Cadangan tampilan lama: `deploy/ui-backup/`.

### Jebakan saat mengubah kode atau tampilan

1. **`opcache.validate_timestamps = 0`** di `docker/php/php.ini`. PHP tidak pernah
   membaca ulang berkas yang berubah, jadi perubahan controller/model TIDAK akan
   terlihat sampai container app direstart:
   ```
   docker compose --env-file .env -f deploy/docker-compose.prod.yml restart app
   ```
   `deploy/05-update.sh` sudah melakukannya.

2. **Jalankan `view:cache` sebagai www-data**, bukan root. Kalau dijalankan root,
   berkas Blade terkompilasi jadi milik root dan php-fpm gagal menimpanya saat
   view berubah — halaman jadi 500 "Permission denied":
   ```
   docker exec -u www-data jsmuguard-app php artisan view:cache
   ```

3. **`backend/api/.env` harus dapat dibaca www-data** (`root:www-data`, 640).
   Kalau 600 milik root, aplikasi 500 dengan "No application encryption key"
   begitu config cache dibersihkan.

### Pengaturan yang mengisi situs

Halaman publik membaca tabel `settings`. Kunci yang dipakai tampilan baru:
`company_name`, `license_number`, `founded_year`, `stat_personnel`, `stat_sites`,
`stat_attendance`, `office_hours`, `logo`, `footer_address`, `footer_email`,
`footer_phone`, `footer_copyright`, dan `social_*`. Selama kosong, situs memakai
keadaan kosong yang dirancang, bukan tampil rusak.

### Perbaikan bug yang ikut dikerjakan

- `CompanyProfileController::application()` dan `downloadApplication()` tidak
  pernah dibuat padahal rutenya terdaftar — `/application` selalu 500.
- Menu dropdown lama hanya bereaksi pada hover, sehingga Visi Misi, HSSE, dan
  Archipelago tidak terjangkau pengguna papan tik. Sekarang memakai tombol
  dengan `aria-expanded`/`aria-controls`, bisa ditutup dengan Escape.
