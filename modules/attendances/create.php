<?php
// modules/attendances/create.php — CREATE absensi manual (HRD/Admin)
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('attendances.create');
$pdo = getPDO();
$employees = $pdo->query('SELECT id, name FROM employees WHERE status = "Aktif" ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'INSERT INTO attendances (employee_id, date, check_in, check_out, status, notes) VALUES (?,?,?,?,?,?)'
    );
    $stmt->execute([
        (int) $_POST['employee_id'], $_POST['date'],
        $_POST['check_in'] ?: null, $_POST['check_out'] ?: null,
        $_POST['status'], trim($_POST['notes'] ?? ''),
    ]);
    setFlash('success', 'Absensi berhasil ditambahkan.');
    redirect('index.php');
}
$pageTitle = 'Input Absensi';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Input Absensi Manual</h1>
<form method="post">
  <label>Karyawan <select name="employee_id"><?php foreach ($employees as $e): ?><option value="<?php echo (int) $e['id']; ?>"><?php echo e($e['name']); ?></option><?php endforeach; ?></select></label>
  <label>Tanggal <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required></label>
  <label>Jam Masuk <input type="time" name="check_in"></label>
  <label>Jam Pulang <input type="time" name="check_out"></label>
  <label>Status <select name="status"><option>Hadir</option><option>Terlambat</option><option>Izin</option><option>Sakit</option><option>Alpa</option></select></label>
  <label>Catatan <input name="notes"></label>
  <button type="submit">Simpan</button>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
