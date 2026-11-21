<?php
// modules/rbac/index.php — Pengelolaan RBAC: matriks izin per role
// Hanya Admin (rbac.view / rbac.edit). Perubahan langsung berlaku karena
// hasPermission() selalu membaca tabel role_permissions.
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('rbac.view');
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePermission('rbac.edit');
    $roleId = (int) ($_POST['role_id'] ?? 0);
    $allowed = $_POST['permissions'] ?? [];
    $pdo->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$roleId]);
    $stmt = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?,?)');
    foreach ($allowed as $permId) {
        $stmt->execute([$roleId, (int) $permId]);
    }
    setFlash('success', 'Matriks izin berhasil disimpan dan langsung berlaku.');
    redirect('index.php?role_id=' . $roleId);
}

$roles = $pdo->query('SELECT * FROM roles ORDER BY id')->fetchAll();
$permissions = $pdo->query('SELECT * FROM permissions ORDER BY module, id')->fetchAll();
$selectedRole = (int) ($_GET['role_id'] ?? $roles[0]['id']);
$granted = $pdo->prepare('SELECT permission_id FROM role_permissions WHERE role_id = ?');
$granted->execute([$selectedRole]);
$grantedIds = array_column($granted->fetchAll(), 'permission_id');
$roleName = '';
foreach ($roles as $r) {
    if ((int) $r['id'] === $selectedRole) {
        $roleName = $r['name'];
    }
}
$pageTitle = 'Pengelolaan RBAC';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Pengelolaan RBAC</h1>
<p>Centang izin yang dimiliki role, lalu simpan. Menu dan aksi yang tidak dicentang akan hilang / ditolak (403).</p>
<form method="get">
  <label>Role <select name="role_id" onchange="this.form.submit()">
    <?php foreach ($roles as $r): ?>
      <option value="<?php echo (int) $r['id']; ?>" <?php echo (int) $r['id'] === $selectedRole ? 'selected' : ''; ?>><?php echo e($r['name']); ?></option>
    <?php endforeach; ?>
  </select></label>
</form>
<form method="post">
  <input type="hidden" name="role_id" value="<?php echo $selectedRole; ?>">
  <table>
  <tr><th>Modul</th><th>Izin</th><th>Deskripsi</th><th>Diberikan ke <?php echo e($roleName); ?></th></tr>
  <?php foreach ($permissions as $p): ?>
  <tr>
    <td><?php echo e($p['module']); ?></td>
    <td><code><?php echo e($p['name']); ?></code></td>
    <td><?php echo e($p['description']); ?></td>
    <td><input type="checkbox" name="permissions[]" value="<?php echo (int) $p['id']; ?>" <?php echo in_array((int) $p['id'], array_map('intval', $grantedIds)) ? 'checked' : ''; ?>></td>
  </tr>
  <?php endforeach; ?>
  </table>
  <?php if (hasPermission('rbac.edit')): ?><button type="submit">Simpan Matriks Izin</button><?php endif; ?>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
