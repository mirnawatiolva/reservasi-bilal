<?php
session_start();
require_once 'database/koneksi.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=reservasi.php');
    exit;
}

$checkBuktiColumn = mysqli_query($conn, "SHOW COLUMNS FROM reservasi LIKE 'bukti_pembayaran'");
if ($checkBuktiColumn && mysqli_num_rows($checkBuktiColumn) === 0) {
    mysqli_query($conn, 'ALTER TABLE reservasi ADD COLUMN bukti_pembayaran VARCHAR(255) NULL AFTER status_dp');
}
if ($checkBuktiColumn) {
    mysqli_free_result($checkBuktiColumn);
}

$message = '';
$messageType = 'danger';
$showNotaModal = false;
$notaData = null;

$paketList = [];
$paketMap = [];
$result = mysqli_query($conn, 'SELECT id_paket, nama_paket, harga FROM paket ORDER BY id_paket ASC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $paketList[] = $row;
        $paketMap[(int) $row['id_paket']] = $row;
    }
    mysqli_free_result($result);
}

$selectedPaketId = (int) ($_GET['paket_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedPaketId = (int) ($_POST['id_paket'] ?? 0);
    $scheduleInput = trim($_POST['schedule'] ?? '');
    $statusDp = (int) ($_POST['status_dp'] ?? 50);
    $buktiPath = null;
    $buktiFile = $_FILES['bukti_pembayaran'] ?? null;

    if (!isset($paketMap[$selectedPaketId]) || $scheduleInput === '' || !in_array($statusDp, [50, 100], true)) {
        $message = 'Data reservasi tidak valid.';
    } elseif (!$buktiFile || !isset($buktiFile['error']) || $buktiFile['error'] !== UPLOAD_ERR_OK) {
        $message = 'Bukti pembayaran wajib diunggah.';
    } else {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        $fileName = (string) ($buktiFile['name'] ?? '');
        $tmpFile = (string) ($buktiFile['tmp_name'] ?? '');
        $fileSize = (int) ($buktiFile['size'] ?? 0);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $message = 'Format bukti pembayaran harus JPG, JPEG, PNG, WEBP, atau PDF.';
        } elseif ($fileSize <= 0 || $fileSize > (5 * 1024 * 1024)) {
            $message = 'Ukuran bukti pembayaran maksimal 5MB.';
        } elseif (!is_uploaded_file($tmpFile)) {
            $message = 'File bukti pembayaran tidak valid.';
        } else {
            $uploadDir = __DIR__ . '/asset/bukti_pembayaran';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = 'bukti_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetPath = $uploadDir . '/' . $newFileName;
            $dbPath = 'asset/bukti_pembayaran/' . $newFileName;

            if (!move_uploaded_file($tmpFile, $targetPath)) {
                $message = 'Gagal mengunggah bukti pembayaran.';
            } else {
                $buktiPath = $dbPath;
            }
        }
    }

    if ($message === '') {
        $schedule = date('Y-m-d H:i:s', strtotime($scheduleInput));
        $status = 'Menunggu Verifikasi';
        $idUser = (int) $_SESSION['user_id'];

        $stmt = mysqli_prepare($conn, 'INSERT INTO reservasi (id_user, id_paket, status, status_dp, bukti_pembayaran, schedule) VALUES (?, ?, ?, ?, ?, ?)');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iisiss', $idUser, $selectedPaketId, $status, $statusDp, $buktiPath, $schedule);
            $ok = mysqli_stmt_execute($stmt);
            $insertId = $ok ? (int) mysqli_insert_id($conn) : 0;
            mysqli_stmt_close($stmt);

            if ($ok && isset($paketMap[$selectedPaketId])) {
                $hargaPaket = (float) $paketMap[$selectedPaketId]['harga'];
                $totalBayar = ($hargaPaket * $statusDp) / 100;

                $notaData = [
                    'id_reservasi' => $insertId,
                    'nama_user' => (string) ($_SESSION['username'] ?? ''),
                    'nama_paket' => (string) $paketMap[$selectedPaketId]['nama_paket'],
                    'harga_paket' => $hargaPaket,
                    'persentase_bayar' => $statusDp,
                    'total_bayar' => $totalBayar,
                    'jadwal' => $schedule,
                    'status' => $status,
                    'tanggal_nota' => date('Y-m-d H:i:s'),
                ];

                $message = 'Reservasi berhasil dibuat. Nota akan ditampilkan dan otomatis diunduh.';
                $messageType = 'success';
                $showNotaModal = true;
            } else {
                $message = 'Gagal menyimpan reservasi. Silakan coba lagi.';
            }
        } else {
            $message = 'Gagal memproses reservasi. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi - Exco Detailing</title>
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

        .navbar {
            background: linear-gradient(125deg, rgba(23, 23, 23, 0.91), rgba(48, 48, 48, 0.86)) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 12px 30px rgba(10, 10, 10, 0.2);
        }

        .page-wrap {
            position: relative;
            z-index: 1;
        }

        .title-main {
            color: #dc3545;
            letter-spacing: 0.3px;
        }

        .booking-card {
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 20px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.8));
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(4px);
        }

        .form-label {
            color: #1f2937;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border-color: #dbe1ea;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.18rem rgba(220, 53, 69, 0.2);
        }

        #infoPembayaran {
            border-radius: 14px;
            background: linear-gradient(145deg, #eef5ff, #f8fbff);
        }

        .nota-preview {
            border: 1px dashed #b9c2d0;
            border-radius: 12px;
            background: #fff;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #bc2230);
            border: 0;
            box-shadow: 0 12px 28px rgba(220, 53, 69, 0.25);
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
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: linear-gradient(135deg, #444444 0%, #2d2d2d 100%);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-car-side text-danger"></i> Exco Detailing</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-3 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#tentang">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#paket">Paket</a></li>
                <li class="nav-item"><a class="nav-link active" href="reservasi.php">Reservasi</a></li>
                <li class="nav-item"><a class="nav-link" href="riwayat_booking.php">Riwayat Booking</a></li>
                <li class="nav-item"><span class="nav-link text-light"><i class="fas fa-user"></i> <?php echo h($_SESSION['username'] ?? ''); ?></span></li>
                <li class="nav-item"><a class="btn btn-sm btn-outline-light" href="index.php?logout=1">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="text-center mb-4 fw-bold" style="color: #dc3545;"><i class="fas fa-clipboard-list"></i> Form Booking</h2>
                <div class="card border-0 shadow-lg booking-card">
                    <div class="card-body p-5">
                        <?php if ($message !== ''): ?>
                            <div class="alert alert-<?php echo h($messageType); ?>"><?php echo h($message); ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama User</label>
                                <input type="text" class="form-control form-control-lg border-2" value="<?php echo h($_SESSION['username'] ?? ''); ?>" readonly>
                            </div>

                            <div class="mb-4">
                                <label for="id_paket" class="form-label fw-bold">Jenis Paket</label>
                                <select class="form-select form-select-lg border-2" id="id_paket" name="id_paket" required onchange="updateRingkasan()">
                                    <option value="">-- Pilih Paket Detailing --</option>
                                    <?php foreach ($paketList as $paket): ?>
                                        <option
                                            value="<?php echo (int) $paket['id_paket']; ?>"
                                            data-harga="<?php echo (float) $paket['harga']; ?>"
                                            <?php echo ((int) $paket['id_paket'] === $selectedPaketId) ? 'selected' : ''; ?>>
                                            <?php echo h($paket['nama_paket']); ?> - Rp<?php echo number_format((float) $paket['harga'], 0, ',', '.'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="schedule" class="form-label fw-bold">Jadwal Booking</label>
                                <input type="datetime-local" class="form-control form-control-lg border-2" id="schedule" name="schedule" required>
                            </div>

                            <div class="mb-4">
                                <label for="status_dp" class="form-label fw-bold">Pembayaran</label>
                                <select class="form-select form-select-lg border-2" id="status_dp" name="status_dp" required onchange="updateRingkasan()">
                                    <option value="50" selected>DP 50%</option>
                                    <option value="100">Lunas 100%</option>
                                </select>
                            </div>

                            <div class="alert alert-warning border-2 mb-4">
                                <h6 class="fw-bold mb-2"><i class="fas fa-university"></i> Rekening Pembayaran (Dummy)</h6>
                                <p class="mb-1"><strong>Bank:</strong> BCA</p>
                                <p class="mb-1"><strong>No. Rekening:</strong> 1234567890</p>
                                <p class="mb-0"><strong>Atas Nama:</strong> EXCO DETAILING</p>
                            </div>

                            <div class="mb-4">
                                <label for="bukti_pembayaran" class="form-label fw-bold">Bukti Pembayaran</label>
                                <input type="file" class="form-control form-control-lg border-2" id="bukti_pembayaran" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP, PDF. Maksimal 5MB.</small>
                            </div>

                            <div class="alert alert-info border-2" id="infoPembayaran" style="display: none;">
                                <h6 class="fw-bold mb-3"><i class="fas fa-credit-card"></i> Ringkasan Pembayaran</h6>
                                <p class="mb-2"><span class="text-muted">Harga Paket:</span> <strong id="displayHargaPaket">-</strong></p>
                                <p class="mb-2"><span class="text-muted">Persentase Bayar:</span> <strong id="displayPersen">-</strong></p>
                                <p class="mb-0"><span class="text-muted">Total Dibayar:</span> <strong id="displayDibayar" style="color: #dc3545;">-</strong></p>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg fw-bold"><i class="fas fa-check-circle"></i> Proses Reservasi</button>
                                <a href="riwayat_booking.php" class="btn btn-outline-secondary btn-lg fw-bold"><i class="fas fa-history"></i> Lihat Riwayat Booking</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="notaReservasiModal" tabindex="-1" aria-labelledby="notaReservasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="notaReservasiLabel"><i class="fas fa-receipt text-danger"></i> Nota Reservasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 nota-preview" id="notaPreviewContent"></div>
                <p class="small text-muted mt-3 mb-0">File PDF nota akan otomatis terunduh. Jika belum terunduh, klik tombol Download PDF.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger" id="btnDownloadNota"><i class="fas fa-download"></i> Download PDF</button>
            </div>
        </div>
    </div>
</div>

<footer class="py-4 text-white" style="background-color: #000;">
    <div class="container text-center text-muted"><p>&copy; 2026 Exco Detailing. All Rights Reserved.</p></div>
</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
function toRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
}

function updateRingkasan() {
    const paket = document.getElementById('id_paket');
    const statusDp = document.getElementById('status_dp');
    const info = document.getElementById('infoPembayaran');
    const selected = paket.options[paket.selectedIndex];

    if (!selected || !selected.dataset.harga) {
        info.style.display = 'none';
        return;
    }

    const harga = parseFloat(selected.dataset.harga);
    const dp = parseInt(statusDp.value, 10);
    const bayar = (harga * dp) / 100;

    document.getElementById('displayHargaPaket').textContent = toRupiah(harga);
    document.getElementById('displayPersen').textContent = dp + '%';
    document.getElementById('displayDibayar').textContent = toRupiah(bayar);
    info.style.display = 'block';
}

function formatTanggalId(datetime) {
    if (!datetime) {
        return '-';
    }

    const date = new Date(datetime.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) {
        return datetime;
    }

    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) + ' WIB';
}

function buildNotaPreview(invoice) {
    return [
        'EXCO DETAILING',
        'Jl. Contoh No. 123',
        '--------------------------------',
        'ID Reservasi : #' + invoice.id_reservasi,
        'Tanggal Nota : ' + formatTanggalId(invoice.tanggal_nota),
        'Pelanggan    : ' + invoice.nama_user,
        'Paket        : ' + invoice.nama_paket,
        'Jadwal       : ' + formatTanggalId(invoice.jadwal),
        'Status       : ' + invoice.status,
        '--------------------------------',
        'Harga Paket  : ' + toRupiah(invoice.harga_paket),
        'Pembayaran   : ' + invoice.persentase_bayar + '%',
        'Total Bayar  : ' + toRupiah(invoice.total_bayar),
        '--------------------------------',
        'Terima kasih telah melakukan',
        'reservasi di Exco Detailing.'
    ].join('<br>');
}

function downloadNotaPdf(invoice) {
    const jsPDFRef = window.jspdf && window.jspdf.jsPDF;
    if (!jsPDFRef) {
        return;
    }

    const doc = new jsPDFRef({
        orientation: 'portrait',
        unit: 'mm',
        format: [80, 170]
    });

    let y = 8;
    const left = 5;

    doc.setFont('courier', 'bold');
    doc.setFontSize(11);
    doc.text('EXCO DETAILING', 40, y, { align: 'center' });
    y += 5;

    doc.setFont('courier', 'normal');
    doc.setFontSize(8.5);
    doc.text('Jl. Contoh No. 123', 40, y, { align: 'center' });
    y += 4;
    doc.text('--------------------------------', left, y);
    y += 4;

    const lines = [
        'ID Reservasi : #' + invoice.id_reservasi,
        'Tanggal Nota : ' + formatTanggalId(invoice.tanggal_nota),
        'Pelanggan    : ' + invoice.nama_user,
        'Paket        : ' + invoice.nama_paket,
        'Jadwal       : ' + formatTanggalId(invoice.jadwal),
        'Status       : ' + invoice.status,
        '--------------------------------',
        'Harga Paket  : ' + toRupiah(invoice.harga_paket),
        'Pembayaran   : ' + invoice.persentase_bayar + '%',
        'Total Bayar  : ' + toRupiah(invoice.total_bayar),
        '--------------------------------',
        'Terima kasih atas kepercayaan',
        'Anda menggunakan layanan kami.'
    ];

    lines.forEach(function(line) {
        const wrapped = doc.splitTextToSize(line, 70);
        doc.text(wrapped, left, y);
        y += wrapped.length * 4;
    });

    const fileName = 'nota_reservasi_' + invoice.id_reservasi + '.pdf';
    doc.save(fileName);
}

document.addEventListener('DOMContentLoaded', function() {
    updateRingkasan();

    const invoiceData = <?php echo $notaData ? json_encode($notaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'null'; ?>;
    const shouldShowNota = <?php echo $showNotaModal ? 'true' : 'false'; ?>;

    if (!shouldShowNota || !invoiceData) {
        return;
    }

    const preview = document.getElementById('notaPreviewContent');
    const btnDownload = document.getElementById('btnDownloadNota');
    const modalEl = document.getElementById('notaReservasiModal');

    if (preview) {
        preview.innerHTML = buildNotaPreview(invoiceData);
    }

    if (btnDownload) {
        btnDownload.addEventListener('click', function() {
            downloadNotaPdf(invoiceData);
        });
    }

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    window.setTimeout(function() {
        downloadNotaPdf(invoiceData);
    }, 400);
});
</script>
</body>
</html>
