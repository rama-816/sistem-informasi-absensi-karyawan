<?php
// modules/attendances/edit.php — UPDATE absensi
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('attendances.edit');
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM attendances WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    setFlash('error', 'Absensi tidak ditemukan.');
    redirect('index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE attendances SET date=?, check_in=?, check_out=?, status=?, notes=? WHERE id=?');
    $stmt->execute([
        $_POST['date'], $_POST['check_in'] ?: null, $_POST['check_out'] ?: null,
        $_POST['status'], trim($_POST['notes'] ?? ''), $id,
    ]);
    setFlash('success', 'Absensi berhasil diubah.');
    redirect('index.php');
}
$pageTitle = 'Ubah Absensi';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Ubah Absensi</h1>
<form method="post">
  <label>Tanggal <input type="date" name="date" value="<?php echo e($row['date']); ?>" required></label>
  <label>Jam Masuk <input type="time" name="check_in" value="<?php echo e($row['check_in'] ?? ''); ?>"></label>
  <label>Jam Pulang <input type="time" name="check_out" value="<?php echo e($row['check_out'] ?? ''); ?>"></label>
  <label>Status <select name="status">
    <?php foreach (['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'] as $s): ?>
      <option <?php echo $row['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
    <?php endforeach; ?>
  </select></label>
  <label>Catatan <input name="notes" value="<?php echo e($row['notes']); ?>"></label>
  <button type="submit">Simpan Perubahan</button>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
