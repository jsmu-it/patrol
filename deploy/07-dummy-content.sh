#!/usr/bin/env bash
# Isi CMS dengan konten contoh + foto sementara, agar tampilan bisa ditinjau
# sebelum materi asli tersedia.
#
#   bash deploy/07-dummy-content.sh isi     -> masukkan konten contoh
#   bash deploy/07-dummy-content.sh hapus   -> bersihkan seluruhnya
#
# Semua baris yang dibuat ditandai kolom slug/nama berawalan "contoh-" atau
# berkas di storage/app/public/contoh/, jadi penghapusannya pasti bersih dan
# tidak menyentuh data asli yang kamu masukkan sendiri.
set -euo pipefail
cd "$(dirname "$0")/.."

AKSI="${1:-}"
DUMMY_DIR="backend/api/public/assets/dummy"
STORAGE_DIR="backend/api/storage/app/public/contoh"
source .env
SQL() { docker exec -i jsmuguard-db mysql -ujsmu_user -p"$DB_PASSWORD" "$DB_DATABASE" -e "$1" 2>&1 | grep -v "Using a password" || true; }

case "$AKSI" in
isi)
    echo "==> Menyalin foto contoh ke storage"
    docker exec jsmuguard-app mkdir -p storage/app/public/contoh
    for f in hero kegiatan-1 kegiatan-2 kegiatan-3 sektor-industri sektor-perbankan sektor-kesehatan sektor-perkantoran sektor-logistik sektor-ritel pagehead-legalitas; do
        [ -f "$DUMMY_DIR/$f.jpg" ] && cp "$DUMMY_DIR/$f.jpg" "$STORAGE_DIR/$f.jpg"
    done
    docker exec jsmuguard-app chown -R www-data:www-data storage/app/public/contoh

    echo "==> Pengaturan perusahaan"
    for kv in "company_name|PT. Jaya Sakti Mandiri Unggul" \
              "license_number|SIO/CONTOH/2025/BAHARKAM" \
              "founded_year|2013" \
              "stat_personnel|340" \
              "stat_sites|12" \
              "stat_attendance|99" \
              "office_hours|Senin–Jumat, 08.00–17.00 WIB" \
              "footer_address|Jl. Contoh Raya No. 12\nJakarta Selatan 12750" \
              "footer_phone|+62 21 5000 1234" \
              "footer_email|info@jsmu.co.id"; do
        k="${kv%%|*}"; v="${kv#*|}"
        SQL "INSERT INTO settings (\`key\`,\`value\`,created_at,updated_at) VALUES ('$k','$v',NOW(),NOW())
             ON DUPLICATE KEY UPDATE \`value\`=VALUES(\`value\`), updated_at=NOW();"
    done

    echo "==> Layanan"
    SQL "INSERT INTO cms_services (title,slug,short_description,full_description,image,\`order\`,created_at,updated_at) VALUES
     ('Tenaga Pengamanan','security-guards','Anggota Satpam bersertifikat untuk penjagaan, pengawalan, dan pengaturan akses.','<p>Penempatan anggota Satpam bersertifikat yang terdaftar, berseragam sesuai ketentuan, dan diawasi supervisor lapangan.</p>','contoh/sektor-industri.jpg',1,NOW(),NOW()),
     ('Teknologi Pengamanan','technology','CCTV, kontrol akses, absensi dan patroli digital dengan bukti titik dan waktu.','<p>Sistem yang membuat pengamanan bisa diperiksa, bukan sekadar dipercaya.</p>','contoh/sektor-perkantoran.jpg',2,NOW(),NOW()),
     ('Pelatihan & Pendidikan','training','Penyegaran Gada Pratama, bela diri, penanganan kebakaran, dan tanggap darurat.','<p>Kemampuan anggota tidak berhenti di sertifikat awal.</p>','contoh/kegiatan-1.jpg',3,NOW(),NOW()),
     ('Konsultansi & Risiko','consultancy','Kajian kerawanan lokasi, penyusunan prosedur, dan audit penerapan.','<p>Kajian sebelum penempatan: titik rawan, kebutuhan personel, prosedur.</p>','contoh/sektor-logistik.jpg',4,NOW(),NOW()),
     ('Unit K-9','k9','Anjing terlatih untuk deteksi bahan berbahaya dan patroli area luas.','<p>Untuk tugas yang tidak bisa dikerjakan pengamanan konvensional.</p>','contoh/sektor-kesehatan.jpg',5,NOW(),NOW());"

    echo "==> Kegiatan"
    SQL "INSERT INTO cms_activities (title,slug,short_description,content,image,type,date,created_at,updated_at) VALUES
     ('Apel pagi dan serah terima jaga','contoh-apel-pagi','Apel rutin sebelum pergantian regu di lokasi klien.','<p>Apel pagi menjadi titik kendali harian: pemeriksaan kelengkapan, penyampaian informasi, dan serah terima tanggung jawab antar regu.</p>','contoh/kegiatan-3.jpg','internal','2026-08-12',NOW(),NOW()),
     ('Penyegaran Gada Pratama angkatan XII','contoh-gada-pratama','Pelatihan penyegaran bagi 40 anggota.','<p>Materi mencakup pengaturan akses, penanganan kebakaran awal, dan pertolongan pertama.</p>','contoh/kegiatan-1.jpg','internal','2026-07-28',NOW(),NOW()),
     ('Koordinasi pengamanan dengan manajemen kawasan','contoh-koordinasi','Rapat bulanan bersama pengelola kawasan industri.','<p>Evaluasi laporan patroli dan penyesuaian titik jaga.</p>','contoh/kegiatan-2.jpg','internal','2026-07-05',NOW(),NOW());"

    echo "==> Klien"
    SQL "INSERT INTO cms_clients (name,\`order\`,created_at,updated_at) VALUES
     ('Contoh Kawasan Industri',1,NOW(),NOW()),('Contoh Bank Nasional',2,NOW(),NOW()),
     ('Contoh Rumah Sakit',3,NOW(),NOW()),('Contoh Logistik Nusantara',4,NOW(),NOW()),
     ('Contoh Menara Perkantoran',5,NOW(),NOW()),('Contoh Ritel Sejahtera',6,NOW(),NOW());"

    echo "==> Legalitas"
    SQL "INSERT INTO cms_achievements (title,year,description,image,\`order\`,created_at,updated_at) VALUES
     ('Izin Operasional BUJP','2025','<p>Izin operasional sebagai badan usaha jasa pengamanan, berlaku nasional.</p>','contoh/pagehead-legalitas.jpg',1,NOW(),NOW()),
     ('Sertifikat Sistem Manajemen Mutu','2024','<p>Penerapan sistem manajemen mutu pada proses rekrutmen dan penempatan.</p>',NULL,2,NOW(),NOW()),
     ('Penghargaan Mitra Pengamanan Terbaik','2023','<p>Diberikan oleh pengelola kawasan industri atas capaian nihil insiden.</p>',NULL,3,NOW(),NOW());"

    echo "==> Testimoni"
    SQL "INSERT INTO testimonials (client_name,client_position,client_company,content,rating,status,is_featured,token,\`order\`,created_at,updated_at) VALUES
     ('Budi Santoso','Manajer GA','Contoh Kawasan Industri','Laporan patroli bisa kami periksa sendiri setiap pagi. Itu yang membedakan dengan penyedia sebelumnya.',5,'approved',1,'contoh-t1',1,NOW(),NOW()),
     ('Sri Wahyuni','Facility Manager','Contoh Menara Perkantoran','Pergantian regu tertib dan anggotanya konsisten. Keluhan penghuni turun jauh.',5,'approved',0,'contoh-t2',2,NOW(),NOW()),
     ('Andi Pratama','Kepala Cabang','Contoh Bank Nasional','Respons saat ada kejadian cepat dan terdokumentasi dengan baik.',4,'approved',0,'contoh-t3',3,NOW(),NOW());"

    echo "==> Lowongan"
    SQL "INSERT INTO cms_careers (title,slug,location,type,description,requirements,is_active,created_at,updated_at) VALUES
     ('Anggota Satpam — Kawasan Industri','contoh-satpam-industri','Bekasi','Penuh waktu','<p>Penjagaan pos dan pengaturan akses kendaraan di kawasan industri.</p>','<p>Pria/wanita, sehat jasmani, memiliki KTA Satpam Gada Pratama.</p>',1,NOW(),NOW()),
     ('Anggota Satpam — Perbankan','contoh-satpam-bank','Jakarta Selatan','Penuh waktu','<p>Penjagaan kantor cabang dan pengawalan kas.</p>','<p>Diutamakan berpengalaman di sektor perbankan.</p>',1,NOW(),NOW()),
     ('Supervisor Lapangan','contoh-supervisor','Jakarta','Penuh waktu','<p>Mengawasi beberapa titik penempatan dan menyusun laporan.</p>','<p>Minimal 3 tahun pengalaman, memiliki Gada Madya.</p>',1,NOW(),NOW());"

    echo "==> FAQ"
    SQL "INSERT INTO faqs (question,answer,category,\`order\`,is_active,created_at,updated_at) VALUES
     ('Apakah perusahaan ini berizin?','Ya. Salinan izin operasional dan dokumen legalitas dapat kami kirimkan untuk keperluan verifikasi tender.','Legalitas',1,1,NOW(),NOW()),
     ('Berapa lama proses penempatan anggota?','Bergantung jumlah titik dan hasil kajian lokasi. Untuk kebutuhan standar, penempatan dapat dimulai setelah kesepakatan lingkup kerja.','Layanan',2,1,NOW(),NOW()),
     ('Bagaimana kehadiran anggota dipantau?','Melalui sistem absensi dan patroli digital milik kami sendiri. Kehadiran diverifikasi dengan lokasi dan patroli tercatat per titik.','Layanan',3,1,NOW(),NOW()),
     ('Apakah proses lamaran dipungut biaya?','Tidak. Seluruh tahapan seleksi tidak dipungut biaya apa pun.','Karier',4,1,NOW(),NOW());"

    echo "==> Hero"
    SQL "INSERT INTO cms_hero_slides (title,subtitle,image,\`order\`,created_at,updated_at) VALUES
     ('Mitra Anda dalam pengamanan yang terukur','Anggota Satpam bersertifikat di kawasan industri, perbankan, dan fasilitas kesehatan — dengan kehadiran dan patroli yang tercatat digital.','contoh/hero.jpg',1,NOW(),NOW());"

    echo ""
    echo "Selesai. Buka https://jsmu.co.id untuk meninjau."
    echo "Untuk membersihkan: bash deploy/07-dummy-content.sh hapus"
    ;;

hapus)
    echo "==> Menghapus konten contoh"
    SQL "DELETE FROM cms_services WHERE slug IN ('security-guards','technology','training','consultancy','k9');"
    SQL "DELETE FROM cms_activities WHERE slug LIKE 'contoh-%';"
    SQL "DELETE FROM cms_clients WHERE name LIKE 'Contoh %';"
    SQL "DELETE FROM cms_achievements WHERE image LIKE 'contoh/%' OR title IN ('Izin Operasional BUJP','Sertifikat Sistem Manajemen Mutu','Penghargaan Mitra Pengamanan Terbaik');"
    SQL "DELETE FROM testimonials WHERE token LIKE 'contoh-%' OR client_company LIKE 'Contoh %';"
    SQL "DELETE FROM cms_careers WHERE slug LIKE 'contoh-%';"
    SQL "DELETE FROM faqs WHERE question IN ('Apakah perusahaan ini berizin?','Berapa lama proses penempatan anggota?','Bagaimana kehadiran anggota dipantau?','Apakah proses lamaran dipungut biaya?');"
    SQL "DELETE FROM cms_hero_slides WHERE image LIKE 'contoh/%';"
    SQL "DELETE FROM settings WHERE \`key\` IN ('license_number','stat_personnel','stat_sites','stat_attendance','founded_year','company_name','office_hours','footer_address','footer_phone','footer_email');"
    echo "==> Menghapus berkas foto contoh"
    rm -rf "$STORAGE_DIR" "$DUMMY_DIR"
    echo "Selesai. Situs kembali ke keadaan kosong yang dirancang."
    ;;

*)
    echo "Pemakaian: bash deploy/07-dummy-content.sh [isi|hapus]"; exit 1 ;;
esac
