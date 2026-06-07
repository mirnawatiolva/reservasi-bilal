-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Jun 2026 pada 08.26
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_car_detailing`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `email`) VALUES
(1, 'admin', '$2y$10$1BVyEmrGKs764OWvn8GssOxy9eMjD7iB2cjKGWykXOBThGJhPTV3q', 'admin@gmail.com');

-- --------------------------------------------------------

--
-- Struktur dari tabel `paket`
--

CREATE TABLE `paket` (
  `id_paket` int(11) NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `paket`
--

INSERT INTO `paket` (`id_paket`, `nama_paket`, `gambar`, `harga`, `deskripsi`) VALUES
(1, 'Paket Silver', 'asset/foto/paket_1776606764_9939.jpg', 3000000.00, 'Full Body Coating\r\nEngine Detailing\r\n2 Lapis Coating'),
(2, 'Paket Gold', 'asset/foto/paket_1776606771_8983.jpg', 3500000.00, 'Full Body Coating\r\nInterior Detailing\r\nEngine Detailing\r\n3 Lapis Coating'),
(3, 'Paket Platinum', 'asset/foto/paket_1776606870_8907.jpg', 4000000.00, 'Full Body Coating\r\nInterior Detailing\r\nEngine Detailing\r\nKaca Full Coating\r\nLampu Full Coating\r\nVelg Coating\r\n5 Lapis Coating'),
(4, 'Paket Premium', 'asset/foto/paket_1776607127_9948.jpg', 5000000.00, 'Full Body Coating\r\nInterior Detailing\r\nEngine Detailing\r\nKaca Full Coating\r\nLampu Full Coating\r\nVelg Coating\r\n5 Lapis Coating');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reservasi`
--

CREATE TABLE `reservasi` (
  `id_reservasi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_paket` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `status_dp` tinyint(3) UNSIGNED NOT NULL DEFAULT 100,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `schedule` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reservasi`
--

INSERT INTO `reservasi` (`id_reservasi`, `id_user`, `id_paket`, `status`, `status_dp`, `bukti_pembayaran`, `schedule`) VALUES
(3, 1, 4, 'Selesai', 100, 'asset/bukti_pembayaran/bukti_20260419_075143_7a884e6e.jpg', '2026-04-19 14:51:00'),
(4, 1, 2, 'Menunggu Verifikasi', 100, 'asset/bukti_pembayaran/bukti_20260419_170641_9554518a.jpg', '2026-04-19 22:06:00'),
(5, 1, 2, 'Menunggu Verifikasi', 100, 'asset/bukti_pembayaran/bukti_20260419_171051_29892797.jpg', '2026-04-20 09:00:00'),
(6, 1, 4, 'Cancel', 100, 'asset/bukti_pembayaran/bukti_20260420_171414_fddc6e88.png', '2026-04-21 22:14:00'),
(7, 2, 1, 'Selesai', 100, 'asset/bukti_pembayaran/bukti_20260505_154228_bd3db018.png', '2026-05-05 20:41:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tentang_kami`
--

CREATE TABLE `tentang_kami` (
  `id_tentang_kami` int(11) NOT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tentang_kami`
--

INSERT INTO `tentang_kami` (`id_tentang_kami`, `judul`, `foto`, `deskripsi`) VALUES
(1, 'Detailing Berstandar Profesional untuk Kendaraan Harian Hingga Premium', '1780590478_6a21a78e85213.jpg', '<div>Exco Detailing hadir sebagai partner perawatan kendaraan yang mengutamakan kualitas proses, ketelitian finishing, dan kepuasan pelanggan jangka panjang. Kami memadukan teknik detailing modern, material premium, serta SOP yang konsisten untuk menjaga tampilan mobil tetap prima.</div><div><br></div><div>Fokus kami bukan hanya membuat kendaraan terlihat bersih, tetapi juga menjaga nilai estetika dan proteksi cat agar lebih tahan terhadap cuaca, debu, dan pemakaian harian.</div><div><br></div><ul><li>Produk &amp; Material Premium Grade</li><li>Teknisi Bersertifikasi Internal</li><li>Proses QC Berlapis Sebelum Serah Terima</li><li>Konsultasi Paket Sesuai Kebutuhan Kendaraan</li></ul>');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `No_Telepon` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `email`, `No_Telepon`) VALUES
(1, 'bilal', '$2y$10$1BVyEmrGKs764OWvn8GssOxy9eMjD7iB2cjKGWykXOBThGJhPTV3q', 'bilal123@gmail.com', '089691212012'),
(2, 'tama', '$2y$10$ylQThUh.BJ06k.WNERPzKesLVf5DSb/mARHvgxcuUvAbX.Ao3WdSi', 'bilalytexn@gmail.com', '086585789');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `paket`
--
ALTER TABLE `paket`
  ADD PRIMARY KEY (`id_paket`);

--
-- Indeks untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_paket` (`id_paket`);

--
-- Indeks untuk tabel `tentang_kami`
--
ALTER TABLE `tentang_kami`
  ADD PRIMARY KEY (`id_tentang_kami`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `paket`
--
ALTER TABLE `paket`
  MODIFY `id_paket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id_reservasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `tentang_kami`
--
ALTER TABLE `tentang_kami`
  MODIFY `id_tentang_kami` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  ADD CONSTRAINT `reservasi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `reservasi_ibfk_2` FOREIGN KEY (`id_paket`) REFERENCES `paket` (`id_paket`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
