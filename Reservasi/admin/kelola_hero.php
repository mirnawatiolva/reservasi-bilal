<?php
require_once 'auth.php';
require_once '../database/koneksi.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$message = '';
$messageType = 'success';
global $conn;

// Ensure table exists
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'hero_section'");
if ($checkTable && mysqli_num_rows($checkTable) === 0) {
    $createTableQuery = "
        CREATE TABLE `hero_section` (
            `id_hero` INT(11) NOT NULL AUTO_INCREMENT,
            `judul` VARCHAR(255) NOT NULL,
            `deskripsi` TEXT NOT NULL,
            PRIMARY KEY (`id_hero`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    mysqli_query($conn, $createTableQuery);
}
if ($checkTable) {
    mysqli_free_result($checkTable);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = $_POST['deskripsi'] ?? '';

        if ($judul === '' || $deskripsi === '') {
            $message = 'Semua field wajib diisi.';
            $messageType = 'danger';
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO `hero_section` (judul, deskripsi) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'ss', $judul, $deskripsi);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: kelola_hero.php?msg=tambah_berhasil');
            exit;
        }
    }

    if ($action === 'update') {
        $idHero = (int) ($_POST['id_hero'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = $_POST['deskripsi'] ?? '';

        if ($idHero <= 0 || $judul === '' || $deskripsi === '') {
            $message = 'Data edit tidak valid.';
            $messageType = 'danger';
        } else {
            $stmt = mysqli_prepare($conn, 'UPDATE `hero_section` SET judul = ?, deskripsi = ? WHERE id_hero = ?');
            mysqli_stmt_bind_param($stmt, 'ssi', $judul, $deskripsi, $idHero);

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: kelola_hero.php?msg=ubah_berhasil');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    $idDelete = (int) $_GET['delete'];
    if ($idDelete > 0) {
        $stmt = mysqli_prepare($conn, 'DELETE FROM `hero_section` WHERE id_hero = ?');
        mysqli_stmt_bind_param($stmt, 'i', $idDelete);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: kelola_hero.php?msg=hapus_berhasil');
    exit;
}

$msgParam = $_GET['msg'] ?? '';
if ($msgParam === 'tambah_berhasil') {
    $message = 'Teks beranda berhasil ditambahkan.';
}
if ($msgParam === 'ubah_berhasil') {
    $message = 'Teks beranda berhasil diperbarui.';
}
if ($msgParam === 'hapus_berhasil') {
    $message = 'Teks beranda berhasil dihapus.';
}

$heroList = [];
$result = mysqli_query($conn, 'SELECT id_hero, judul, deskripsi FROM `hero_section` ORDER BY id_hero DESC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $heroList[] = $row;
    }
    mysqli_free_result($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Beranda (Hero)</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        #sidebarCollapse {
            padding: 0.5rem 0.75rem;
        }

        .content-preview ul {
            list-style: none;
            padding-left: 0;
        }

        .content-preview ul li {
            position: relative;
            padding-left: 25px;
            margin-bottom: 5px;
        }

        .content-preview ul li::before {
            content: "\f058"; 
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 2px;
            color: #dc3545;
            font-size: 0.9rem;
        }
    </style>
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
                    <span class="topbar-page"><i class="bi bi-stars"></i> Kelola Teks Beranda</span>
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
                <h1 class="page-title"><i class="bi bi-image"></i> Kelola Teks Beranda (Hero)</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle"></i> Tambah Slide Beranda
                </button>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo h($messageType); ?> alert-dismissible fade show">
                    <?php echo h($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchHero" placeholder="Cari ...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="heroTable">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($heroList) === 0): ?>
                                <tr><td colspan="4" class="text-center text-muted">Belum ada data teks beranda.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($heroList as $hero): ?>
                                <tr>
                                    <td><?php echo (int) $hero['id_hero']; ?></td>
                                    <td style="max-width: 250px;"><?php echo h($hero['judul']); ?></td>
                                    <td><div class="content-preview"><?php echo ($hero['deskripsi']); ?></div></td>
                                    <td style="white-space: nowrap;">
                                        <button
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?php echo (int) $hero['id_hero']; ?>"
                                            data-judul="<?php echo h($hero['judul']); ?>"
                                            data-deskripsi="<?php echo h($hero['deskripsi']); ?>"
                                            onclick="setEditHero(this)">
                                            Edit
                                        </button>
                                        <a href="kelola_hero.php?delete=<?php echo (int) $hero['id_hero']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus teks beranda ini?')">
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

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Slide Beranda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" class="form-control" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control summernote" name="deskripsi"></textarea>
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

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Slide Beranda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_hero" id="edit_id_hero">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" class="form-control" name="judul" id="edit_judul" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control summernote" name="deskripsi" id="edit_deskripsi"></textarea>
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
setupTableSearch('searchHero', 'heroTable');

function setEditHero(button) {
    document.getElementById('edit_id_hero').value = button.getAttribute('data-id');
    document.getElementById('edit_judul').value = button.getAttribute('data-judul');
    
    const deskripsi = button.getAttribute('data-deskripsi');
    $('#edit_deskripsi').summernote('code', deskripsi);
}
</script>
<!-- jquery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
<script>$(".summernote").summernote({height:200});</script>
</body>
</html>
