<?php
session_start();
require_once 'database/koneksi.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

$paketList = [];
$result = mysqli_query($conn, 'SELECT id_paket, nama_paket, gambar, harga, deskripsi FROM paket ORDER BY id_paket ASC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $paketList[] = $row;
    }
    mysqli_free_result($result);
}

$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exco Detailing - Detailing Mobil Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --pro-bg-1: #f4f6fb;
            --pro-bg-2: #fefefe;
            --pro-ink: #171717;
            --pro-red: #dc3545;
            --pro-red-soft: #ff6b78;
            --pro-glass: rgba(255, 255, 255, 0.16);
            --pro-border: rgba(255, 255, 255, 0.28);
            --pro-shadow-lg: 0 26px 65px rgba(8, 18, 42, 0.24);
            --pro-shadow-md: 0 14px 36px rgba(15, 23, 42, 0.17);
        }

        body {
            background:
                radial-gradient(circle at 12% 14%, rgba(220, 53, 69, 0.09), transparent 32%),
                radial-gradient(circle at 83% 20%, rgba(13, 110, 253, 0.09), transparent 26%),
                linear-gradient(175deg, var(--pro-bg-1) 0%, var(--pro-bg-2) 40%, #ffffff 100%);
            color: var(--pro-ink);
            overflow-x: clip;
        }

        section:not(.hero-section) {
            content-visibility: auto;
            contain-intrinsic-size: 1px 760px;
        }

        .navbar {
            background: linear-gradient(125deg, rgba(23, 23, 23, 0.91), rgba(48, 48, 48, 0.86)) !important;
            backdrop-filter: blur(6px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.17);
            box-shadow: 0 12px 30px rgba(10, 10, 10, 0.2);
        }

        .navbar .btn-danger {
            box-shadow: 0 10px 24px rgba(220, 53, 69, 0.38);
        }

        .site-bg-effects {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
            isolation: isolate;
        }

        .site-bg-effects::before {
            content: '';
            position: absolute;
            inset: -10%;
            background:
                radial-gradient(circle at 20% 18%, rgba(255, 255, 255, 0.28), transparent 25%),
                radial-gradient(circle at 78% 76%, rgba(255, 255, 255, 0.2), transparent 24%);
            filter: blur(16px);
            opacity: 0.52;
        }

        .site-bg-effects .orb {
            position: absolute;
            border-radius: 50%;
            opacity: 0.4;
            transform-style: preserve-3d;
            mix-blend-mode: normal;
            animation: floatOrb 26s ease-in-out infinite;
            box-shadow: inset 0 0 16px rgba(255, 255, 255, 0.16), 0 14px 32px rgba(17, 24, 39, 0.15);
        }

        .site-bg-effects .orb::before,
        .site-bg-effects .orb::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .site-bg-effects .orb.orb-1 {
            width: 360px;
            height: 360px;
            background: radial-gradient(circle at 34% 30%, rgba(168, 211, 255, 0.96), rgba(40, 112, 255, 0.6) 42%, rgba(24, 56, 118, 0.2) 68%, transparent 100%);
            top: -110px;
            left: -90px;
        }

        .site-bg-effects .orb.orb-1::before {
            inset: 16%;
            border: 1px solid rgba(255, 255, 255, 0.25);
            opacity: 0.75;
        }

        .site-bg-effects .orb.orb-1::after {
            width: 24%;
            height: 24%;
            top: 18%;
            left: 14%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0));
            filter: blur(1px);
        }

        .site-bg-effects .orb.orb-2 {
            width: 280px;
            height: 280px;
            background: radial-gradient(circle at 32% 30%, rgba(255, 182, 191, 0.95), rgba(220, 53, 69, 0.62) 44%, rgba(112, 20, 36, 0.18) 74%, transparent 100%);
            bottom: 10%;
            right: -65px;
            animation-delay: 0.9s;
        }

        .site-bg-effects .orb.orb-2::before {
            inset: 14%;
            border: 1px solid rgba(255, 224, 228, 0.42);
            transform: rotate(25deg);
        }

        .site-bg-effects .orb.orb-2::after {
            width: 20%;
            height: 20%;
            top: 22%;
            left: 20%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.62), rgba(255, 255, 255, 0));
        }

        .site-bg-effects .orb.orb-3 {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle at 35% 35%, rgba(181, 255, 233, 0.94), rgba(57, 195, 212, 0.58) 48%, rgba(17, 90, 101, 0.15) 78%, transparent 100%);
            top: 34%;
            right: 14%;
            animation-delay: 1.8s;
        }

        .site-bg-effects .orb.orb-3::before {
            inset: 18%;
            border: 1px solid rgba(219, 255, 245, 0.36);
        }

        .site-bg-effects .orb.orb-4 {
            width: 130px;
            height: 130px;
            background: radial-gradient(circle at 35% 30%, rgba(255, 255, 255, 0.88), rgba(232, 232, 232, 0.42) 45%, rgba(150, 150, 150, 0.06) 78%, transparent 100%);
            top: 66%;
            left: 11%;
            opacity: 0.25;
            animation-delay: 2.4s;
            animation-duration: 16s;
        }

        .site-bg-effects .halo {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.42);
            opacity: 0.35;
            animation: pulseHalo 12s ease-in-out infinite;
        }

        .site-bg-effects .halo.halo-1 {
            width: 420px;
            height: 420px;
            top: -150px;
            left: -120px;
            border-color: rgba(141, 197, 255, 0.48);
        }

        .site-bg-effects .halo.halo-2 {
            width: 320px;
            height: 320px;
            bottom: 2%;
            right: -95px;
            border-color: rgba(255, 151, 165, 0.45);
            animation-delay: -3s;
        }

        .site-bg-effects .halo.halo-3 {
            width: 210px;
            height: 210px;
            top: 32%;
            right: 11%;
            border-color: rgba(132, 239, 212, 0.44);
            animation-duration: 17s, 7s;
            animation-delay: -2.2s;
        }

        .hero-section {
            position: relative;
            overflow: hidden;
            z-index: 1;
            perspective: 1300px;
            isolation: isolate;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(0, 0, 0, 0.7), rgba(22, 25, 36, 0.26) 48%, rgba(220, 53, 69, 0.33));
            z-index: 1;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(to bottom, rgba(255, 255, 255, 0.12), transparent 28%),
                repeating-linear-gradient(
                    90deg,
                    rgba(255, 255, 255, 0.028) 0,
                    rgba(255, 255, 255, 0.028) 1px,
                    transparent 1px,
                    transparent 44px
                );
            mix-blend-mode: screen;
            z-index: 1;
            pointer-events: none;
        }

        .hero-overlay-shape {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            opacity: 0.35;
            filter: blur(1px);
        }

        .hero-overlay-shape.shape-a {
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(13, 110, 253, 0.7), rgba(13, 110, 253, 0));
            top: 12%;
            right: 8%;
            animation: drift 9s ease-in-out infinite;
        }

        .hero-overlay-shape.shape-b {
            width: 170px;
            height: 170px;
            background: radial-gradient(circle, rgba(220, 53, 69, 0.65), rgba(220, 53, 69, 0));
            bottom: 8%;
            left: 10%;
            animation: drift 11s ease-in-out infinite reverse;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            transform-style: preserve-3d;
            transition: transform 120ms linear;
            max-width: 930px;
            margin: 0 auto;
            padding: clamp(1.4rem, 3vw, 2.6rem);
            border-radius: 26px;
            background: linear-gradient(120deg, rgba(17, 17, 17, 0.68), rgba(17, 17, 17, 0.28));
            border: 1px solid rgba(255, 255, 255, 0.28);
            box-shadow: 0 24px 58px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(4px);
        }

        .hero-content::before {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.17);
            pointer-events: none;
        }

        .hero-section h1,
        .hero-section p,
        .hero-section .btn {
            will-change: transform;
        }

        .hero-section h1 {
            transition: transform 140ms linear;
            text-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
        }

        .hero-section p {
            transition: transform 180ms linear;
            color: rgba(245, 245, 245, 0.96);
        }

        .hero-section .btn {
            transition: transform 200ms linear;
        }

        .hero-title {
            color: #ff4a59 !important;
            letter-spacing: 0.5px;
        }

        .btn-hero {
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: 0 16px 38px rgba(220, 53, 69, 0.38);
        }

        .btn-hero::after {
            content: '';
            position: absolute;
            top: -130%;
            left: -35%;
            width: 42%;
            height: 350%;
            transform: rotate(18deg);
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.58), transparent);
            opacity: 0;
            transition: left 520ms ease, opacity 300ms ease;
        }

        .btn-hero:hover::after {
            left: 125%;
            opacity: 1;
        }

        .card,
        .p-4.border.rounded-4,
        .p-3 {
            border: 1px solid rgba(24, 24, 24, 0.06);
            box-shadow: var(--pro-shadow-md);
        }

        .tilt-card {
            position: relative;
            transform-style: preserve-3d;
            transition: transform 180ms ease, box-shadow 180ms ease;
            will-change: transform;
        }

        .tilt-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: radial-gradient(circle at var(--mx, 50%) var(--my, 50%), rgba(255, 255, 255, 0.26), transparent 45%);
            opacity: 0;
            transition: opacity 220ms ease;
            pointer-events: none;
        }

        .tilt-card:hover::before {
            opacity: 1;
        }

        .paket-card {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid rgba(20, 20, 20, 0.08);
            --tier-lift: -8px;
            --tier-glow: rgba(17, 24, 39, 0.22);
            --tier-shadow: 0 16px 34px rgba(15, 23, 42, 0.16);
            box-shadow: var(--tier-shadow);
            transition: transform 240ms ease, box-shadow 240ms ease;
        }

        .paket-card > * {
            position: relative;
            z-index: 1;
        }

        .paket-card .card-body,
        .paket-card .card-footer {
            transition: transform 220ms ease;
        }

        .paket-card:hover {
            transform: translateY(var(--tier-lift));
            box-shadow: 0 26px 50px var(--tier-glow);
        }

        .paket-card:hover .card-body,
        .paket-card:hover .card-footer {
            transform: translateY(-2px);
        }

        .paket-card.tier-1 {
            --tier-lift: -6px;
            --tier-glow: rgba(107, 114, 128, 0.26);
            --tier-shadow: 0 14px 30px rgba(93, 105, 120, 0.18);
        }

        .paket-card.tier-2 {
            --tier-lift: -8px;
            --tier-glow: rgba(184, 137, 24, 0.28);
            --tier-shadow: 0 15px 32px rgba(150, 118, 42, 0.2);
            border-color: rgba(184, 137, 24, 0.35);
        }

        .paket-card.tier-3 {
            --tier-lift: -10px;
            --tier-glow: rgba(102, 122, 146, 0.3);
            --tier-shadow: 0 16px 35px rgba(90, 106, 126, 0.22);
            border-color: rgba(102, 122, 146, 0.36);
        }

        .paket-card.tier-4 {
            --tier-lift: -12px;
            --tier-glow: rgba(194, 46, 66, 0.34);
            --tier-shadow: 0 18px 38px rgba(171, 41, 58, 0.24);
            border-color: rgba(194, 46, 66, 0.42);
        }

        .paket-card.tier-3::after,
        .paket-card.tier-4::after {
            content: '';
            position: absolute;
            inset: -34%;
            pointer-events: none;
            z-index: 0;
            opacity: 0;
            transition: opacity 260ms ease;
            border-radius: 40%;
        }

        .paket-card.tier-3::after {
            background: radial-gradient(circle at 24% 18%, rgba(218, 230, 245, 0.6), transparent 44%), radial-gradient(circle at 78% 82%, rgba(128, 147, 169, 0.38), transparent 48%);
        }

        .paket-card.tier-4::after {
            background: conic-gradient(from 0deg, rgba(255, 235, 209, 0.2), rgba(255, 167, 178, 0.28), rgba(255, 235, 209, 0.2));
            animation: spinPremiumAura 9s linear infinite;
        }

        .paket-card.tier-3:hover::after,
        .paket-card.tier-4:hover::after {
            opacity: 1;
        }

        .paket-card.tier-4 .paket-tier-chip {
            animation: pulsePremiumChip 2.4s ease-in-out infinite;
        }

        .paket-tier-chip {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 2;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.4px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 7px 18px rgba(0, 0, 0, 0.14);
        }

        .paket-card .card-header {
            position: relative;
            overflow: hidden;
        }

        .paket-card .card-header::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 20%, rgba(255, 255, 255, 0.4) 45%, transparent 72%);
            transform: translateX(-140%);
            transition: transform 560ms ease;
        }

        .paket-card:hover .card-header::after {
            transform: translateX(130%);
        }

        #tentang,
        #paket,
        section.bg-light {
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        #tentang,
        #paket {
            background-color: rgba(248, 249, 250, 0.78) !important;
        }

        #tentang .container,
        #paket .container,
        section.bg-light .container {
            position: relative;
            z-index: 2;
        }

        #tentang::before,
        #paket::before,
        section.bg-light::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 8% 24%, rgba(13, 110, 253, 0.22), transparent 22%),
                radial-gradient(circle at 94% 78%, rgba(220, 53, 69, 0.22), transparent 24%),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.26), rgba(255, 255, 255, 0));
            pointer-events: none;
            z-index: 0;
        }

        #tentang::after,
        #paket::after {
            content: '';
            position: absolute;
            inset: -6% -4%;
            background:
                radial-gradient(circle at 18% 72%, rgba(89, 170, 255, 0.28) 0, rgba(89, 170, 255, 0.12) 10%, transparent 23%),
                radial-gradient(circle at 80% 18%, rgba(255, 120, 136, 0.26) 0, rgba(255, 120, 136, 0.1) 11%, transparent 24%),
                radial-gradient(circle at 62% 62%, rgba(95, 224, 204, 0.2) 0, rgba(95, 224, 204, 0.08) 10%, transparent 22%);
            animation: sectionOrbDrift 18s ease-in-out infinite;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes floatOrb {
            0%, 100% {
                transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
            }
            50% {
                transform: translate3d(18px, -22px, 0) scale(1.08) rotate(5deg);
            }
        }

        @keyframes pulseHalo {
            0%, 100% {
                opacity: 0.28;
            }
            50% {
                opacity: 0.56;
            }
        }

        @keyframes drift {
            0%, 100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
            50% {
                transform: translate3d(12px, -14px, 0) scale(1.08);
            }
        }

        @keyframes sectionOrbDrift {
            0%, 100% {
                transform: translate3d(0, 0, 0);
            }
            50% {
                transform: translate3d(10px, -12px, 0);
            }
        }

        @keyframes spinPremiumAura {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes pulsePremiumChip {
            0%, 100% {
                box-shadow: 0 7px 18px rgba(0, 0, 0, 0.14);
            }
            50% {
                box-shadow: 0 10px 24px rgba(194, 46, 66, 0.28);
            }
        }

        .reveal-item {
            opacity: 0;
            transform: translate3d(0, 30px, 0);
            transition: opacity 700ms ease, transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--reveal-delay, 0ms);
            will-change: opacity, transform;
        }

        .reveal-item.reveal-left {
            transform: translate3d(-36px, 0, 0);
        }

        .reveal-item.reveal-right {
            transform: translate3d(36px, 0, 0);
        }

        .reveal-item.reveal-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }

        @media (prefers-reduced-motion: reduce) {
            .site-bg-effects .orb,
            .site-bg-effects .halo,
            .hero-overlay-shape,
            #tentang::after,
            #paket::after {
                animation: none;
            }

            .paket-card.tier-4::after,
            .paket-card.tier-4 .paket-tier-chip {
                animation: none;
            }

            .reveal-item,
            .reveal-item.reveal-left,
            .reveal-item.reveal-right,
            .reveal-item.reveal-visible {
                opacity: 1;
                transform: none;
                transition: none;
            }

            .tilt-card,
            .tilt-card:hover {
                transform: none !important;
            }
        }

        @media (max-width: 991.98px) {
            .site-bg-effects .halo.halo-2,
            .site-bg-effects .halo.halo-3,
            .site-bg-effects .orb.orb-4 {
                display: none;
            }

            .hero-section {
                min-height: 540px !important;
            }

            .hero-content {
                padding: 1.25rem 1.1rem;
            }

            .hero-title {
                font-size: clamp(2rem, 7vw, 2.8rem);
            }
        }

        @media (max-width: 575.98px) {
            .site-bg-effects {
                opacity: 0.65;
            }

            .site-bg-effects .orb.orb-1 {
                width: 260px;
                height: 260px;
            }

            .site-bg-effects .orb.orb-2 {
                width: 220px;
                height: 220px;
            }

            .site-bg-effects .orb.orb-3 {
                width: 150px;
                height: 150px;
                right: 5%;
            }

            .site-bg-effects .halo.halo-1 {
                width: 280px;
                height: 280px;
                left: -110px;
            }

            .site-bg-effects .halo.halo-2 {
                width: 210px;
                height: 210px;
                display: none;
            }

            .site-bg-effects .halo.halo-3 {
                width: 140px;
                height: 140px;
                right: -6%;
                display: none;
            }

            .hero-section {
                min-height: 500px !important;
            }

            .hero-content {
                border-radius: 18px;
            }

            .hero-content::before {
                border-radius: 16px;
            }
        }
    </style>
