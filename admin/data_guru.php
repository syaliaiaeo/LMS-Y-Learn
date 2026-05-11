<?php
session_start();
// Proteksi halaman admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// ==========================================
// 1. LOGIKA FILTER PENCARIAN & TAHUN AJARAN
// ==========================================
$kata_kunci = "";
$tahun_terpilih = "";
$query_search = "";
$query_tahun = "";

// Jika tombol FILTER ditekan atau ada pencarian
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
    if (!empty($kata_kunci)) {
        $query_search = " AND (u.nama_lengkap LIKE '%$kata_kunci%' OR u.username LIKE '%$kata_kunci%' OR u.nip LIKE '%$kata_kunci%') ";
    }
}

if (isset($_GET['tahun']) && $_GET['tahun'] != "") {
    $tahun_terpilih = mysqli_real_escape_string($koneksi, $_GET['tahun']);
    
    // Logika Pemfilteran Diperketat:
    $query_tahun = " AND (
        u.tahun_masuk IS NOT NULL 
        AND u.tahun_masuk != '' 
        AND u.tahun_masuk <= '$tahun_terpilih' 
        AND (u.tahun_keluar >= '$tahun_terpilih' OR u.tahun_keluar = 'Sekarang' OR u.tahun_keluar IS NULL OR u.tahun_keluar = '')
    ) ";
}

// Mengambil daftar tahun ajaran unik dari database untuk isi dropdown
$q_list_tahun = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC");

include '../includes/header.php'; 
?>

<?php if (isset($_SESSION['sukses'])): ?>
    <div id="alert-sukses" class="mb-6 px-5 py-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3 font-bold text-sm">
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <i class="ph ph-check-circle text-xl"></i>
            </div>
            <?php echo $_SESSION['sukses']; ?>
        </div>
        <button onclick="document.getElementById('alert-sukses').style.display='none'" class="text-emerald-500 hover:text-emerald-700 transition-colors tooltip" title="Tutup">
            <i class="ph ph-x text-lg font-bold"></i>
        </button>
    </div>
