<?php
require_once 'auth.php';
require_once '../database/koneksi.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function parseRupiahInput($value)
{
    $normalized = preg_replace('/[^0-9]/', '', (string) $value);
    return (float) ($normalized === '' ? 0 : $normalized);
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $idPaket = isset($_POST['id_paket']) ? (int) $_POST['id_paket'] : 0;
        $namaPaket = trim($_POST['nama_paket'] ?? '');
        $harga = parseRupiahInput($_POST['harga'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $gambarLama = trim($_POST['gambar_lama'] ?? '');
        $gambarPath = $gambarLama;

        if ($namaPaket === '' || $harga <= 0) {
            $message = 'Nama paket dan harga wajib diisi.';
            $messageType = 'danger';
        } else {
            if (!empty($_FILES['gambar']['name'])) {
                $uploadDir = realpath(__DIR__ . '/../asset/foto');
                if ($uploadDir !== false) {
                    $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($ext, $allowedExt, true)) {
                        $newFileName = 'paket_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                        $targetFile = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

                        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
                            $gambarPath = 'asset/foto/' . $newFileName;
                        } else {
                            $message = 'Upload gambar gagal.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Format gambar tidak valid. Gunakan jpg, jpeg, png, atau webp.';
                        $messageType = 'danger';
                    }
                }
            }

            if ($message === '') {
                if ($action === 'create') {
                    $stmt = mysqli_prepare($conn, 'INSERT INTO paket (nama_paket, gambar, harga, deskripsi) VALUES (?, ?, ?, ?)');
                    mysqli_stmt_bind_param($stmt, 'ssds', $namaPaket, $gambarPath, $harga, $deskripsi);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    header('Location: kelola_paket.php?msg=tambah_berhasil');
                    exit;
                }

                $stmt = mysqli_prepare($conn, 'UPDATE paket SET nama_paket = ?, gambar = ?, harga = ?, deskripsi = ? WHERE id_paket = ?');
                mysqli_stmt_bind_param($stmt, 'ssdsi', $namaPaket, $gambarPath, $harga, $deskripsi, $idPaket);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                header('Location: kelola_paket.php?msg=ubah_berhasil');
                exit;
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $idDelete = (int) $_GET['delete'];
    if ($idDelete > 0) {
        $stmt = mysqli_prepare($conn, 'DELETE FROM paket WHERE id_paket = ?');
        mysqli_stmt_bind_param($stmt, 'i', $idDelete);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: kelola_paket.php?msg=hapus_berhasil');
    exit;
}

$msgParam = $_GET['msg'] ?? '';
if ($msgParam === 'tambah_berhasil') {
    $message = 'Paket berhasil ditambahkan.';
}
if ($msgParam === 'ubah_berhasil') {
    $message = 'Paket berhasil diperbarui.';
}
if ($msgParam === 'hapus_berhasil') {
    $message = 'Paket berhasil dihapus.';
}

$paketList = [];
$result = mysqli_query($conn, 'SELECT id_paket, nama_paket, gambar, harga, deskripsi FROM paket ORDER BY id_paket ASC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $paketList[] = $row;
    }
    mysqli_free_result($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket</title>
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
            <li><a href="kelola_paket.php" class="active"><i class="bi bi-box-seam"></i><span class="menu-label"> Paket</span></a></li>
            <li><a href="kelola_reservasi.php"><i class="bi bi-calendar-check"></i><span class="menu-label"> Reservasi</span></a></li>
            <li><a href="kelola_pelanggan.php"><i class="bi bi-people"></i><span class="menu-label"> Pelanggan</span></a></li>
            <li><a href="kelola_admin.php"><i class="bi bi-person-gear"></i><span class="menu-label"> Admin</span></a></li>
            <li><a href="kelola_riwayat.php"><i class="bi bi-clock-history"></i><span class="menu-label"> Riwayat Booking</span></a></li>
            <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i><span class="menu-label"> Logout</span></a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary"><i class="bi bi-list"></i></button>
                <div class="topbar-meta ms-auto">
                    <span class="topbar-page"><i class="bi bi-stars"></i> Kelola Paket Layanan</span>
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
                <h1 class="page-title"><i class="bi bi-box"></i> Paket Layanan</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle"></i> Tambah Data
                </button>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo h($messageType); ?>"><?php echo h($message); ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <?php if (count($paketList) === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">Belum ada data paket.</div>
                    </div>
                <?php endif; ?>

                <?php foreach ($paketList as $paket): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-lg h-100 paket-card" style="transition: all 0.3s ease;">
                            <div class="card-header p-4" style="background: linear-gradient(120deg, #f8f9fa 0%, #e9ecef 100%); color: #212529;">
                                <h5 class="card-title mb-2 fw-bold"><?php echo h($paket['nama_paket']); ?></h5>
                                <p class="mb-0">ID Paket: <?php echo (int) $paket['id_paket']; ?></p>
                            </div>
                            <div class="card-body flex-grow-1">
                                <?php if (!empty($paket['gambar'])): ?>
                                    <img src="../<?php echo h($paket['gambar']); ?>" class="img-fluid rounded mb-3" alt="<?php echo h($paket['nama_paket']); ?>" style="height: 130px; width: 100%; object-fit: cover;">
                                <?php endif; ?>
                                <h5 class="text-danger fw-bold">Rp<?php echo number_format((float) $paket['harga'], 0, ',', '.'); ?></h5>
                                <ul class="list-unstyled small mb-0">
                                    <?php
                                    $fiturList = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $paket['deskripsi'])));
                                    if (count($fiturList) === 0) {
                                        echo '<li><i class="bi bi-dot"></i> -</li>';
                                    } else {
                                        foreach ($fiturList as $fitur) {
                                            echo '<li class="mb-1"><i class="bi bi-check-circle text-success me-2"></i>' . h($fitur) . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="card-footer border-0 bg-transparent p-4 pt-0 d-flex gap-2">
                                <button
                                    class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="<?php echo (int) $paket['id_paket']; ?>"
                                    data-nama="<?php echo h($paket['nama_paket']); ?>"
                                    data-harga="<?php echo (int) round((float) $paket['harga']); ?>"
                                    data-deskripsi="<?php echo h($paket['deskripsi']); ?>"
                                    data-gambar="<?php echo h($paket['gambar']); ?>"
                                    onclick="setEditPaket(this)">
                                    Edit
                                </button>
                                <a href="kelola_paket.php?delete=<?php echo (int) $paket['id_paket']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus paket ini?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Nama Paket</label>
                        <input type="text" class="form-control" name="nama_paket" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="text" class="form-control harga-rupiah" name="harga" inputmode="numeric" placeholder="Rp 0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (satu fitur per baris)</label>
                        <textarea class="form-control" name="deskripsi" rows="4" placeholder="Body Full Coating&#10;Interior Detailing"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <input type="file" class="form-control" name="gambar" accept=".jpg,.jpeg,.png,.webp">
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
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_paket" id="edit_id_paket">
                    <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
                    <div class="mb-3">
                        <label class="form-label">Nama Paket</label>
                        <input type="text" class="form-control" name="nama_paket" id="edit_nama_paket" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="text" class="form-control harga-rupiah" name="harga" id="edit_harga" inputmode="numeric" placeholder="Rp 0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (satu fitur per baris)</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Baru (opsional)</label>
                        <input type="file" class="form-control" name="gambar" accept=".jpg,.jpeg,.png,.webp">
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
function setEditPaket(button) {
    document.getElementById('edit_id_paket').value = button.getAttribute('data-id');
    document.getElementById('edit_nama_paket').value = button.getAttribute('data-nama');
    const hargaEdit = document.getElementById('edit_harga');
    hargaEdit.value = formatRupiahInput(button.getAttribute('data-harga'));
    document.getElementById('edit_deskripsi').value = button.getAttribute('data-deskripsi');
    document.getElementById('edit_gambar_lama').value = button.getAttribute('data-gambar');
}

function formatRupiahInput(value) {
    const raw = String(value || '').trim();
    if (raw === '') {
        return '';
    }

    // Handle numeric strings from DB such as "5000000.00" before digit-only cleanup.
    if (/^\d+[\.,]\d{1,2}$/.test(raw)) {
        const normalized = raw.replace(',', '.');
        const parsed = Math.round(parseFloat(normalized));
        if (!Number.isNaN(parsed) && parsed > 0) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(parsed);
        }
    }

    const digits = raw.replace(/\D/g, '');
    if (!digits) {
        return '';
    }

    return 'Rp ' + new Intl.NumberFormat('id-ID').format(parseInt(digits, 10));
}

function stripRupiah(value) {
    return String(value || '').replace(/\D/g, '');
}

document.addEventListener('DOMContentLoaded', function () {
    const hargaInputs = document.querySelectorAll('.harga-rupiah');

    hargaInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            const cursorAtEnd = input.selectionStart === input.value.length;
            input.value = formatRupiahInput(input.value);
            if (cursorAtEnd) {
                input.setSelectionRange(input.value.length, input.value.length);
            }
        });

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                input.value = stripRupiah(input.value);
            });
        }
    });
});
</script>
</body>
</html>
