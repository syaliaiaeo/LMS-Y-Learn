<?php
session_start();
// Proteksi: Hanya role 'guru' yang boleh masuk halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 

// ==========================================
// MENGAMBIL DATA REAL-TIME KHAS UNTUK GURU INI
// ==========================================

// 1. Hitung Total Kelas yang Diajar oleh Guru Ini Sahaja
$query_kelas = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT id_kelas) as total_kelas 
    FROM penugasan_guru 
    WHERE id_guru = '$id_guru'
");
$data_kelas = mysqli_fetch_assoc($query_kelas);
$total_kelas = $data_kelas['total_kelas'];

// 2. Hitung Total Pelajar (Siswa) di dalam Kelas yang Diajar oleh Guru Ini
$query_siswa = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT u.id_user) as total_siswa 
    FROM users u 
    JOIN penugasan_guru pg ON u.id_kelas = pg.id_kelas 
    WHERE u.role = 'siswa' AND pg.id_guru = '$id_guru'
");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total_siswa'];

// 3. Hitung Tugas Menunggu Nilai (Hanya tugas milik guru yang log masuk & belum dinilai)
$query_koreksi = mysqli_query($koneksi, "
    SELECT COUNT(pt.id_pengumpulan) as nunggu_nilai 
    FROM pengumpulan_tugas pt 
    JOIN tugas t ON pt.id_tugas = t.id_tugas 
    WHERE t.id_guru = '$id_guru' AND (pt.nilai IS NULL OR pt.nilai = '')
");
$data_koreksi = mysqli_fetch_assoc($query_koreksi);
$tugas_menunggu = $data_koreksi['nunggu_nilai'];

include 'includes/header.php';
?>

<div class="mb-10">
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Selamat datang, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama'])[0]); ?>!</h1>
    <p class="text-slate-500 font-medium">Ringkasan aktiviti kelas dan tugasan yang perlu Anda nilai hari ini.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    
    <a href="ruang_kelas.php" class="block bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:shadow-guru-500/10 hover:border-guru-200 hover:-translate-y-1.5 transition-all duration-300 group cursor-pointer">
        <div class="w-12 h-12 bg-guru-50 text-guru-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-guru-100 transition-all duration-300">
            <i class="ph ph-door text-2xl"></i>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1 group-hover:text-guru-600 transition-colors">Kelas Tersedia</p>
        <h3 class="text-3xl font-black text-slate-800 group-hover:text-guru-700 transition-colors"><?php echo $total_kelas; ?> <span class="text-sm font-medium text-slate-500 tracking-normal">Kelas</span></h3>
    </a>

    <a href="data_siswa.php" class="block bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:shadow-blue-500/10 hover:border-blue-200 hover:-translate-y-1.5 transition-all duration-300 group cursor-pointer">
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-blue-100 transition-all duration-300">
            <i class="ph ph-users-three text-2xl"></i>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1 group-hover:text-blue-600 transition-colors">Total Peserta Didik</p>
        <h3 class="text-3xl font-black text-slate-800 group-hover:text-blue-700 transition-colors"><?php echo $total_siswa; ?> <span class="text-sm font-medium text-slate-500 tracking-normal">Siswa</span></h3>
    </a>

    <a href="penilaian.php" class="block bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:shadow-rose-500/10 hover:border-rose-200 hover:-translate-y-1.5 transition-all duration-300 group cursor-pointer relative overflow-hidden">
        <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center mb-4 relative z-10 group-hover:scale-110 group-hover:bg-rose-100 transition-all duration-300">
            <i class="ph ph-file-dashed text-2xl"></i>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1 relative z-10 group-hover:text-rose-600 transition-colors">Tugas Menunggu Nilai</p>
        <h3 class="text-3xl font-black text-slate-800 relative z-10 group-hover:text-rose-700 transition-colors">
            <?php echo $tugas_menunggu; ?> 
            <span class="text-sm font-medium text-slate-500 tracking-normal">Berkas</span>
        </h3>
        
        <?php if($tugas_menunggu > 0): ?>
            <div class="absolute top-6 right-6 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
            </div>
        <?php endif; ?>
        
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 rounded-full opacity-50 z-0 group-hover:scale-150 group-hover:bg-rose-100 transition-all duration-500"></div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 h-full">
            <h4 class="text-lg font-extrabold text-slate-900 mb-6">Aktiviti Belajar Mengajar</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="materi.php" class="p-5 border border-slate-100 rounded-xl hover:border-guru-200 hover:bg-guru-50 transition-all flex flex-col gap-3 group">
                    <div class="w-10 h-10 rounded-lg bg-guru-100 text-guru-600 flex items-center justify-center"><i class="ph ph-upload-simple text-xl"></i></div>
                    <div>
                        <h5 class="font-bold text-slate-800 group-hover:text-guru-700">Muat Naik Bahan</h5>
                        <p class="text-xs text-slate-500 mt-1">Kongsikan modul atau video pembelajaran kepada pelajar.</p>
                    </div>
                </a>
                
                <a href="tugas.php" class="p-5 border border-slate-100 rounded-xl hover:border-blue-200 hover:bg-blue-50 transition-all flex flex-col gap-3 group">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="ph ph-clipboard-text text-xl"></i></div>
                    <div>
                        <h5 class="font-bold text-slate-800 group-hover:text-blue-700">Buat Tugasan Baru</h5>
                        <p class="text-xs text-slate-500 mt-1">Berikan tugasan individu atau berkumpulan.</p>
                    </div>
                </a>

                <a href="penilaian.php" class="p-5 border border-slate-100 rounded-xl hover:border-amber-200 hover:bg-amber-50 transition-all flex flex-col gap-3 group">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center"><i class="ph ph-check-square-offset text-xl"></i></div>
                    <div>
                        <h5 class="font-bold text-slate-800 group-hover:text-amber-700">Semak & Nilai</h5>
                        <p class="text-xs text-slate-500 mt-1">Berikan markah dan maklum balas pada tugasan pelajar.</p>
                    </div>
                </a>

                <a href="forum.php" class="p-5 border border-slate-100 rounded-xl hover:border-purple-200 hover:bg-purple-50 transition-all flex flex-col gap-3 group">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center"><i class="ph ph-chats text-xl"></i></div>
                    <div>
                        <h5 class="font-bold text-slate-800 group-hover:text-purple-700">Mulakan Perbincangan</h5>
                        <p class="text-xs text-slate-500 mt-1">Jawab pertanyaan pelajar di forum interaktif.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-slate-900 rounded-2xl p-8 text-white relative overflow-hidden shadow-lg h-full">
            <h4 class="font-bold mb-6 flex items-center gap-2 relative z-10"><i class="ph ph-calendar-check text-guru-400 text-xl"></i> Peringatan Penting</h4>
            
            <div class="space-y-4 relative z-10">
                <div class="bg-slate-800/80 border border-slate-700 p-4 rounded-xl">
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Penilaian</p>
                    <p class="text-sm font-medium">Pastikan semua tugasan dan kuiz telah dinilai sebelum tempoh Peperiksaan bermula.</p>
                </div>
                
                <div class="bg-slate-800/80 border border-slate-700 p-4 rounded-xl mt-4">
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Forum</p>
                    <p class="text-sm font-medium">Semak ruang perbincangan secara berkala untuk memantau penglibatan pelajar.</p>
                </div>
            </div>
            
            <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-guru-500 rounded-full blur-3xl opacity-20 z-0 pointer-events-none"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>