<?php
session_start();
// Proteksi: Hanya guru yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; // Ambil ID guru yang sedang login

// PROSES HAPUS MATERI
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Pastikan materi yang dihapus adalah milik guru ini (Keamanan)
    $cek_file = mysqli_query($koneksi, "SELECT file_materi FROM materi WHERE id_materi='$id_hapus' AND id_guru='$id_guru'");
    if(mysqli_num_rows($cek_file) > 0) {
        $data_file = mysqli_fetch_assoc($cek_file);
        $path_file = "../uploads/materi/" . $data_file['file_materi'];
        
        // Hapus file fisik dari folder server jika ada
        if (file_exists($path_file)) {
            unlink($path_file);
        }
        
        // Hapus data dari database
        mysqli_query($koneksi, "DELETE FROM materi WHERE id_materi='$id_hapus'");
        header("Location: materi.php?pesan=dihapus");
        exit;
    }
}

// Fitur Pencarian Materi
$kata_kunci = "";
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
}

include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Materi & Pembelajaran</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola dan bagikan modul pembelajaran untuk siswa Anda.</p>
    </div>
    <a href="tambah_materi.php" class="px-5 py-2.5 bg-guru-600 text-white font-semibold rounded-xl shadow-md shadow-guru-500/30 hover:bg-guru-700 transition-all flex items-center gap-2 text-sm w-fit">
        <i class="ph ph-upload-simple text-lg"></i> Unggah Materi
    </a>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
        <i class="ph ph-check-circle text-xl"></i>
        <span class="text-sm font-semibold">
            <?php 
                if($_GET['pesan'] == 'sukses') echo "Materi baru berhasil diunggah!";
                if($_GET['pesan'] == 'dihapus') echo "Materi berhasil dihapus dari sistem.";
            ?>
        </span>
    </div>
<?php endif; ?>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    
    <div class="p-4 border-b border-slate-100 bg-slate-50/50">
        <form action="" method="GET" class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="ph ph-magnifying-glass text-slate-400 text-lg"></i>
            </div>
            <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari judul materi..." autocomplete="off"
                class="w-full pl-10 pr-24 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-guru-500 focus:ring-4 focus:ring-guru-500/10 transition-all">
            
            <div class="absolute inset-y-0 right-1 flex items-center">
                <?php if(!empty($kata_kunci)): ?>
                    <a href="materi.php" class="p-1 mr-1 text-slate-400 hover:text-rose-500 transition-colors">
                        <i class="ph ph-x-circle text-lg"></i>
                    </a>
                <?php endif; ?>
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-lg text-xs transition-colors">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-widest">
                    <th class="px-6 py-4 font-bold w-16">No</th>
                    <th class="px-6 py-4 font-bold">Informasi Materi</th>
                    <th class="px-6 py-4 font-bold text-center">Kelas</th>
                    <th class="px-6 py-4 font-bold text-center">Mata Pelajaran</th>
                    <th class="px-6 py-4 font-bold text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                
                <?php
                $no = 1;
                // Query JOIN untuk mengambil nama kelas dan nama mapel, dan HANYA milik guru yang login
                $query_sql = "
                    SELECT materi.*, kelas.nama_kelas, mapel.nama_mapel 
                    FROM materi 
                    LEFT JOIN kelas ON materi.id_kelas = kelas.id_kelas 
                    LEFT JOIN mapel ON materi.id_mapel = mapel.id_mapel 
                    WHERE materi.id_guru = '$id_guru'
                ";

                if (!empty($kata_kunci)) {
                    $query_sql .= " AND materi.judul_materi LIKE '%$kata_kunci%' ";
                }
                
                $query_sql .= " ORDER BY materi.tgl_upload DESC";
                $query = mysqli_query($koneksi, $query_sql);

                if(mysqli_num_rows($query) > 0):
                    while($data = mysqli_fetch_assoc($query)):
                        $tanggal = date('d M Y, H:i', strtotime($data['tgl_upload']));
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-6 py-4 text-slate-500 font-medium"><?php echo $no++; ?></td>
                    <td class="px-6 py-4 max-w-sm">
                        <h4 class="font-bold text-slate-800 text-sm truncate"><?php echo htmlspecialchars($data['judul_materi']); ?></h4>
                        <p class="text-[11px] text-slate-400 font-medium mt-1"><i class="ph ph-clock mr-1"></i> Diunggah: <?php echo $tanggal; ?></p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-guru-50 text-guru-600 border border-guru-200 font-bold text-xs rounded-lg">
                            <i class="ph ph-door"></i> <?php echo htmlspecialchars($data['nama_kelas']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-slate-700 font-semibold text-sm">
                            <?php echo htmlspecialchars($data['nama_mapel']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="../uploads/materi/<?php echo $data['file_materi']; ?>" target="_blank" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors tooltip" title="Lihat/Unduh Dokumen">
                                <i class="ph ph-download-simple text-lg"></i>
                            </a>
                            <a href="?hapus=<?php echo $data['id_materi']; ?>" onclick="return confirm('Yakin ingin menghapus materi ini secara permanen?');" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                <i class="ph ph-trash text-lg"></i>
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
                            <i class="ph ph-folder-open text-4xl text-slate-300 mb-3"></i>
                            <?php if(!empty($kata_kunci)): ?>
                                <p class="font-medium text-slate-600">Pencarian "<b><?php echo htmlspecialchars($kata_kunci); ?></b>" tidak ditemukan.</p>
                                <a href="materi.php" class="mt-2 text-sm text-guru-600 font-semibold hover:underline">Tampilkan semua materi</a>
                            <?php else: ?>
                                <p class="font-medium text-slate-600">Anda belum mengunggah materi apapun.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Unggah Materi" di pojok kanan atas untuk memulai.</p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>