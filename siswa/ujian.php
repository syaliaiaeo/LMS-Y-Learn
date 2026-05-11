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
$waktu_sekarang = time();

// Fitur Pencarian
$kata_kunci = "";
$query_tambahan = "";
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
    $query_tambahan = " AND (u.judul_ujian LIKE '%$kata_kunci%' OR mp.nama_mapel LIKE '%$kata_kunci%') ";
}

include 'includes/header.php';
?>

<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kuis & Ujian</h1>
        <p class="text-slate-500 text-sm mt-1">Ikuti evaluasi pembelajaran dan perhatikan sisa waktu pengerjaan Anda.</p>
    </div>
    
    <form action="" method="GET" class="relative w-full md:w-72 shrink-0">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400"></i>
        </div>
        <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari ujian / mapel..." autocomplete="off"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all shadow-sm">
        <?php if(!empty($kata_kunci)): ?>
            <a href="ujian.php" class="absolute inset-y-0 right-2 flex items-center text-slate-400 hover:text-rose-500">
                <i class="ph ph-x-circle text-lg"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php
    $query_sql = "
        SELECT u.*, mp.nama_mapel, usr.nama_lengkap AS nama_guru,
               nu.id_nilai, nu.nilai, nu.waktu_selesai
        FROM ujian u
        JOIN mapel mp ON u.id_mapel = mp.id_mapel
        JOIN users usr ON u.id_guru = usr.id_user
        LEFT JOIN nilai_ujian nu ON u.id_ujian = nu.id_ujian AND nu.id_siswa = '$id_siswa'
        WHERE u.id_kelas = '$id_kelas_siswa' 
        $query_tambahan
        ORDER BY u.tgl_mulai DESC
    ";
    
    $q_ujian = mysqli_query($koneksi, $query_sql);

    if(mysqli_num_rows($q_ujian) > 0):
        while($ujian = mysqli_fetch_assoc($q_ujian)):
            
            $tgl_mulai_str = strtotime($ujian['tgl_mulai']);
            $tgl_selesai_str = strtotime($ujian['tgl_selesai']);
            $sudah_dikerjakan = !empty($ujian['id_nilai']);
            
            if ($sudah_dikerjakan) {
                $status = 'selesai';
                $badge_color = 'bg-emerald-100 text-emerald-700';
                $badge_icon = 'ph-check-circle';
                $badge_text = 'Telah Dikerjakan';
            } elseif ($waktu_sekarang < $tgl_mulai_str) {
                $status = 'belum_buka';
                $badge_color = 'bg-slate-100 text-slate-500';
                $badge_icon = 'ph-lock-key';
                $badge_text = 'Belum Dibuka';
            } elseif ($waktu_sekarang > $tgl_selesai_str) {
                $status = 'ditutup';
                $badge_color = 'bg-rose-100 text-rose-700';
                $badge_icon = 'ph-x-circle';
                $badge_text = 'Waktu Habis';
            } else {
                $status = 'aktif';
                $badge_color = 'bg-amber-100 text-amber-700 animate-pulse';
                $badge_icon = 'ph-play-circle';
                $badge_text = 'Ujian Berlangsung';
            }
    ?>
    
    <div class="bg-white border <?php echo ($status == 'aktif') ? 'border-amber-300 shadow-amber-100' : 'border-slate-200'; ?> rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col group relative overflow-hidden">
        
        <div class="flex justify-between items-start mb-4 relative z-10">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-600 font-bold text-[10px] uppercase tracking-wider rounded-lg">
                <i class="ph ph-exam text-sm"></i> <?php echo htmlspecialchars($ujian['nama_mapel']); ?>
            </span>
            <span class="px-3 py-1 <?php echo $badge_color; ?> font-bold text-[10px] uppercase rounded-full flex items-center gap-1">
                <i class="ph <?php echo $badge_icon; ?> text-sm"></i> <?php echo $badge_text; ?>
            </span>
        </div>
        
        <h3 class="font-extrabold text-slate-800 text-lg mb-1 group-hover:text-amber-600 transition-colors relative z-10">
            <?php echo htmlspecialchars($ujian['judul_ujian']); ?>
        </h3>
        
        <div class="flex items-center gap-2 mb-4 text-xs font-medium text-slate-500 relative z-10">
            Pengampu: <span class="font-bold text-slate-700"><?php echo htmlspecialchars($ujian['nama_guru']); ?></span>
        </div>
        
        <div class="bg-slate-50 p-4 rounded-xl mb-6 relative z-10 border border-slate-100 grid grid-cols-2 gap-4">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1"><i class="ph ph-calendar-plus"></i> Dibuka</p>
                <p class="text-xs font-bold text-slate-700"><?php echo date('d M Y, H:i', $tgl_mulai_str); ?></p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1"><i class="ph ph-calendar-x"></i> Ditutup</p>
                <p class="text-xs font-bold text-rose-600"><?php echo date('d M Y, H:i', $tgl_selesai_str); ?></p>
            </div>
            <div class="col-span-2 flex items-center justify-between border-t border-slate-200 pt-3 mt-1">
                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600">
                    <i class="ph ph-timer text-amber-500 text-lg"></i> Durasi: <?php echo $ujian['waktu']; ?> Menit
                </div>
            </div>
        </div>
        
        <div class="mt-auto relative z-10 flex gap-3 items-center border-t border-slate-100 pt-4">
            
            <?php if($status == 'selesai'): ?>
                <div class="flex-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Nilai Anda</p>
                    
                    <?php
                    // LOGIKA PENYEMBUNYIAN NILAI UNTUK UTS / UAS
                    $judul_huruf_besar = strtoupper($ujian['judul_ujian']);
                    
                    $is_ujian_besar = (strpos($judul_huruf_besar, 'UTS') !== false || 
                                       strpos($judul_huruf_besar, 'UAS') !== false || 
                                       (strpos($judul_huruf_besar, 'UJIAN') !== false && strpos($judul_huruf_besar, 'KUIS') === false));
                                       
                    if ($is_ujian_besar):
                    ?>
                        <p class="text-sm font-bold text-amber-600 flex items-center gap-1"><i class="ph ph-lock-key"></i> Dirahasiakan</p>
                        <p class="text-[9px] text-slate-400 mt-0.5">Menunggu validasi guru</p>
                    <?php else: ?>
                        <p class="text-xl font-black text-emerald-600"><?php echo $ujian['nilai']; ?> <span class="text-xs text-slate-400 font-medium">/ 100</span></p>
                    <?php endif; ?>
                    
                </div>
                <button disabled class="px-6 py-2.5 bg-slate-100 text-slate-400 font-bold text-sm rounded-xl cursor-not-allowed">
                    Selesai
                </button>
                
            <?php elseif($status == 'aktif'): ?>
                <a href="kerjakan_ujian.php?id=<?php echo $ujian['id_ujian']; ?>" class="w-full py-2.5 bg-amber-500 text-white hover:bg-amber-600 font-bold text-sm rounded-xl transition-colors flex items-center justify-center gap-2 shadow-md">
                    <i class="ph ph-play-circle text-lg"></i> Mulai Kerjakan Sekarang
                </a>
                
            <?php elseif($status == 'belum_buka'): ?>
                <button disabled class="w-full py-2.5 bg-slate-50 text-slate-400 border border-slate-200 font-bold text-sm rounded-xl cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="ph ph-lock text-lg"></i> Ujian Belum Tersedia
                </button>
                
            <?php elseif($status == 'ditutup'): ?>
                <button disabled class="w-full py-2.5 bg-rose-50 text-rose-400 border border-rose-100 font-bold text-sm rounded-xl cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="ph ph-x-circle text-lg"></i> Waktu Pengerjaan Habis
                </button>
            <?php endif; ?>

        </div>
    </div>

    <?php 
        endwhile;
    else:
    ?>
    
    <div class="col-span-full bg-white border border-slate-200 border-dashed rounded-2xl p-12 text-center flex flex-col items-center justify-center">
        <i class="ph ph-exam text-5xl text-slate-300 mb-4"></i>
        <?php if(!empty($kata_kunci)): ?>
            <p class="text-slate-600 font-medium">Tidak menemukan ujian dengan kata kunci "<b><?php echo htmlspecialchars($kata_kunci); ?></b>".</p>
            <a href="ujian.php" class="mt-3 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold text-sm rounded-lg transition-colors">Lihat Semua Ujian</a>
        <?php else: ?>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Jadwal Ujian</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto">Guru belum menjadwalkan kuis atau ujian untuk kelas Anda. Persiapkan diri Anda sebaik mungkin!</p>
        <?php endif; ?>
    </div>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>