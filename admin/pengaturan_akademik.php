<?php
session_start();
// Proteksi halaman admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// ==========================================
// 1. LOGIKA TAMBAH DATA
// ==========================================
if (isset($_POST['simpan'])) {
    $nama_tahun = mysqli_real_escape_string($koneksi, $_POST['nama_tahun']);
    $semester = mysqli_real_escape_string($koneksi, $_POST['semester']);
    
    // Secara default saat ditambah, statusnya tidak aktif (N)
    $query = "INSERT INTO tahun_ajaran (nama_tahun, semester, status_aktif) VALUES ('$nama_tahun', '$semester', 'N')";
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['sukses'] = "Tahun ajaran baru berhasil ditambahkan!";
        header("Location: pengaturan_akademik.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan data!";
    }
}

// ==========================================
// 2. LOGIKA HAPUS DATA
// ==========================================
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Cek apakah yang dihapus sedang aktif? (Mencegah sistem error jika tidak ada tahun aktif)
    $cek_aktif = mysqli_query($koneksi, "SELECT status_aktif FROM tahun_ajaran WHERE id_tahun='$id_hapus'");
    $data_cek = mysqli_fetch_assoc($cek_aktif);
    
    if($data_cek['status_aktif'] == 'Y') {
        $_SESSION['error'] = "Tidak dapat menghapus Tahun Ajaran yang sedang AKTIF!";
    } else {
        mysqli_query($koneksi, "DELETE FROM tahun_ajaran WHERE id_tahun='$id_hapus'");
        $_SESSION['sukses'] = "Tahun ajaran berhasil dihapus!";
    }
    header("Location: pengaturan_akademik.php");
    exit;
}

// ==========================================
// 3. LOGIKA AKTIFKAN TAHUN AJARAN
// ==========================================
if (isset($_GET['aktifkan'])) {
    $id_aktif = mysqli_real_escape_string($koneksi, $_GET['aktifkan']);
    
    // Langkah A: Matikan semua tahun ajaran terlebih dahulu
    mysqli_query($koneksi, "UPDATE tahun_ajaran SET status_aktif='N'");
    
    // Langkah B: Hidupkan hanya tahun ajaran yang dipilih
    mysqli_query($koneksi, "UPDATE tahun_ajaran SET status_aktif='Y' WHERE id_tahun='$id_aktif'");
    
    $_SESSION['sukses'] = "Tahun ajaran berhasil diaktifkan! Seluruh sistem sekarang menggunakan tahun ini.";
    header("Location: pengaturan_akademik.php");
    exit;
}

include '../includes/header.php'; 
?>

<?php if (isset($_SESSION['sukses'])): ?>
    <div id="alert-sukses" class="mb-6 px-5 py-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3 font-bold text-sm">
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0"><i class="ph ph-check-circle text-xl"></i></div>
            <?php echo $_SESSION['sukses']; ?>
        </div>
        <button onclick="document.getElementById('alert-sukses').style.display='none'" class="text-emerald-500 hover:text-emerald-700"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php unset($_SESSION['sukses']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div id="alert-error" class="mb-6 px-5 py-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3 font-bold text-sm">
            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0"><i class="ph ph-warning-circle text-xl"></i></div>
            <?php echo $_SESSION['error']; ?>
        </div>
        <button onclick="document.getElementById('alert-error').style.display='none'" class="text-rose-500 hover:text-rose-700"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php unset($_SESSION['error']); endif; ?>

<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pengaturan Akademik</h1>
    <p class="text-slate-500 text-sm mt-1">Kelola data tahun ajaran dan semester yang sedang berjalan.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-1">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6">
            <h2 class="text-lg font-extrabold text-slate-800 mb-6">Buka Tahun Ajaran Baru</h2>
            
            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Ajaran</label>
                    <input type="text" name="nama_tahun" required placeholder="Contoh: 2026/2027" 
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Semester</label>
                    <div class="relative">
                        <select name="semester" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-slate-400"></i></div>
                    </div>
                </div>

                <button type="submit" name="simpan" class="w-full py-3 mt-2 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors shadow-md">
                    Tambahkan Data
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">
                        <th class="px-6 py-4">Tahun Ajaran</th>
                        <th class="px-6 py-4">Semester</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $q_tahun = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC, semester ASC");
                    while($row = mysqli_fetch_assoc($q_tahun)):
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4 font-bold text-slate-800"><?php echo htmlspecialchars($row['nama_tahun']); ?></td>
                        <td class="px-6 py-4 text-sm text-slate-600 font-medium"><?php echo htmlspecialchars($row['semester']); ?></td>
                        
                        <td class="px-6 py-4 text-center">
                            <?php if($row['status_aktif'] == 'Y'): ?>
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider rounded-full">
                                    Aktif Saat Ini
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-wider rounded-full">
                                    Tidak Aktif
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <?php if($row['status_aktif'] == 'N'): ?>
                                    <a href="?aktifkan=<?php echo $row['id_tahun']; ?>" onclick="return confirm('Aktifkan tahun ajaran ini?')" class="px-4 py-1.5 bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors shadow-sm">
                                        Aktifkan
                                    </a>
                                <?php endif; ?>
                                
                                <a href="edit_tahun.php?id=<?php echo $row['id_tahun']; ?>" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Edit">
                                    <i class="ph ph-pencil-simple text-base"></i>
                                </a>
                                
                                <a href="?hapus=<?php echo $row['id_tahun']; ?>" onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Hapus">
                                    <i class="ph ph-trash text-base"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
            <i class="ph ph-info text-amber-500 text-xl mt-0.5"></i>
            <p class="text-sm text-amber-800 font-medium leading-relaxed">
                Hanya satu Tahun Ajaran yang dapat <b>AKTIF</b> dalam satu waktu. Mengubah Tahun Ajaran akan otomatis mengubah seluruh tampilan data siswa, rapor, dan jadwal yang dilihat oleh Guru dan Siswa.
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>