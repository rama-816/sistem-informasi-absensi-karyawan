<?php
// modules/attendances/index.php — READ Data Absensi
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('attendances.view');
$pdo = getPDO();
$user = currentUser();
// Karyawan hanya melihat absensinya sendiri.
if (isRole('Karyawan') && $user['employee_id']) {
    $stmt = $pdo->prepare(
        'SELECT a.*, e.name FROM attendances a JOIN employees e ON e.id = a.employee_id ' .
        'WHERE a.employee_id = ? ORDER BY a.date DESC LIMIT 100'
    );
    $stmt->execute([$user['employee_id']]);
} else {
    $stmt = $pdo->query(
        'SELECT a.*, e.name FROM attendances a JOIN employees e ON e.id = a.employee_id ' .
        'ORDER BY a.date DESC, e.name LIMIT 100'
    );
}
$rows = $stmt->fetchAll();
$pageTitle = 'Data Absensi';
require __DIR__ . '/../../includes/header.php';
?>

<div class="page-head">
  <div><h1>Data Absensi</h1><p>Catatan check-in dan check-out karyawan beserta status kehadirannya.</p></div>
  <span class="spacer"></span>
  <?php if (hasPermission('attendances.checkin')): ?><a class="btn btn-light" href="checkin.php">✓ Absen Masuk / Pulang</a><?php endif; ?>
  <?php if (hasPermission('attendances.create')): ?><a class="btn" href="create.php">+ Input Absensi Manual</a><?php endif; ?>
</div>
<div class="table-wrap">
<table>
<tr><th>Tanggal</th><th>Karyawan</th><th>Masuk</th><th>Pulang</th><th>Status</th><th>Keterangan</th><th>Aksi</th></tr>
<?php foreach ($rows as $r): ?>
<?php $badge = ['Hadir' => 'badge-green', 'Terlambat' => 'badge-amber', 'Izin' => 'badge-blue', 'Sakit' => 'badge-violet', 'Cuti' => 'badge-teal', 'Alpa' => 'badge-red'][$r['status']] ?? 'badge-grey'; ?>
<tr>
  <td><?php echo e($r['date']); ?></td>
  <td><strong><?php echo e($r['name']); ?></strong></td>
  <td><?php echo $r['check_in'] ? e(substr((string) $r['check_in'], 0, 5)) : '—'; ?></td>
  <td><?php echo $r['check_out'] ? e(substr((string) $r['check_out'], 0, 5)) : '—'; ?></td>
  <td><span class="badge <?php echo $badge; ?>"><?php echo e($r['status']); ?></span></td>
  <td class="muted"><?php echo e($r['notes'] ?? ''); ?></td>
  <td><span class="actions">
    <?php if (hasPermission('attendances.edit')): ?><a class="btn btn-sm btn-edit" href="edit.php?id=<?php echo (int) $r['id']; ?>">Ubah</a><?php endif; ?>
    <?php if (hasPermission('attendances.delete')): ?>
    <form method="post" action="delete.php" onsubmit="return confirm('Hapus absensi ini?')">
      <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
      <button type="submit" class="btn btn-sm btn-red">Hapus</button>
    </form>
    <?php endif; ?>
  </span></td>
</tr>
<?php endforeach; ?>
</table>
<div class="table-foot">Menampilkan <?php echo count($rows); ?> catatan absensi terbaru</div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
