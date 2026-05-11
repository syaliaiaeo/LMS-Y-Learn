<?php
session_start();
// Proteksi halaman siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_siswa = $_SESSION['id_user'];
$nama_siswa = htmlspecialchars($_SESSION['nama']);
$nama_depan = explode(' ', $nama_siswa)[0]; // Mengambil nama panggilan

// 1. Ambil Nama Kelas Siswa
$q_kelas = mysqli_query($koneksi, "
    SELECT k.nama_kelas, u.id_kelas 
    FROM users u 
    JOIN kelas k ON u.id_kelas = k.id_kelas 
    WHERE u.id_user = '$id_siswa'
");
$data_kelas = mysqli_fetch_assoc($q_kelas);
$nama_kelas = $data_kelas ? $data_kelas['nama_kelas'] : 'Belum Ada Kelas';
$id_kelas = $data_kelas ? $data_kelas['id_kelas'] : '';

// 2. LOGIKA STATISTIK
// Hitung Tugas
$q_tugas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tugas WHERE id_kelas = '$id_kelas'");
$total_tugas = ($q_tugas) ? mysqli_fetch_assoc($q_tugas)['total'] : 0;

// Hitung Ujian
$q_ujian = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM ujian WHERE id_kelas = '$id_kelas'");
$total_ujian = ($q_ujian) ? mysqli_fetch_assoc($q_ujian)['total'] : 0;

// Hitung Materi
$q_materi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM materi WHERE id_kelas = '$id_kelas'");
$total_materi = ($q_materi) ? mysqli_fetch_assoc($q_materi)['total'] : 0;

include 'includes/header.php'; 
?>

<div class="relative bg-slate-800 rounded-[2rem] p-8 md:p-10 mb-10 overflow-hidden shadow-sm">
    <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-slate-700/50 to-transparent pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-10 w-64 h-64 border-[40px] border-slate-700/30 rounded-full pointer-events-none"></div>
    
    <div class="relative z-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-700/50 border border-slate-600/50 text-slate-200 text-xs font-bold rounded-xl mb-4 tracking-wider uppercase">
            <i class="ph ph-door"></i> KELAS <?php echo htmlspecialchars($nama_kelas); ?>
        </span>
        <h1 class="text-3xl md:text-4xl font-black text-white mb-2 tracking-tight">Halo, <?php echo $nama_depan; ?>!</h1>
        <p class="text-slate-300 text-sm md:text-base max-w-xl font-medium leading-relaxed">
            Tetap semangat belajar hari ini! Berikut adalah ringkasan tugas dan jadwalmu.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    
    <a href="tugas.php" class="block bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:-translate-y-1.5 hover:shadow-lg hover:border-rose-200 transition-all duration-300 group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-slate-50 transform group-hover:scale-110 transition-transform duration-500 pointer-events-none">
            <i class="ph-fill ph-clipboard-text text-9xl"></i>
        </div>
        <div class="relative z-10">
            <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mb-6 border border-slate-100 group-hover:bg-rose-50 group-hover:text-rose-500 group-hover:border-rose-100 transition-colors">
                <i class="ph ph-clipboard-text text-2xl"></i>
            </div>
            <p class="text-slate-400 text-xs font-extrabold uppercase tracking-widest mb-1 group-hover:text-rose-500 transition-colors">Tugas Menunggu</p>
            <h3 class="text-4xl font-black text-slate-800 flex items-baseline gap-2">
                <?php echo $total_tugas; ?> <span class="text-sm font-semibold text-slate-500 tracking-normal">Belum Selesai</span>
            </h3>
        </div>
    </a>

    <a href="ujian.php" class="block bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:-translate-y-1.5 hover:shadow-lg hover:border-blue-200 transition-all duration-300 group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-slate-50 transform group-hover:scale-110 transition-transform duration-500 pointer-events-none">
            <i class="ph-fill ph-exam text-9xl"></i>
        </div>
        <div class="relative z-10">
            <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mb-6 border border-slate-100 group-hover:bg-blue-50 group-hover:text-blue-500 group-hover:border-blue-100 transition-colors">
                <i class="ph ph-exam text-2xl"></i>
            </div>
            <p class="text-slate-400 text-xs font-extrabold uppercase tracking-widest mb-1 group-hover:text-blue-500 transition-colors">Ujian & Kuis</p>
            <h3 class="text-4xl font-black text-slate-800 flex items-baseline gap-2">
                <?php echo $total_ujian; ?> <span class="text-sm font-semibold text-slate-500 tracking-normal">Jadwal</span>
            </h3>
        </div>
    </a>

    <a href="materi.php" class="block bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:-translate-y-1.5 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-slate-50 transform group-hover:scale-110 transition-transform duration-500 pointer-events-none">
            <i class="ph-fill ph-books text-9xl"></i>
        </div>
        <div class="relative z-10">
            <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mb-6 border border-slate-100 group-hover:bg-emerald-50 group-hover:text-emerald-500 group-hover:border-emerald-100 transition-colors">
                <i class="ph ph-books text-2xl"></i>
            </div>
            <p class="text-slate-400 text-xs font-extrabold uppercase tracking-widest mb-1 group-hover:text-emerald-500 transition-colors">Materi Baru</p>
            <h3 class="text-4xl font-black text-slate-800 flex items-baseline gap-2">
                <?php echo $total_materi; ?> <span class="text-sm font-semibold text-slate-500 tracking-normal">Tersedia</span>
            </h3>
        </div>
    </a>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2 bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
        <h4 class="text-lg font-extrabold text-slate-800 mb-6 flex items-center gap-2"><i class="ph-fill ph-chalkboard-teacher text-indigo-500 text-xl"></i> Ruang Kelas Digital</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="materi.php" class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-indigo-200 hover:bg-white hover:shadow-md transition-all group">
                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                    <i class="ph ph-book-open-text text-xl"></i>
                </div>
                <div>
                    <h5 class="font-bold text-slate-700 group-hover:text-indigo-600">Materi Pelajaran</h5>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Baca modul dan tonton video pembelajaran dari gurumu.</p>
                </div>
            </a>
            
            <a href="tugas.php" class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-rose-200 hover:bg-white hover:shadow-md transition-all group">
                <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                    <i class="ph ph-pencil-line text-xl"></i>
                </div>
                <div>
                    <h5 class="font-bold text-slate-700 group-hover:text-rose-600">Tugas Harian</h5>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Kerjakan tugas yang diberikan dan kumpulkan tepat waktu.</p>
                </div>
            </a>
            
            <a href="forum.php" class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-amber-200 hover:bg-white hover:shadow-md transition-all group">
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                    <i class="ph ph-chats-circle text-xl"></i>
                </div>
                <div>
                    <h5 class="font-bold text-slate-700 group-hover:text-amber-600">Forum Diskusi</h5>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Tanya jawab dan berdiskusi dengan guru serta teman sekelas.</p>
                </div>
            </a>

            <a href="raport.php" class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-teal-200 hover:bg-white hover:shadow-md transition-all group">
                <div class="w-10 h-10 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                    <i class="ph ph-medal text-xl"></i>
                </div>
                <div>
                    <h5 class="font-bold text-slate-700 group-hover:text-teal-600">Raport Nilai</h5>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pantau perkembangan nilai tugas dan ujianmu selama semester ini.</p>
                </div>
            </a>
        </div>
    </div>

    <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
        
        <div class="relative z-10">
            <h4 class="text-lg font-extrabold text-slate-800 mb-6 flex items-center gap-2"><i class="ph-fill ph-bell-ringing text-amber-500 text-xl"></i> Pengingat</h4>
            
            <div class="space-y-4">
                <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Sistem LMS</p>
                    <p class="text-sm text-slate-700 font-medium">Periksa menu <b>Tugas Harian</b> secara rutin untuk memastikan tidak ada tugas yang terlewat.</p>
                </div>
                
                <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Keamanan Akun</p>
                    <p class="text-sm text-slate-700 font-medium">Ganti kata sandi bawaan Anda melalui menu Profil di pojok kanan atas agar akun lebih aman.</p>
                </div>
            </div>
        </div>
        
    </div>

</div>

<?php include 'includes/footer.php'; ?>