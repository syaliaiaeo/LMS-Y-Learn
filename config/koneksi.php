<?php
// Konfigurasi Database
$host       = "localhost";
$user       = "root"; // Username bawaan XAMPP
$password   = "";     // Password bawaan XAMPP (biasanya dikosongkan)
$database   = "lms_sekolah"; // Nama database yang kamu buat di phpMyAdmin sebelumnya

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $password, $database);

// Mengecek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// --- SISTEM DETEKSI TAHUN AJARAN GLOBAL ---
// Pastikan session sudah berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cari tahun ajaran yang statusnya 'Aktif'
$q_tahun_aktif = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran WHERE status='Aktif' LIMIT 1");
if ($tahun_aktif = mysqli_fetch_assoc($q_tahun_aktif)) {
    // Simpan ke dalam Session Global agar bisa dipanggil dari halaman manapun
    $_SESSION['id_tahun_aktif']   = $tahun_aktif['id_tahun'];
    $_SESSION['nama_tahun_aktif'] = $tahun_aktif['nama_tahun'];
    $_SESSION['semester_aktif']   = $tahun_aktif['semester'];
}

?>