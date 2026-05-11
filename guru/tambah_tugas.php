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

if (empty($id_mapel_otomatis)) {
    $_SESSION['error'] = "Anda belum memiliki Mata Pelajaran spesialisasi. Silakan hubungi Admin.";
    header("Location: tugas.php"); 
    exit;
}

// ==========================================
// 2. PROSES SIMPAN TUGAS BARU
// ==========================================
if (isset($_POST['simpan'])) {
    $judul_tugas = mysqli_real_escape_string($koneksi, $_POST['judul_tugas']);
    $deskripsi   = mysqli_real_escape_string($koneksi, $_POST['deskripsi']); // TANGKAP INSTRUKSI
    $id_kelas    = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $tgl_mulai   = mysqli_real_escape_string($koneksi, $_POST['tgl_mulai']);
    $tgl_selesai = mysqli_real_escape_string($koneksi, $_POST['tgl_selesai']);

    // QUERY INSERT: Memasukkan data instruksi (deskripsi) ke database
    $query_insert = "
        INSERT INTO tugas (judul_tugas, deskripsi, id_kelas, id_mapel, id_guru, tgl_mulai, tgl_selesai) 
        VALUES ('$judul_tugas', '$deskripsi', '$id_kelas', '$id_mapel_otomatis', '$id_guru', '$tgl_mulai', '$tgl_selesai')
    ";

    if (mysqli_query($koneksi, $query_insert)) {
        $_SESSION['sukses'] = "Tugas baru berhasil dibuat dan dibagikan ke kelas!";
        header("Location: tugas.php"); 
        exit;
    } else {
        $error = "Gagal membuat tugas: " . mysqli_error($koneksi);
    }
}

include 'includes/header.php'; 
?>

<div class="mb-8">
    <a href="tugas.php" class="text-sm font-medium text-slate-500 hover:text-emerald-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Daftar Tugas
    </a>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Buat Tugas Baru</h1>
    <p class="text-slate-500 text-sm mt-1">Berikan instruksi dan atur rentang waktu pengerjaan untuk siswa.</p>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 md:p-8 max-w-4xl">
    
    <?php if(isset($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Judul Tugas <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="ph ph-text-t text-slate-400 text-lg"></i>
                </div>
                <input type="text" name="judul_tugas" required placeholder="Contoh: Latihan Soal Bab 1" 
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Instruksi / Deskripsi Tugas <span class="text-rose-500">*</span></label>
            <textarea name="deskripsi" required rows="4" placeholder="Jelaskan apa yang harus dikerjakan oleh siswa di sini..." 
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all resize-y"></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Ditugaskan ke Kelas <span class="text-rose-500">*</span></label>
            <div class="relative">
                <select name="id_kelas" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 appearance-none transition-all cursor-pointer">
                    <option value="">-- Pilih Kelas --</option>
                    <?php 
                    $q_kelas = mysqli_query($koneksi, "SELECT DISTINCT k.id_kelas, k.nama_kelas FROM kelas k JOIN penugasan_guru pg ON k.id_kelas = pg.id_kelas WHERE pg.id_guru = '$id_guru' ORDER BY k.nama_kelas ASC");
                    while($k = mysqli_fetch_assoc($q_kelas)): 
                    ?>
                        <option value="<?php echo $k['id_kelas']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                    <?php endwhile; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <i class="ph ph-caret-down text-slate-400"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Mulai <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="tgl_mulai" required 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all cursor-pointer">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Selesai (Deadline) <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="tgl_selesai" required 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all cursor-pointer">
            </div>
        </div>

        <div class="pt-8 border-t border-slate-100 flex justify-end gap-3">
            <a href="tugas.php" class="px-6 py-3 border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-sm rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" name="simpan" class="px-6 py-3 bg-[#10B981] hover:bg-emerald-600 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-paper-plane-tilt text-lg"></i> Bagikan Tugas
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>