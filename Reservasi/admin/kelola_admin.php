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
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($username === '' || $password === '' || $email === '') {
            $message = 'Semua field admin wajib diisi.';
            $messageType = 'danger';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, 'INSERT INTO admin (username, password, email) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sss', $username, $passwordHash, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: kelola_admin.php?msg=tambah_berhasil');
            exit;
        }
    }

    if ($action === 'update') {
        $idAdmin = (int) ($_POST['id_admin'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $newPassword = trim($_POST['password'] ?? '');

        if ($idAdmin <= 0 || $username === '' || $email === '') {
            $message = 'Data edit admin tidak valid.';
            $messageType = 'danger';
        } else {
            if ($newPassword !== '') {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, 'UPDATE admin SET username = ?, password = ?, email = ? WHERE id_admin = ?');
                mysqli_stmt_bind_param($stmt, 'sssi', $username, $passwordHash, $email, $idAdmin);
            } else {
                $stmt = mysqli_prepare($conn, 'UPDATE admin SET username = ?, email = ? WHERE id_admin = ?');
                mysqli_stmt_bind_param($stmt, 'ssi', $username, $email, $idAdmin);
            }

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: kelola_admin.php?msg=ubah_berhasil');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    $idDelete = (int) $_GET['delete'];
    if ($idDelete > 0) {
        $stmt = mysqli_prepare($conn, 'DELETE FROM admin WHERE id_admin = ?');
        mysqli_stmt_bind_param($stmt, 'i', $idDelete);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: kelola_admin.php?msg=hapus_berhasil');
    exit;
}

$msgParam = $_GET['msg'] ?? '';
if ($msgParam === 'tambah_berhasil') {
    $message = 'Admin berhasil ditambahkan.';
}
if ($msgParam === 'ubah_berhasil') {
    $message = 'Admin berhasil diperbarui.';
}
if ($msgParam === 'hapus_berhasil') {
    $message = 'Admin berhasil dihapus.';
}

$adminList = [];
$result = mysqli_query($conn, 'SELECT id_admin, username, email FROM admin ORDER BY id_admin DESC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $adminList[] = $row;
    }
    mysqli_free_result($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin</title>
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
            <li><a href="kelola_admin.php" class="active"><i class="bi bi-person-gear"></i><span class="menu-label"> Admin</span></a></li>
            <li><a href="kelola_riwayat.php"><i class="bi bi-clock-history"></i><span class="menu-label"> Riwayat Booking</span></a></li>
            <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i><span class="menu-label"> Logout</span></a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary"><i class="bi bi-list"></i></button>
                <div class="topbar-meta ms-auto">
                    <span class="topbar-page"><i class="bi bi-stars"></i> Kelola Admin</span>
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
                <h1 class="page-title"><i class="bi bi-person-gear"></i> Kelola Admin</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle"></i> Tambah Admin
                </button>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo h($messageType); ?>"><?php echo h($message); ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchAdmin" placeholder="Cari admin...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="adminTable">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($adminList) === 0): ?>
                                <tr><td colspan="4" class="text-center text-muted">Belum ada data admin.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($adminList as $admin): ?>
                                <tr>
                                    <td><?php echo (int) $admin['id_admin']; ?></td>
                                    <td><?php echo h($admin['username']); ?></td>
                                    <td><?php echo h($admin['email']); ?></td>
                                    <td>
                                        <button
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?php echo (int) $admin['id_admin']; ?>"
                                            data-username="<?php echo h($admin['username']); ?>"
                                            data-email="<?php echo h($admin['email']); ?>"
                                            onclick="setEditAdmin(this)">
                                            Edit
                                        </button>
                                        <a href="kelola_admin.php?delete=<?php echo (int) $admin['id_admin']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus admin ini?')">
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
                    <h5 class="modal-title">Tambah Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
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
                    <h5 class="modal-title">Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_admin" id="edit_id_admin">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru (opsional)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
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
setupTableSearch('searchAdmin', 'adminTable');

function setEditAdmin(button) {
    document.getElementById('edit_id_admin').value = button.getAttribute('data-id');
    document.getElementById('edit_username').value = button.getAttribute('data-username');
    document.getElementById('edit_email').value = button.getAttribute('data-email');
}
</script>
</body>
</html>
