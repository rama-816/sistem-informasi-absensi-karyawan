<?php
// dashboard.php — ringkasan kehadiran hari ini, absensi terbaru, pengajuan menunggu
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';
requirePermission('dashboard.view');

$pdo = getPDO();
$today = date('Y-m-d');
$totalKaryawan = (int) $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Aktif'")->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendances WHERE date = ? AND status IN ('Hadir','Terlambat')");
$stmt->execute([$today]);
$hadirHariIni = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendances WHERE date = ? AND status = 'Terlambat'");
$stmt->execute([$today]);
$terlambat = (int) $stmt->fetchColumn();
$menunggu = (int) $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'Menunggu'")->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT a.*, e.name AS employee_name, e.position, e.department FROM attendances a ' .
    'JOIN employees e ON e.id = a.employee_id WHERE a.date = ? ORDER BY a.check_in'
);
$stmt->execute([$today]);
$absensiHariIni = $stmt->fetchAll();
$pengajuan = $pdo->query(
    'SELECT l.*, e.name AS employee_name FROM leave_requests l ' .
    'JOIN employees e ON e.id = l.employee_id WHERE l.status = \'Menunggu\' ORDER BY l.start_date'
)->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div>
    <h1>Selamat datang, <?php echo e($user['name']); ?> 👋</h1>
    <p>Ringkasan kehadiran hari ini, <?php echo e(date('l, d F Y')); ?>.</p>
  </div>
  <span class="spacer"></span>
  <?php if (hasPermission('attendances.checkin')): ?>
    <a class="btn" href="/modules/attendances/checkin.php">✓ Absen Masuk / Pulang</a>
  <?php endif; ?>
</div>
<div class="cards">
  <div class="card"><span class="stat-ico ico-blue">👥</span><div><strong><?php echo $totalKaryawan; ?></strong><span>Karyawan Aktif</span></div></div>
  <div class="card"><span class="stat-ico ico-green">✅</span><div><strong><?php echo $hadirHariIni; ?></strong><span>Hadir Hari Ini</span></div></div>
  <div class="card"><span class="stat-ico ico-amber">⏰</span><div><strong><?php echo $terlambat; ?></strong><span>Terlambat Hari Ini</span></div></div>
  <div class="card"><span class="stat-ico ico-violet">📩</span><div><strong><?php echo $menunggu; ?></strong><span>Pengajuan Menunggu</span></div></div>
</div>
<div class="grid-2">
  <div class="panel">
    <div class="panel-head"><h2>Kehadiran Terbaru — Hari Ini</h2><span class="spacer"></span><a href="/modules/attendances/index.php">Lihat semua →</a></div>
    <table>
      <tr><th>Karyawan</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr>
      <?php foreach ($absensiHariIni as $r): ?>
      <tr>
        <td><strong><?php echo e($r['employee_name']); ?></strong><br><small class="muted"><?php echo e($r['department']); ?></small></td>
        <td><?php echo e(substr((string) $r['check_in'], 0, 5)); ?></td>
        <td><?php echo $r['check_out'] ? e(substr((string) $r['check_out'], 0, 5)) : '—'; ?></td>
        <td><span class="badge <?php echo $r['status'] === 'Terlambat' ? 'badge-amber' : 'badge-green'; ?>"><?php echo e($r['status']); ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$absensiHariIni): ?><tr><td colspan="4" class="muted">Belum ada catatan absensi hari ini.</td></tr><?php endif; ?>
    </table>
  </div>
  <div class="panel">
    <div class="panel-head"><h2>Pengajuan Menunggu</h2></div>
    <?php foreach ($pengajuan as $l): ?>
      <p><strong><?php echo e($l['employee_name']); ?></strong> — <?php echo e($l['type']); ?><br>
      <small class="muted"><?php echo e($l['start_date']); ?> s.d. <?php echo e($l['end_date']); ?> · <?php echo e($l['reason']); ?></small></p>
    <?php endforeach; ?>
    <?php if (!$pengajuan): ?><p class="muted">Tidak ada pengajuan yang menunggu persetujuan.</p><?php endif; ?>
    <p><a href="/modules/leave_requests/index.php">Kelola Izin / Cuti →</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
