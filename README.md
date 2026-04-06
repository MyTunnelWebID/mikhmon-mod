# MIKHMON HOTSPOT & PPPOE

Modifikasi aplikasi Mikhmon untuk manajemen Hotspot dan PPPoE pada router MikroTik.

## Gambaran Umum

Project ini adalah panel berbasis PHP untuk mengelola layanan hotspot voucher dan PPPoE melalui RouterOS API. Selain fungsi dasar Mikhmon, repositori ini juga memuat beberapa penyesuaian seperti dukungan Agent Reseller per router, report penjualan voucher, Quick Print, editor template voucher, upload logo, backup-restore konfigurasi, dan komponen Progressive Web App dasar.

## Fitur Utama

### 1. Dashboard Monitoring Router

- Menampilkan tanggal, waktu, uptime, timezone, dan identitas router.
- Menampilkan informasi board, model RouterBOARD, versi RouterOS, CPU load, free memory, dan free storage.
- Menyajikan ringkasan jumlah Hotspot Active, Hotspot Users, PPP Profiles, PPP Secrets, dan PPP Active.

### 2. Manajemen Multi Router / Session

- Mendukung banyak router dalam satu panel melalui session yang terpisah.
- Router dapat ditambah, diedit, dipilih, dan dihapus dari halaman Admin Settings.
- Perpindahan router aktif dapat dilakukan langsung dari dropdown session di antarmuka.
- Tersedia fitur backup dan restore file konfigurasi `include/config.php`.

### 3. Fitur Hotspot

- Daftar user hotspot.
- Tambah user hotspot manual.
- Generate voucher secara massal.
- Kelola user profile hotspot.
- Monitoring hotspot active.
- Monitoring hosts.
- Kelola IP Binding.
- Monitoring hotspot cookies.
- Filter user berdasarkan profile dan Agent Reseller.
- Reset, enable, disable, dan hapus user melalui modul proses yang tersedia.

### 4. Voucher dan Pencetakan

- Generate voucher dari profile yang dipilih.
- Cetak voucher dalam beberapa mode, termasuk default, QR, small, dan template lain yang tersedia pada folder `voucher/`.
- Quick Print berbasis data script RouterOS untuk mempercepat pencetakan voucher berulang.
- Tersedia halaman daftar Quick Print.
- Ada dukungan opsi cetak QR/BT dari pengaturan admin.
- Tersedia Template Editor untuk menyesuaikan tampilan voucher.
- Upload logo per session untuk identitas cetak voucher dan tampilan hotspot.

### 5. Fitur PPPoE Server

- Daftar PPP Secrets.
- Tambah secret PPPoE.
- Monitoring PPP Active.
- Daftar PPP Profiles.
- Tambah profile PPP.
- Pengelolaan secret dan profile melalui RouterOS API.

### 6. Report dan Monitoring Operasional

- Report penjualan voucher.
- User log report.
- Hotspot log.
- Report Agent Reseller.
- DHCP leases monitor.
- Traffic monitor per interface.
- Halaman status voucher untuk pengecekan masa aktif, uptime, pemakaian data, dan sisa kuota oleh user.

### 7. Agent Reseller

- Pengelolaan Agent Reseller dilakukan per router/session.
- Tersedia daftar member Agent Reseller dan report penjualannya.
- Penandaan penjualan voucher menggunakan marker komentar agar report dapat ditelusuri per agent.
- Agent tidak bisa dihapus atau diubah sembarangan jika masih terkait dengan data voucher/user yang sudah ada.

### 8. Sistem dan Otomasi

- Monitoring dan pengelolaan System Scheduler.
- Aksi reboot router.
- Aksi shutdown router.
- Beberapa modul hotspot dan PPP memanfaatkan scheduler untuk otomasi masa aktif atau proses tertentu.

### 9. Kustomisasi Antarmuka

- Dukungan beberapa tema tampilan.
- Dukungan multi bahasa.
- Bahasa yang tersedia di repositori ini antara lain Indonesia, Inggris, Spanyol, Tagalog, dan Turki.
- Upload logo dan editor template untuk kebutuhan branding.

### 10. Progressive Web App Dasar

- Tersedia `manifest.json`.
- Registrasi service worker dilakukan melalui `index.js`.
- Sudah ada dasar cache asset pada `service-worker.js`.

Catatan: implementasi PWA di repositori ini masih bersifat dasar. Service worker sudah disiapkan, tetapi strategi cache untuk request fetch masih dinonaktifkan di kode saat ini.

## Struktur Modul Penting

- `dashboard/` untuk halaman ringkasan dan monitoring utama.
- `hotspot/` untuk pengelolaan user, profile, quick print, active session, hosts, cookies, dan Agent Reseller.
- `ppp/` untuk pengelolaan PPP Secrets, PPP Profiles, dan PPP Active.
- `report/` untuk laporan penjualan, user log, live report, dan report Agent Reseller.
- `system/` untuk scheduler dan aksi sistem.
- `settings/` untuk session router, upload logo, dan editor template.
- `status/` untuk halaman cek status voucher/user.
- `process/` untuk aksi backend seperti enable, disable, remove, reboot, shutdown, dan scheduler.
- `include/` untuk konfigurasi, login, tema, bahasa, helper, dan komponen layout.
- `voucher/` untuk template cetak voucher.

## Kebutuhan Dasar

- Web server yang dapat menjalankan PHP.
- Router MikroTik dengan RouterOS API yang aktif dan dapat diakses dari server aplikasi.
- File konfigurasi router pada `include/config.php`.

## Cara Menggunakan Secara Singkat

1. Letakkan source code ini di web server PHP.
2. Pastikan router MikroTik dapat diakses dari server aplikasi.
3. Siapkan data koneksi router pada `include/config.php`, atau unggah file konfigurasi dari menu Admin Settings jika aplikasi sudah berjalan.
4. Tambahkan session router dari halaman admin bila diperlukan.
5. Login ke panel dan pilih router/session yang ingin dikelola.
6. Gunakan menu Hotspot, PPPoE Server, Report, System, dan Settings sesuai kebutuhan operasional.

## File dan Halaman Penting

- `admin.php` untuk login dan pengaturan admin/session.
- `index.php` sebagai router utama halaman aplikasi.
- `status/index.php` untuk halaman cek status voucher.
- `manifest.json`, `index.js`, dan `service-worker.js` untuk komponen PWA.

## Kredit

- Aplikasi dasar berasal dari Mikhmon karya Laksamadi Guko.
- Library API yang digunakan: `routeros-api`.
- Repositori ini berisi versi modifikasi yang menambahkan penyesuaian fitur untuk kebutuhan hotspot dan PPPoE.

## Lisensi

Lihat file `LICENSE` dan `LICENSE.txt` untuk informasi lisensi yang disertakan pada repositori ini.
