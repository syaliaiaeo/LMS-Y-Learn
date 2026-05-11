<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}
require_once '../config/koneksi.php';

// Validasi ID
if (!isset($_GET['id'])) {
    header("Location: pengaturan_akademik.php");
    exit;
}
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Proses Update Data
if (isset($_POST['update'])) {
    $nama_tahun = mysqli_real_escape_string($koneksi, $_POST['nama_tahun']);
    $semester = mysqli_real_escape_string($koneksi, $_POST['semester']);

    $query = "UPDATE tahun_ajaran SET nama_tahun='$nama_tahun', semester='$semester' WHERE id_tahun='$id'";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['sukses'] = "Data tahun ajaran berhasil diperbarui!";
        header("Location: pengaturan_akademik.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal memperbarui data!";
    }
}

// Ambil data yang akan diedit
$q_data = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran WHERE id_tahun='$id'");
$data = mysqli_fetch_assoc($q_data);

include '../includes/header.php'; 
?>

<div class="mb-6">
    <a href="pengaturan_akademik.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Pengaturan Akademik
    </a>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Tahun Ajaran</h1>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 md:p-8 max-w-xl">
    <form action="" method="POST" class="space-y-5">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Ajaran</label>
            <input type="text" name="nama_tahun" required value="<?php echo htmlspecialchars($data['nama_tahun']); ?>" 
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
        </div>
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Semester</label>
            <div class="relative">
                <select name="semester" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                    <option value="Ganjil" <?php echo ($data['semester'] == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                    <option value="Genap" <?php echo ($data['semester'] == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-slate-400"></i></div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end gap-3">
            <button type="submit" name="update" class="px-6 py-3 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>