<?php
session_start();
require_once 'database/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$messageType = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $telepon = trim($_POST['No_Telepon'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username === '' || $password === '' || $telepon === '' || $email === '') {
        $message = 'Semua field wajib diisi.';
    } else {
        $checkStmt = mysqli_prepare($conn, 'SELECT id_user FROM `user` WHERE username = ? OR email = ? LIMIT 1');
        mysqli_stmt_bind_param($checkStmt, 'ss', $username, $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        $exists = $checkResult && mysqli_fetch_assoc($checkResult);
        mysqli_stmt_close($checkStmt);

        if ($exists) {
            $message = 'Username atau email sudah terdaftar.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, 'INSERT INTO `user` (username, password, email, No_Telepon) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssss', $username, $passwordHash, $email, $telepon);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: login.php?msg=registered');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Exito Detailing</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at 14% 16%, rgba(220, 53, 69, 0.15), transparent 35%),
                radial-gradient(circle at 85% 16%, rgba(13, 110, 253, 0.13), transparent 28%),
                linear-gradient(180deg, #f2f5fb 0%, #ffffff 100%);
            color: #1c1c1c;
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
            animation: floatOrb 20s ease-in-out infinite;
        }

        .site-bg-effects .orb-1 {
            width: 320px;
            height: 320px;
            top: -100px;
            left: -70px;
            background: radial-gradient(circle at 35% 35%, rgba(160, 205, 255, 0.95), rgba(63, 132, 255, 0.45), transparent 72%);
        }

        .site-bg-effects .orb-2 {
            width: 250px;
            height: 250px;
            right: -70px;
            bottom: 10%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 170, 182, 0.92), rgba(220, 53, 69, 0.42), transparent 72%);
            animation-delay: 1.2s;
        }

        .site-bg-effects .orb-3 {
            width: 170px;
            height: 170px;
            right: 16%;
            top: 36%;
            background: radial-gradient(circle at 30% 30%, rgba(167, 252, 226, 0.9), rgba(23, 162, 184, 0.34), transparent 74%);
            animation-delay: 2.2s;
        }

        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.78));
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 22px;
            box-shadow: 0 24px 56px rgba(15, 23, 42, 0.18);
            padding: 34px;
            backdrop-filter: blur(6px);
        }

        .brand-title {
            text-align: center;
            margin-bottom: 8px;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.4px;
        }

        .brand-title i {
            color: #dc3545;
        }

        .brand-subtitle {
            text-align: center;
            margin-bottom: 28px;
            font-size: 14px;
            color: #5f6773;
        }

        .form-label {
            font-weight: 600;
            color: #1d2430;
        }

        .form-control {
            height: 50px;
            border-radius: 12px;
            border: 1px solid #d8dee8;
        }

        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.18rem rgba(220, 53, 69, 0.2);
        }

        .input-group-text {
            background: #fff;
            border-left: 0;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
            color: #6c757d;
        }

        .btn-login {
            height: 50px;
            border-radius: 12px;
            font-weight: 700;
            border: 0;
            background: linear-gradient(135deg, #dc3545, #bb1f2d);
            box-shadow: 0 14px 30px rgba(220, 53, 69, 0.26);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(220, 53, 69, 0.3);
        }

        .register-text {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
            color: #6c757d;
        }

        .register-text a {
            text-decoration: none;
            font-weight: 700;
            color: #dc3545;
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
<section class="login-section">
    <div class="login-card">
        <h1 class="brand-title"><i class="fas fa-car-side"></i> EXCO DETAILING</h1>
        <p class="brand-subtitle">Silakan registrasi untuk melakukan reservasi</p>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    <span class="input-group-text" onclick="togglePassword()">👁</span>
                </div>
            </div>

            <div class="mb-3">
                <p class="form-text text-muted">Pastikan Nomor Telepon Dapat Dihubungi via WhatsApp/Telepon</p>
                <label for="No_Telepon" class="form-label">No Telepon</label>
                <input type="text" id="No_Telepon" name="No_Telepon" class="form-control" placeholder="Masukkan nomor telepon" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-login">Registrasi</button>
            <div class="register-text">Sudah punya akun? <a href="login.php">Login di sini</a></div>
        </form>
    </div>
</section>

<script>
function togglePassword() {
    const password = document.getElementById('password');
    password.type = password.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