</head>
<body>
<div class="site-bg-effects" aria-hidden="true">
    <span class="orb orb-1"></span>
    <span class="orb orb-2"></span>
    <span class="orb orb-3"></span>
    <span class="orb orb-4"></span>
    <span class="halo halo-1"></span>
    <span class="halo halo-2"></span>
    <span class="halo halo-3"></span>
</div>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: linear-gradient(135deg, #444444 0%, #2d2d2d 100%);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-car-side text-danger"></i> Exco Detailing
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-3 align-items-lg-center">
                <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="#paket">Paket</a></li>
                <li class="nav-item"><a class="nav-link" href="reservasi.php">Reservasi</a></li>
                <li class="nav-item"><a class="nav-link" href="riwayat_booking.php">Riwayat Booking</a></li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><span class="nav-link text-light"><i class="fas fa-user"></i> <?php echo h($username); ?></span></li>
                    <li class="nav-item"><a class="btn btn-sm btn-outline-light" href="index.php?logout=1">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-sm btn-danger" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<section class="hero-section text-white text-center py-5" style="background: url('asset/foto/bg.jpg'); background-size: cover; background-position: center; min-height: 620px; display: flex; align-items: center;">
    <div class="hero-overlay-shape shape-a" aria-hidden="true"></div>
    <div class="hero-overlay-shape shape-b" aria-hidden="true"></div>
    <div class="container">
        <div class="hero-content" id="heroParallax">
        <h1 class="display-2 fw-bold mb-3 hero-title">Detailing Mobil Premium</h1>
        <p class="lead mb-3" style="font-size: 1.24rem;">Transformasi kendaraan Anda dengan layanan detailing terbaik, presisi showroom-level, dan sentuhan profesional berkelas.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="reservasi.php" class="btn btn-danger btn-lg px-5 fw-bold btn-hero"><i class="fas fa-calendar-check"></i> Reservasi Sekarang</a>
        </div>
        </div>
    </div>
</section>

<section id="tentang" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold" style="color: #dc3545;"><i class="fas fa-info-circle"></i> Tentang Kami</h2>
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <img src="asset/foto/tentang.jpg" alt="Tentang Kami" class="img-fluid rounded-lg shadow" loading="lazy" decoding="async">
            </div>
            <div class="col-lg-6">
                <h3 class="mb-3 fw-bold">Detailing Berstandar Profesional untuk Kendaraan Harian Hingga Premium</h3>
                <p class="text-muted mb-3">Exco Detailing hadir sebagai partner perawatan kendaraan yang mengutamakan kualitas proses, ketelitian finishing, dan kepuasan pelanggan jangka panjang. Kami memadukan teknik detailing modern, material premium, serta SOP yang konsisten untuk menjaga tampilan mobil tetap prima.</p>
                <p class="text-muted mb-3">Fokus kami bukan hanya membuat kendaraan terlihat bersih, tetapi juga menjaga nilai estetika dan proteksi cat agar lebih tahan terhadap cuaca, debu, dan pemakaian harian.</p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="fas fa-check-circle text-danger"></i> Produk & Material Premium Grade</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-danger"></i> Teknisi Bersertifikasi Internal</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-danger"></i> Proses QC Berlapis Sebelum Serah Terima</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-danger"></i> Konsultasi Paket Sesuai Kebutuhan Kendaraan</li>
                </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="paket" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold" style="color: #dc3545;"><i class="fas fa-list"></i> Paket Layanan Kami</h2>
        <div class="row g-4 mb-5">
            <?php if (count($paketList) === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">Belum ada data paket.</div>
                </div>
            <?php endif; ?>

            <?php foreach ($paketList as $idx => $paket): ?>
                <?php
                $paketNameLower = strtolower((string) $paket['nama_paket']);
                $headerStyle = 'background: linear-gradient(135deg, #f2f2f2 0%, #dcdcdc 100%); color: #121212;';
                $accentColor = '#dc3545';
                $buttonStyle = 'color: #dc3545; border-color: #dc3545;';
                $tierLevel = min(4, $idx + 1);
                $tierLabel = 'CLASSIC';
                $tierChipStyle = 'background: rgba(255,255,255,0.84); color: #212529;';

                if (strpos($paketNameLower, 'silver') !== false) {
                    $tierLevel = 1;
                    $tierLabel = 'SILVER';
                    $headerStyle = 'background: linear-gradient(135deg, #f7f9fc 0%, #cfd6de 100%); color: #1f2730;';
                    $accentColor = '#7c8794';
                    $buttonStyle = 'color: #6f7b89; border-color: #6f7b89;';
                    $tierChipStyle = 'background: linear-gradient(135deg, #f7f9fc, #d8dee6); color: #44515f;';
                } elseif (strpos($paketNameLower, 'gold') !== false) {
                    $tierLevel = 2;
                    $tierLabel = 'GOLD';
                    $headerStyle = 'background: linear-gradient(135deg, #fff7d6 0%, #dbb24c 100%); color: #342000;';
                    $accentColor = '#b88918';
                    $buttonStyle = 'color: #a9780c; border-color: #b88918;';
                    $tierChipStyle = 'background: linear-gradient(135deg, #fff7d6, #e9c56c); color: #6f4b00;';
                } elseif (strpos($paketNameLower, 'platinum') !== false) {
                    $tierLevel = 3;
                    $tierLabel = 'PLATINUM';
                    $headerStyle = 'background: linear-gradient(135deg, #f1f5fb 0%, #9eb0c4 100%); color: #15202b;';
                    $accentColor = '#667a92';
                    $buttonStyle = 'color: #5b6f86; border-color: #5b6f86;';
                    $tierChipStyle = 'background: linear-gradient(135deg, #f1f5fb, #b6c4d3); color: #324558;';
                } elseif (strpos($paketNameLower, 'premium') !== false) {
                    $tierLevel = 4;
                    $tierLabel = 'PREMIUM';
                    $headerStyle = 'background: linear-gradient(135deg, #ffe4e8 0%, #ff9fad 100%); color: #4a0a14;';
                    $accentColor = '#c22e42';
                    $buttonStyle = 'background: linear-gradient(135deg, #d93449, #b62437); color: #fff; border-color: #b62437;';
                    $tierChipStyle = 'background: linear-gradient(135deg, #ffd6dc, #ffadb8); color: #7b1323;';
                }

                if ($tierLevel === 3 && strpos($paketNameLower, 'platinum') === false) {
                    $tierLabel = 'SIGNATURE';
                }
                if ($tierLevel === 4 && strpos($paketNameLower, 'premium') === false) {
                    $tierLabel = 'ELITE';
                }
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-lg h-100 paket-card tilt-card tier-<?php echo (int) $tierLevel; ?>" style="transition: all 0.3s ease;">
                        <div class="card-header p-4" style="<?php echo h($headerStyle); ?>">
                            <span class="paket-tier-chip" style="<?php echo h($tierChipStyle); ?>"><?php echo h($tierLabel); ?></span>
                            <h5 class="card-title mb-2 fw-bold"><?php echo h($paket['nama_paket']); ?></h5>
                            <p class="mb-0" style="opacity: 0.85;">Paket Layanan</p>
                        </div>
                        <div class="card-body flex-grow-1">
                            <?php if (!empty($paket['gambar'])): ?>
                                <img src="<?php echo h($paket['gambar']); ?>" alt="<?php echo h($paket['nama_paket']); ?>" class="img-fluid rounded mb-3" style="height: 140px; width: 100%; object-fit: cover;" loading="lazy" decoding="async">
                            <?php endif; ?>
                            <div class="mb-3">
                                <h4 class="fw-bold" style="color: <?php echo h($accentColor); ?>;">Rp<?php echo number_format((float) $paket['harga'], 0, ',', '.'); ?></h4>
                            </div>
                            <ul class="list-unstyled mb-3">
                                <?php
                                $fiturList = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $paket['deskripsi'])));
                                if (count($fiturList) === 0) {
                                    echo '<li class="mb-1"><i class="fas fa-check-circle" style="color:' . h($accentColor) . ';"></i> Paket detailing</li>';
                                } else {
                                    foreach ($fiturList as $fitur) {
                                        echo '<li class="mb-1"><i class="fas fa-check-circle" style="color:' . h($accentColor) . ';"></i> ' . h($fitur) . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="card-footer border-0 bg-transparent p-4 pt-0">
                            <a href="reservasi.php?paket_id=<?php echo (int) $paket['id_paket']; ?>" class="btn w-100" style="<?php echo h($buttonStyle); ?>">Pilih Paket</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5" style="background: #ffffff;">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="p-4 border rounded-4 h-100 shadow-sm tilt-card">
                    <i class="fas fa-shield-alt fa-2x text-danger mb-3"></i>
                    <h5 class="fw-bold">Produk Terjamin</h5>
                    <p class="text-muted mb-0">Menggunakan chemical premium yang aman untuk cat kendaraan.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4 border rounded-4 h-100 shadow-sm tilt-card">
                    <i class="fas fa-user-check fa-2x text-danger mb-3"></i>
                    <h5 class="fw-bold">Teknisi Ahli</h5>
                    <p class="text-muted mb-0">Ditangani oleh tim berpengalaman dengan SOP profesional.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4 border rounded-4 h-100 shadow-sm tilt-card">
                    <i class="fas fa-stopwatch fa-2x text-danger mb-3"></i>
                    <h5 class="fw-bold">Proses Cepat</h5>
                    <p class="text-muted mb-0">Estimasi kerja jelas dan transparan sesuai kebutuhan paket.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-4 border rounded-4 h-100 shadow-sm tilt-card">
                    <i class="fas fa-star fa-2x text-danger mb-3"></i>
                    <h5 class="fw-bold">Hasil Maksimal</h5>
                    <p class="text-muted mb-0">Finishing mengkilap dengan kualitas hasil yang konsisten.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold" style="color: #dc3545;"><i class="fas fa-cogs"></i> Proses  Detailing Kami</h2>
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="p-3">
                    <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">1</div>
                    <h6 class="fw-bold">Inspeksi Awal</h6>
                    <p class="text-muted small mb-0">Pengecekan detail kondisi bodi, interior, dan area mesin.</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-3">
                    <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">2</div>
                    <h6 class="fw-bold">Deep Cleaning</h6>
                    <p class="text-muted small mb-0">Pembersihan menyeluruh untuk mengangkat kotoran dan jamur.</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-3">
                    <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">3</div>
                    <h6 class="fw-bold">Polishing</h6>
                    <p class="text-muted small mb-0">Tahap polishing untuk mengembalikan kilap dan warna cat.</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-3">
                    <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">4</div>
                    <h6 class="fw-bold">Final Protection</h6>
                    <p class="text-muted small mb-0">Pelapisan coating untuk proteksi tahan lama.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- <section class="py-5" style="background: #ffffff;">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold" style="color: #dc3545;"><i class="fas fa-comments"></i> Testimoni Pelanggan</h2>
        <div class="row g-4">
            <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 tilt-card">
                    <div class="card-body p-4">
                        <p class="text-muted">"Mobil saya seperti baru keluar showroom. Detail dan finishing-nya rapi banget."</p>
                        <h6 class="fw-bold mb-0">Rizki A.</h6>
                        <small class="text-danger">Pelanggan Paket Platinum</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 tilt-card">
                    <div class="card-body p-4">
                        <p class="text-muted">"Pelayanannya cepat, admin responsif, dan hasil coating tahan lama."</p>
                        <h6 class="fw-bold mb-0">Fauzan M.</h6>
                        <small class="text-danger">Pelanggan Paket Gold</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 tilt-card">
                    <div class="card-body p-4">
                        <p class="text-muted">"Worth it banget. Interior jadi bersih, wangi, dan nyaman dipakai harian."</p>
                        <h6 class="fw-bold mb-0">Dina K.</h6>
                        <small class="text-danger">Pelanggan Paket Premium</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<section class="py-5" style="background: linear-gradient(135deg, #1f1f1f 0%, #000 100%);">
    <div class="container text-center text-white">
        <h2 class="fw-bold text-light mb-3">Siap Bikin Mobil Anda Tampil Maksimal?</h2>
        <p class="mb-4 text-light">Pilih paket terbaik dan booking sekarang untuk jadwal detailing terdekat.</p>
        <a href="reservasi.php" class="btn btn-danger btn-lg px-5 fw-bold"><i class="fas fa-calendar-alt"></i> Booking Sekarang</a>
    </div>
</section>

<footer class="py-4 text-white" style="background-color: #fdfdfd;">
    <div class="container">
        <div class="text-center text-muted">
            <p>&copy; 2026 Exco Detailing. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const hero = document.querySelector('.hero-section');
        const content = document.getElementById('heroParallax');
        const coarsePointer = window.matchMedia('(pointer: coarse)').matches;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const lowEndDevice = (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4) || (navigator.deviceMemory && navigator.deviceMemory <= 4);

        if (!hero || !content || coarsePointer || reduceMotion || lowEndDevice) {
            return;
        }

        const heading = content.querySelector('h1');
        const subtitle = content.querySelector('p');
        const cta = content.querySelector('.btn');
        let rafId = 0;
        let mouseX = 0;
        let mouseY = 0;

        function applyHeroTransform() {
            rafId = 0;
            const rotateX = mouseY * -8;
            const rotateY = mouseX * 10;

            content.style.transform = 'rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg)';
            if (heading) {
                heading.style.transform = 'translate3d(' + (mouseX * 16) + 'px,' + (mouseY * 12) + 'px,40px)';
            }
            if (subtitle) {
                subtitle.style.transform = 'translate3d(' + (mouseX * 10) + 'px,' + (mouseY * 8) + 'px,28px)';
            }
            if (cta) {
                cta.style.transform = 'translate3d(' + (mouseX * 8) + 'px,' + (mouseY * 6) + 'px,20px)';
            }
        }

        hero.addEventListener('mousemove', function (event) {
            const rect = hero.getBoundingClientRect();
            mouseX = (event.clientX - rect.left) / rect.width - 0.5;
            mouseY = (event.clientY - rect.top) / rect.height - 0.5;

            if (!rafId) {
                rafId = requestAnimationFrame(applyHeroTransform);
            }
        }, { passive: true });

        hero.addEventListener('mouseleave', function () {
            if (rafId) {
                cancelAnimationFrame(rafId);
                rafId = 0;
            }
            content.style.transform = 'rotateX(0deg) rotateY(0deg)';
            if (heading) heading.style.transform = 'translate3d(0,0,0)';
            if (subtitle) subtitle.style.transform = 'translate3d(0,0,0)';
            if (cta) cta.style.transform = 'translate3d(0,0,0)';
        });
    })();

    (function () {
        const revealTargets = document.querySelectorAll(
            '#tentang .col-lg-6, #tentang .col-md-4, #paket .col-md-6, section .col-md-3 .p-4, section .col-md-3 .p-3, section .col-md-4 .card, section .container > h2, section .container > .row'
        );

        if (!revealTargets.length) {
            return;
        }

        revealTargets.forEach(function (el, index) {
            el.classList.add('reveal-item');

            if (index % 3 === 0) {
                el.classList.add('reveal-left');
            } else if (index % 3 === 2) {
                el.classList.add('reveal-right');
            }

            el.style.setProperty('--reveal-delay', (index % 6) * 80 + 'ms');
        });

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                },
                {
                    threshold: 0.16,
                    rootMargin: '0px 0px -40px 0px'
                }
            );

            revealTargets.forEach(function (el) {
                observer.observe(el);
            });
            return;
        }

        revealTargets.forEach(function (el) {
            el.classList.add('reveal-visible');
        });
    })();

    (function () {
        const tiltCards = document.querySelectorAll('.tilt-card');
        const mobileLike = window.matchMedia('(max-width: 991.98px)').matches;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const lowEndDevice = (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4) || (navigator.deviceMemory && navigator.deviceMemory <= 4);

        if (!tiltCards.length || mobileLike || reduceMotion || lowEndDevice) {
            return;
        }

        Array.prototype.slice.call(tiltCards, 0, 8).forEach(function (card) {
            let rafId = 0;
            let rotateX = 0;
            let rotateY = 0;
            let mx = '50%';
            let my = '50%';

            function applyTilt() {
                rafId = 0;
                card.style.transform = 'perspective(900px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateY(-4px)';
                card.style.setProperty('--mx', mx);
                card.style.setProperty('--my', my);
                card.style.boxShadow = '0 22px 46px rgba(15, 23, 42, 0.2)';
            }

            card.addEventListener('mousemove', function (event) {
                const rect = card.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                const px = x / rect.width;
                const py = y / rect.height;
                rotateY = (px - 0.5) * 10;
                rotateX = (0.5 - py) * 8;
                mx = Math.round(px * 100) + '%';
                my = Math.round(py * 100) + '%';

                if (!rafId) {
                    rafId = requestAnimationFrame(applyTilt);
                }
            }, { passive: true });

            card.addEventListener('mouseleave', function () {
                if (rafId) {
                    cancelAnimationFrame(rafId);
                    rafId = 0;
                }
                card.style.transform = 'perspective(900px) rotateX(0deg) rotateY(0deg) translateY(0)';
                card.style.boxShadow = '';
            });
        });
    })();
</script>
</body>
</html>
