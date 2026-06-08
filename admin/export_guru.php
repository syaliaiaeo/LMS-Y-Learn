<?php
include '../config/koneksi.php';

// Setting header agar browser mendownload file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Data_Guru_Export_' . date('Ymd') . '.csv');

// Buka koneksi output
$output = fopen("php://output", "w");

// Tulis Baris Pertama (Header Kolom)
fputcsv($output, array('NIP', 'Nama Lengkap', 'Username', 'Mata Pelajaran', 'Tahun Masuk', 'Tahun Keluar'));

// Query data guru
$query = "
    SELECT u.nip, u.nama_lengkap, u.username, mp.nama_mapel, u.tahun_masuk, u.tahun_keluar 
    FROM users u
    LEFT JOIN mapel mp ON u.id_mapel = mp.id_mapel
    WHERE u.role = 'guru' 
    ORDER BY u.nama_lengkap ASC
";
$result = mysqli_query($koneksi, $query);

// Looping untuk memasukkan data ke dalam CSV
while($row = mysqli_fetch_assoc($result)) {
    // Jika null, ganti menjadi string kosong
    $row['nama_mapel'] = $row['nama_mapel'] ? $row['nama_mapel'] : 'Belum diatur';
    fputcsv($output, $row);
}

fclose($output);
exit();
?>