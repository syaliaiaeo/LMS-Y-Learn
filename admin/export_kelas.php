<?php
include '../config/koneksi.php';

// Atur header untuk memaksa browser mengunduh sebagai CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Data_Ruang_Kelas_' . date('Ymd') . '.csv');

// Buka koneksi output
$output = fopen("php://output", "w");

// Tulis baris pertama (Header) di dalam CSV
fputcsv($output, array('Nama Ruang Kelas', 'Jumlah Siswa Terdaftar'));

// Query data kelas beserta jumlah siswanya
$query_kelas = "
    SELECT k.nama_kelas, 
    (SELECT COUNT(id_user) FROM users WHERE role='siswa' AND id_kelas = k.id_kelas) as jml_siswa 
    FROM kelas k 
    ORDER BY k.nama_kelas ASC
";
$result = mysqli_query($koneksi, $query_kelas);

// Looping untuk memasukkan baris data ke dalam CSV
while($row = mysqli_fetch_assoc($result)) {
    // Format array sesuai dengan header di atas
    fputcsv($output, array(
        $row['nama_kelas'],
        $row['jml_siswa'] . ' Siswa'
    ));
}

fclose($output);
exit();
?>