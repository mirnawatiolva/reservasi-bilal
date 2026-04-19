<?php
require_once 'auth.php';
require_once '../database/koneksi.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $idUser = (int) ($_POST['id_user'] ?? 0);
        $idPaket = (int) ($_POST['id_paket'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $scheduleInput = trim($_POST['schedule'] ?? '');
        $schedule = $scheduleInput !== '' ? date('Y-m-d H:i:s', strtotime($scheduleInput)) : '';

        if ($idUser <= 0 || $idPaket <= 0 || $status === '' || $schedule === '') {
            $message = 'Data riwayat belum lengkap.';
            $messageType = 'danger';
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO reservasi (id_user, id_paket, status, schedule) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iiss', $idUser, $idPaket, $status, $schedule);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: kelola_riwayat.php?msg=tambah_berhasil');
            exit;
        }
    }

    if ($action === 'update') {
        $idReservasi = (int) ($_POST['id_reservasi'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if ($idReservasi <= 0 || $status === '') {
            $message = 'Data update riwayat tidak valid.';
            $messageType = 'danger';
        } else {
            $stmt = mysqli_prepare($conn, 'UPDATE reservasi SET status = ? WHERE id_reservasi = ?');
            mysqli_stmt_bind_param($stmt, 'si', $status, $idReservasi);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: kelola_riwayat.php?msg=ubah_berhasil');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    $idDelete = (int) $_GET['delete'];
    if ($idDelete > 0) {
        $stmt = mysqli_prepare($conn, 'DELETE FROM reservasi WHERE id_reservasi = ?');
        mysqli_stmt_bind_param($stmt, 'i', $idDelete);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: kelola_riwayat.php?msg=hapus_berhasil');
    exit;
}

$msgParam = $_GET['msg'] ?? '';
if ($msgParam === 'tambah_berhasil') {
    $message = 'Riwayat booking berhasil ditambahkan.';
}
if ($msgParam === 'ubah_berhasil') {
    $message = 'Status riwayat booking berhasil diperbarui.';
}
if ($msgParam === 'hapus_berhasil') {
    $message = 'Riwayat booking berhasil dihapus.';
}

$userList = [];
$userResult = mysqli_query($conn, 'SELECT id_user, username FROM `user` ORDER BY username ASC');
if ($userResult) {
    while ($row = mysqli_fetch_assoc($userResult)) {
        $userList[] = $row;
    }
    mysqli_free_result($userResult);
}

$paketList = [];
$paketResult = mysqli_query($conn, 'SELECT id_paket, nama_paket, harga FROM paket ORDER BY id_paket ASC');
if ($paketResult) {
    while ($row = mysqli_fetch_assoc($paketResult)) {
        $paketList[] = $row;
    }
    mysqli_free_result($paketResult);
}

$riwayatList = [];
$sql = "SELECT r.id_reservasi, r.id_user, r.id_paket, r.status, r.schedule, u.username, p.nama_paket, p.harga
        FROM reservasi r
        JOIN `user` u ON u.id_user = r.id_user
        JOIN paket p ON p.id_paket = r.id_paket
        WHERE r.status IN ('Selesai', 'Cancel')
        ORDER BY r.id_reservasi DESC";
$resultRiwayat = mysqli_query($conn, $sql);
if ($resultRiwayat) {
    while ($row = mysqli_fetch_assoc($resultRiwayat)) {
        $riwayatList[] = $row;
    }
    mysqli_free_result($resultRiwayat);
}

$statusOptions = ['Selesai', 'Cancel'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <h3><i class="bi bi-car-front-fill"></i><span>Admin Exco</span></h3>
        </div>
        <ul class="list-unstyled components">
            <li><a href="index.php"><i class="bi bi-speedometer2"></i><span class="menu-label"> Dashboard</span></a></li>
            <li><a href="kelola_paket.php"><i class="bi bi-box-seam"></i><span class="menu-label"> Paket</span></a></li>
            <li><a href="kelola_reservasi.php"><i class="bi bi-calendar-check"></i><span class="menu-label"> Reservasi</span></a></li>
            <li><a href="kelola_pelanggan.php"><i class="bi bi-people"></i><span class="menu-label"> Pelanggan</span></a></li>
            <li><a href="kelola_admin.php"><i class="bi bi-person-gear"></i><span class="menu-label"> Admin</span></a></li>
            <li><a href="kelola_riwayat.php" class="active"><i class="bi bi-clock-history"></i><span class="menu-label"> Riwayat Booking</span></a></li>
            <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i><span class="menu-label"> Logout</span></a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary"><i class="bi bi-list"></i></button>
                <div class="topbar-meta ms-auto">
                    <span class="topbar-page"><i class="bi bi-stars"></i> Kelola Riwayat Booking</span>
                    <div class="admin-chip">
                        <span class="admin-avatar"><i class="bi bi-person-badge-fill"></i></span>
                        <div>
                            <div class="admin-name">Admin Bilal</div>
                            <div class="admin-role">Super Admin</div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title"><i class="bi bi-clock-history"></i> Riwayat Booking</h1>
                <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle"></i> Tambah Riwayat
                </button> -->
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo h($messageType); ?>"><?php echo h($message); ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 justify-content-end">
                        <div class="col-md-5">
                            <input type="text" class="form-control" id="searchRiwayat" placeholder="Cari riwayat...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="riwayatTable">
                            <thead class="table-light">
                            <tr>
                                <th>ID Booking</th>
                                <th>Nama Pelanggan</th>
                                <th>Paket</th>
                                <th>Tanggal</th>
                                <th>Total Pembayaran</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($riwayatList) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted">Belum ada data riwayat selesai/cancel.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($riwayatList as $row): ?>
                                <tr>
                                    <td><?php echo (int) $row['id_reservasi']; ?></td>
                                    <td><?php echo h($row['username']); ?></td>
                                    <td><?php echo h($row['nama_paket']); ?></td>
                                    <td><?php echo h(date('d-m-Y H:i', strtotime($row['schedule']))); ?></td>
                                    <td>Rp<?php echo number_format((float) $row['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'Selesai'): ?>
                                            <span class="badge bg-success">Selesai</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancel</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Riwayat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Pelanggan</label>
                        <select class="form-select" name="id_user" required>
                            <option value="">Pilih pelanggan</option>
                            <?php foreach ($userList as $user): ?>
                                <option value="<?php echo (int) $user['id_user']; ?>"><?php echo h($user['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Paket</label>
                        <select class="form-select" name="id_paket" required>
                            <option value="">Pilih paket</option>
                            <?php foreach ($paketList as $paket): ?>
                                <option value="<?php echo (int) $paket['id_paket']; ?>"><?php echo h($paket['nama_paket']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal dan Jam</label>
                        <input type="datetime-local" class="form-control" name="schedule" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <?php foreach ($statusOptions as $status): ?>
                                <option value="<?php echo h($status); ?>"><?php echo h($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
setupTableSearch('searchRiwayat', 'riwayatTable');
</script>
</body>
</html>
