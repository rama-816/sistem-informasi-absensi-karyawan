<?php
// config/app.php
// Konfigurasi umum aplikasi

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Sistem Informasi Absensi Karyawan');
define('APP_VERSION', '1.0.0');
define('BASE_URL', ''); // kosong = relative path, isi mis. '/absensi' bila di subfolder

date_default_timezone_set('Asia/Jakarta');
