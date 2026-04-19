<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasboard</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3><i class="bi bi-car-front-fill"></i><span>Admin Exco </span></h3>
            </div>
            <ul class="list-unstyled components">
                <li><a href="index.php" class="active"><i class="bi bi-speedometer2"></i><span class="menu-label"> Dashboard</span></a></li>
                <li><a href="kelola_paket.php"><i class="bi bi-box-seam"></i><span class="menu-label"> Paket</span></a></li>
                <li><a href="kelola_reservasi.php"><i class="bi bi-calendar-check"></i><span class="menu-label"> Reservasi</span></a></li>
                <li><a href="kelola_pelanggan.php"><i class="bi bi-people"></i><span class="menu-label"> Pelanggan</span></a></li>
                <li><a href="kelola_admin.php"><i class="bi bi-person-gear"></i><span class="menu-label"> Admin</span></a></li>
                <li><a href="kelola_riwayat.php"><i class="bi bi-clock-history"></i><span class="menu-label"> Riwayat Booking</span></a></li>
                <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i><span class="menu-label"> Logout</span></a></li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="topbar-meta ms-auto">
                        <span class="topbar-page"><i class="bi bi-stars"></i> Dashboard</span>
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

            <!-- Main Content -->
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#photoModal">
                        <i class="bi bi-plus-circle"></i> Tambah Foto
                    </button> -->
                </div>
               <div>
                    <h1 style="text-align: center; padding-top: 180px;">SELAMAT DATANG ADMIN</h1>
                </div> 
        
            </div>
        </div>
    </div>

    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Tambah Foto ke Galeri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="photoForm">
                        <div class="mb-3">
                            <label for="photoName" class="form-label">Nama Foto</label>
                            <input type="text" class="form-control" id="photoName" required>
                        </div>
                        <div class="mb-3">
                            <label for="photoCategory" class="form-label">Kategori</label>
                            <select class="form-select" id="photoCategory" required>
                                <option value="">Pilih Kategori</option>
                                <option value="keluarga">Keluarga</option>
                                <option value="perorangan">Perorangan</option>
                                <option value="pasangan">Pasangan</option>
                                <option value="pesta">Pesta</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="photoFile" class="form-label">Upload Foto</label>
                            <input type="file" class="form-control" id="photoFile" accept="image/*" required>
                            <small class="text-muted">Format: JPG, PNG | Maksimal 5MB</small>
                        </div>
                        <div class="mb-3">
                            <label for="photoDescription" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="photoDescription" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="savePhoto()">Upload Foto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        function deletePhoto(name) {
            if (confirmDelete(name)) {
                showSuccess('Foto ' + name + ' berhasil dihapus dari galeri');
            }
        }

        function savePhoto() {
            if (validateForm('photoForm')) {
                showSuccess('Foto berhasil ditambahkan ke galeri');
                document.getElementById('photoForm').reset();
                const modal = bootstrap.Modal.getInstance(document.getElementById('photoModal'));
                modal.hide();
            } else {
                showError('Mohon lengkapi semua field yang diperlukan');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupTableSearch('searchPhoto', 'galleryTable');
        });
    </script>
</body>
</html>
