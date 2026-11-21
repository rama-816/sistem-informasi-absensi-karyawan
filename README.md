# Sistem Informasi Absensi Karyawan

**Rancangan Aplikasi Sistem Informasi Absensi Karyawan — UAS**

| | |
|---|---|
| Nama | Rama Achmad Fadillah |
| NIM | 231011402168 |
| Kelas | 07TPLE004 |
| Nama file UAS | `UAS_07TPLE004_231011402168_RAMA-ACHMAD-FADILLAH` |
| Pengumpulan source code & SQL | siklik.id/5fWV3hcG |
| Penilaian | Dimulai pekan 14 |

## Deskripsi Aplikasi

Aplikasi web untuk mencatat kehadiran karyawan secara terpusat: karyawan
absen masuk/pulang dari dashboard, HRD memantau dan mengoreksi absensi,
Admin mengelola data karyawan, pengguna, dan hak akses. Pengajuan
Izin/Cuti/Sakit diajukan karyawan dan disetujui atau ditolak oleh
HRD/Admin, sehingga rekap kehadiran tidak lagi dicatat manual di kertas
atau spreadsheet terpisah.

Teknologi: **PHP native (tanpa framework), PDO MySQL, HTML/CSS modern** — antarmuka admin dengan sidebar navigasi, kartu statistik dashboard, tabel data dengan badge status (Hadir/Terlambat/Izin/Sakit/Cuti dan Menunggu/Disetujui/Ditolak), avatar inisial karyawan, serta halaman login khusus. Seluruh gaya terpusat di `assets/style.css` tanpa framework CSS.
Struktur MVC sederhana: `config/` (koneksi), `includes/` (auth, helper,
layout), `modules/` (satu folder per fitur), `assets/` (CSS).

## Cara Instalasi

### Prasyarat
- XAMPP atau Laragon (PHP 8.x, MySQL/MariaDB, Apache)
- Browser modern

### Langkah-langkah
1. Salin folder ini ke `htdocs` XAMPP (mis. `C:\xampp\htdocs\absensi-karyawan`)
   atau ke `www` Laragon.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel / Laragon.
3. Buat database baru bernama `absensi_karyawan`
   (phpMyAdmin > Baru > `absensi_karyawan`, collation `utf8mb4_general_ci`).
4. Import `database.sql` (phpMyAdmin > pilih database > Import > pilih
   file `database.sql` > Kirim), atau lewat terminal:
   ```bash
   mysql -u root -p absensi_karyawan < database.sql
   ```
5. Atur koneksi di `config/database.php` bila perlu — default:
   `host = localhost`, `dbname = absensi_karyawan`,
   `user = root`, `password = ''` (kosong, sesuai default XAMPP).
6. Buka di browser: `http://localhost/absensi-karyawan/login.php`
   (sesuaikan nama folder bila berbeda).
7. Login dengan salah satu akun demo di bawah ini.

> Catatan: bila aplikasi diletakkan di subfolder, link absolut di
> `includes/header.php` (`/dashboard.php`, `/assets/style.css`, dan
> sejenisnya) perlu disesuaikan ke path subfolder tersebut, atau
> letakkan aplikasi di document root web server.

## Akun Demo

Login memakai **email**. Password di database tersimpan sebagai hash
bcrypt (`password_hash()` PHP) — password plain hanya tercantum di sini.

| Role | Email | Password |
|---|---|---|
| Admin | admin@absensi.test | admin123 |
| HRD | hrd@absensi.test | hrd12345 |
| Karyawan | karyawan@absensi.test | karyawan123 |

> Akun Karyawan terhubung ke data karyawan **Rama Achmad Fadillah**
> (NIP 2024001). Akun HRD terhubung ke **Rina Marlina** (NIP 2024002).

## Daftar Fitur dan CRUD

### 1. Dashboard (`dashboard.php`)
- Ringkasan hari ini: jumlah karyawan aktif, hadir hari ini,
  terlambat hari ini, dan pengajuan izin/cuti yang masih menunggu.
- Tombol Absen Masuk/Pulang (untuk role yang punya izin `attendances.checkin`).

### 2. Data Karyawan — CRUD lengkap (`modules/employees/`)
- **Create:** `create.php` — tambah karyawan (NIP, nama, email, telepon,
  jabatan, departemen, tanggal masuk, status).
- **Read:** `index.php` — daftar + pencarian nama/NIP.
- **Update:** `edit.php` — ubah data karyawan.
- **Delete:** `delete.php` — hapus karyawan (POST + konfirmasi).

### 3. Data Absensi — CRUD lengkap (`modules/attendances/`)
- **Create:** `checkin.php` — absen masuk/pulang satu tombol
  (masuk setelah 08:00 otomatis `Terlambat`); `create.php` — input
  absensi manual oleh HRD/Admin.
- **Read:** `index.php` — daftar absensi (karyawan hanya melihat
  absensinya sendiri).
- **Update:** `edit.php` — ubah tanggal, jam masuk/pulang, status, catatan.
- **Delete:** `delete.php` — hapus absensi (POST + konfirmasi).

### 4. Pengajuan Izin/Cuti — CRUD lengkap + persetujuan (`modules/leave_requests/`)
- **Create:** `create.php` — ajukan Izin/Cuti/Sakit (karyawan mengajukan
  untuk dirinya sendiri).
