<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit; }

require_once '../config/koneksi.php';

// Menangkap jenis data yang ingin dihapus dan ID-nya
$jenis = $_GET['jenis'];
$id    = $_GET['id'];

if ($jenis == 'guru') {
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id' AND role='guru'");
    header("Location: data_guru.php");
} elseif ($jenis == 'siswa') {
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id' AND role='siswa'");
    header("Location: data_siswa.php");
} elseif ($jenis == 'kelas') {
    mysqli_query($koneksi, "DELETE FROM kelas WHERE id_kelas='$id'");
    header("Location: data_kelas.php");
} elseif ($jenis == 'mapel') {
    mysqli_query($koneksi, "DELETE FROM mapel WHERE id_mapel='$id'");
    header("Location: data_mapel.php");
} else {
    // Jika ada yang iseng mengubah URL
    header("Location: index.php");
}
?>