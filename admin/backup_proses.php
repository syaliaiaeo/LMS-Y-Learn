<?php
session_start();
// Pastikan hanya admin yang bisa melakukan backup
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Akses ditolak.");
}

require_once '../config/koneksi.php';

// 1. Ambil semua daftar tabel dari database
$tables = array();
$result = mysqli_query($koneksi, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

$return = "-- LMS YAPAN Database Backup\n";
$return .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

// 2. Loop setiap tabel untuk mengambil struktur dan datanya
foreach ($tables as $table) {
    // Ambil perintah CREATE TABLE
    $result = mysqli_query($koneksi, "SHOW CREATE TABLE $table");
    $row = mysqli_fetch_row($result);
    $return .= "\n\n" . $row[1] . ";\n\n";

    // Ambil semua isi data tabel
    $result = mysqli_query($koneksi, "SELECT * FROM $table");
    $num_fields = mysqli_num_fields($result);

    for ($i = 0; $i < $num_fields; $i++) {
        while ($row = mysqli_fetch_row($result)) {
            $return .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) {
                    $return .= '"' . $row[$j] . '"';
                } else {
                    $return .= '""';
                }
                if ($j < ($num_fields - 1)) {
                    $return .= ',';
                }
            }
            $return .= ");\n";
        }
    }
    $return .= "\n\n\n";
}

// 3. Header untuk memaksa browser mengunduh file
$fileName = 'backup_lms_' . date('d-m-Y') . '_' . time() . '.sql';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $fileName . "\"");

echo $return;
exit;
