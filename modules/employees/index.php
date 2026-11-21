<?php
// modules/employees/index.php — READ Data Karyawan
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('employees.view');
$pdo = getPDO();
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE name LIKE ? OR nip LIKE ? ORDER BY name');
    $stmt->execute(['%' . $q . '%', '%' . $q . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM employees ORDER BY name');
}
$rows = $stmt->fetchAll();
$pageTitle = 'Data Karyawan';
require __DIR__ . '/../../includes/header.php';
?>

<div class="page-head">
  <div><h1>Data Karyawan</h1><p>Kelola data induk karyawan — tambah, ubah, dan hapus sesuai hak akses Anda.</p></div>
  <span class="spacer"></span>
  <?php if (hasPermission('employees.create')): ?><a class="btn" href="create.php">+ Tambah Karyawan</a><?php endif; ?>
</div>
<div class="toolbar">
  <form method="get" class="search"><input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Cari nama / NIP karyawan…"><button type="submit">Cari</button></form>
</div>
<div class="table-wrap">
<table>
<tr><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Departemen</th><th>Status</th><th>Aksi</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?php echo e($r['nip']); ?></td>
  <td><strong><?php echo e($r['name']); ?></strong><br><small class="muted"><?php echo e($r['email']); ?></small></td>
  <td><?php echo e($r['position']); ?></td>
  <td><?php echo e($r['department']); ?></td>
  <td><span class="badge <?php echo $r['status'] === 'Aktif' ? 'badge-green' : 'badge-grey'; ?>"><?php echo e($r['status']); ?></span></td>
  <td><span class="actions">
    <?php if (hasPermission('employees.edit')): ?><a class="btn btn-sm btn-edit" href="edit.php?id=<?php echo (int) $r['id']; ?>">Ubah</a><?php endif; ?>
    <?php if (hasPermission('employees.delete')): ?>
    <form method="post" action="delete.php" onsubmit="return confirm('Hapus karyawan ini?')">
      <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
      <button type="submit" class="btn btn-sm btn-red">Hapus</button>
    </form>
    <?php endif; ?>
  </span></td>
</tr>
<?php endforeach; ?>
</table>
<div class="table-foot">Menampilkan <?php echo count($rows); ?> karyawan</div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
