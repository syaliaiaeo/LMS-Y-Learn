<?php
session_start();
// Proteksi halaman guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user'];

if(!isset($_GET['id'])) {
    header("Location: ujian.php");
    exit;
}
$id_ujian = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data ujian lama
$q_ujian = mysqli_query($koneksi, "SELECT * FROM ujian WHERE id_ujian='$id_ujian' AND id_guru='$id_guru'");
if(mysqli_num_rows($q_ujian) == 0) {
    $_SESSION['error'] = "Jadwal ujian tidak ditemukan atau Anda tidak memiliki akses.";
    header("Location: ujian.php");
    exit;
}
$data_lama = mysqli_fetch_assoc($q_ujian);

// Ambil MAPEL OTOMATIS
$q_guru = mysqli_query($koneksi, "SELECT id_mapel FROM users WHERE id_user = '$id_guru'");
$id_mapel_otomatis = mysqli_fetch_assoc($q_guru)['id_mapel'];

// ==========================================
// PROSES UPDATE UJIAN
// ==========================================
if (isset($_POST['update'])) {
    $judul_ujian    = mysqli_real_escape_string($koneksi, $_POST['judul_ujian']);
    $jenis_evaluasi = mysqli_real_escape_string($koneksi, $_POST['jenis_evaluasi']);
    $id_kelas       = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $tgl_mulai      = mysqli_real_escape_string($koneksi, $_POST['tgl_mulai']);
    $tgl_selesai    = mysqli_real_escape_string($koneksi, $_POST['tgl_selesai']);

    // QUERY UPDATE: Tanpa kolom durasi
    $query_update = "
        UPDATE ujian 
        SET judul_ujian='$judul_ujian', 
            jenis_evaluasi='$jenis_evaluasi',
            id_kelas='$id_kelas', 
            id_mapel='$id_mapel_otomatis',
            tgl_mulai='$tgl_mulai',
            tgl_selesai='$tgl_selesai'
        WHERE id_ujian='$id_ujian' AND id_guru='$id_guru'
    ";

    if (mysqli_query($koneksi, $query_update)) {
        $_SESSION['sukses'] = "Jadwal ujian berhasil diperbarui!";
        header("Location: ujian.php"); 
        exit;
    } else {
        $error = "Gagal memperbarui ujian: " . mysqli_error($koneksi);
    }
}

include 'includes/header.php'; 
?>

<div class="mb-8">
    <a href="ujian.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Jadwal Ujian
    </a>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Edit Ujian: <span class="text-blue-600"><?php echo htmlspecialchars($data_lama['judul_ujian']); ?></span></h1>
    <p class="text-slate-500 text-sm mt-1">Perbarui judul, kelas target, atau rentang waktu ujian.</p>
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
                <input type="text" name="judul_ujian" required value="<?php echo htmlspecialchars($data_lama['judul_ujian']); ?>" 
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
                        <option value="Kuis Singkat" <?php if($data_lama['jenis_evaluasi'] == 'Kuis Singkat') echo 'selected'; ?>>Kuis Singkat</option>
                        <option value="Ulangan Harian" <?php if($data_lama['jenis_evaluasi'] == 'Ulangan Harian') echo 'selected'; ?>>Ulangan Harian</option>
                        <option value="Ujian Tengah Semester" <?php if($data_lama['jenis_evaluasi'] == 'Ujian Tengah Semester') echo 'selected'; ?>>Ujian Tengah Semester (UTS)</option>
                        <option value="Ujian Akhir Semester" <?php if($data_lama['jenis_evaluasi'] == 'Ujian Akhir Semester') echo 'selected'; ?>>Ujian Akhir Semester (UAS)</option>
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
                        $q_kelas = mysqli_query($koneksi, "SELECT DISTINCT k.id_kelas, k.nama_kelas FROM kelas k JOIN penugasan_guru pg ON k.id_kelas = pg.id_kelas WHERE pg.id_guru = '$id_guru' ORDER BY k.nama_kelas ASC");
                        while($k = mysqli_fetch_assoc($q_kelas)): 
                            $selected = ($k['id_kelas'] == $data_lama['id_kelas']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $k['id_kelas']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
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
                <input type="datetime-local" name="tgl_mulai" required value="<?php echo !empty($data_lama['tgl_mulai']) ? date('Y-m-d\TH:i', strtotime($data_lama['tgl_mulai'])) : ''; ?>" 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all cursor-pointer">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Selesai <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="tgl_selesai" required value="<?php echo !empty($data_lama['tgl_selesai']) ? date('Y-m-d\TH:i', strtotime($data_lama['tgl_selesai'])) : ''; ?>" 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all cursor-pointer">
            </div>
        </div>

        <div class="pt-8 border-t border-slate-100 flex justify-end gap-3">
            <a href="ujian.php" class="px-6 py-3 border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-sm rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" name="update" class="px-6 py-3 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>