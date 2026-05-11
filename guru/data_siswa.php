<?php
session_start();
// Proteksi halaman guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user'];

// ==========================================
// 1. LOGIKA FILTER PENCARIAN & KELAS
// ==========================================
$kata_kunci = "";
$kelas_terpilih = "";

// Tangkap pencarian
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
}

// Tangkap filter kelas (Sangat berguna saat di-klik dari halaman Ruang Kelas)
if (isset($_GET['kelas']) && $_GET['kelas'] != "") {
    $kelas_terpilih = mysqli_real_escape_string($koneksi, $_GET['kelas']);
}

// Ambil daftar KELAS YANG DIAJAR OLEH GURU INI SAJA untuk isi Dropdown
$q_list_kelas = mysqli_query($koneksi, "
    SELECT DISTINCT k.id_kelas, k.nama_kelas 
    FROM kelas k 
    JOIN penugasan_guru pg ON k.id_kelas = pg.id_kelas 
    WHERE pg.id_guru = '$id_guru' 
    ORDER BY k.nama_kelas ASC
");

include 'includes/header.php'; 
?>

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <a href="index.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-2">
            <i class="ph ph-arrow-left text-lg"></i> Kembali ke Beranda
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Peserta Didik Saya</h1>
        <p class="text-slate-500 text-sm mt-1">Daftar siswa dari kelas-kelas yang menjadi tanggung jawab Anda mengajar.</p>
    </div>
</div>

<form action="" method="GET" class="flex flex-col md:flex-row gap-3 items-center bg-white p-3 border border-slate-200 rounded-2xl shadow-sm mb-6">
    
    <div class="relative flex-1 w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400 text-lg"></i>
        </div>
        
        <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari nama, NISN, atau NIS lalu tekan Enter..." 
            class="w-full pl-11 pr-12 py-3 bg-slate-50 hover:bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
        
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1.5">
            <?php if(!empty($kata_kunci)): ?>
                <a href="?kelas=<?php echo urlencode($kelas_terpilih); ?>" class="p-1 text-rose-500 hover:text-rose-700 transition-colors tooltip" title="Batal Cari">
                    <i class="ph ph-x-circle text-xl"></i>
                </a>
            <?php endif; ?>
            <button type="submit" class="p-1 text-slate-400 hover:text-blue-600 transition-colors tooltip" title="Cari Data">
                <i class="ph ph-magnifying-glass text-xl font-bold"></i>
            </button>
        </div>
    </div>

    <div class="relative w-full md:w-56 shrink-0">
        <select name="kelas" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer transition-all">
            <option value="">Semua Kelas Saya</option>
            <?php 
            if(mysqli_num_rows($q_list_kelas) > 0) {
                while($kls = mysqli_fetch_assoc($q_list_kelas)) {
                    $selected = ($kls['id_kelas'] == $kelas_terpilih) ? "selected" : "";
                    echo "<option value='{$kls['id_kelas']}' {$selected}>{$kls['nama_kelas']}</option>";
                }
            }
            ?>
        </select>
        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
            <i class="ph ph-caret-down text-slate-400"></i>
        </div>
    </div>
</form>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">
                    <th class="px-6 py-4 w-12 text-center">No</th>
                    <th class="px-6 py-4">Profil Siswa</th>
                    <th class="px-6 py-4">NISN / NIS</th>
                    <th class="px-6 py-4 text-center">Kelas</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                
                <?php
                // ==========================================
                // 2. EKSEKUSI QUERY TABEL (INNER JOIN)
                // ==========================================
                // Hanya memunculkan siswa yang kelasnya ada di tabel penugasan_guru milik guru ini
                $query_sql = "
                    SELECT u.*, k.nama_kelas 
                    FROM users u
                    JOIN kelas k ON u.id_kelas = k.id_kelas
                    JOIN penugasan_guru pg ON k.id_kelas = pg.id_kelas
                    WHERE u.role = 'siswa' AND pg.id_guru = '$id_guru'
                ";
                
                if (!empty($kata_kunci)) {
                    $query_sql .= " AND (u.nama_lengkap LIKE '%$kata_kunci%' OR u.nisn LIKE '%$kata_kunci%' OR u.nis LIKE '%$kata_kunci%')";
                }
                
                if (!empty($kelas_terpilih)) {
                    $query_sql .= " AND u.id_kelas = '$kelas_terpilih'";
                }
                
                // Group by id_user untuk mencegah nama siswa muncul dobel jika guru ditugaskan berkali-kali di kelas yang sama
                $query_sql .= " GROUP BY u.id_user ORDER BY k.nama_kelas ASC, u.nama_lengkap ASC";
                
                $q_siswa = mysqli_query($koneksi, $query_sql);
                $no = 1;

                if(mysqli_num_rows($q_siswa) > 0):
                    while($siswa = mysqli_fetch_assoc($q_siswa)):
                        // Buat Inisial Nama
                        $inisial = '';
                        $pecah_nama = explode(' ', $siswa['nama_lengkap']);
                        if (isset($pecah_nama[0])) $inisial .= strtoupper(substr($pecah_nama[0], 0, 1));
                        if (isset($pecah_nama[1])) $inisial .= strtoupper(substr($pecah_nama[1], 0, 1));
                ?>
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="px-6 py-4 text-sm font-medium text-slate-500 text-center"><?php echo $no++; ?></td>
                    
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold text-sm shrink-0">
                                <?php echo $inisial; ?>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></p>
                                <p class="text-[11px] font-mono text-slate-500 mt-0.5 line-clamp-1"><i class="ph ph-user"></i> <?php echo htmlspecialchars($siswa['username']); ?></p>
                            </div>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4">
                        <p class="text-sm text-slate-800 font-bold"><?php echo !empty($siswa['nisn']) ? htmlspecialchars($siswa['nisn']) : '-'; ?></p>
                        <p class="text-[11px] text-slate-500 mt-0.5">NIS: <?php echo !empty($siswa['nis']) ? htmlspecialchars($siswa['nis']) : '-'; ?></p>
                    </td>

                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 text-xs font-bold rounded-lg">
                            <i class="ph ph-door"></i> <?php echo htmlspecialchars($siswa['nama_kelas']); ?>
                        </span>
                    </td>
                    
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="penilaian.php?siswa=<?php echo $siswa['id_user']; ?>" class="px-3 py-1.5 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-colors font-bold text-xs flex items-center gap-1 tooltip" title="Input Nilai">
                                <i class="ph ph-check-square-offset text-sm"></i> Beri Nilai
                            </a>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                <i class="ph ph-student text-3xl text-slate-400"></i>
                            </div>
                            <h3 class="text-slate-800 font-bold text-lg mb-1">Tidak Ditemukan</h3>
                            <p class="text-sm">Tidak ada siswa yang cocok atau Anda belum ditugaskan di kelas mana pun.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>