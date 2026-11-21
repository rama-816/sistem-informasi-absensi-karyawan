<?php
// modules/employees/create.php — CREATE Data Karyawan
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';
requirePermission('employees.create');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = getPDO()->prepare(
        'INSERT INTO employees (nip, name, email, phone, position, department, join_date, status) VALUES (?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([
        trim($_POST['nip']), trim($_POST['name']), trim($_POST['email']),
        trim($_POST['phone'] ?? ''), trim($_POST['position']), trim($_POST['department']),
        $_POST['join_date'], $_POST['status'],
    ]);
    setFlash('success', 'Karyawan berhasil ditambahkan.');
    redirect('index.php');
}
$pageTitle = 'Tambah Karyawan';
require __DIR__ . '/../../includes/header.php';
?>
<h1>Tambah Karyawan</h1>
<form method="post">
  <label>NIP <input name="nip" required></label>
  <label>Nama <input name="name" required></label>
  <label>Email <input type="email" name="email" required></label>
  <label>Telepon <input name="phone"></label>
  <label>Jabatan <input name="position" required></label>
  <label>Departemen <input name="department" required></label>
  <label>Tanggal Masuk <input type="date" name="join_date" required></label>
  <label>Status <select name="status"><option>Aktif</option><option>Nonaktif</option><option>Cuti</option></select></label>
  <button type="submit">Simpan</button>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