- **Read:** `index.php` — daftar pengajuan (karyawan hanya melihat
  pengajuannya sendiri).
- **Update:** `edit.php` — ubah pengajuan yang masih `Menunggu`.
- **Delete:** `delete.php` — hapus pengajuan.
- **Approve:** `approve.php` — HRD/Admin menyetujui atau menolak
  pengajuan; pencatat persetujuan (`approved_by`, `approved_at`)
  tersimpan di database.

## Role dan RBAC (Role-Based Access Control)

Ada **3 role**: **Admin**, **HRD**, dan **Karyawan**.

RBAC diatur lewat tabel `roles`, `permissions`, dan `role_permissions`:

- `permissions` berisi **18 izin** dengan pola `modul.aksi`,
  mis. `employees.create`, `attendances.delete`, `leave_requests.approve`,
  `rbac.edit`.
- `role_permissions` adalah matriks role × izin: Admin 18 izin,
  HRD 13 izin, Karyawan 6 izin.
- Setiap halaman/aksi memanggil `requirePermission('modul.aksi')`
  di `includes/auth.php`. Fungsi `hasPermission()` selalu membaca
  `role_permissions` dari database, jadi perubahan matriks langsung
  berlaku tanpa mengubah kode.
- Menu di `includes/header.php` juga hanya menampilkan menu yang
  izinkan role pengguna.

Matriks izin dapat diubah di halaman **RBAC** (`modules/rbac/index.php`,
hanya Admin): pilih role, centang/hilangkan izin, simpan — perubahan
langsung berlaku untuk semua pengguna role tersebut.

| Izin | Admin | HRD | Karyawan |
|---|:---:|:---:|:---:|
| dashboard.view | ✓ | ✓ | ✓ |
| employees.view / create / edit | ✓ | ✓ | – |
| employees.delete | ✓ | – | – |
| attendances.view / create / edit | ✓ | ✓ | ✓ (view saja) |
| attendances.delete | ✓ | – | – |
| attendances.checkin | ✓ | ✓ | ✓ |
| leave_requests.view / create / edit | ✓ | ✓ | ✓ |
| leave_requests.delete | ✓ | – | – |
| leave_requests.approve | ✓ | ✓ | – |
| rbac.view / rbac.edit | ✓ | – | – |
| reports.view | ✓ | ✓ | – |

## Struktur Database

File: `database.sql` — 8 tabel (InnoDB, `utf8mb4`):

| Tabel | Isi |
|---|---|
| `roles` | Daftar role (Admin, HRD, Karyawan) |
| `permissions` | 18 izin `modul.aksi` |
| `role_permissions` | Matriks RBAC (role_id, permission_id) |
| `users` | Akun login (email + password hash, role_id, employee_id) |
| `employees` | Data karyawan (NIP unik, jabatan, departemen, status) |
| `schedules` | Jam kerja / shift |
| `attendances` | Absensi harian (unik per karyawan+tanggal) |
| `leave_requests` | Pengajuan Izin/Cuti/Sakit + persetujuan |

Relasi (foreign key): `users.role_id → roles.id`,
`users.employee_id → employees.id`, `role_permissions` ke `roles` dan
`permissions`, `attendances.employee_id → employees.id`,
`leave_requests.employee_id → employees.id`,
`leave_requests.approved_by → users.id`.

Data seed: 3 role, 18 izin, 37 baris `role_permissions`,
3 user demo, 8 karyawan, 3 jadwal shift, 15 absensi,
4 pengajuan izin/cuti.

## Struktur Folder

```
UAS_07TPLE004_231011402168_RAMA-ACHMAD-FADILLAH/
├── README.md
├── database.sql
├── index.php
├── login.php
├── logout.php
├── dashboard.php
├── .htaccess
├── config/
│   ├── app.php
│   └── database.php
├── includes/
│   ├── auth.php        # requireLogin, hasPermission, requirePermission
│   ├── helpers.php     # escape, redirect, flash message
│   ├── header.php      # layout + menu berbasis izin
│   └── footer.php
├── modules/
│   ├── employees/      # index, create, edit, delete
│   ├── attendances/    # index, create, edit, delete, checkin
│   ├── leave_requests/ # index, create, edit, approve, delete
│   └── rbac/           # index (matriks izin)
└── assets/
    └── style.css
```

## Hosting

Hosting web: **belum di-deploy / placeholder**.
Aplikasi ini disiapkan untuk di-hosting (boleh subdomain, mis.
`absensi.<domain-anda>` atau hosting PHP/MySQL seperti Niagahoster,
Hostinger, atau server kampus) setelah source code dan `database.sql`
di-upload ke siklik.id/5fWV3hcG. Untuk penilaian, jalankan secara lokal
sesuai langkah instalasi di atas, atau isi URL hosting di sini setelah
deploy:
- URL aplikasi: *(isi setelah hosting)*
- Panel hosting / subdomain: *(isi setelah hosting)*

## Catatan

- Ganti password akun demo setelah instalasi di lingkungan produksi.
- File ini (README + `database.sql` + source code) adalah satu paket
  dengan nama `UAS_07TPLE004_231011402168_RAMA-ACHMAD-FADILLAH`.
