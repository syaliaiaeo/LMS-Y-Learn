<?php
session_start();
// Proteksi: Hanya role 'siswa' yang boleh mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_siswa = $_SESSION['id_user']; 
$id_kelas_siswa = $_SESSION['id_kelas'];

// Fitur Pencarian (Opsional untuk memudahkan siswa)
$kata_kunci = "";
$query_tambahan = "";
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
    $query_tambahan = " AND (m.judul_materi LIKE '%$kata_kunci%' OR mp.nama_mapel LIKE '%$kata_kunci%') ";
}

include 'includes/header.php';
?>

<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Materi Pelajaran</h1>
        <p class="text-slate-500 text-sm mt-1">Unduh dan pelajari modul atau bahan ajar yang telah dibagikan oleh guru Anda.</p>
    </div>
    
    <form action="" method="GET" class="relative w-full md:w-72 shrink-0">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400"></i>
        </div>
        <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari materi / mapel..." autocomplete="off"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-sm">
        <?php if(!empty($kata_kunci)): ?>
            <a href="materi.php" class="absolute inset-y-0 right-2 flex items-center text-slate-400 hover:text-rose-500">
                <i class="ph ph-x-circle text-lg"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <?php
    // KUNCI ISOLASI DATA: WHERE m.id_kelas = '$id_kelas_siswa'
    $query_sql = "
        SELECT m.*, u.nama_lengkap AS nama_guru, mp.nama_mapel 
        FROM materi m
        JOIN users u ON m.id_guru = u.id_user
        JOIN mapel mp ON m.id_mapel = mp.id_mapel
        WHERE m.id_kelas = '$id_kelas_siswa' 
        $query_tambahan
        ORDER BY m.tgl_upload DESC
    ";
    
    $q_materi = mysqli_query($koneksi, $query_sql);

    if(mysqli_num_rows($q_materi) > 0):
        while($materi = mysqli_fetch_assoc($q_materi)):
            $tgl_upload = date('d M Y', strtotime($materi['tgl_upload']));
            $file_path = '../uploads/materi/' . $materi['file_materi'];
            $file_exists = (!empty($materi['file_materi']) && file_exists($file_path));
    ?>
    
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col group">
        <div class="flex justify-between items-start mb-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-600 font-bold text-[10px] uppercase tracking-wider rounded-lg">
                <i class="ph ph-book-bookmark text-sm"></i> <?php echo htmlspecialchars($materi['nama_mapel']); ?>
            </span>
            <span class="text-xs font-bold text-slate-400"><?php echo $tgl_upload; ?></span>
        </div>
        
        <h3 class="font-extrabold text-slate-800 text-lg mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
            <?php echo htmlspecialchars($materi['judul_materi']); ?>
        </h3>
        
        <p class="text-sm text-slate-500 mb-4 line-clamp-3 leading-relaxed flex-1">
            <?php echo nl2br(htmlspecialchars($materi['deskripsi'])); ?>
        </p>
        
        <div class="flex items-center gap-2 mb-6 text-xs font-medium text-slate-500">
            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                <i class="ph ph-chalkboard-teacher"></i>
            </div>
            Oleh: <span class="font-bold text-slate-700"><?php echo htmlspecialchars($materi['nama_guru']); ?></span>
        </div>
        
        <div class="mt-auto border-t border-slate-100 pt-4">
            <?php if($file_exists): ?>
                <a href="<?php echo $file_path; ?>" download class="w-full py-2.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white font-bold text-sm rounded-xl transition-colors flex items-center justify-center gap-2">
                    <i class="ph ph-download-simple text-lg"></i> Unduh Lampiran
                </a>
            <?php else: ?>
                <button disabled class="w-full py-2.5 bg-slate-50 text-slate-400 font-bold text-sm rounded-xl border border-slate-200 flex items-center justify-center gap-2 cursor-not-allowed tooltip" title="File tidak ditemukan di server">
                    <i class="ph ph-warning-circle text-lg"></i> File Tidak Tersedia
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php 
        endwhile;
    else:
    ?>
    
    <div class="col-span-full bg-white border border-slate-200 border-dashed rounded-2xl p-12 text-center flex flex-col items-center justify-center">
        <i class="ph ph-folder-open text-5xl text-slate-300 mb-4"></i>
        <?php if(!empty($kata_kunci)): ?>
            <p class="text-slate-600 font-medium">Tidak menemukan materi dengan kata kunci "<b><?php echo htmlspecialchars($kata_kunci); ?></b>".</p>
            <a href="materi.php" class="mt-3 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold text-sm rounded-lg transition-colors">Lihat Semua Materi</a>
        <?php else: ?>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Materi</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto">Guru Anda belum mengunggah materi pelajaran apa pun untuk kelas ini. Silakan periksa kembali nanti secara berkala.</p>
        <?php endif; ?>
    </div>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>