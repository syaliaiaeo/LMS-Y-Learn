<?php
session_start();
// Proteksi: Hanya guru yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 

// PROSES HAPUS TOPIK DISKUSI
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Pastikan topik yang dihapus adalah milik guru ini
    $cek_topik = mysqli_query($koneksi, "SELECT * FROM forum_topik WHERE id_topik='$id_hapus' AND id_guru='$id_guru'");
    if(mysqli_num_rows($cek_topik) > 0) {
        // Hapus data topik (Otomatis menghapus semua balasan di dalamnya karena ON DELETE CASCADE)
        mysqli_query($koneksi, "DELETE FROM forum_topik WHERE id_topik='$id_hapus'");
        header("Location: forum.php?pesan=dihapus");
        exit;
    }
}

// Fitur Pencarian Topik
$kata_kunci = "";
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
}

include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Forum Diskusi</h1>
        <p class="text-slate-500 text-sm mt-1">Buat ruang diskusi interaktif bersama siswa di luar jam pelajaran.</p>
    </div>
    <a href="tambah_forum.php" class="px-5 py-2.5 bg-guru-600 text-white font-semibold rounded-xl shadow-md shadow-guru-500/30 hover:bg-guru-700 transition-all flex items-center gap-2 text-sm w-fit">
        <i class="ph ph-plus-circle text-lg"></i> Buat Topik Baru
    </a>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
        <i class="ph ph-check-circle text-xl"></i>
        <span class="text-sm font-semibold">
            <?php 
                if($_GET['pesan'] == 'sukses') echo "Topik diskusi berhasil dibuka!";
                if($_GET['pesan'] == 'dihapus') echo "Topik dan seluruh pesannya berhasil dihapus.";
            ?>
        </span>
    </div>
