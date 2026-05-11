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

date_default_timezone_set('Asia/Jakarta');
$waktu_sekarang = date('Y-m-d H:i:s');

// Fitur Pencarian
$kata_kunci = "";
$query_tambahan = "";
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
    $query_tambahan = " AND (t.judul_tugas LIKE '%$kata_kunci%' OR mp.nama_mapel LIKE '%$kata_kunci%') ";
}

include 'includes/header.php';
?>

<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tugas Harian</h1>
        <p class="text-slate-500 text-sm mt-1">Pantau deadline dan kumpulkan tugas-tugas dari guru Anda di sini.</p>
    </div>
    
    <form action="" method="GET" class="relative w-full md:w-72 shrink-0">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400"></i>
        </div>
        <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari tugas / mapel..." autocomplete="off"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all shadow-sm">
        <?php if(!empty($kata_kunci)): ?>
            <a href="tugas.php" class="absolute inset-y-0 right-2 flex items-center text-slate-400 hover:text-rose-500">
                <i class="ph ph-x-circle text-lg"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php
    // KUNCI: LEFT JOIN ke pengumpulan_tugas untuk mengecek status pengerjaan siswa ini
    $query_sql = "
        SELECT t.*, mp.nama_mapel, u.nama_lengkap AS nama_guru,
               pt.id_pengumpulan, pt.tgl_kumpul, pt.nilai
        FROM tugas t
        JOIN mapel mp ON t.id_mapel = mp.id_mapel
        JOIN users u ON t.id_guru = u.id_user
        LEFT JOIN pengumpulan_tugas pt ON t.id_tugas = pt.id_tugas AND pt.id_siswa = '$id_siswa'
        WHERE t.id_kelas = '$id_kelas_siswa' 
        $query_tambahan
        ORDER BY 
            CASE WHEN pt.id_pengumpulan IS NULL THEN 0 ELSE 1 END, -- Yang belum dikerjakan taruh di atas
            t.tgl_selesai ASC
    ";
    
    $q_tugas = mysqli_query($koneksi, $query_sql);

    if(mysqli_num_rows($q_tugas) > 0):
        while($tugas = mysqli_fetch_assoc($q_tugas)):
            $tgl_selesai = date('d M Y, H:i', strtotime($tugas['tgl_selesai']));
            $sudah_kumpul = !empty($tugas['id_pengumpulan']);
            $sudah_dinilai = ($sudah_kumpul && $tugas['nilai'] !== null);
            $terlambat = (!$sudah_kumpul && $waktu_sekarang > $tugas['tgl_selesai']);
    ?>
    
    <div class="bg-white border <?php echo $terlambat ? 'border-rose-300 shadow-rose-100' : 'border-slate-200'; ?> rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col group relative overflow-hidden">
        
        <?php if($terlambat): ?>
            <div class="absolute top-0 right-0 w-16 h-16 bg-rose-50 rounded-bl-full -z-0"></div>
        <?php endif; ?>

        <div class="flex justify-between items-start mb-4 relative z-10">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 text-rose-600 font-bold text-[10px] uppercase tracking-wider rounded-lg">
                <i class="ph ph-book-bookmark text-sm"></i> <?php echo htmlspecialchars($tugas['nama_mapel']); ?>
            </span>
            
            <?php if($sudah_dinilai): ?>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold text-[10px] uppercase rounded-full flex items-center gap-1">
                    <i class="ph ph-check-circle text-sm"></i> Dinilai
                </span>
            <?php elseif($sudah_kumpul): ?>
                <span class="px-3 py-1 bg-amber-100 text-amber-700 font-bold text-[10px] uppercase rounded-full flex items-center gap-1 animate-pulse">
                    <i class="ph ph-clock text-sm"></i> Menunggu Koreksi
                </span>
            <?php elseif($terlambat): ?>
                <span class="px-3 py-1 bg-rose-100 text-rose-700 font-bold text-[10px] uppercase rounded-full flex items-center gap-1">
                    <i class="ph ph-warning-circle text-sm"></i> Terlambat
                </span>
            <?php else: ?>
                <span class="px-3 py-1 bg-slate-100 text-slate-600 font-bold text-[10px] uppercase rounded-full flex items-center gap-1">
                    <i class="ph ph-minus-circle text-sm"></i> Belum Dikerjakan
                </span>
            <?php endif; ?>
        </div>
        
        <h3 class="font-extrabold text-slate-800 text-lg mb-1 group-hover:text-rose-600 transition-colors relative z-10">
            <?php echo htmlspecialchars($tugas['judul_tugas']); ?>
        </h3>
        
        <div class="flex items-center gap-2 mb-4 text-xs font-medium text-slate-500 relative z-10">
            Oleh: <span class="font-bold text-slate-700"><?php echo htmlspecialchars($tugas['nama_guru']); ?></span>
        </div>
        
        <div class="bg-slate-50 p-4 rounded-xl mb-6 relative z-10 flex items-center justify-between border border-slate-100">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Batas Waktu (Deadline)</p>
                <p class="text-sm font-bold <?php echo $terlambat ? 'text-rose-600' : 'text-slate-800'; ?>">
                    <?php echo $tgl_selesai; ?> WIB
                </p>
            </div>
            <i class="ph ph-calendar-blank text-2xl <?php echo $terlambat ? 'text-rose-300' : 'text-slate-300'; ?>"></i>
        </div>
        
        <div class="mt-auto relative z-10 flex gap-3 items-center border-t border-slate-100 pt-4">
            
            <?php if($sudah_dinilai): ?>
                <div class="flex-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Nilai Anda</p>
                    <p class="text-xl font-black text-emerald-600"><?php echo $tugas['nilai']; ?> <span class="text-xs text-slate-400 font-medium">/ 100</span></p>
                </div>
            <?php endif; ?>

            <a href="detail_tugas.php?id=<?php echo $tugas['id_tugas']; ?>" class="<?php echo $sudah_dinilai ? 'px-6' : 'w-full'; ?> py-2.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-sm rounded-xl transition-colors flex items-center justify-center gap-2 text-center">
                <?php 
                    if($sudah_kumpul) {
                        echo '<i class="ph ph-eye text-lg"></i> Lihat Jawaban';
                    } else {
                        echo '<i class="ph ph-pencil-simple text-lg"></i> Kerjakan Tugas';
                    }
                ?>
            </a>
        </div>
    </div>

    <?php 
        endwhile;
    else:
    ?>
    
    <div class="col-span-full bg-white border border-slate-200 border-dashed rounded-2xl p-12 text-center flex flex-col items-center justify-center">
        <i class="ph ph-clipboard-text text-5xl text-slate-300 mb-4"></i>
        <?php if(!empty($kata_kunci)): ?>
            <p class="text-slate-600 font-medium">Tidak menemukan tugas dengan kata kunci "<b><?php echo htmlspecialchars($kata_kunci); ?></b>".</p>
            <a href="tugas.php" class="mt-3 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold text-sm rounded-lg transition-colors">Lihat Semua Tugas</a>
        <?php else: ?>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Hore! Tidak Ada Tugas</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto">Saat ini belum ada tugas yang diberikan untuk kelas Anda. Nikmati waktu istirahat Anda!</p>
        <?php endif; ?>
    </div>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>