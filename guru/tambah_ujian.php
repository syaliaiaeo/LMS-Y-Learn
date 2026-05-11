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
    header("Location: ujian.php"); 
    exit;
}

// ==========================================
// 2. PROSES SIMPAN UJIAN BARU
// ==========================================
if (isset($_POST['simpan'])) {
    $judul_ujian    = mysqli_real_escape_string($koneksi, $_POST['judul_ujian']);
    $jenis_evaluasi = mysqli_real_escape_string($koneksi, $_POST['jenis_evaluasi']);
    $id_kelas       = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    
    // TANGKAP TANGGAL MULAI & SELESAI
    $tgl_mulai      = mysqli_real_escape_string($koneksi, $_POST['tgl_mulai']);
    $tgl_selesai    = mysqli_real_escape_string($koneksi, $_POST['tgl_selesai']);

    // QUERY INSERT: Kolom durasi sudah dihapus
    $query_insert = "
        INSERT INTO ujian (judul_ujian, jenis_evaluasi, id_kelas, id_mapel, id_guru, tgl_mulai, tgl_selesai) 
        VALUES ('$judul_ujian', '$jenis_evaluasi', '$id_kelas', '$id_mapel_otomatis', '$id_guru', '$tgl_mulai', '$tgl_selesai')
    ";

    if (mysqli_query($koneksi, $query_insert)) {
        $_SESSION['sukses'] = "Jadwal ujian berhasil dibuat!";
        header("Location: ujian.php"); 
        exit;
    } else {
        $error = "Gagal membuat ujian: " . mysqli_error($koneksi);
    }
}

include 'includes/header.php'; 
?>

<div class="mb-8">
    <a href="ujian.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Jadwal Ujian
    </a>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Jadwalkan Ujian Baru</h1>
    <p class="text-slate-500 text-sm mt-1">Buat jadwal kuis atau ujian dan tentukan rentang waktu pelaksanaannya.</p>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 md:p-8 max-w-4xl">
    
    <?php if(isset($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Judul Evaluasi <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="ph ph-text-a-underline text-slate-400 text-lg"></i>
                </div>
                <input type="text" name="judul_ujian" required placeholder="Contoh: Ulangan Harian 1 - Jaringan Komputer" 
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Evaluasi <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="ph ph-tag text-slate-400 text-lg"></i>
                    </div>
                    <select name="jenis_evaluasi" required class="w-full pl-11 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none transition-all cursor-pointer">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Kuis Singkat">Kuis Singkat</option>
                        <option value="Ulangan Harian">Ulangan Harian</option>
                        <option value="Ujian Tengah Semester">Ujian Tengah Semester (UTS)</option>
                        <option value="Ujian Akhir Semester">Ujian Akhir Semester (UAS)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <i class="ph ph-caret-down text-slate-400"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Target Kelas <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select name="id_kelas" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none transition-all cursor-pointer">
                        <option value="">-- Pilih Kelas --</option>
                        <?php 
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
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Mulai <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="tgl_mulai" required 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all cursor-pointer">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Selesai <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="tgl_selesai" required 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all cursor-pointer">
            </div>
        </div>

        <div class="pt-8 border-t border-slate-100 flex justify-end gap-3">
            <a href="ujian.php" class="px-6 py-3 border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-sm rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" name="simpan" class="px-6 py-3 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-floppy-disk text-lg"></i> Buat Jadwal Ujian
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>