-- ============================================================
-- database.sql
-- Sistem Informasi Absensi Karyawan
-- UAS — Rama Achmad Fadillah
-- NIM  : 231011402168
-- Kelas: 07TPLE004
-- Nama file: UAS_07TPLE004_231011402168_RAMA-ACHMAD-FADILLAH
-- ------------------------------------------------------------
-- Cara pakai: buat database `absensi_karyawan`, lalu import file
-- ini (phpMyAdmin > Import, atau: mysql -u root -p absensi_karyawan < database.sql)
-- Password akun demo disimpan sebagai hash bcrypt (password_hash PHP).
-- Password plain hanya tercantum di README.md, bukan di file ini.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `leave_requests`;
DROP TABLE IF EXISTS `attendances`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `schedules`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Tabel 1: roles
-- ------------------------------------------------------------
CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel 2: permissions (18 izin, dikelompokkan per modul)
-- ------------------------------------------------------------
CREATE TABLE `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permissions_name` (`name`),
  KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel 3: employees
-- ------------------------------------------------------------
CREATE TABLE `employees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nip` VARCHAR(20) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `position` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `join_date` DATE NOT NULL,
  `status` ENUM('Aktif','Nonaktif','Cuti') NOT NULL DEFAULT 'Aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_employees_nip` (`nip`),
  UNIQUE KEY `uk_employees_email` (`email`),
  KEY `idx_employees_department` (`department`),
  KEY `idx_employees_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel 4: schedules (jam kerja / shift)
-- ------------------------------------------------------------
CREATE TABLE `schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `description` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel 5: users (login memakai email)
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role` (`role_id`),
  KEY `idx_users_employee` (`employee_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_users_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel 6: role_permissions (matriks RBAC)
