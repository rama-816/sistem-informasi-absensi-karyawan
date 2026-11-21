<?php
// modules/employees/edit.php — UPDATE Data Karyawan
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('employees.edit');
$pdo = getPDO();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM employees WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    setFlash('error', 'Karyawan tidak ditemukan.');
    redirect('index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'UPDATE employees SET nip=?, name=?, email=?, phone=?, position=?, department=?, join_date=?, status=? WHERE id=?'
    );
    $stmt->execute([
        trim($_POST['nip']), trim($_POST['name']), trim($_POST['email']),
        trim($_POST['phone'] ?? ''), trim($_POST['position']), trim($_POST['department']),
        $_POST['join_date'], $_POST['status'], $id,
    ]);
    setFlash('success', 'Karyawan berhasil diubah.');
    redirect('index.php');
}
$pageTitle = 'Ubah Karyawan';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Ubah Karyawan</h1>
<form method="post">
  <label>NIP <input name="nip" value="<?php echo e($row['nip']); ?>" required></label>
  <label>Nama <input name="name" value="<?php echo e($row['name']); ?>" required></label>
  <label>Email <input type="email" name="email" value="<?php echo e($row['email']); ?>" required></label>
  <label>Telepon <input name="phone" value="<?php echo e($row['phone']); ?>"></label>
  <label>Jabatan <input name="position" value="<?php echo e($row['position']); ?>" required></label>
  <label>Departemen <input name="department" value="<?php echo e($row['department']); ?>" required></label>
  <label>Tanggal Masuk <input type="date" name="join_date" value="<?php echo e($row['join_date']); ?>" required></label>
  <label>Status <select name="status">
    <?php foreach (['Aktif', 'Nonaktif', 'Cuti'] as $s): ?>
      <option <?php echo $row['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
    <?php endforeach; ?>
  </select></label>
  <button type="submit">Simpan Perubahan</button>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
