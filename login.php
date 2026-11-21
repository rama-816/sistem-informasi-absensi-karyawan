<?php
// login.php — login dengan email + password, role diambil dari tabel users/roles
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = getPDO()->prepare(
        'SELECT u.*, r.name AS role_name FROM users u ' .
        'JOIN roles r ON r.id = u.role_id WHERE u.email = ? AND u.is_active = 1 LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        redirect('dashboard.php');
    }
    $error = 'Email atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — <?php echo APP_NAME; ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div class="login-brand">
      <span class="logo">A</span>
      <h1><?php echo APP_NAME; ?></h1>
    </div>
    <p class="sub">Masuk dengan akun Anda untuk mencatat dan mengelola kehadiran.</p>
    <?php if ($error): ?><div class="flash error"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post">
      <label>Alamat Email <input type="email" name="email" placeholder="nama@perusahaan.test" required autofocus></label>
      <label>Password <input type="password" name="password" placeholder="••••••••" required></label>
      <button type="submit">Masuk ke Dashboard</button>
    </form>
    <div class="login-demo">
      <h2>Akun Demo untuk Penilaian</h2>
      <table>
        <tr><th>Role</th><th>Email</th><th>Password</th></tr>
        <tr><td>Admin</td><td>admin@absensi.test</td><td>admin123</td></tr>
        <tr><td>HRD</td><td>hrd@absensi.test</td><td>hrd12345</td></tr>
        <tr><td>Karyawan</td><td>karyawan@absensi.test</td><td>karyawan123</td></tr>
      </table>
    </div>
  </div>
</div>
</body>
</html>