-- ------------------------------------------------------------
CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  KEY `idx_rp_permission` (`permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel 7: attendances
-- ------------------------------------------------------------
CREATE TABLE `attendances` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `check_in` TIME NULL,
  `check_out` TIME NULL,
  `status` ENUM('Hadir','Terlambat','Izin','Sakit','Alpa') NOT NULL DEFAULT 'Hadir',
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_attendance_employee_date` (`employee_id`, `date`),
  KEY `idx_attendances_date` (`date`),
  KEY `idx_attendances_status` (`status`),
  CONSTRAINT `fk_attendances_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel 8: leave_requests
-- ------------------------------------------------------------
CREATE TABLE `leave_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `type` ENUM('Izin','Cuti','Sakit') NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `approved_by` INT UNSIGNED NULL,
  `approved_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leave_employee` (`employee_id`),
  KEY `idx_leave_status` (`status`),
  KEY `idx_leave_start` (`start_date`),
  CONSTRAINT `fk_leave_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_leave_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DATA SEED
-- ============================================================

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Admin', 'Mengelola seluruh data, pengguna, dan RBAC'),
(2, 'HRD', 'Mengelola karyawan, absensi, dan persetujuan izin/cuti'),
(3, 'Karyawan', 'Absen mandiri dan mengajukan izin/cuti');

INSERT INTO `permissions` (`id`, `name`, `module`, `description`) VALUES
(1, 'dashboard.view', 'dashboard', 'Melihat dashboard'),
(2, 'employees.view', 'employees', 'Melihat data karyawan'),
(3, 'employees.create', 'employees', 'Menambah karyawan'),
(4, 'employees.edit', 'employees', 'Mengubah karyawan'),
(5, 'employees.delete', 'employees', 'Menghapus karyawan'),
(6, 'attendances.view', 'attendances', 'Melihat data absensi'),
(7, 'attendances.create', 'attendances', 'Input absensi manual'),
(8, 'attendances.edit', 'attendances', 'Mengubah absensi'),
(9, 'attendances.delete', 'attendances', 'Menghapus absensi'),
(10, 'attendances.checkin', 'attendances', 'Absen masuk / pulang'),
(11, 'leave_requests.view', 'leave_requests', 'Melihat pengajuan izin/cuti'),
(12, 'leave_requests.create', 'leave_requests', 'Mengajukan izin/cuti'),
(13, 'leave_requests.edit', 'leave_requests', 'Mengubah pengajuan'),
(14, 'leave_requests.delete', 'leave_requests', 'Menghapus pengajuan'),
(15, 'leave_requests.approve', 'leave_requests', 'Menyetujui / menolak pengajuan'),
(16, 'rbac.view', 'rbac', 'Melihat matriks RBAC'),
(17, 'rbac.edit', 'rbac', 'Mengubah matriks RBAC'),
(18, 'reports.view', 'reports', 'Melihat laporan / rekap');

INSERT INTO `employees` (`id`, `nip`, `name`, `email`, `phone`, `position`, `department`, `join_date`, `status`) VALUES
(1, '2024001', 'Rama Achmad Fadillah', 'rama@absensi.test', '081234567801', 'Staff IT', 'IT', '2024-01-15', 'Aktif'),
(2, '2024002', 'Rina Marlina', 'rina@absensi.test', '081234567802', 'HR Staff', 'HRD', '2024-02-01', 'Aktif'),
(3, '2024003', 'Budi Santoso', 'budi@absensi.test', '081234567803', 'Staff Keuangan', 'Keuangan', '2024-02-10', 'Aktif'),
(4, '2024004', 'Siti Aminah', 'siti@absensi.test', '081234567804', 'Staff Administrasi', 'Administrasi', '2024-03-01', 'Aktif'),
(5, '2024005', 'Andi Pratama', 'andi@absensi.test', '081234567805', 'Staff IT', 'IT', '2024-03-15', 'Aktif'),
(6, '2024006', 'Dewi Lestari', 'dewi@absensi.test', '081234567806', 'Staff HRD', 'HRD', '2024-04-01', 'Aktif'),
(7, '2024007', 'Joko Susilo', 'joko@absensi.test', '081234567807', 'Staff Gudang', 'Logistik', '2024-04-15', 'Aktif'),
(8, '2024008', 'Maya Putri', 'maya@absensi.test', '081234567808', 'Staff Marketing', 'Marketing', '2024-05-01', 'Aktif');

INSERT INTO `schedules` (`id`, `name`, `start_time`, `end_time`, `description`) VALUES
(1, 'Shift Pagi', '08:00:00', '17:00:00', 'Jam kerja reguler Senin–Jumat'),
(2, 'Shift Siang', '13:00:00', '22:00:00', 'Shift siang'),
(3, 'Shift Fleksibel', '09:00:00', '18:00:00', 'Jam kerja fleksibel');

-- Akun demo: password di bawah ini adalah hash bcrypt.
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `employee_id`, `is_active`) VALUES
(1, 'Administrator', 'admin@absensi.test', '$2b$10$7RK4/NZJI/R3MMgSwBddT.1R//L4bBay91nElvfMgH4s3tj1qNrpW', 1, NULL, 1),
(2, 'HRD Absensi', 'hrd@absensi.test', '$2b$10$fVLk83d70emdwdU5FuB/xO9yheiAcftLLaqD2SIFTB1I1./2YnBpW', 2, 2, 1),
(3, 'Rama Achmad Fadillah', 'karyawan@absensi.test', '$2b$10$ZMO3mVnVLwRDz5VnP/usQukPp2jcagQnnH94kxHyOxQ56BrgMmhme', 3, 1, 1);

-- RBAC: Admin 18 izin, HRD 13 izin, Karyawan 6 izin (total 37 baris)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9),
(1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16), (1, 17), (1, 18),
(2, 1), (2, 2), (2, 3), (2, 4),
(2, 6), (2, 7), (2, 8), (2, 10),
(2, 11), (2, 12), (2, 13), (2, 15),
(2, 18),
(3, 1), (3, 6), (3, 10), (3, 11), (3, 12), (3, 13);

INSERT INTO `attendances` (`id`, `employee_id`, `date`, `check_in`, `check_out`, `status`, `notes`) VALUES
(1, 1, '2026-11-16', '08:02:00', '17:05:00', 'Hadir', NULL),
(2, 2, '2026-11-16', '07:58:00', '17:00:00', 'Hadir', NULL),
(3, 3, '2026-11-16', '08:25:00', '17:10:00', 'Terlambat', 'Macet di jalan'),
(4, 1, '2026-11-17', '07:55:00', '17:02:00', 'Hadir', NULL),
(5, 4, '2026-11-17', '08:00:00', '17:00:00', 'Hadir', NULL),
(6, 5, '2026-11-17', '08:40:00', '17:15:00', 'Terlambat', 'Terlambat bangun'),
(7, 1, '2026-11-18', '08:05:00', '17:00:00', 'Hadir', NULL),
(8, 6, '2026-11-18', '07:59:00', '16:58:00', 'Hadir', NULL),
(9, 7, '2026-11-18', '08:10:00', '17:05:00', 'Hadir', NULL),
(10, 2, '2026-11-19', '08:01:00', '17:03:00', 'Hadir', NULL),
(11, 3, '2026-11-19', '07:57:00', '17:00:00', 'Hadir', NULL),
(12, 8, '2026-11-19', '08:12:00', '17:08:00', 'Hadir', NULL),
(13, 1, '2026-11-20', '08:32:00', '17:04:00', 'Terlambat', 'Kendaraan mogok'),
(14, 4, '2026-11-20', '08:03:00', '17:01:00', 'Hadir', NULL),
(15, 5, '2026-11-20', '07:56:00', '17:00:00', 'Hadir', NULL);

INSERT INTO `leave_requests` (`id`, `employee_id`, `type`, `start_date`, `end_date`, `reason`, `status`, `approved_by`, `approved_at`) VALUES
(1, 2, 'Cuti', '2026-11-24', '2026-11-26', 'Cuti tahunan bersama keluarga', 'Disetujui', 1, '2026-11-17 10:00:00'),
(2, 3, 'Izin', '2026-11-20', '2026-11-20', 'Izin keperluan keluarga', 'Menunggu', NULL, NULL),
(3, 4, 'Sakit', '2026-11-18', '2026-11-19', 'Sakit demam', 'Disetujui', 2, '2026-11-18 09:15:00'),
(4, 5, 'Cuti', '2026-12-01', '2026-12-03', 'Cuti liburan akhir tahun', 'Ditolak', 1, '2026-11-19 14:20:00');

-- Akhir database.sql
