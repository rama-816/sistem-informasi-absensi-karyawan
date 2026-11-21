<?php
// includes/header.php
// Layout utama: sidebar navigasi (menu tampil sesuai izin RBAC) + topbar.
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
$flash = getFlash();
$initials = '';
foreach (preg_split('/\s+/', trim($user['name'])) as $part) {
    if ($part !== '') { $initials .= strtoupper($part[0]); }
    if (strlen($initials) >= 2) { break; }
}
$active = $pageTitle ?? '';
function menuLink($href, $label, $icon, $title, $active) {
    $cls = ($title === $active) ? ' class="active"' : '';
    echo '<a href="' . $href . '"' . $cls . '><span class="ico">' . $icon . '</span>' . $label . '</a>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($pageTitle ?? APP_NAME); ?> — <?php echo APP_NAME; ?></title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar">
  <div class="brand">
    <span class="logo">A</span>
    <span><strong>Absensi Karyawan</strong><small>Sistem Informasi v<?php echo APP_VERSION; ?></small></span>
  </div>
  <nav class="menu">
    <span class="menu-label">Menu Utama</span>
    <?php if (hasPermission('dashboard.view')) { menuLink('/dashboard.php', 'Dashboard', '▦', 'Dashboard', $active); } ?>
    <?php if (hasPermission('employees.view')) { menuLink('/modules/employees/index.php', 'Data Karyawan', '☰', 'Data Karyawan', $active); } ?>
    <?php if (hasPermission('attendances.view')) { menuLink('/modules/attendances/index.php', 'Data Absensi', '✓', 'Data Absensi', $active); } ?>
    <?php if (hasPermission('leave_requests.view')) { menuLink('/modules/leave_requests/index.php', 'Izin / Cuti', '✎', 'Izin / Cuti', $active); } ?>
    <?php if (hasPermission('rbac.view')): ?>
    <span class="menu-label">Pengaturan</span>
    <?php menuLink('/modules/rbac/index.php', 'Role &amp; Hak Akses', '⚙', 'Pengelolaan RBAC', $active); ?>
    <?php endif; ?>
  </nav>
  <div class="side-user">
    <span class="avatar"><?php echo e($initials); ?></span>
    <span><strong><?php echo e($user['name']); ?></strong><small><?php echo e($user['role_name']); ?></small></span>
  </div>
</aside>
<div class="main">
<header class="topbar">
  <span class="page-title"><?php echo e($pageTitle ?? 'Dashboard'); ?></span>
  <span class="spacer"></span>
  <span class="date"><?php echo e(date('l, d F Y')); ?></span>
  <span class="user-chip"><span class="avatar"><?php echo e($initials); ?></span><?php echo e($user['name']); ?> · <?php echo e($user['role_name']); ?></span>
  <a class="logout" href="/logout.php">Logout</a>
</header>
<main class="container">
<?php if ($flash): ?>
  <div class="flash <?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
<?php endif; ?>
