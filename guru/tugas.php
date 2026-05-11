<?php
session_start();
// Proteksi: Hanya guru yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 

// Atur zona waktu sesuai Indonesia
date_default_timezone_set('Asia/Jakarta');

// ==========================================
// PROSES HAPUS TUGAS
// ==========================================
if(isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Keamanan ekstra: Pastikan tugas yang dihapus milik guru yang sedang login
    $query_hapus = "DELETE FROM tugas WHERE id_tugas='$id_hapus' AND id_guru='$id_guru'";
    if(mysqli_query($koneksi, $query_hapus)) {
        $_SESSION['sukses'] = "Data tugas berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus tugas: " . mysqli_error($koneksi);
    }
    header("Location: tugas.php");
    exit;
}

// Fitur Pencarian
$kata_kunci = "";
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
}

include 'includes/header.php'; 
?>

<?php if (isset($_SESSION['sukses'])): ?>
    <div id="alert-sukses" class="mb-6 px-5 py-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3 font-bold text-sm">
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0"><i class="ph ph-check-circle text-xl"></i></div>
            <?php echo $_SESSION['sukses']; ?>
        </div>
        <button onclick="document.getElementById('alert-sukses').style.display='none'" class="text-emerald-500 hover:text-emerald-700 transition-colors tooltip" title="Tutup"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php unset($_SESSION['sukses']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div id="alert-error" class="mb-6 px-5 py-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3 font-bold text-sm">
            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0"><i class="ph ph-warning-circle text-xl"></i></div>
            <?php echo $_SESSION['error']; ?>
        </div>
        <button onclick="document.getElementById('alert-error').style.display='none'" class="text-rose-500 hover:text-rose-700 transition-colors tooltip" title="Tutup"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php unset($_SESSION['error']); endif; ?>

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Kelola Tugas</h1>
        <p class="text-slate-500 text-sm mt-1">Berikan penugasan dan atur tenggat waktu (deadline) untuk siswa.</p>
    </div>
    
    <a href="tambah_tugas.php" class="px-5 py-2.5 bg-[#10B981] hover:bg-emerald-600 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md shadow-emerald-500/20 shrink-0">
        <i class="ph ph-plus-circle text-lg"></i> Buat Tugas Baru
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    
    <div class="p-4 border-b border-slate-100 bg-slate-50/50">
        <form action="" method="GET" class="relative max-w-lg">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="ph ph-magnifying-glass text-slate-400 text-lg"></i>
            </div>
            <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari judul tugas..." autocomplete="off"
                class="w-full pl-11 pr-24 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
            
            <div class="absolute inset-y-0 right-1.5 flex items-center">
                <?php if(!empty($kata_kunci)): ?>
                    <a href="tugas.php" class="p-1 mr-1 text-slate-400 hover:text-rose-500 transition-colors tooltip" title="Hapus Pencarian">
                        <i class="ph ph-x-circle text-lg"></i>
                    </a>
                <?php endif; ?>
                <button type="submit" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition-colors">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">
                    <th class="px-6 py-4 w-12 text-center">No</th>
                    <th class="px-6 py-4">Informasi Tugas</th>
                    <th class="px-6 py-4">Kelas & Mapel</th>
                    <th class="px-6 py-4">Batas Waktu (Deadline)</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                
                <?php
                $no = 1;
                $waktu_sekarang = date('Y-m-d H:i:s');

                // Query mengambil data tugas + join kelas & mapel
                $query_sql = "
                    SELECT t.*, k.nama_kelas, m.nama_mapel 
                    FROM tugas t
                    LEFT JOIN kelas k ON t.id_kelas = k.id_kelas
                    LEFT JOIN mapel m ON t.id_mapel = m.id_mapel
                    WHERE t.id_guru = '$id_guru'
                ";

                if (!empty($kata_kunci)) {
                    $query_sql .= " AND t.judul_tugas LIKE '%$kata_kunci%'";
                }
                
                $query_sql .= " ORDER BY t.tgl_selesai DESC";
                $q_tugas = mysqli_query($koneksi, $query_sql);

                if(mysqli_num_rows($q_tugas) > 0):
                    while($tugas = mysqli_fetch_assoc($q_tugas)):
                        
                        // Konversi format tanggal selesai menjadi deadline yang ditampilkan
                        $deadline = !empty($tugas['tgl_selesai']) ? date('d M Y, H:i', strtotime($tugas['tgl_selesai'])) : 'Belum diatur';
                        
                        // LOGIKA CEK STATUS BERDASARKAN TGL_MULAI & TGL_SELESAI
                        $status_deadline = "";
                        if (empty($tugas['tgl_mulai']) || empty($tugas['tgl_selesai'])) {
                            $status_deadline = '<span class="text-slate-500 text-xs font-medium"><i class="ph ph-minus"></i> Waktu belum diatur</span>';
                        } elseif ($waktu_sekarang < $tugas['tgl_mulai']) {
                            $status_deadline = '<span class="text-amber-500 text-xs font-medium"><i class="ph ph-clock"></i> Belum Dibuka</span>';
                        } elseif ($waktu_sekarang > $tugas['tgl_selesai']) {
                            $status_deadline = '<span class="text-rose-500 text-xs font-medium"><i class="ph ph-warning-circle"></i> Ditutup (Berakhir)</span>';
                        } else {
                            $status_deadline = '<span class="text-emerald-500 text-xs font-medium animate-pulse"><i class="ph ph-broadcast"></i> Sedang Berjalan</span>';
                        }
                ?>
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="px-6 py-4 text-sm font-medium text-slate-500 text-center"><?php echo $no++; ?></td>
                    
                    <td class="px-6 py-4 max-w-[200px]">
                        <h4 class="text-sm font-extrabold text-slate-800 uppercase truncate" title="<?php echo htmlspecialchars($tugas['judul_tugas']); ?>">
                            <?php echo htmlspecialchars($tugas['judul_tugas']); ?>
                        </h4>
                    </td>
                    
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold rounded-md w-fit">
                                <i class="ph ph-door"></i> <?php echo htmlspecialchars($tugas['nama_kelas']); ?>
                            </span>
                            <span class="text-xs text-slate-600 font-medium"><?php echo htmlspecialchars($tugas['nama_mapel']); ?></span>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2 text-sm font-bold text-slate-800">
                                <i class="ph ph-calendar-blank text-slate-400"></i>
                                <?php echo $deadline; ?>
                            </div>
                            <?php echo $status_deadline; ?>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            
                            <a href="penilaian.php?id_tugas=<?php echo $tugas['id_tugas']; ?>" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Lihat Hasil Pengumpulan">
                                <i class="ph ph-eye text-base"></i>
                            </a>

                            <a href="edit_tugas.php?id=<?php echo $tugas['id_tugas']; ?>" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-colors tooltip" title="Edit Tugas">
                                <i class="ph ph-pencil-simple text-base"></i>
                            </a>

                            <a href="?hapus=<?php echo $tugas['id_tugas']; ?>" onclick="return confirm('Yakin ingin menghapus tugas ini? File pengumpulan dari siswa terkait tugas ini akan ikut terhapus.')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Hapus Tugas">
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
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                <i class="ph ph-clipboard-text text-3xl text-slate-400"></i>
                            </div>
                            <?php if(!empty($kata_kunci)): ?>
                                <h3 class="text-slate-800 font-bold text-lg mb-1">Tidak Ditemukan</h3>
                                <p class="text-sm">Tugas dengan judul "<b><?php echo htmlspecialchars($kata_kunci); ?></b>" tidak ada.</p>
                                <a href="tugas.php" class="mt-2 text-sm text-emerald-600 font-bold hover:underline">Lihat semua tugas</a>
                            <?php else: ?>
                                <h3 class="text-slate-800 font-bold text-lg mb-1">Belum Ada Tugas</h3>
                                <p class="text-sm max-w-md mx-auto">Anda belum memberikan penugasan apa pun ke kelas. Klik tombol hijau "Buat Tugas Baru" untuk mulai.</p>
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