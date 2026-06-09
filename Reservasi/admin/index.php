<?php 
require_once 'auth.php'; 
require_once '../database/koneksi.php';

// --- TOP 4 METRICS --- //
// 1. Total Reservasi (Aktif)
$q_total_reservasi = mysqli_query($conn, "SELECT COUNT(*) as total FROM reservasi WHERE status NOT IN ('Cancel', 'Refund Selesai')");
$r_total_reservasi = mysqli_fetch_assoc($q_total_reservasi);
$total_reservasi = $r_total_reservasi['total'] ?? 0;

// 2. Menunggu Verifikasi
$q_menunggu = mysqli_query($conn, "SELECT COUNT(*) as total FROM reservasi WHERE status = 'Menunggu Verifikasi'");
$r_menunggu = mysqli_fetch_assoc($q_menunggu);
$menunggu_verifikasi = $r_menunggu['total'] ?? 0;

// 3. Total Pelanggan
$q_pelanggan = mysqli_query($conn, "SELECT COUNT(*) as total FROM user");
$r_pelanggan = mysqli_fetch_assoc($q_pelanggan);
$total_pelanggan = $r_pelanggan['total'] ?? 0;

// 4. Total Pendapatan Keseluruhan (Selesai + Proses - Refund)
$q_pendapatan_total = mysqli_query($conn, "
    SELECT SUM(
        CASE 
            WHEN r.status IN ('Cancel', 'Refund Selesai') THEN ((p.harga * r.status_dp / 100) - IFNULL(r.refund_amount, 0))
            ELSE (p.harga * r.status_dp / 100)
        END
    ) as total_pendapatan
    FROM reservasi r
    JOIN paket p ON r.id_paket = p.id_paket
");
$r_pendapatan_total = mysqli_fetch_assoc($q_pendapatan_total);
$total_pendapatan = $r_pendapatan_total['total_pendapatan'] ?? 0;


// --- BREAKDOWN METRICS --- //
// 1. Pendapatan Selesai
$q_selesai = mysqli_query($conn, "
    SELECT SUM(p.harga * r.status_dp / 100) as total
    FROM reservasi r
    JOIN paket p ON r.id_paket = p.id_paket
    WHERE r.status = 'Selesai'
");
$r_selesai = mysqli_fetch_assoc($q_selesai);
$pendapatan_selesai = $r_selesai['total'] ?? 0;

// 2. Pendapatan Proses (Diverifikasi)
$q_proses = mysqli_query($conn, "
    SELECT SUM(p.harga * r.status_dp / 100) as total
    FROM reservasi r
    JOIN paket p ON r.id_paket = p.id_paket
    WHERE r.status = 'Diverifikasi'
");
$r_proses = mysqli_fetch_assoc($q_proses);
$pendapatan_proses = $r_proses['total'] ?? 0;

// 3. Nominal Refund / Cancel
$q_refund = mysqli_query($conn, "
    SELECT SUM(IFNULL(refund_amount, 0)) as total
    FROM reservasi
    WHERE status IN ('Cancel', 'Refund Selesai')
");
$r_refund = mysqli_fetch_assoc($q_refund);
$nominal_refund = $r_refund['total'] ?? 0;


// --- CHART DATA --- //
// Data Chart Pendapatan Bulanan Selesai (Tahun Ini)
$q_chart_selesai = mysqli_query($conn, "
    SELECT MONTH(r.schedule) as bulan, 
           SUM(p.harga * r.status_dp / 100) as pendapatan
    FROM reservasi r
    JOIN paket p ON r.id_paket = p.id_paket
    WHERE YEAR(r.schedule) = YEAR(CURDATE()) AND r.status = 'Selesai'
    GROUP BY MONTH(r.schedule)
");
$chart_selesai_data = array_fill(1, 12, 0); 
while($row = mysqli_fetch_assoc($q_chart_selesai)) {
    $chart_selesai_data[(int)$row['bulan']] = (float)$row['pendapatan'];
}

$chart_labels = "['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des']";
$js_chart_selesai = "[" . implode(",", $chart_selesai_data) . "]";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasboard</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .summary-card {
            border-radius: 10px;
            padding: 20px;
            color: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); }
        .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); }
        .bg-gradient-warning { background: linear-gradient(45deg, #f6c23e, #dda20a); }
        .bg-gradient-info { background: linear-gradient(45deg, #36b9cc, #258391); }
        .summary-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        /* Stylings for Breakdown Cards */
        .border-left-success { border-left: .25rem solid #1cc88a !important; }
        .border-left-primary { border-left: .25rem solid #4e73df !important; }
        .border-left-danger { border-left: .25rem solid #e74a3b !important; }

        .shortcut-card {
            transition: transform 0.2s;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .shortcut-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            color: #4e73df;
            text-decoration: none;
        }
        .shortcut-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #4e73df;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

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
                
                <!-- Top Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="summary-card bg-gradient-primary h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="font-size:0.85rem">Total Reservasi (Aktif)</div>
                                    <div class="h3 mb-0 font-weight-bold"><?= $total_reservasi ?></div>
                                </div>
                                <div class="summary-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="summary-card bg-gradient-warning h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="font-size:0.85rem">Menunggu Verifikasi</div>
                                    <div class="h3 mb-0 font-weight-bold"><?= $menunggu_verifikasi ?></div>
                                </div>
                                <div class="summary-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="summary-card bg-gradient-success h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="font-size:0.85rem">Total Pendapatan</div>
                                    <div class="h5 mb-0 font-weight-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                                </div>
                                <div class="summary-icon">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="summary-card bg-gradient-info h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="font-size:0.85rem">Total Pelanggan</div>
                                    <div class="h3 mb-0 font-weight-bold"><?= $total_pelanggan ?></div>
                                </div>
                                <div class="summary-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Chart Section -->
                    <div class="col-lg-8 mb-4">
                        
                        <!-- Breakdown Cards Above Charts -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="card border-left-success shadow-sm h-100 py-2">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.8rem;">Pendapatan Selesai</div>
                                        <div class="h6 mb-0 font-weight-bold text-dark">Rp <?= number_format($pendapatan_selesai, 0, ',', '.') ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="card border-left-primary shadow-sm h-100 py-2">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="font-size: 0.8rem;">Masih Proses</div>
                                        <div class="h6 mb-0 font-weight-bold text-dark">Rp <?= number_format($pendapatan_proses, 0, ',', '.') ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-left-danger shadow-sm h-100 py-2">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1" style="font-size: 0.8rem;">Refund / Cancel</div>
                                        <div class="h6 mb-0 font-weight-bold text-dark">Rp <?= number_format($nominal_refund, 0, ',', '.') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts -->
                        <div class="card shadow-sm h-100 mb-4">
                            <div class="card-header bg-white py-3">
                                <h6 class="m-0 font-weight-bold text-success">Grafik Pendapatan Selesai Tahun Ini</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartSelesai" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Shortcuts Section -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Akses Cepat</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <a href="kelola_reservasi.php" class="d-block text-center p-3 shortcut-card bg-light">
                                            <i class="bi bi-calendar-date shortcut-icon"></i>
                                            <div class="small fw-bold">Kelola Reservasi</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="kelola_paket.php" class="d-block text-center p-3 shortcut-card bg-light">
                                            <i class="bi bi-box-seam shortcut-icon"></i>
                                            <div class="small fw-bold">Kelola Paket</div>
                                        </a>
                                    </div>

                                    <div class="col-6">
                                        <a href="kelola_hero.php" class="d-block text-center p-3 shortcut-card bg-light">
                                            <i class="bi bi-images shortcut-icon"></i>
                                            <div class="small fw-bold">Kelola Beranda</div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart Selesai
            var ctxSelesai = document.getElementById('chartSelesai').getContext('2d');
            new Chart(ctxSelesai, {
                type: 'bar',
                data: {
                    labels: <?= $chart_labels ?>,
                    datasets: [{
                        label: 'Pendapatan Selesai (Rp)',
                        data: <?= $js_chart_selesai ?>,
                        backgroundColor: 'rgba(28, 200, 138, 0.5)',
                        borderColor: 'rgba(28, 200, 138, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: getChartOptions()
            });

            function getChartOptions() {
                return {
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                };
            }
        });
    </script>
</body>
</html>
