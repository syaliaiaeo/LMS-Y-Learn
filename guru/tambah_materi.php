<?php
session_start();
// Proteksi halaman guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user'];

// ==========================================
// 1. AMBIL MAPEL OTOMATIS MILIK GURU INI
// ==========================================
$q_guru = mysqli_query($koneksi, "SELECT id_mapel FROM users WHERE id_user = '$id_guru'");
$data_guru = mysqli_fetch_assoc($q_guru);
$id_mapel_otomatis = $data_guru['id_mapel'];

// Cegah guru mengunggah jika admin belum mengatur spesialisasi mapelnya
if (empty($id_mapel_otomatis)) {
    $_SESSION['error'] = "Anda belum memiliki Mata Pelajaran spesialisasi. Silakan hubungi Admin untuk mengatur profil Anda.";
    header("Location: materi.php"); // Sesuaikan dengan halaman daftar materimu
    exit;
}

// ==========================================
// 2. PROSES SIMPAN MATERI
// ==========================================
if (isset($_POST['simpan'])) {
    $judul_materi = mysqli_real_escape_string($koneksi, $_POST['judul_materi']);
    $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    
    // (Opsional) Jika ada proses upload file PDF/Word, tambahkan logikanya di sini
    // $nama_file = $_FILES['file_materi']['name']; ... dst

    // QUERY INSERT BARU: Kita langsung masukkan variabel $id_mapel_otomatis
    // Catatan: Sesuaikan nama tabel 'materi' dan kolomnya dengan yang ada di database-mu
    $query_insert = "
        INSERT INTO materi (judul_materi, id_kelas, id_mapel, id_guru) 
        VALUES ('$judul_materi', '$id_kelas', '$id_mapel_otomatis', '$id_guru')
    ";

    if (mysqli_query($koneksi, $query_insert)) {
        $_SESSION['sukses'] = "Materi berhasil diunggah!";
        header("Location: materi.php"); // Sesuaikan dengan halaman daftar materimu
        exit;
    } else {
        $error = "Gagal mengunggah materi: " . mysqli_error($koneksi);
    }
}

include 'includes/header.php'; 
?>

<div class="mb-8">
    <a href="materi.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Daftar Materi
    </a>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Unggah Materi Pembelajaran</h1>
    <p class="text-slate-500 text-sm mt-1">Bagikan modul, presentasi, atau bahan bacaan kepada siswa.</p>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 md:p-8 max-w-3xl">
    
    <?php if(isset($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Judul Materi <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="ph ph-text-t text-slate-400 text-lg"></i>
                </div>
                <input type="text" name="judul_materi" required placeholder="Contoh: Modul 1 - Pengenalan Sel Hewan" 
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Tujuan Kelas <span class="text-rose-500">*</span></label>
            <div class="relative">
                <select name="id_kelas" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none transition-all cursor-pointer">
                    <option value="">-- Pilih Kelas Tujuan --</option>
                    <?php 
                    // FILTER CERDAS: Hanya tampilkan kelas yang diajar oleh guru ini
                    $q_kelas = mysqli_query($koneksi, "
                        SELECT DISTINCT k.id_kelas, k.nama_kelas 
                        FROM kelas k 
                        JOIN penugasan_guru pg ON k.id_kelas = pg.id_kelas 
                        WHERE pg.id_guru = '$id_guru' 
                        ORDER BY k.nama_kelas ASC
                    ");
                    while($k = mysqli_fetch_assoc($q_kelas)): 
                    ?>
                        <option value="<?php echo $k['id_kelas']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                    <?php endwhile; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <i class="ph ph-caret-down text-slate-400"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">Daftar ini hanya menampilkan kelas yang ditugaskan kepada Anda.</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Unggah File (PDF/Word/PPT) <span class="text-rose-500">*</span></label>
            <input type="file" name="file_materi" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-600 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
            <a href="materi.php" class="px-6 py-3 border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-sm rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" name="simpan" class="px-6 py-3 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-upload-simple text-lg"></i> Bagikan Materi
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>