<?php unset($_SESSION['sukses']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div id="alert-error" class="mb-6 px-5 py-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3 font-bold text-sm">
            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                <i class="ph ph-warning-circle text-xl"></i>
            </div>
            <?php echo $_SESSION['error']; ?>
        </div>
        <button onclick="document.getElementById('alert-error').style.display='none'" class="text-rose-500 hover:text-rose-700 transition-colors tooltip" title="Tutup">
            <i class="ph ph-x text-lg font-bold"></i>
        </button>
    </div>
<?php unset($_SESSION['error']); endif; ?>

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Guru</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola data akun pengajar dan riwayat masa aktif tahun ajaran.</p>
    </div>
    
    <a href="tambah_guru.php" class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md shrink-0">
        <i class="ph ph-plus-circle text-lg"></i> Tambah Guru
    </a>
</div>

<form action="" method="GET" class="flex flex-col md:flex-row gap-3 items-center bg-white p-3 border border-slate-200 rounded-2xl shadow-sm mb-6">
    
    <div class="relative flex-1 w-full">
        <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari nama, username, atau NIP..." 
            class="w-full pl-5 pr-20 py-3 bg-slate-50 hover:bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
        
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1.5">
            <?php if(!empty($kata_kunci)): ?>
                <a href="?tahun=<?php echo urlencode($tahun_terpilih); ?>" class="p-1 text-rose-500 hover:text-rose-700 transition-colors tooltip" title="Batal Cari">
                    <i class="ph ph-x-circle text-xl"></i>
                </a>
            <?php endif; ?>
            
            <button type="submit" class="p-1 text-slate-400 hover:text-blue-600 transition-colors tooltip" title="Cari Data">
                <i class="ph ph-magnifying-glass text-xl font-bold"></i>
            </button>
        </div>
    </div>

    <div class="relative w-full md:w-56 shrink-0">
        <select name="tahun" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer transition-all">
            <option value="">Semua Tahun Ajaran</option>
            <?php 
            if($q_list_tahun && mysqli_num_rows($q_list_tahun) > 0) {
                while($thn = mysqli_fetch_assoc($q_list_tahun)) {
                    $selected = ($thn['nama_tahun'] == $tahun_terpilih) ? "selected" : "";
                    echo "<option value='{$thn['nama_tahun']}' {$selected}>Tahun {$thn['nama_tahun']}</option>";
                }
            } else {
                $tahun_sekarang = date('Y');
                for($i = 0; $i < 3; $i++) {
                    $thn_str = ($tahun_sekarang - $i) . "/" . ($tahun_sekarang - $i + 1);
                    $selected = ($thn_str == $tahun_terpilih) ? "selected" : "";
                    echo "<option value='{$thn_str}' {$selected}>Tahun {$thn_str}</option>";
                }
            }
            ?>
        </select>
        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
            <i class="ph ph-caret-down text-slate-400"></i>
        </div>
    </div>
</form>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">
                <th class="px-6 py-4 w-12 text-center">No</th>
                <th class="px-6 py-4 min-w-[200px]">Profil Guru</th>
                <th class="px-6 py-4">NIP / Username</th>
                <th class="px-6 py-4 text-center">Mata Pelajaran</th>
                <th class="px-6 py-4 text-center">Masa Aktif (Tahun)</th>
                <th class="px-6 py-4 text-center">Status</th>
                <th class="px-6 py-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            
            <?php
            // ==========================================
            // 2. EKSEKUSI QUERY TABEL
            // ==========================================
           $query_sql = "
                SELECT u.*, mp.nama_mapel 
                FROM users u
                LEFT JOIN mapel mp ON u.id_mapel = mp.id_mapel
                WHERE u.role = 'guru' 
                $query_search 
                $query_tahun
                GROUP BY u.id_user
                ORDER BY u.nama_lengkap ASC
            ";
            
            $q_guru = mysqli_query($koneksi, $query_sql);
            $no = 1;

            if(mysqli_num_rows($q_guru) > 0):
                while($guru = mysqli_fetch_assoc($q_guru)):
                    $inisial = '';
                    $pecah_nama = explode(' ', $guru['nama_lengkap']);
                    if (isset($pecah_nama[0])) $inisial .= strtoupper(substr($pecah_nama[0], 0, 1));
                    if (isset($pecah_nama[1])) $inisial .= strtoupper(substr($pecah_nama[1], 0, 1));
                    
                    $is_aktif = (empty($guru['tahun_keluar']) || strtolower($guru['tahun_keluar']) == 'sekarang');
                    $tahun_masuk_display = !empty($guru['tahun_masuk']) ? $guru['tahun_masuk'] : 'Belum Diatur';
                    $tahun_keluar_display = $is_aktif ? 'Sekarang' : $guru['tahun_keluar'];
            ?>
            <tr class="hover:bg-slate-50 transition-colors group">
                <td class="px-6 py-4 text-sm font-medium text-slate-500 text-center"><?php echo $no++; ?></td>
                
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full <?php echo $is_aktif ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-slate-100 text-slate-400 border-slate-200'; ?> border flex items-center justify-center font-bold text-sm shrink-0 transition-colors">
                            <?php echo $inisial; ?>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($guru['nama_lengkap']); ?></p>
                            <span class="inline-block mt-0.5 px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-bold uppercase tracking-wider rounded border border-blue-100">Pengajar</span>
                        </div>
                    </div>
                </td>
                
                <td class="px-6 py-4">
                    <p class="text-sm text-slate-800 font-bold"><?php echo !empty($guru['nip']) ? htmlspecialchars($guru['nip']) : '-'; ?></p>
                    <p class="text-[11px] font-mono text-slate-500 mt-0.5"><?php echo htmlspecialchars($guru['username']); ?></p>
                </td>
                
                <td class="px-6 py-4 text-center">
                    <?php if(!empty($guru['nama_mapel'])): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 text-xs font-bold rounded-lg whitespace-nowrap">
                            <i class="ph ph-books"></i> <?php echo htmlspecialchars($guru['nama_mapel']); ?>
                        </span>
                    <?php else: ?>
                        <span class="text-xs text-slate-400 italic">Belum mengampu</span>
                    <?php endif; ?>
                </td>

                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded border border-slate-200"><?php echo $tahun_masuk_display; ?></span>
                        <i class="ph ph-arrow-right text-slate-400"></i>
                        <span class="px-2.5 py-1 <?php echo $is_aktif ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200'; ?> text-xs font-bold rounded border"><?php echo $tahun_keluar_display; ?></span>
                    </div>
                </td>
                
                <td class="px-6 py-4 text-center">
                    <?php if($is_aktif): ?>
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                        </span>
                    <?php endif; ?>
                </td>
                
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="edit_guru.php?id=<?php echo $guru['id_user']; ?>" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Edit Riwayat / Profil">
                            <i class="ph ph-pencil-simple text-base"></i>
                        </a>
                        <a href="hapus_guru.php?id=<?php echo $guru['id_user']; ?>" onclick="return confirm('Yakin ingin menghapus guru ini?')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Hapus Guru">
                            <i class="ph ph-trash text-base"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php 
                endwhile;
            else: 
            ?>
            <tr>
                <td colspan="7" class="px-6 py-16 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4">
                        <i class="ph ph-users-slash text-3xl text-slate-400"></i>
                    </div>
                    <h3 class="text-slate-800 font-bold text-lg mb-1">Tidak Ditemukan</h3>
                    <p class="text-sm text-slate-500">Tidak ada data guru yang cocok dengan filter tahun atau pencarian Anda.</p>
                </td>
            </tr>
            <?php endif; ?>
            
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>