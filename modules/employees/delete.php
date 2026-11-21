<?php
// modules/employees/delete.php — DELETE Data Karyawan (POST saja)
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('employees.delete');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = getPDO()->prepare('DELETE FROM employees WHERE id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    setFlash('success', 'Karyawan berhasil dihapus.');
}
redirect('index.php');
