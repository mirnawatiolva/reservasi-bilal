<?php
require_once 'auth.php';
require_once '../database/koneksi.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function normalizeWhatsappNumber($value)
{
    $digits = preg_replace('/\D+/', '', (string) $value);
    if ($digits === '') {
        return '';
    }

    if (str_starts_with($digits, '0')) {
        return '62' . substr($digits, 1);
    }

    if (str_starts_with($digits, '62')) {
        return $digits;
    }

    return '62' . $digits;
}

$message = '';
$messageType = 'success';
global $conn;

$checkBuktiColumn = mysqli_query($conn, "SHOW COLUMNS FROM reservasi LIKE 'bukti_pembayaran'");
if ($checkBuktiColumn && mysqli_num_rows($checkBuktiColumn) === 0) {
    mysqli_query($conn, 'ALTER TABLE reservasi ADD COLUMN bukti_pembayaran VARCHAR(255) NULL AFTER status_dp');
}
if ($checkBuktiColumn) {
    mysqli_free_result($checkBuktiColumn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $idUser = (int) ($_POST['id_user'] ?? 0);
        $idPaket = (int) ($_POST['id_paket'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $statusDp = (int) ($_POST['status_dp'] ?? 100);
        $bookingDate = trim($_POST['booking_date'] ?? '');
        $bookingTime = trim($_POST['booking_time'] ?? '');
        $schedule = ($bookingDate !== '' && $bookingTime !== '') ? date('Y-m-d H:i:s', strtotime($bookingDate . ' ' . $bookingTime)) : '';
        $allowedStatus = ['Menunggu Verifikasi', 'Diverifikasi', 'Selesai', 'Cancel', 'Refund Selesai'];
        
        if ($status === 'Selesai') {
            $statusDp = 100;
        }
        
        if ($idUser <= 0 || $idPaket <= 0 || $status === '' || $schedule === '' || !in_array($statusDp, [50, 100], true) || !in_array($status, $allowedStatus, true)) {
            $message = 'Data reservasi belum lengkap.';
            $messageType = 'danger';
        } else {
            $isCollision = false;
            if ($status !== 'Cancel' && $status !== 'Refund Selesai') {
                $collisionQuery = "SELECT COUNT(*) as count FROM reservasi WHERE status NOT IN ('Cancel', 'Refund Selesai') AND DATE(schedule) = ? AND TIME(schedule) = ?";
                $stmt_check = mysqli_prepare($conn, $collisionQuery);
                if ($stmt_check) {
                    $checkDate = date('Y-m-d', strtotime($bookingDate));
                    $checkTime = date('H:i:s', strtotime($bookingTime));
                    mysqli_stmt_bind_param($stmt_check, 'ss', $checkDate, $checkTime);
                    mysqli_stmt_execute($stmt_check);
                    $res_check = mysqli_stmt_get_result($stmt_check);
                    $row_check = mysqli_fetch_assoc($res_check);
                    mysqli_stmt_close($stmt_check);
                    if ($row_check && (int)$row_check['count'] > 0) {
                        $isCollision = true;
                    }
                }
            }

            if ($isCollision) {
                $message = 'Gagal menyimpan. Jadwal booking bertabrakan dengan pemesanan lain (selisih kurang dari 2 jam).';
                $messageType = 'danger';
            } else {
                $stmt = mysqli_prepare($conn, 'INSERT INTO reservasi (id_user, id_paket, status, status_dp, schedule) VALUES (?, ?, ?, ?, ?)');
                mysqli_stmt_bind_param($stmt, 'iisis', $idUser, $idPaket, $status, $statusDp, $schedule);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if (in_array($status, ['Selesai', 'Refund Selesai'], true)) {
                    header('Location: kelola_riwayat.php?msg=tambah_berhasil');
                    exit;
                }

                header('Location: kelola_reservasi.php?msg=tambah_berhasil');
                exit;
            }
        }
    }

    if ($action === 'update') {
        $idReservasi = (int) ($_POST['id_reservasi'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $allowedStatus = ['Menunggu Verifikasi', 'Diverifikasi', 'Selesai', 'Cancel', 'Refund Selesai'];

        if ($idReservasi <= 0 || $status === '' || !in_array($status, $allowedStatus, true)) {
            $message = 'Data edit tidak valid.';
            $messageType = 'danger';
        } else {
            if ($status === 'Selesai') {
                $stmt = mysqli_prepare($conn, 'UPDATE reservasi SET status = ?, status_dp = 100 WHERE id_reservasi = ?');
                mysqli_stmt_bind_param($stmt, 'si', $status, $idReservasi);
            } else {
                $stmt = mysqli_prepare($conn, 'UPDATE reservasi SET status = ? WHERE id_reservasi = ?');
                mysqli_stmt_bind_param($stmt, 'si', $status, $idReservasi);
            }
            
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if (in_array($status, ['Selesai', 'Refund Selesai'], true)) {
                header('Location: kelola_riwayat.php?msg=ubah_berhasil');
                exit;
            }

            header('Location: kelola_reservasi.php?msg=ubah_berhasil');
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
    header('Location: kelola_reservasi.php?msg=hapus_berhasil');
    exit;
}

$msgParam = $_GET['msg'] ?? '';
if ($msgParam === 'tambah_berhasil') {
    $message = 'Reservasi berhasil ditambahkan.';
}
if ($msgParam === 'ubah_berhasil') {
    $message = 'Reservasi berhasil diperbarui.';
}
if ($msgParam === 'hapus_berhasil') {
    $message = 'Reservasi berhasil dihapus.';
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

$reservasiList = [];
$sql = 'SELECT r.id_reservasi, r.id_user, r.id_paket, r.status, r.status_dp, r.bukti_pembayaran, r.refund_amount, r.schedule, u.username, u.No_Telepon, p.nama_paket, p.harga
        FROM reservasi r
        JOIN `user` u ON u.id_user = r.id_user
        JOIN paket p ON p.id_paket = r.id_paket
    WHERE r.status NOT IN ("Selesai", "Refund Selesai")
        ORDER BY r.id_reservasi DESC';
$reservasiResult = mysqli_query($conn, $sql);
if ($reservasiResult) {
    while ($row = mysqli_fetch_assoc($reservasiResult)) {
        $reservasiList[] = $row;
    }
    mysqli_free_result($reservasiResult);
}

$statusOptions = ['Menunggu Verifikasi', 'Diverifikasi', 'Selesai', 'Cancel', 'Refund Selesai'];
$statusDpOptions = [50, 100];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Reservasi</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary"><i class="bi bi-list"></i></button>
                <div class="topbar-meta ms-auto">
                    <span class="topbar-page"><i class="bi bi-stars"></i> Kelola Reservasi</span>
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
            <!-- <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title"><i class="bi bi-calendar-check"></i> Reservasi</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle"></i> Tambah Reservasi
                </button>
            </div> -->

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo h($messageType); ?>"><?php echo h($message); ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchReservasi" placeholder="Cari reservasi...">
                        </div>
                        <div class="col-md-9 text-md-end">
                            <div class="btn-group" role="group" aria-label="Filter Status">
                                <input type="radio" class="btn-check" name="filterStatus" id="filterAll" autocomplete="off" value="" checked>
                                <label class="btn btn-outline-secondary" for="filterAll">Semua</label>

                                <input type="radio" class="btn-check" name="filterStatus" id="filterMenunggu" autocomplete="off" value="Menunggu Verifikasi">
                                <label class="btn btn-outline-warning" for="filterMenunggu">Menunggu</label>

                                <input type="radio" class="btn-check" name="filterStatus" id="filterDiverifikasi" autocomplete="off" value="Diverifikasi">
                                <label class="btn btn-outline-info" for="filterDiverifikasi">Diverifikasi</label>
                                
                                <input type="radio" class="btn-check" name="filterStatus" id="filterCancel" autocomplete="off" value="Cancel">
                                <label class="btn btn-outline-danger" for="filterCancel">Cancel</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="reservasiTable">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Paket</th>
                                <th>Total Pembayaran</th>
                                <th>Status DP</th>
                                <th>Bukti Pembayaran</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Refund</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($reservasiList) === 0): ?>
                                <tr><td colspan="9" class="text-center text-muted">Belum ada data reservasi.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($reservasiList as $row): ?>
                                <tr>
                                    <td><?php echo (int) $row['id_reservasi']; ?></td>
                                    <td><?php echo h($row['username']); ?></td>
                                    <td><?php echo h($row['nama_paket']); ?></td>
                                    <td>
                                        <?php
                                        $totalPembayaran = (float) $row['harga'];
                                        if ((int) $row['status_dp'] === 50) {
                                            $totalPembayaran = $totalPembayaran / 2;
                                        }
                                        ?>
                                        Rp<?php echo number_format($totalPembayaran, 0, ',', '.'); ?>
                                    </td>
                                    <td><?php echo (int) $row['status_dp']; ?>%</td>
                                    <td>
                                        <?php if (!empty($row['bukti_pembayaran'])): ?>
                                            <a href="../<?php echo h($row['bukti_pembayaran']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-receipt"></i> Lihat Bukti
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo h(date('d-m-Y H:i', strtotime($row['schedule']))); ?></td>
                                    <td>
                                        <?php
                                        $badge = 'secondary';
                                        if ($row['status'] === 'Menunggu Verifikasi') {
                                            $badge = 'warning';
                                        } elseif ($row['status'] === 'Sedang Diproses') {
                                            $badge = 'primary';
                                        } elseif ($row['status'] === 'Diverifikasi') {
                                            $badge = 'info';
                                        } elseif ($row['status'] === 'Selesai') {
                                            $badge = 'success';
                                        } elseif ($row['status'] === 'Cancel') {
                                            $badge = 'danger';
                                        } elseif ($row['status'] === 'Refund Selesai') {
                                            $badge = 'dark';
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo h($badge); ?>"><?php echo h($row['status']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'Cancel' && isset($row['refund_amount'])): ?>
                                            <span class="text-danger fw-bold">Rp<?php echo number_format((float)$row['refund_amount'], 0, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $waNumber = normalizeWhatsappNumber($row['No_Telepon'] ?? '');
                                        $waMessage = 'Halo ' . (string) $row['username'] . ', reservasi Anda dengan ID #' . (int) $row['id_reservasi'] . ' untuk paket ' . (string) $row['nama_paket'] . ' sedang kami proses.';
                                        $waLink = $waNumber !== '' ? 'https://wa.me/' . $waNumber . '?text=' . urlencode($waMessage) : '';
                                        ?>
                                        <?php if ($waLink !== ''): ?>
                                            <a href="<?php echo h($waLink); ?>" target="_blank" class="btn btn-sm btn-success" title="Chat WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Nomor WhatsApp tidak tersedia">
                                                <i class="bi bi-whatsapp"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?php echo (int) $row['id_reservasi']; ?>"
                                            data-id-user="<?php echo (int) $row['id_user']; ?>"
                                            data-id-paket="<?php echo (int) $row['id_paket']; ?>"
                                            data-status="<?php echo h($row['status']); ?>"
                                            data-status-dp="<?php echo (int) $row['status_dp']; ?>"
                                            data-schedule="<?php echo h(date('Y-m-d\TH:i', strtotime($row['schedule']))); ?>"
                                            data-username="<?php echo h($row['username']); ?>"
                                            data-nama-paket="<?php echo h($row['nama_paket']); ?>"
                                            data-wa="<?php echo h($waNumber); ?>"
                                            data-schedule-formatted="<?php echo h(date('d-m-Y H:i', strtotime($row['schedule']))); ?>"
                                            onclick="setEditReservasi(this)">
                                            Edit
                                        </button>
                                        <a href="kelola_reservasi.php?delete=<?php echo (int) $row['id_reservasi']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus reservasi ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
                    <h5 class="modal-title">Tambah Reservasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="alert alert-info small">
                        Jika status <strong>Selesai</strong>, DP otomatis menjadi <strong>100%</strong> dan data akan masuk ke Riwayat Booking.
                    </div>
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
                        <label class="form-label">Tanggal Booking</label>
                        <input type="date" class="form-control" name="booking_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sesi Waktu</label>
                        <select class="form-select" name="booking_time" required>
                            <option value="08:00:00">Sesi Pagi (08:00 - 12:30)</option>
                            <option value="12:30:00">Sesi Siang (12:30 - 17:00)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status DP</label>
                        <select class="form-select" name="status_dp" id="create_status_dp" required>
                            <?php foreach ($statusDpOptions as $dp): ?>
                                <option value="<?php echo (int) $dp; ?>"><?php echo (int) $dp; ?>%</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="create_status" required onchange="syncDpWithStatus('create_status', 'create_status_dp')">
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

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Reservasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_reservasi" id="edit_id_reservasi">
                    <div class="alert alert-info small mb-3">
                        Jika status <strong>Selesai</strong>, DP otomatis menjadi <strong>100%</strong> dan data akan masuk ke Riwayat Booking.
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted mb-1">Detail Reservasi</label>
                        <div class="p-3 border rounded bg-light small">
                            <div class="row mb-1">
                                <div class="col-4 fw-bold">Pelanggan</div>
                                <div class="col-8" id="edit_info_username"></div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 fw-bold">No WA</div>
                                <div class="col-8" id="edit_info_wa"></div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-4 fw-bold">Paket</div>
                                <div class="col-8" id="edit_info_paket"></div>
                            </div>
                            <div class="row">
                                <div class="col-4 fw-bold">Jadwal</div>
                                <div class="col-8" id="edit_info_schedule"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <?php foreach ($statusOptions as $status): ?>
                                <option value="<?php echo h($status); ?>"><?php echo h($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
function syncDpWithStatus(statusId, dpId) {
    const statusSelect = document.getElementById(statusId);
    const dpSelect = document.getElementById(dpId);
    if (!statusSelect || !dpSelect) {
        return;
    }

    if (statusSelect.value === 'Selesai') {
        dpSelect.value = '100';
        dpSelect.setAttribute('readonly', 'readonly');
        dpSelect.setAttribute('disabled', 'disabled');
        return;
    }

    dpSelect.removeAttribute('readonly');
    dpSelect.removeAttribute('disabled');
}

function setEditReservasi(button) {
    document.getElementById('edit_id_reservasi').value = button.getAttribute('data-id');
    document.getElementById('edit_status').value = button.getAttribute('data-status');
    
    document.getElementById('edit_info_username').textContent = button.getAttribute('data-username') || '-';
    document.getElementById('edit_info_wa').textContent = button.getAttribute('data-wa') || '-';
    document.getElementById('edit_info_paket').textContent = button.getAttribute('data-nama-paket') || '-';
    document.getElementById('edit_info_schedule').textContent = button.getAttribute('data-schedule-formatted') || '-';
}

document.addEventListener('DOMContentLoaded', function() {
    syncDpWithStatus('create_status', 'create_status_dp');

    // Filter and Search Logic
    const searchInput = document.getElementById('searchReservasi');
    const filterRadios = document.querySelectorAll('input[name="filterStatus"]');
    const tableBody = document.querySelector('#reservasiTable tbody');

    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        let selectedStatus = '';
        filterRadios.forEach(r => {
            if (r.checked) selectedStatus = r.value;
        });

        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            // Abaikan row jika itu pesan "Belum ada data"
            if (row.cells.length === 1) return;

            const textContent = row.textContent.toLowerCase();
            const statusBadge = row.querySelector('.badge');
            const statusText = statusBadge ? statusBadge.textContent.trim() : '';

            const matchesSearch = textContent.includes(searchText);
            const matchesStatus = selectedStatus === '' || statusText === selectedStatus;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if(searchInput) {
        searchInput.addEventListener('input', filterTable);
    }
    filterRadios.forEach(r => r.addEventListener('change', filterTable));
});
</script>
</body>
</html>
