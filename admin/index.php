<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit; }

require_once '../config/koneksi.php';

// Menghitung Total Guru
$q_guru = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role='guru'");
$tot_guru = mysqli_fetch_assoc($q_guru)['total'];

// Menghitung Total Siswa
$q_siswa = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role='siswa'");
$tot_siswa = mysqli_fetch_assoc($q_siswa)['total'];

// Menghitung Total Kelas
$q_kelas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kelas");
$tot_kelas = mysqli_fetch_assoc($q_kelas)['total'];

// Menghitung Total Mapel
$q_mapel = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM mapel");
$tot_mapel = mysqli_fetch_assoc($q_mapel)['total'];



include '../includes/header.php';
?>

<div class="mb-10 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Selamat datang, <?php echo explode(' ', $_SESSION['nama'])[0]; ?>!</h1>
        <p class="text-slate-500 font-medium">Ringkasan aktivitas akademik SMA YAPAN hari ini.</p>
    </div>
    <a href="unduh_laporan.php" target="_blank" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2 text-sm">
    <i class="ph ph-download-simple text-lg"></i> Unduh Laporan
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    
    <a href="data_guru.php" class="block bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-sekolah-200 transition-all group">
        <div class="w-12 h-12 bg-slate-50 text-slate-600 group-hover:bg-sekolah-50 group-hover:text-sekolah-600 transition-colors rounded-xl flex items-center justify-center mb-4">
            <i class="ph ph-chalkboard-teacher text-2xl"></i>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Guru</p>
        <h3 class="text-3xl font-black text-slate-800"><?php echo number_format($tot_guru); ?></h3>
    </a>

    <a href="data_siswa.php" class="block bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-sekolah-200 transition-all group">
        <div class="w-12 h-12 bg-slate-50 text-slate-600 group-hover:bg-sekolah-50 group-hover:text-sekolah-600 transition-colors rounded-xl flex items-center justify-center mb-4">
            <i class="ph ph-student text-2xl"></i>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Siswa</p>
        <h3 class="text-3xl font-black text-slate-800"><?php echo number_format($tot_siswa); ?></h3>
    </a>

    <a href="data_mapel.php" class="block bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-sekolah-200 transition-all group">
        <div class="w-12 h-12 bg-slate-50 text-slate-600 group-hover:bg-sekolah-50 group-hover:text-sekolah-600 transition-colors rounded-xl flex items-center justify-center mb-4">
            <i class="ph ph-books text-2xl"></i>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Mata Pelajaran</p>
        <h3 class="text-3xl font-black text-slate-800"><?php echo number_format($tot_mapel); ?></h3>
    </a>

    <a href="data_kelas.php" class="block bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-sekolah-200 transition-all group">
        <div class="w-12 h-12 bg-slate-50 text-slate-600 group-hover:bg-sekolah-50 group-hover:text-sekolah-600 transition-colors rounded-xl flex items-center justify-center mb-4">
            <i class="ph ph-folders text-2xl"></i>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Kelas</p>
        <h3 class="text-3xl font-black text-slate-800"><?php echo number_format($tot_kelas); ?></h3>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2">
        <div class="bg-sekolah-900 rounded-[2rem] p-10 text-white relative overflow-hidden shadow-xl shadow-sekolah-900/20">
            <div class="relative z-10">
                <div class="inline-block px-3 py-1 bg-sekolah-500/30 border border-sekolah-500/50 rounded-full text-xs font-bold tracking-wider mb-4">
                    TAHUN AJARAN 2025/2026
                </div>
                <h4 class="text-2xl font-bold mb-3 leading-tight">Konfigurasi Jadwal Akademik</h4>
                <p class="text-sekolah-100 mb-8 max-w-md leading-relaxed text-sm">Kelola distribusi pengajaran guru. Pastikan setiap mata pelajaran memiliki pengampu yang tepat agar sistem LMS berjalan lancar.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="data_guru.php" class="px-6 py-3 bg-white text-sekolah-900 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all shadow-sm">Atur Jadwal Guru</a>
                    <a href="data_mapel.php" class="px-6 py-3 bg-sekolah-800 border border-sekolah-700 text-white rounded-xl font-bold text-sm hover:bg-sekolah-700 transition-all">Lihat Kurikulum</a>
                </div>
            </div>
            <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-sekolah-600 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        </div>
    </div>
    
    <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
        <h5 class="font-extrabold text-slate-900 mb-6 text-lg">Aksi Cepat</h5>
        <div class="space-y-3">
            <a href="tambah_guru.php" class="w-full text-left p-4 rounded-xl border border-slate-200 hover:border-sekolah-300 hover:bg-sekolah-50 hover:text-sekolah-700 transition-all flex items-center justify-between group text-slate-600">
                <span class="text-sm font-bold">Tambah Guru Baru</span>
                <i class="ph ph-plus-circle text-xl text-slate-400 group-hover:text-sekolah-600 transition-colors"></i>
            </a>
            <a href="cetak_siswa.php" target="_blank" class="w-full text-left p-4 rounded-xl border border-slate-200 hover:border-sekolah-300 hover:bg-sekolah-50 hover:text-sekolah-700 transition-all flex items-center justify-between group text-slate-600">
                <span class="text-sm font-bold">Cetak Laporan Siswa</span>
                <i class="ph ph-printer text-xl text-slate-400 group-hover:text-sekolah-600 transition-colors"></i>
            </a>
            <a href="backup_proses.php" class="w-full text-left p-4 rounded-xl border border-slate-200 hover:border-sekolah-300 hover:bg-sekolah-50 hover:text-sekolah-700 transition-all flex items-center justify-between group text-slate-600">
                <span class="text-sm font-bold">Backup Database</span>
                <i class="ph ph-database text-xl text-slate-400 group-hover:text-sekolah-600 transition-colors"></i>
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>