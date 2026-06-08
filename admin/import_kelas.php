<?php
session_start();
include '../config/koneksi.php';

// Cek apakah ada file yang dikirim
if(isset($_POST["import"])){
    
    $fileName = $_FILES["file_csv"]["tmp_name"];
    
    // Pastikan ukuran file lebih dari 0
    if($_FILES["file_csv"]["size"] > 0){
        
        $file = fopen($fileName, "r");
        $is_header = true;
        
        // Membaca file CSV baris per baris
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            
            // Melewati baris pertama (Header Kolom)
            if($is_header) {
                $is_header = false;
                continue;
            }
            
            // Asumsi format CSV hanya 1 kolom: Nama Kelas
            $nama_kelas = mysqli_real_escape_string($koneksi, trim($column[0]));
            
            // Mencegah input baris kosong
            if(!empty($nama_kelas)) {
                // Cek agar tidak ada nama kelas yang duplikat / ganda
                $cek = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE nama_kelas = '$nama_kelas'");
                
                if(mysqli_num_rows($cek) == 0){
                    // Insert data kelas
                    $sqlInsert = "INSERT INTO kelas (nama_kelas) VALUES ('$nama_kelas')";
                    mysqli_query($koneksi, $sqlInsert);
                }
            }
        }
        fclose($file);
        
        // Beri pesan sukses dan arahkan kembali
        $_SESSION['sukses'] = "Data Kelas berhasil diimport secara massal!";
        header("Location: data_kelas.php");
        exit;
        
    } else {
        $_SESSION['error'] = "File CSV kosong atau format tidak valid!";
        header("Location: data_kelas.php");
        exit;
    }
}
?>