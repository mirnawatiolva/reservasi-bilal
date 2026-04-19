<?php
session_start();
require_once 'database/koneksi.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=riwayat_booking.php');
    exit;
}

$idUser = (int) $_SESSION['user_id'];

// Pastikan booking yang sudah selesai tercatat lunas di DB.
$stmtLunas = mysqli_prepare($conn, "UPDATE reservasi SET status_dp = 100 WHERE id_user = ? AND status = 'Selesai' AND status_dp < 100");
if ($stmtLunas) {
    mysqli_stmt_bind_param($stmtLunas, 'i', $idUser);
    mysqli_stmt_execute($stmtLunas);
    mysqli_stmt_close($stmtLunas);
}

$riwayatList = [];
$stmt = mysqli_prepare($conn, 'SELECT r.id_reservasi, r.schedule, r.status, r.status_dp, p.nama_paket, p.harga
    FROM reservasi r
    JOIN paket p ON p.id_paket = r.id_paket
    WHERE r.id_user = ?
    ORDER BY r.id_reservasi DESC');
mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $riwayatList[] = $row;
    }
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking - Exco Detailing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background:
                radial-gradient(circle at 12% 14%, rgba(220, 53, 69, 0.1), transparent 34%),
                radial-gradient(circle at 85% 18%, rgba(13, 110, 253, 0.1), transparent 30%),
                linear-gradient(180deg, #f3f6fc 0%, #ffffff 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .site-bg-effects {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .site-bg-effects .orb {
            position: absolute;
            border-radius: 50%;
            opacity: 0.34;
            animation: floatOrb 22s ease-in-out infinite;
        }

        .site-bg-effects .orb-1 {
            width: 330px;
            height: 330px;
            top: -100px;
            left: -70px;
            background: radial-gradient(circle at 35% 35%, rgba(168, 211, 255, 0.95), rgba(40, 112, 255, 0.46), transparent 72%);
        }

        .site-bg-effects .orb-2 {
            width: 260px;
            height: 260px;
            right: -70px;
            bottom: 8%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 182, 191, 0.95), rgba(220, 53, 69, 0.4), transparent 72%);
            animation-delay: 1.3s;
        }

        .site-bg-effects .orb-3 {
            width: 180px;
            height: 180px;
            right: 16%;
            top: 35%;
            background: radial-gradient(circle at 30% 30%, rgba(181, 255, 233, 0.92), rgba(57, 195, 212, 0.34), transparent 74%);
            animation-delay: 2.2s;
        }

        .page-wrap {
            position: relative;
            z-index: 1;
        }

        .navbar {
            background: linear-gradient(125deg, rgba(23, 23, 23, 0.91), rgba(48, 48, 48, 0.86)) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 12px 30px rgba(10, 10, 10, 0.2);
        }

        .history-card {
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: 18px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.8));
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
            backdrop-filter: blur(4px);
            overflow: hidden;
        }

        .history-card .card-header {
            background: linear-gradient(135deg, #dc3545, #b8222f) !important;
        }

        #alertNoData {
            border-radius: 16px;
            background: linear-gradient(145deg, #eef5ff, #f8fbff);
        }

        @keyframes floatOrb {
            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
            50% {
                transform: translate3d(12px, -14px, 0) scale(1.05);
            }
        }
    </style>
</head>
<body>
<div class="site-bg-effects" aria-hidden="true">
    <span class="orb orb-1"></span>
    <span class="orb orb-2"></span>
    <span class="orb orb-3"></span>
</div>
<div class="page-wrap">
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: linear-gradient(135deg, #dc3545 0%, #000 100%);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-car-side text-danger"></i> Exco Detailing</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-3 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#tentang">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#paket">Paket</a></li>
                <li class="nav-item"><a class="nav-link" href="reservasi.php">Reservasi</a></li>
                <li class="nav-item"><a class="nav-link active" href="riwayat_booking.php">Riwayat Booking</a></li>
                <li class="nav-item"><span class="nav-link text-light"><i class="fas fa-user"></i> <?php echo h($_SESSION['username'] ?? ''); ?></span></li>
                <li class="nav-item"><a class="btn btn-sm btn-outline-light" href="index.php?logout=1">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold" style="color: #dc3545;"><i class="fas fa-history"></i> Daftar Pemesanan</h2>
        </div>

        <?php if (count($riwayatList) === 0): ?>
            <div class="alert alert-info border-2 text-center py-5" id="alertNoData">
                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                <h5 class="fw-bold mb-2">Belum Ada Riwayat Booking</h5>
                <p class="text-muted mb-4">Anda belum melakukan pemesanan apapun. Silakan membuat pemesanan baru.</p>
                <a href="reservasi.php" class="btn btn-danger"><i class="fas fa-calendar-check"></i> Buat Pemesanan Baru</a>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($riwayatList as $item): ?>
                <?php
                $harga = (float) $item['harga'];
                $dp = (int) $item['status_dp'];

                if ($item['status'] === 'Selesai') {
                    $dp = 100;
                }

                $dibayar = ($harga * $dp) / 100;
                $sisa = $harga - $dibayar;

                if ($item['status'] === 'Selesai') {
                    $sisa = 0;
                }

                $badge = 'secondary';
                if ($item['status'] === 'Menunggu Verifikasi') {
                    $badge = 'warning';
                } elseif ($item['status'] === 'Diverifikasi') {
                    $badge = 'info';
                } elseif ($item['status'] === 'Selesai') {
                    $badge = 'success';
                } elseif ($item['status'] === 'Cancel') {
                    $badge = 'danger';
                }
                ?>
                <div class="col-md-12 mb-4">
                    <div class="card h-100 shadow-sm border-0 history-card">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0" style="color: white;"><i class="fas fa-car"></i> ID Booking <?php echo (int) $item['id_reservasi']; ?></h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Paket:</strong> <?php echo h($item['nama_paket']); ?></p>
                            <p class="mb-2"><strong>Tanggal:</strong> <?php echo h(date('d-m-Y', strtotime($item['schedule']))); ?></p>
                            <p class="mb-2"><strong>Jam:</strong> <?php echo h(date('H:i', strtotime($item['schedule']))); ?> WIB</p>
                            <p class="mb-2"><strong>Status:</strong> <span class="badge bg-<?php echo h($badge); ?>"><?php echo h($item['status']); ?></span></p>
                            <p class="mb-3">
                                <strong>Total Paket:</strong> <span class="text-danger fw-bold">Rp<?php echo number_format($harga, 0, ',', '.'); ?></span><br>
                                <strong>Dibayar (<?php echo $dp; ?>%):</strong> <span class="text-danger fw-bold">Rp<?php echo number_format($dibayar, 0, ',', '.'); ?></span><br>
                                <strong>Sisa Pembayaran:</strong> <span class="text-danger fw-bold">Rp<?php echo number_format($sisa, 0, ',', '.'); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<footer class="py-4 text-white" style="background-color: #000;">
    <div class="container text-center text-muted"><p>&copy; 2026 Exco Detailing. All Rights Reserved.</p></div>
</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
