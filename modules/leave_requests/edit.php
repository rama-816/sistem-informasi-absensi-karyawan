<?php
// modules/leave_requests/edit.php — UPDATE pengajuan (hanya yang masih Menunggu)
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('leave_requests.edit');
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM leave_requests WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row || $row['status'] !== 'Menunggu') {
    setFlash('error', 'Pengajuan tidak ditemukan atau sudah diproses.');
    redirect('index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE leave_requests SET type=?, start_date=?, end_date=?, reason=? WHERE id=?');
    $stmt->execute([$_POST['type'], $_POST['start_date'], $_POST['end_date'], trim($_POST['reason']), $id]);
    setFlash('success', 'Pengajuan berhasil diubah.');
    redirect('index.php');
}
$pageTitle = 'Ubah Pengajuan';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Ubah Pengajuan</h1>
<form method="post">
  <label>Jenis <select name="type">
    <?php foreach (['Izin', 'Cuti', 'Sakit'] as $t): ?>
      <option <?php echo $row['type'] === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
    <?php endforeach; ?>
  </select></label>
  <label>Tanggal Mulai <input type="date" name="start_date" value="<?php echo e($row['start_date']); ?>" required></label>
  <label>Tanggal Selesai <input type="date" name="end_date" value="<?php echo e($row['end_date']); ?>" required></label>
  <label>Alasan <textarea name="reason" required><?php echo e($row['reason']); ?></textarea></label>
  <button type="submit">Simpan Perubahan</button>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
