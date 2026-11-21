<?php
// modules/leave_requests/delete.php — DELETE pengajuan (POST saja)
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('leave_requests.delete');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = getPDO()->prepare('DELETE FROM leave_requests WHERE id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Pengajuan berhasil dihapus.');
}
redirect('index.php');
