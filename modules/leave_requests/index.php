<?php
// modules/leave_requests/index.php — READ Pengajuan Izin/Cuti
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('leave_requests.view');
$pdo = getPDO();
$user = currentUser();
if (isRole('Karyawan') && $user['employee_id']) {
    $stmt = $pdo->prepare(
        'SELECT lr.*, e.name FROM leave_requests lr JOIN employees e ON e.id = lr.employee_id ' .
        'WHERE lr.employee_id = ? ORDER BY lr.created_at DESC'
    );
    $stmt->execute([$user['employee_id']]);
} else {
    $stmt = $pdo->query(
        'SELECT lr.*, e.name FROM leave_requests lr JOIN employees e ON e.id = lr.employee_id ' .
        'ORDER BY lr.created_at DESC'
    );
}
$rows = $stmt->fetchAll();
$pageTitle = 'Izin / Cuti';
require __DIR__ . '/../../includes/header.php';
?>

<div class="page-head">
  <div><h1>Pengajuan Izin / Cuti</h1><p>Pengajuan izin, sakit, dan cuti karyawan beserta status persetujuannya.</p></div>
  <span class="spacer"></span>
  <?php if (hasPermission('leave_requests.create')): ?><a class="btn" href="create.php">+ Ajukan Izin / Cuti</a><?php endif; ?>
</div>
<div class="table-wrap">
<table>
<tr><th>Karyawan</th><th>Jenis</th><th>Periode</th><th>Alasan</th><th>Status</th><th>Aksi</th></tr>
<?php foreach ($rows as $r): ?>
<?php $badgeStatus = ['Menunggu' => 'badge-amber', 'Disetujui' => 'badge-green', 'Ditolak' => 'badge-red'][$r['status']] ?? 'badge-grey'; ?>
<?php $badgeJenis = ['Cuti' => 'badge-teal', 'Izin' => 'badge-blue', 'Sakit' => 'badge-violet'][$r['type']] ?? 'badge-grey'; ?>
<tr>
  <td><strong><?php echo e($r['name']); ?></strong></td>
  <td><span class="badge <?php echo $badgeJenis; ?>"><?php echo e($r['type']); ?></span></td>
  <td><?php echo e($r['start_date']); ?> s.d. <?php echo e($r['end_date']); ?></td>
  <td class="muted"><?php echo e($r['reason']); ?></td>
  <td><span class="badge <?php echo $badgeStatus; ?>"><?php echo e($r['status']); ?></span></td>
  <td><span class="actions">
    <?php if (hasPermission('leave_requests.approve') && $r['status'] === 'Menunggu'): ?>
    <form method="post" action="approve.php">
      <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
      <input type="hidden" name="decision" value="Disetujui">
      <button type="submit" class="btn btn-sm btn-green">Setujui</button>
    </form>
    <form method="post" action="approve.php">
      <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
      <input type="hidden" name="decision" value="Ditolak">
      <button type="submit" class="btn btn-sm btn-red">Tolak</button>
    </form>
    <?php endif; ?>
    <?php if (hasPermission('leave_requests.edit') && $r['status'] === 'Menunggu'): ?><a class="btn btn-sm btn-edit" href="edit.php?id=<?php echo (int) $r['id']; ?>">Ubah</a><?php endif; ?>
    <?php if (hasPermission('leave_requests.delete')): ?>
    <form method="post" action="delete.php" onsubmit="return confirm('Hapus pengajuan ini?')">
      <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
      <button type="submit" class="btn btn-sm btn-red">Hapus</button>
    </form>
    <?php endif; ?>
  </span></td>
</tr>
<?php endforeach; ?>
</table>
<div class="table-foot">Menampilkan <?php echo count($rows); ?> pengajuan</div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