<?php endif; ?>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8 p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
    <form action="" method="GET" class="relative w-full md:max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400 text-lg"></i>
        </div>
        <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari judul diskusi..." autocomplete="off"
            class="w-full pl-10 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-guru-500 focus:ring-4 focus:ring-guru-500/10 transition-all">
        
        <div class="absolute inset-y-0 right-1 flex items-center">
            <?php if(!empty($kata_kunci)): ?>
                <a href="forum.php" class="p-1 mr-1 text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="ph ph-x-circle text-lg"></i>
                </a>
            <?php endif; ?>
            <button type="submit" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg text-xs transition-colors">
                Cari
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 gap-4">
    
    <?php
    // Query untuk mengambil topik sekaligus menghitung jumlah balasannya
    $query_sql = "
        SELECT forum_topik.*, kelas.nama_kelas, mapel.nama_mapel,
        (SELECT COUNT(*) FROM forum_balasan WHERE forum_balasan.id_topik = forum_topik.id_topik) as jumlah_pesan,
        (SELECT tgl_balas FROM forum_balasan WHERE forum_balasan.id_topik = forum_topik.id_topik ORDER BY tgl_balas DESC LIMIT 1) as pesan_terakhir
        FROM forum_topik 
        LEFT JOIN kelas ON forum_topik.id_kelas = kelas.id_kelas 
        LEFT JOIN mapel ON forum_topik.id_mapel = mapel.id_mapel 
        WHERE forum_topik.id_guru = '$id_guru'
    ";

    if (!empty($kata_kunci)) {
        $query_sql .= " AND forum_topik.judul_topik LIKE '%$kata_kunci%' ";
    }
    
    // Urutkan berdasarkan yang ada pesan terbarunya, atau berdasarkan waktu dibuat
    $query_sql .= " ORDER BY COALESCE(pesan_terakhir, forum_topik.tgl_buat) DESC";
    $query = mysqli_query($koneksi, $query_sql);

    if(mysqli_num_rows($query) > 0):
        while($data = mysqli_fetch_assoc($query)):
            $tgl_buat = date('d M Y, H:i', strtotime($data['tgl_buat']));
            $waktu_terakhir = !empty($data['pesan_terakhir']) ? date('d M Y, H:i', strtotime($data['pesan_terakhir'])) : 'Belum ada balasan';
    ?>
    
    <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-md hover:border-guru-300 transition-all group flex flex-col md:flex-row gap-5 items-start md:items-center">
        
        <div class="hidden md:flex w-14 h-14 bg-blue-50 text-blue-600 rounded-full items-center justify-center shrink-0">
            <i class="ph ph-chats-circle text-3xl"></i>
        </div>

        <div class="flex-1">
            <div class="flex flex-wrap gap-2 mb-2">
                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 font-bold text-[10px] uppercase rounded flex items-center gap-1">
                    <i class="ph ph-door"></i> Kelas <?php echo htmlspecialchars($data['nama_kelas']); ?>
                </span>
                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 font-bold text-[10px] uppercase rounded flex items-center gap-1">
                    <i class="ph ph-book-open"></i> <?php echo htmlspecialchars($data['nama_mapel']); ?>
                </span>
            </div>
            
            <a href="detail_forum.php?id=<?php echo $data['id_topik']; ?>" class="block font-bold text-lg text-slate-800 hover:text-guru-600 transition-colors mb-1">
                <?php echo htmlspecialchars($data['judul_topik']); ?>
            </a>
            
            <p class="text-sm text-slate-500 line-clamp-1 mb-3"><?php echo htmlspecialchars($data['deskripsi']); ?></p>
            
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 font-medium">
                <span class="flex items-center gap-1.5"><i class="ph ph-clock text-lg"></i> Dibuat: <?php echo $tgl_buat; ?></span>
                <span class="hidden md:inline">&bull;</span>
                <span class="flex items-center gap-1.5"><i class="ph ph-chat-centered-text text-lg"></i> Aktivitas terakhir: <?php echo $waktu_terakhir; ?></span>
            </div>
        </div>

        <div class="w-full md:w-auto flex md:flex-col items-center md:items-end justify-between md:justify-center gap-4 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-5">
            <div class="text-center">
                <p class="text-2xl font-black text-slate-800 leading-none"><?php echo $data['jumlah_pesan']; ?></p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Balasan</p>
            </div>
            
            <div class="flex gap-2">
                <a href="detail_forum.php?id=<?php echo $data['id_topik']; ?>" class="px-4 py-2 bg-guru-50 text-guru-600 hover:bg-guru-600 hover:text-white font-bold text-xs rounded-xl transition-all">
                    Buka Diskusi
                </a>
                
                <a href="?hapus=<?php echo $data['id_topik']; ?>" onclick="return confirm('Yakin ingin menghapus ruang diskusi ini secara permanen? Semua chat di dalamnya akan hilang!');" class="p-2 text-rose-500 bg-rose-50 hover:bg-rose-500 hover:text-white rounded-xl transition-all tooltip" title="Hapus Topik">
                    <i class="ph ph-trash text-lg"></i>
                </a>
            </div>
        </div>

    </div>
    <?php 
        endwhile;
    else: 
    ?>
    <div class="bg-white border border-slate-200 border-dashed rounded-2xl py-16 text-center text-slate-500">
        <div class="flex flex-col items-center justify-center">
            <i class="ph ph-chats-teardrop text-5xl text-slate-300 mb-4"></i>
            <?php if(!empty($kata_kunci)): ?>
                <p class="font-medium text-slate-600">Pencarian "<b><?php echo htmlspecialchars($kata_kunci); ?></b>" tidak ditemukan.</p>
                <a href="forum.php" class="mt-2 text-sm text-guru-600 font-semibold hover:underline">Tampilkan semua ruang diskusi</a>
            <?php else: ?>
                <p class="font-medium text-slate-800 text-lg mb-1">Belum ada ruang diskusi.</p>
                <p class="text-sm text-slate-400">Mulailah interaksi dengan siswa dengan mengeklik tombol "Buat Topik Baru".</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>