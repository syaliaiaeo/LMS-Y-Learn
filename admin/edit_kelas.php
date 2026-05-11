<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit; }

require_once '../config/koneksi.php';

// 1. Ambil data kelas yang akan diedit berdasarkan ID
$id = $_GET['id'];
$query_lama = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas='$id'");
$data_lama = mysqli_fetch_assoc($query_lama);

if(!$data_lama) {
    header("Location: data_kelas.php");
    exit;
}

// 2. Proses pembaruan data ketika tombol ditekan
if (isset($_POST['update'])) {
    $nama_kelas = trim(mysqli_real_escape_string($koneksi, $_POST['nama_kelas']));

    if (empty($nama_kelas)) {
        $error = "Nama kelas wajib diisi!";
    } else {
        // Mencegah duplikasi nama kelas (mengecek apakah nama kelas sudah dipakai oleh ID lain)
        $cek = mysqli_query($koneksi, "SELECT * FROM kelas WHERE nama_kelas='$nama_kelas' AND id_kelas != '$id'");
        
        if (mysqli_num_rows($cek) > 0) {
            $error = "Ruang Kelas '$nama_kelas' sudah ada di sistem!";
        } else {
            $q_update = "UPDATE kelas SET nama_kelas='$nama_kelas' WHERE id_kelas='$id'";
            
            if (mysqli_query($koneksi, $q_update)) {
                header("Location: data_kelas.php?pesan=sukses");
                exit;
            } else {
                $error = "Gagal mengupdate data: " . mysqli_error($koneksi);
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
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Ruang Kelas</h1>
    <p class="text-slate-500 text-sm mt-1">Perbarui penamaan ruang kelas yang sudah ada.</p>
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
                    <input type="text" name="nama_kelas" required autocomplete="off" value="<?php echo htmlspecialchars($data_lama['nama_kelas']); ?>"
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-sekolah-500 focus:ring-4 focus:ring-sekolah-500/10 transition-all">
                </div>
            </div>

            <hr class="border-slate-100">

            <div class="flex justify-end gap-3 pt-2">
                <a href="data_kelas.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Batal</a>
                <button type="submit" name="update" class="px-6 py-3 bg-amber-500 text-white font-bold rounded-xl shadow-md hover:bg-amber-600 transition-all flex items-center gap-2">
                    <i class="ph ph-pencil-simple text-lg"></i> Perbarui Ruang Kelas
                </button>
            </div>

        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>