-- Migration script for db_car_detailing

CREATE DATABASE IF NOT EXISTS db_car_detailing;
USE db_car_detailing;

-- Table: admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL
);

-- Table: user
CREATE TABLE IF NOT EXISTS user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    No_Telepon VARCHAR(20) NOT NULL
);

-- Table: paket
CREATE TABLE IF NOT EXISTS paket (
    id_paket INT AUTO_INCREMENT PRIMARY KEY,
    nama_paket VARCHAR(100) NOT NULL,
    gambar VARCHAR(255),
    harga DECIMAL(15,2) NOT NULL,
    deskripsi TEXT
);

-- Table: reservasi
CREATE TABLE IF NOT EXISTS reservasi (
    id_reservasi INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_paket INT NOT NULL,
    status ENUM('Menunggu Verifikasi', 'Sedang Diproses', 'Diverifikasi', 'Selesai', 'Cancel') NOT NULL DEFAULT 'Menunggu Verifikasi',
    status_dp TINYINT UNSIGNED NOT NULL DEFAULT 100,
    bukti_pembayaran VARCHAR(255) NULL,
    schedule DATETIME NOT NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user),
    FOREIGN KEY (id_paket) REFERENCES paket(id_paket)
);

-- Sinkronisasi untuk DB yang sudah terlanjur dibuat sebelumnya
ALTER TABLE reservasi
    MODIFY COLUMN status ENUM('Menunggu Verifikasi', 'Sedang Diproses', 'Diverifikasi', 'Selesai', 'Cancel') NOT NULL DEFAULT 'Menunggu Verifikasi';

ALTER TABLE reservasi
    ADD COLUMN IF NOT EXISTS bukti_pembayaran VARCHAR(255) NULL AFTER status_dp;
