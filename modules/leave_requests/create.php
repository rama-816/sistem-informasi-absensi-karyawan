<?php
// modules/leave_requests/create.php — CREATE pengajuan Izin/Cuti
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('leave_requests.create');
$pdo = getPDO();
$user = currentUser();
// Karyawan mengajukan untuk dirinya sendiri; HRD/Admin memilih karyawan.
$employees = $pdo->query('SELECT id, name FROM employees WHERE status = "Aktif" ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = isRole('Karyawan') ? (int) $user['employee_id'] : (int) $_POST['employee_id'];
    $stmt = $pdo->prepare(
        'INSERT INTO leave_requests (employee_id, type, start_date, end_date, reason, status) VALUES (?,?,?,?,?,"Menunggu")'
    );
    $stmt->execute([$employeeId, $_POST['type'], $_POST['start_date'], $_POST['end_date'], trim($_POST['reason'])]);
    setFlash('success', 'Pengajuan berhasil dikirim, menunggu persetujuan.');
    redirect('index.php');
}
$pageTitle = 'Ajukan Izin / Cuti';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Ajukan Izin / Cuti</h1>
<form method="post">
  <?php if (!isRole('Karyawan')): ?>
  <label>Karyawan <select name="employee_id"><?php foreach ($employees as $e): ?><option value="<?php echo (int) $e['id']; ?>"><?php echo e($e['name']); ?></option><?php endforeach; ?></select></label>
  <?php endif; ?>
  <label>Jenis <select name="type"><option>Izin</option><option>Cuti</option><option>Sakit</option></select></label>
  <label>Tanggal Mulai <input type="date" name="start_date" required></label>
  <label>Tanggal Selesai <input type="date" name="end_date" required></label>
  <label>Alasan <textarea name="reason" required></textarea></label>
  <button type="submit">Kirim Pengajuan</button>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
