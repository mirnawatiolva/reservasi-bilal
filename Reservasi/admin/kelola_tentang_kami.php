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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = $_POST['deskripsi'] ?? '';

        $foto = '';

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $uploadDir = __DIR__ . '/../asset/tentang-kami/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $foto = time() . '_' . uniqid() . '.' . $ext;

            move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $foto);
        }

        if ($judul === '' || $foto === '' || $deskripsi === '') {
            $message = 'Semua field tentang kami wajib diisi.';
            $messageType = 'danger';
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO `tentang_kami` (judul, foto, deskripsi) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sss', $judul, $foto, $deskripsi);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: kelola_tentang_kami.php?msg=tambah_berhasil');
            exit;
        }
    }

    if ($action === 'update') {
        $idTentang = (int) ($_POST['id_tentang_kami'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = $_POST['deskripsi'] ?? '';

        if ($idTentang <= 0 || $judul === '' || $deskripsi === '') {
            $message = 'Data edit tentang kami tidak valid.';
            $messageType = 'danger';
        } else {
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $uploadDir = __DIR__ . '/../asset/tentang-kami/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $foto = time() . '_' . uniqid() . '.' . $ext;

                move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $foto);

                $stmt = mysqli_prepare($conn, 'UPDATE `tentang_kami` SET judul = ?, foto = ?, deskripsi = ? WHERE id_tentang_kami = ?');
                mysqli_stmt_bind_param($stmt, 'sssi', $judul, $foto, $deskripsi, $idTentang);
            } else {
                $stmt = mysqli_prepare($conn, 'UPDATE `tentang_kami` SET judul = ?, deskripsi = ? WHERE id_tentang_kami = ?');
                mysqli_stmt_bind_param($stmt, 'ssi', $judul, $deskripsi, $idTentang);
            }

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: kelola_tentang_kami.php?msg=ubah_berhasil');
            exit;
        }
    }

    if (isset($_GET['delete'])) {
        $idDelete = (int) $_GET['delete'];
        if ($idDelete > 0) {
            $stmt = mysqli_prepare($conn, 'DELETE FROM `tentang_kami` WHERE id_tentang_kami = ?');
            mysqli_stmt_bind_param($stmt, 'i', $idDelete);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        header('Location: kelola_tentang_kami.php?msg=hapus_berhasil');
        exit;
    }
}

$msgParam = $_GET['msg'] ?? '';
if ($msgParam === 'tambah_berhasil') {
    $message = 'Tentang kami berhasil ditambahkan.';
}
if ($msgParam === 'ubah_berhasil') {
    $message = 'Tentang kami berhasil diperbarui.';
}
if ($msgParam === 'hapus_berhasil') {
    $message = 'Tentang kami berhasil dihapus.';
}

$tentangKamiList = [];
$result = mysqli_query($conn, 'SELECT id_tentang_kami, judul, foto, deskripsi FROM `tentang_kami` ORDER BY id_tentang_kami DESC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tentangKamiList[] = $row;
    }
    mysqli_free_result($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tentang Kami</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.css" rel="stylesheet">
<style>
        /* ...existing code... */
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

        /* .content-preview {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        } */
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
                    <span class="topbar-page"><i class="bi bi-stars"></i> Kelola Tentang Kami</span>
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
                <h1 class="page-title"><i class="bi bi-people"></i> Tentang Kami</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle"></i> Tambah Tentang Kami
                </button>
            </div> -->

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo h($messageType); ?>"><?php echo h($message); ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchPelanggan" placeholder="Cari ...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tentangKamiTable">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Judul</th>
                                <th>Foto</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($tentangKamiList) === 0): ?>
                                <tr><td colspan="5" class="text-center text-muted">Belum ada data tentang kami.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($tentangKamiList as $tentangKami): ?>
                                <tr>
                                    <td><?php echo (int) $tentangKami['id_tentang_kami']; ?></td>
                                    <td><?php echo h($tentangKami['judul']); ?></td>
                                    <td><?php echo '<img src="../asset/tentang-kami/' . h($tentangKami['foto']) . '" alt="' . h($tentangKami['judul']) . '" width="200" class="rounded shadow-sm">';  ?></td>
                                    <td><div class="content-preview"><?php echo ($tentangKami['deskripsi']); ?></div></td>
                                    <td>
                                        <button
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?php echo (int) $tentangKami['id_tentang_kami']; ?>"
                                            data-judul="<?php echo h($tentangKami['judul']); ?>"
                                            data-foto="<?php echo h($tentangKami['foto']); ?>"
                                            data-deskripsi="<?php echo h($tentangKami['deskripsi']); ?>"
                                            onclick="setEditTentangKami(this)">
                                            Edit
                                        </button>
                                        <!-- <a href="kelola_tentang_kami.php?delete=<?php echo (int) $tentangKami['id_tentang_kami']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus tentang kami ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a> -->
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
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tentang Kami</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" class="form-control" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <input type="file" class="form-control" name="foto" required>
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

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tentang Kami</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_tentang_kami" id="edit_id_tentang_kami">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" class="form-control" name="judul" id="edit_judul" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto (opsional)</label>
                        <input type="file" class="form-control" name="foto" id="edit_foto">
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
setupTableSearch('searchTentangKami', 'tentangKamiTable');

function setEditTentangKami(button) {
    document.getElementById('edit_id_tentang_kami').value = button.getAttribute('data-id');
    document.getElementById('edit_judul').value = button.getAttribute('data-judul');
    
    // Set Summernote value properly
    const deskripsi = button.getAttribute('data-deskripsi');
    $('#edit_deskripsi').summernote('code', deskripsi);
}
</script>
<!-- jquery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.js"></script>
<script>$(".summernote").summernote({height:250});</script>
</body>
</html>
