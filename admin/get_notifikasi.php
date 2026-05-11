<?php
session_start();
require_once '../config/koneksi.php';

// Ambil notifikasi 5 terbaru
$query = mysqli_query($koneksi, "SELECT * FROM notifikasi ORDER BY waktu DESC LIMIT 5");

$data_notif = [];
$belum_dibaca = 0;

while($row = mysqli_fetch_assoc($query)) {
    $data_notif[] = $row;
    if($row['status'] == 'belum_dibaca') {
        $belum_dibaca++;
    }
}

// Ubah format output menjadi JSON agar mudah dibaca oleh JavaScript
header('Content-Type: application/json');
echo json_encode([
    'jumlah_baru' => $belum_dibaca,
    'notifikasi' => $data_notif
]);
?>