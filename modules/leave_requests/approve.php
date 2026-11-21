<?php
// modules/leave_requests/approve.php — Persetujuan HRD/Admin (POST saja)
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('leave_requests.approve');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decision = $_POST['decision'] ?? '';
    if ($decision === 'Disetujui' || $decision === 'Ditolak') {
        $stmt = getPDO()->prepare(
            'UPDATE leave_requests SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ? AND status = "Menunggu"'
        );
        $stmt->execute([$decision, currentUser()['id'], (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Pengajuan ' . $decision . '.');
    }
}
redirect('index.php');
