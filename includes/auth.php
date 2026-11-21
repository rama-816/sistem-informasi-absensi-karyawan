<?php
// includes/auth.php
// Autentikasi + otorisasi RBAC
// Setiap halaman memanggil requireLogin() dan requirePermission('modul.aksi').

require_once __DIR__ . '/../config/database.php';

function currentUser()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = getPDO()->prepare(
        'SELECT u.*, r.name AS role_name FROM users u ' .
        'JOIN roles r ON r.id = u.role_id WHERE u.id = ? LIMIT 1'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function requireLogin()
{
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function hasPermission($permissionName)
{
    $user = currentUser();
    if (!$user) {
        return false;
    }
    // Admin selalu lolos selama izinnya ada di role_permissions,
    // pengecekan tetap lewat tabel agar matriks RBAC yang berlaku.
    $stmt = getPDO()->prepare(
        'SELECT COUNT(*) FROM role_permissions rp ' .
        'JOIN permissions p ON p.id = rp.permission_id ' .
        'WHERE rp.role_id = ? AND p.name = ?'
    );
    $stmt->execute([$user['role_id'], $permissionName]);
    return (int) $stmt->fetchColumn() > 0;
}

function requirePermission($permissionName)
{
    requireLogin();
    if (!hasPermission($permissionName)) {
        http_response_code(403);
        echo '<h1>403 — Akses Ditolak</h1>';
        echo '<p>Role Anda tidak memiliki izin <strong>' . e($permissionName) . '</strong>.</p>';
        echo '<p><a href="dashboard.php">Kembali ke Dashboard</a></p>';
        exit;
    }
}

function isRole($roleName)
{
    $user = currentUser();
    return $user && $user['role_name'] === $roleName;
}
