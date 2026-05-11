<?php
// Mulai sesi
session_start();

// Panggil file koneksi database
// Karena posisinya di luar, pemanggilannya cukup masuk ke folder config
require_once 'config/koneksi.php';

// Pastikan data dikirim melalui metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Tangkap data yang dikirim dari form login
    // Gunakan real_escape_string untuk mencegah serangan SQL Injection
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Cari user berdasarkan username di tabel users
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");

    // Cek apakah username ditemukan (jumlah baris > 0)
    if (mysqli_num_rows($query) > 0) {
        
        // Ambil data user tersebut
        $data = mysqli_fetch_assoc($query);

        // Verifikasi kesesuaian password yang diketik dengan password yang di-hash di database
        if (password_verify($password, $data['password'])) {
            
            // ========================================================
            // PENJAGA STATUS: CEK APAKAH AKUN NONAKTIF
            // ========================================================
            if ($data['status'] == 'nonaktif') {
                // Jika nonaktif, tendang kembali ke halaman login dengan pesan error khusus
                $_SESSION['error_login'] = "Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.";
                header("Location: index.php");
                exit();
            }
            // ========================================================

            // Jika cocok dan status aktif, buat sesi (session) untuk menyimpan data pengguna
            $_SESSION['id_user']  = $data['id_user']; 
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama']     = $data['nama_lengkap'];
            $_SESSION['role']     = $data['role'];

            // Arahkan (redirect) pengguna ke halaman dashboard sesuai role-nya
            if ($data['role'] == 'admin') {
                header("Location: admin/index.php");
            } else if ($data['role'] == 'guru') {
                header("Location: guru/index.php");
            } else if ($data['role'] == 'siswa') {
                
                // Menyimpan ID Kelas khusus untuk siswa agar sistem tahu ia berada di kelas mana
                $_SESSION['id_kelas'] = $data['id_kelas'];
                
                header("Location: siswa/index.php");
            } else {
                // Jika role tidak dikenali (mencegah bug)
                $_SESSION['error_login'] = "Hak akses tidak dikenali.";
                header("Location: index.php");
            }
            exit();

        } else {
            // Jika password salah
            $_SESSION['error_login'] = "Kredensial tidak valid. Kata sandi yang Anda masukkan salah.";
            header("Location: index.php");
            exit();
        }
    } else {
        // Jika username tidak ditemukan di database
        $_SESSION['error_login'] = "Kredensial tidak valid. Username tidak ditemukan.";
        header("Location: index.php");
        exit();
    }
} else {
    // Jika file ini diakses langsung melalui URL tanpa menekan tombol login
    header("Location: index.php");
    exit();
}
?>