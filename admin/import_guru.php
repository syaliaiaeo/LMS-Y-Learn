<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST["import"])){
    $fileName = $_FILES["file_csv"]["tmp_name"];
    
    if($_FILES["file_csv"]["size"] > 0){
        $file = fopen($fileName, "r");
        $is_header = true;
        
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            if($is_header) {
                $is_header = false;
                continue;
            }
            
            // Urutan CSV: NIP, Nama Lengkap, Username, Password
            $nip      = mysqli_real_escape_string($koneksi, $column[0]);
            $nama     = mysqli_real_escape_string($koneksi, $column[1]);
            $username = mysqli_real_escape_string($koneksi, $column[2]);
            $password = mysqli_real_escape_string($koneksi, md5($column[3])); 
            $role     = 'guru'; // Otomatis diset sebagai guru
            $tahun_sekarang = date('Y') . '/' . (date('Y') + 1);

            // Cek apakah NIP atau Username sudah terdaftar
            $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE nip='$nip' OR username='$username'");
            if(mysqli_num_rows($cek) == 0){
                $sqlInsert = "INSERT INTO users (nip, nama_lengkap, username, password, role, tahun_masuk) 
                              VALUES ('$nip', '$nama', '$username', '$password', '$role', '$tahun_sekarang')";
                mysqli_query($koneksi, $sqlInsert);
            }
        }
        fclose($file);
        
        $_SESSION['sukses'] = "Data Guru berhasil diimport!";
        header("Location: data_guru.php");
        exit;
    } else {
        $_SESSION['error'] = "File CSV kosong atau format tidak valid!";
        header("Location: data_guru.php");
        exit;
    }
}
?>