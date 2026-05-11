<?php
session_start();
// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// PROSES IMPORT DATA JIKA TOMBOL DITEKAN
if (isset($_POST['import'])) {
    // Pastikan ada file yang diupload dan tidak ada error
    if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['error'] == 0) {
        
        $nama_file = $_FILES['file_csv']['name'];
        $tmp_file  = $_FILES['file_csv']['tmp_name'];
        $ekstensi  = pathinfo($nama_file, PATHINFO_EXTENSION);

        // Validasi hanya boleh file CSV
        if (strtolower($ekstensi) != 'csv') {
            $error = "Gagal! Format file wajib .csv";
        } else {
            // Buka file CSV
            $file = fopen($tmp_file, "r");
            $baris = 0;
            $berhasil = 0;
            $gagal = 0;

            // Looping isi file CSV baris demi baris
            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                $baris++;
                
                // Lewati baris pertama karena biasanya berisi judul kolom (Header Excel)
                if ($baris == 1) continue;

                // Tangkap data dari kolom (Indeks array dimulai dari 0)
                $username     = mysqli_real_escape_string($koneksi, trim($data[0])); // Kolom A
                $nama_lengkap = mysqli_real_escape_string($koneksi, trim($data[1])); // Kolom B
                $id_kelas     = mysqli_real_escape_string($koneksi, trim($data[2])); // Kolom C
                
                // Lewati baris jika username atau nama kosong
                if(empty($username) || empty($nama_lengkap)) continue;

                // Cek apakah username sudah ada di database untuk mencegah duplikat
                $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username='$username'");
                if (mysqli_num_rows($cek) == 0) {
                    // Hash password default untuk siswa baru
                    $password_hash = password_hash('siswa123', PASSWORD_DEFAULT);
                    
                    // Masukkan data ke database
                    $query = "INSERT INTO users (username, password, nama_lengkap, role, id_kelas) 
                              VALUES ('$username', '$password_hash', '$nama_lengkap', 'siswa', '$id_kelas')";
                    
                    if (mysqli_query($koneksi, $query)) {
                        $berhasil++;
                    } else {
                        $gagal++;
                    }
                } else {
                    $gagal++; // Dihitung gagal jika username bentrok
                }
            }
            fclose($file);

            // Jika ada data yang berhasil masuk, kembalikan ke halaman daftar siswa
            if ($berhasil > 0) {
                header("Location: data_siswa.php?pesan=sukses");
                exit;
            } else {
                $error = "Tidak ada data baru yang diimpor. Pastikan format benar atau data mungkin sudah ada.";
            }
        }
    } else {
        $error = "Silakan pilih file CSV terlebih dahulu.";
    }
}

include '../includes/header.php';
?>

<div class="mb-8">
    <a href="data_siswa.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-sekolah-600 transition-colors mb-4">
        <i class="ph ph-arrow-left"></i> Kembali ke Data Siswa
    </a>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Import Data Siswa (CSV)</h1>
    <p class="text-slate-500 text-sm mt-1">Tambahkan puluhan atau ratusan siswa sekaligus ke dalam sistem.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
            
            <?php if(isset($error)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                    <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div class="border-2 border-dashed border-slate-300 rounded-2xl p-10 text-center hover:bg-slate-50 transition-colors">
                    <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="ph ph-file-csv text-3xl font-bold"></i>
                    </div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File CSV Data Siswa</label>
                    <input type="file" name="file_csv" accept=".csv" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer mx-auto max-w-sm">
                    <p class="text-xs text-slate-400 mt-4">Ukuran maksimal file: 2MB.</p>
                </div>

                <hr class="border-slate-100">

                <div class="flex justify-end gap-3">
                    <a href="data_siswa.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Batal</a>
                    <button type="submit" name="import" class="px-6 py-3 bg-sekolah-600 text-white font-bold rounded-xl shadow-md hover:bg-sekolah-700 transition-all flex items-center gap-2">
                        <i class="ph ph-upload-simple text-lg"></i> Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div>
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="ph ph-info text-blue-500 text-xl"></i> Panduan Import
            </h3>
            <p class="text-sm text-slate-600 mb-4">Pastikan file Microsoft Excel Anda disimpan dengan format <b>CSV (Comma delimited)</b> dan susunan kolomnya persis seperti di bawah ini:</p>
            
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-4">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="p-2 border-r border-slate-200">Kolom A</th>
                            <th class="p-2 border-r border-slate-200">Kolom B</th>
                            <th class="p-2">Kolom C</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-500">
                        <tr class="border-b border-slate-100">
                            <td class="p-2 border-r border-slate-200 font-bold text-slate-700">Username</td>
                            <td class="p-2 border-r border-slate-200 font-bold text-slate-700">Nama Siswa</td>
                            <td class="p-2 font-bold text-slate-700">ID Kelas</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="p-2 border-r border-slate-200">0012345</td>
                            <td class="p-2 border-r border-slate-200">Budi Santoso</td>
                            <td class="p-2">1</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-slate-200">0012346</td>
                            <td class="p-2 border-r border-slate-200">Siti Aminah</td>
                            <td class="p-2">2</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-xs text-slate-500 space-y-2">
                <p><b>Penting:</b></p>
                <ul class="list-disc pl-4 space-y-1">
                    <li>Baris pertama (judul kolom) akan dilewati oleh sistem otomatis.</li>
                    <li>Sandi (password) siswa akan diatur menjadi <b>siswa123</b> secara otomatis.</li>
                    <li>Kolom C diisi dengan <b>Angka ID Kelas</b>, bukan nama kelasnya (contoh: isi angka 1 untuk kelas X-1). Lihat daftar angka ID di menu Ruang Kelas.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>