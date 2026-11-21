<?php
// modules/attendances/checkin.php — Absen Masuk / Pulang (satu tombol)
// Masuk setelah 08:00 dicatat Terlambat; bila sudah ada absen hari ini, tombol menjadi Pulang.
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('attendances.checkin');
$pdo = getPDO();
$user = currentUser();
if (!$user['employee_id']) {
    setFlash('error', 'Akun ini tidak terhubung ke data karyawan.');
    redirect('index.php');
}
$today = date('Y-m-d');
$now = date('H:i:s');
$stmt = $pdo->prepare('SELECT * FROM attendances WHERE employee_id = ? AND date = ?');
$stmt->execute([$user['employee_id'], $today]);
$row = $stmt->fetch();
if (!$row) {
    $status = ($now > '08:00:00') ? 'Terlambat' : 'Hadir';
    $stmt = $pdo->prepare('INSERT INTO attendances (employee_id, date, check_in, status) VALUES (?,?,?,?)');
    $stmt->execute([$user['employee_id'], $today, $now, $status]);
    setFlash('success', 'Absen masuk tercatat pukul ' . $now . ' (' . $status . ').');
} elseif (empty($row['check_out'])) {
    $stmt = $pdo->prepare('UPDATE attendances SET check_out = ? WHERE id = ?');
    $stmt->execute([$now, $row['id']]);
    setFlash('success', 'Absen pulang tercatat pukul ' . $now . '.');
} else {
    setFlash('success', 'Anda sudah absen masuk dan pulang hari ini.');
}
redirect('index.php');
