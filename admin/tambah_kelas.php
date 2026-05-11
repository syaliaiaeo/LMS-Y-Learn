<?php
session_start();
// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// PROSES SIMPAN DATA
if (isset($_POST['simpan'])) {
    $nama_kelas = trim(mysqli_real_escape_string($koneksi, $_POST['nama_kelas']));

    // Validasi input tidak boleh kosong
    if (empty($nama_kelas)) {
        $error = "Nama kelas wajib diisi!";
    } else {
        // Cek apakah nama kelas tersebut sudah pernah dibuat sebelumnya
        $cek = mysqli_query($koneksi, "SELECT * FROM kelas WHERE nama_kelas='$nama_kelas'");
        
        if (mysqli_num_rows($cek) > 0) {
            $error = "Ruang Kelas '$nama_kelas' sudah ada di sistem!";
        } else {
            // Jika belum ada, masukkan ke database
            $query = "INSERT INTO kelas (nama_kelas) VALUES ('$nama_kelas')";
            
            if (mysqli_query($koneksi, $query)) {
                // Jika berhasil, arahkan kembali ke tabel data_kelas dengan pesan sukses
                header("Location: data_kelas.php?pesan=sukses");
                exit;
            } else {
                $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="mb-8">
    <a href="data_kelas.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-sekolah-600 transition-colors mb-4">
        <i class="ph ph-arrow-left"></i> Kembali ke Data Ruang Kelas
    </a>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Ruang Kelas</h1>
    <p class="text-slate-500 text-sm mt-1">Buat ruang kelas baru untuk menempatkan siswa dan jadwal pelajaran.</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
        
        <?php if(isset($error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Ruang Kelas</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="ph ph-door text-slate-400 text-lg"></i>
                    </div>
                    <input type="text" name="nama_kelas" required autocomplete="off" placeholder="Contoh: X-A1 atau XII IPA 2"
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sekolah-500 focus:ring-4 focus:ring-sekolah-500/10 transition-all">
                </div>
                <p class="text-xs text-slate-500 mt-2"><i class="ph ph-info mr-1"></i>Gunakan penamaan standar sekolah agar mudah diidentifikasi.</p>
            </div>

            <hr class="border-slate-100">

            <div class="flex justify-end gap-3 pt-2">
                <a href="data_kelas.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Batal</a>
                <button type="submit" name="simpan" class="px-6 py-3 bg-sekolah-600 text-white font-bold rounded-xl shadow-md shadow-sekolah-500/30 hover:bg-sekolah-700 transition-all flex items-center gap-2">
                    <i class="ph ph-floppy-disk text-lg"></i> Simpan Ruang Kelas
                </button>
            </div>

        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>