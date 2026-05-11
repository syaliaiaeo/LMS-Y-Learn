<?php
session_start();
// Proteksi halaman admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// Proses Hapus Mapel
if(isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Cek apakah mapel ini sedang dipakai oleh guru?
    $cek_guru = mysqli_query($koneksi, "SELECT id_user FROM users WHERE id_mapel='$id_hapus'");
    if(mysqli_num_rows($cek_guru) > 0) {
        $_SESSION['error'] = "Gagal menghapus! Mata pelajaran ini masih diampu oleh beberapa guru.";
    } else {
        mysqli_query($koneksi, "DELETE FROM mapel WHERE id_mapel='$id_hapus'");
        $_SESSION['sukses'] = "Mata Pelajaran berhasil dihapus!";
    }
    header("Location: data_mapel.php");
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

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Mata Pelajaran</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola daftar kurikulum mata pelajaran sekolah.</p>
    </div>
    
    <div class="flex items-center gap-3 shrink-0">
        <a href="penugasan_guru.php" class="px-5 py-2.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-sm">
            <i class="ph ph-chalkboard-teacher text-lg"></i> Penugasan Kelas
        </a>
        <a href="tambah_mapel.php" class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
            <i class="ph ph-plus-circle text-lg"></i> Tambah Mapel
        </a>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden max-w-5xl">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">
                <th class="px-6 py-4 w-16 text-center">No</th>
                <th class="px-6 py-4">Mata Pelajaran</th>
                <th class="px-6 py-4 text-center">Total Guru</th>
                <th class="px-6 py-4 text-center">Distribusi Kelas</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
            
            <?php
            // Query cerdas: Menampilkan mapel & menghitung berapa guru spesialis dan penyebaran kelasnya
            $query_mapel = "
                SELECT m.*, 
                (SELECT COUNT(id_user) FROM users WHERE role='guru' AND id_mapel = m.id_mapel) as jml_guru,
                (SELECT COUNT(DISTINCT pg.id_kelas) FROM penugasan_guru pg JOIN users u ON pg.id_guru = u.id_user WHERE u.id_mapel = m.id_mapel) as jml_kelas
                FROM mapel m 
                ORDER BY m.nama_mapel ASC
            ";
            $q_mapel = mysqli_query($koneksi, $query_mapel);
            $no = 1;

            if(mysqli_num_rows($q_mapel) > 0):
                while($mapel = mysqli_fetch_assoc($q_mapel)):
            ?>
            <tr class="hover:bg-slate-50 transition-colors group">
                <td class="px-6 py-4 text-slate-500 font-bold text-center"><?php echo $no++; ?></td>
                
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold shrink-0">
                            <i class="ph ph-book-open-text text-xl"></i>
                        </div>
                        <span class="font-extrabold text-slate-800 text-base"><?php echo htmlspecialchars($mapel['nama_mapel']); ?></span>
                    </div>
                </td>
                
                <td class="px-6 py-4 text-center">
                    <?php if($mapel['jml_guru'] > 0): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-full">
                            <i class="ph ph-users text-sm"></i> <?php echo $mapel['jml_guru']; ?> Guru
                        </span>
                    <?php else: ?>
                        <span class="text-xs text-slate-400 font-medium italic">Belum ada guru</span>
                    <?php endif; ?>
                </td>

                <td class="px-6 py-4 text-center">
                    <span class="text-slate-600 font-bold"><?php echo $mapel['jml_kelas']; ?> Kelas</span>
                </td>
                
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="edit_mapel.php?id=<?php echo $mapel['id_mapel']; ?>" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Edit Mapel">
                            <i class="ph ph-pencil-simple text-base"></i>
                        </a>
                        <a href="?hapus=<?php echo $mapel['id_mapel']; ?>" onclick="return confirm('Yakin ingin menghapus mapel ini?')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Hapus Mapel">
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
                <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                            <i class="ph ph-books text-3xl text-slate-400"></i>
                        </div>
                        <h3 class="text-slate-800 font-bold text-lg mb-1">Data Masih Kosong</h3>
                        <p class="text-sm">Belum ada mata pelajaran yang ditambahkan.</p>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>