<?php
session_start();
// Proteksi halaman admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// Validasi ID Kelas dari URL
if (!isset($_GET['id'])) {
    header("Location: data_kelas.php");
    exit;
}
$id_kelas = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil informasi nama kelas
$q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas = '$id_kelas'");
if (mysqli_num_rows($q_kelas) == 0) {
    header("Location: data_kelas.php");
    exit;
}
$info_kelas = mysqli_fetch_assoc($q_kelas);

include '../includes/header.php'; 
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
    <div>
        <a href="data_kelas.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-3">
            <i class="ph ph-arrow-left text-lg"></i> Kembali ke Ruang Kelas
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Absensi <span class="text-blue-600"><?php echo htmlspecialchars($info_kelas['nama_kelas']); ?></span></h1>
        <p class="text-slate-500 text-sm mt-1">Daftar urut presensi siswa berdasarkan abjad (A-Z).</p>
    </div>
    
    <div class="flex items-center gap-3 shrink-0">
        <a href="data_siswa.php?kelas=<?php echo $id_kelas; ?>" class="px-5 py-2.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-sm">
            <i class="ph ph-users-three text-lg"></i> Kelola Detail Lengkap
        </a>
        
        <button onclick="window.print()" class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-sm">
            <i class="ph ph-printer text-lg"></i> Cetak
        </button>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden max-w-3xl print:border-none print:shadow-none print:max-w-full print:rounded-none">
    
    <div class="bg-slate-50 border-b border-slate-200 p-6 hidden print:block text-center mb-4">
        <h2 class="text-xl font-bold text-black uppercase">DAFTAR HADIR SISWA</h2>
        <p class="text-sm text-gray-600 font-medium mt-1">Kelas: <?php echo htmlspecialchars($info_kelas['nama_kelas']); ?> | Tahun Ajaran: .....................</p>
    </div>

    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest print:bg-white print:text-black">
                <th class="px-6 py-4 w-24 text-center print:border print:border-black">No. Absen</th>
                <th class="px-6 py-4 print:border print:border-black">Nama Lengkap Siswa</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 print:divide-none">
            
            <?php
            // Query hanya mengambil data siswa di kelas ini dan diurutkan sesuai Abjad
            $query_siswa = "
                SELECT nama_lengkap FROM users 
                WHERE role = 'siswa' AND id_kelas = '$id_kelas' 
                ORDER BY nama_lengkap ASC
            ";
            
            $q_siswa = mysqli_query($koneksi, $query_siswa);
            $no = 1; // Inisialisasi Nomor Urut Absen

            if(mysqli_num_rows($q_siswa) > 0):
                while($siswa = mysqli_fetch_assoc($q_siswa)):
            ?>
            <tr class="hover:bg-slate-50 transition-colors print:text-black">
                <td class="px-6 py-4 text-sm font-extrabold text-slate-700 text-center print:border print:border-black">
                    <?php echo $no++; ?>
                </td>
                
                <td class="px-6 py-4 print:border print:border-black">
                    <p class="text-sm font-bold text-slate-800 uppercase"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></p>
                </td>
            </tr>
            <?php 
                endwhile;
            else: 
            ?>
            <tr>
                <td colspan="2" class="px-6 py-16 text-center print:border print:border-black">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4 print:hidden">
                        <i class="ph ph-student text-3xl text-slate-400"></i>
                    </div>
                    <h3 class="text-slate-800 font-bold text-lg mb-1">Kelas Masih Kosong</h3>
                    <p class="text-sm text-slate-500">Belum ada siswa yang ditempatkan di ruang kelas ini.</p>
                </td>
            </tr>
            <?php endif; ?>
            
        </tbody>
    </table>
</div>

<style>
    @media print {
        body { background-color: white !important; }
        .glass-nav, aside { display: none !important; } /* Sembunyikan sidebar dan header utama saat diprint */
        main { padding: 0 !important; margin: 0 !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black !important; padding: 12px 16px !important; }
    }
</style>

<?php include '../includes/footer.php'; ?>