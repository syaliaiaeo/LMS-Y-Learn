<?php
session_start();
// Pastikan hanya admin yang bisa mengunduh laporan
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// Trik ajaib: Memaksa browser mengunduh file ini sebagai Excel (.xls)
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Data_Akademik_YAPAN_" . date('dmY') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Akademik YAPAN</title>
    <style>
        /* Styling dasar agar di dalam Excel tetap terlihat rapi */
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <center>
        <h2>REKAPITULASI DATA AKADEMIK SMA YAPAN INDONESIA</h2>
        <p>Tanggal Unduh: <?php echo date('d-m-Y H:i'); ?></p>
    </center>
    <br>

    <h3>1. DAFTAR TENAGA PENGAJAR (GURU)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap & Gelar</th>
                <th>Username Login</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no_guru = 1;
            $q_guru = mysqli_query($koneksi, "SELECT * FROM users WHERE role='guru' ORDER BY nama_lengkap ASC");
            while($guru = mysqli_fetch_assoc($q_guru)):
            ?>
            <tr>
                <td><?php echo $no_guru++; ?></td>
                <td><?php echo htmlspecialchars($guru['nama_lengkap']); ?></td>
                <td><?php echo htmlspecialchars($guru['username']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br><br>

    <h3>2. DAFTAR PESERTA DIDIK (SISWA)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa Sesuai Akta</th>
                <th>Username (NISN)</th>
                <th>Penempatan Kelas</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no_siswa = 1;
            // Query JOIN untuk mendapatkan nama kelas siswa
            $q_siswa = mysqli_query($koneksi, "
                SELECT users.nama_lengkap, users.username, kelas.nama_kelas 
                FROM users 
                LEFT JOIN kelas ON users.id_kelas = kelas.id_kelas 
                WHERE users.role='siswa' 
                ORDER BY kelas.nama_kelas ASC, users.nama_lengkap ASC
            ");
            while($siswa = mysqli_fetch_assoc($q_siswa)):
            ?>
            <tr>
                <td><?php echo $no_siswa++; ?></td>
                <td><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                <td><?php echo htmlspecialchars($siswa['username']); ?></td>
                <td><?php echo $siswa['nama_kelas'] ? 'Kelas ' . htmlspecialchars($siswa['nama_kelas']) : 'Belum Ada Kelas'; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>