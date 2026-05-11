<?php
session_start();
// Proteksi: Hanya role 'guru' yang boleh masuk halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// Ambil ID Guru yang sedang login
$id_guru = $_SESSION['id_user']; 

include 'includes/header.php'; 
?>

<div class="mb-8">
    <a href="index.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Beranda
    </a>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Ruang Kelas Saya</h1>
    <p class="text-slate-500 text-sm mt-1">Daftar kelas yang ditugaskan kepada Anda pada tahun ajaran ini.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <?php
    // Query Cerdas: Ambil data kelas dari tabel penugasan_guru KHUSUS untuk guru yang sedang login
    $query_kelas = "
        SELECT k.id_kelas, k.nama_kelas, pg.id_tahun,
        (SELECT COUNT(id_user) FROM users WHERE role='siswa' AND id_kelas = k.id_kelas) as jml_siswa
        FROM penugasan_guru pg
        JOIN kelas k ON pg.id_kelas = k.id_kelas
        WHERE pg.id_guru = '$id_guru'
        ORDER BY pg.id_tahun DESC, k.nama_kelas ASC
    ";
    
    $q_kelas = mysqli_query($koneksi, $query_kelas);

    if(mysqli_num_rows($q_kelas) > 0):
        while($kelas = mysqli_fetch_assoc($q_kelas)):
    ?>
    
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 hover:border-indigo-200 hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between">
        
        <div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <i class="ph ph-chalkboard text-2xl"></i>
                </div>
                <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-full border border-slate-200">
                    <?php echo htmlspecialchars($kelas['id_tahun']); ?>
                </span>
            </div>
            
            <h3 class="text-xl font-extrabold text-slate-800 mb-1 group-hover:text-indigo-700 transition-colors"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></h3>
            
            <div class="flex items-center gap-2 mt-3 text-sm font-medium text-slate-500">
                <i class="ph ph-users text-slate-400 text-lg"></i>
                <span><?php echo $kelas['jml_siswa']; ?> Peserta Didik</span>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex gap-3">
            <a href="data_siswa.php?kelas=<?php echo $kelas['id_kelas']; ?>" class="flex-1 py-2.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white font-bold text-xs text-center rounded-xl transition-colors">
                Lihat Siswa
            </a>
            <a href="penilaian.php?kelas=<?php echo $kelas['id_kelas']; ?>" class="flex-1 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs text-center rounded-xl transition-colors">
                Beri Nilai
            </a>
        </div>
        
    </div>

    <?php 
        endwhile;
    else: 
    ?>
    
    <div class="col-span-full bg-white border border-slate-200 rounded-3xl p-12 text-center flex flex-col items-center justify-center shadow-sm">
        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
            <i class="ph ph-door text-4xl text-slate-400"></i>
        </div>
        <h3 class="text-xl font-extrabold text-slate-800 mb-2">Belum Ada Penugasan</h3>
        <p class="text-slate-500 text-sm max-w-md mx-auto">Admin belum menempatkan Anda di kelas manapun untuk mengajar. Silakan hubungi bagian kurikulum atau admin sekolah.</p>
    </div>
    
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>