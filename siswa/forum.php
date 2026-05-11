<?php
session_start();
// Proteksi: Hanya role 'siswa' yang boleh mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_siswa = $_SESSION['id_user']; 
$id_kelas_siswa = $_SESSION['id_kelas'];

date_default_timezone_set('Asia/Jakarta');

// Fitur Pencarian
$kata_kunci = "";
$query_tambahan = "";
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
    $query_tambahan = " AND (ft.judul_topik LIKE '%$kata_kunci%' OR mp.nama_mapel LIKE '%$kata_kunci%') ";
}

include 'includes/header.php';
?>

<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Forum Diskusi</h1>
        <p class="text-slate-500 text-sm mt-1">Ruang interaktif untuk bertanya dan berdiskusi seputar materi pelajaran bersama guru dan teman.</p>
    </div>
    
    <form action="" method="GET" class="relative w-full md:w-72 shrink-0">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400"></i>
        </div>
        <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari topik diskusi..." autocomplete="off"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all shadow-sm">
        <?php if(!empty($kata_kunci)): ?>
            <a href="forum.php" class="absolute inset-y-0 right-2 flex items-center text-slate-400 hover:text-rose-500">
                <i class="ph ph-x-circle text-lg"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php
    // KUNCI ISOLASI DATA DAN PERHITUNGAN BALASAN
    $query_sql = "
        SELECT ft.*, mp.nama_mapel, u.nama_lengkap AS nama_guru,
               (SELECT COUNT(id_balasan) FROM forum_balasan WHERE id_topik = ft.id_topik) AS total_balasan
        FROM forum_topik ft
        JOIN mapel mp ON ft.id_mapel = mp.id_mapel
        JOIN users u ON ft.id_guru = u.id_user
        WHERE ft.id_kelas = '$id_kelas_siswa' 
        $query_tambahan
        ORDER BY ft.tgl_buat DESC
    ";
    
    $q_forum = mysqli_query($koneksi, $query_sql);

    if(mysqli_num_rows($q_forum) > 0):
        while($forum = mysqli_fetch_assoc($q_forum)):
            $tgl_buat = date('d M Y', strtotime($forum['tgl_buat']));
            $total_balasan = $forum['total_balasan'];
    ?>
    
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col group">
        
        <div class="flex justify-between items-start mb-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-600 font-bold text-[10px] uppercase tracking-wider rounded-lg">
                <i class="ph ph-book-open text-sm"></i> <?php echo htmlspecialchars($forum['nama_mapel']); ?>
            </span>
            <span class="text-xs font-bold text-slate-400"><?php echo $tgl_buat; ?></span>
        </div>
        
        <h3 class="font-extrabold text-slate-800 text-lg mb-2 line-clamp-2 group-hover:text-emerald-600 transition-colors">
            <?php echo htmlspecialchars($forum['judul_topik']); ?>
        </h3>
        
        <p class="text-sm text-slate-500 mb-6 line-clamp-2 flex-1">
            <?php echo htmlspecialchars($forum['deskripsi']); ?>
        </p>
        
        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                    <i class="ph ph-chalkboard-teacher"></i>
                </div>
                <span class="font-bold text-slate-700 truncate max-w-[100px] sm:max-w-[150px]"><?php echo htmlspecialchars($forum['nama_guru']); ?></span>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5 text-xs font-bold <?php echo $total_balasan > 0 ? 'text-emerald-600' : 'text-slate-400'; ?>">
                    <i class="ph ph-chats text-lg"></i> <?php echo $total_balasan; ?>
                </span>
                
                <a href="detail_forum.php?id=<?php echo $forum['id_topik']; ?>" class="px-4 py-2 bg-slate-50 text-slate-600 hover:bg-emerald-600 hover:text-white font-bold text-xs rounded-xl transition-colors border border-slate-200 hover:border-emerald-600 flex items-center gap-1.5">
                    Gabung <i class="ph ph-arrow-right"></i>
                </a>
            </div>
        </div>
        
    </div>

    <?php 
        endwhile;
    else:
    ?>
    
    <div class="col-span-full bg-white border border-slate-200 border-dashed rounded-2xl p-12 text-center flex flex-col items-center justify-center">
        <i class="ph ph-chats-circle text-5xl text-slate-300 mb-4"></i>
        <?php if(!empty($kata_kunci)): ?>
            <p class="text-slate-600 font-medium">Tidak menemukan topik diskusi dengan kata kunci "<b><?php echo htmlspecialchars($kata_kunci); ?></b>".</p>
            <a href="forum.php" class="mt-3 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold text-sm rounded-lg transition-colors">Lihat Semua Topik</a>
        <?php else: ?>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Ruang Diskusi Masih Sepi</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto">Guru Anda belum membuka topik diskusi apa pun untuk kelas ini. Nantikan obrolan seru selanjutnya!</p>
        <?php endif; ?>
    </div>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>