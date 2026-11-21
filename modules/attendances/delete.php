<?php
// modules/attendances/delete.php — DELETE absensi (POST saja)
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('attendances.delete');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = getPDO()->prepare('DELETE FROM attendances WHERE id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Absensi berhasil dihapus.');
}
redirect('index.php');
