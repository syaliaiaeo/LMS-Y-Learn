<?php
session_start();
// Proteksi: Hanya guru yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 

// Atur zona waktu
date_default_timezone_set('Asia/Jakarta');

// ==========================================
// PROSES HAPUS UJIAN
// ==========================================
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Pastikan ujian yang dihapus adalah milik guru ini
    $cek_ujian = mysqli_query($koneksi, "SELECT * FROM ujian WHERE id_ujian='$id_hapus' AND id_guru='$id_guru'");
    if(mysqli_num_rows($cek_ujian) > 0) {
        // Hapus data ujian utama 
        mysqli_query($koneksi, "DELETE FROM ujian WHERE id_ujian='$id_hapus'");
        $_SESSION['sukses'] = "Data ujian berhasil dihapus dari sistem.";
    } else {
        $_SESSION['error'] = "Gagal menghapus! Ujian tidak ditemukan atau bukan milik Anda.";
    }
    header("Location: ujian.php");
    exit;
}

// Fitur Pencarian
$kata_kunci = "";
if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
}

include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kuis & Ujian</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola jadwal ujian, kuis harian, dan susun butir soal untuk siswa.</p>
    </div>
    <a href="tambah_ujian.php" class="px-5 py-2.5 bg-guru-600 text-white font-semibold rounded-xl shadow-md shadow-guru-500/30 hover:bg-guru-700 transition-all flex items-center gap-2 text-sm w-fit">
        <i class="ph ph-plus-circle text-lg"></i> Buat Ujian Baru
    </a>
</div>

<?php if (isset($_SESSION['sukses'])): ?>
    <div id="alert-sukses" class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="ph ph-check-circle text-xl"></i>
            <span class="text-sm font-bold"><?php echo $_SESSION['sukses']; ?></span>
        </div>
        <button onclick="document.getElementById('alert-sukses').style.display='none'" class="text-emerald-500 hover:text-emerald-700"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php unset($_SESSION['sukses']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div id="alert-error" class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="ph ph-warning-circle text-xl"></i>
            <span class="text-sm font-bold"><?php echo $_SESSION['error']; ?></span>
        </div>
        <button onclick="document.getElementById('alert-error').style.display='none'" class="text-rose-500 hover:text-rose-700"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php unset($_SESSION['error']); endif; ?>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    
    <div class="p-4 border-b border-slate-100 bg-slate-50/50">
        <form action="" method="GET" class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="ph ph-magnifying-glass text-slate-400 text-lg"></i>
            </div>
            <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari judul ujian atau kuis..." autocomplete="off"
                class="w-full pl-10 pr-24 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-guru-500 focus:ring-4 focus:ring-guru-500/10 transition-all">
            
            <div class="absolute inset-y-0 right-1 flex items-center">
                <?php if(!empty($kata_kunci)): ?>
                    <a href="ujian.php" class="p-1 mr-1 text-slate-400 hover:text-rose-500 transition-colors">
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
                    <th class="px-6 py-4 font-bold">Informasi Ujian</th>
                    <th class="px-6 py-4 font-bold">Kelas & Mapel</th>
                    <th class="px-6 py-4 font-bold">Waktu Pelaksanaan</th>
                    <th class="px-6 py-4 font-bold text-center">Status</th>
                    <th class="px-6 py-4 font-bold text-center w-40">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                
                <?php
                $no = 1;
                $waktu_sekarang = date('Y-m-d H:i:s');

                $query_sql = "
                    SELECT ujian.*, kelas.nama_kelas, mapel.nama_mapel 
                    FROM ujian 
                    LEFT JOIN kelas ON ujian.id_kelas = kelas.id_kelas 
                    LEFT JOIN mapel ON ujian.id_mapel = mapel.id_mapel 
                    WHERE ujian.id_guru = '$id_guru'
                ";

                if (!empty($kata_kunci)) {
                    $query_sql .= " AND ujian.judul_ujian LIKE '%$kata_kunci%' ";
                }
                
                $query_sql .= " ORDER BY ujian.tgl_mulai DESC";
                $query = mysqli_query($koneksi, $query_sql);

                if(mysqli_num_rows($query) > 0):
                    while($data = mysqli_fetch_assoc($query)):
                        // Cegah error jika tgl_mulai / tgl_selesai masih kosong
                        $mulai = !empty($data['tgl_mulai']) ? date('d M Y, H:i', strtotime($data['tgl_mulai'])) : '-';
                        $selesai = !empty($data['tgl_selesai']) ? date('d M Y, H:i', strtotime($data['tgl_selesai'])) : '-';
                        $durasi = !empty($data['durasi']) ? $data['durasi'] : '0';
                        
                        // LOGIKA 3 STATUS UJIAN
                        $status_badge = "";
                        if (empty($data['tgl_mulai']) || empty($data['tgl_selesai'])) {
                            $status_badge = '<span class="px-3 py-1 bg-slate-100 text-slate-500 font-bold text-[10px] uppercase tracking-wider rounded-full"><i class="ph ph-warning-circle mr-1"></i> Draft</span>';
                        } elseif ($waktu_sekarang < $data['tgl_mulai']) {
                            $status_badge = '<span class="px-3 py-1 bg-amber-100 text-amber-700 font-bold text-[10px] uppercase tracking-wider rounded-full"><i class="ph ph-clock mr-1"></i> Belum Mulai</span>';
                        } elseif ($waktu_sekarang >= $data['tgl_mulai'] && $waktu_sekarang <= $data['tgl_selesai']) {
                            $status_badge = '<span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold text-[10px] uppercase tracking-wider rounded-full animate-pulse"><i class="ph ph-broadcast mr-1"></i> Berlangsung</span>';
                        } else {
                            $status_badge = '<span class="px-3 py-1 bg-rose-100 text-rose-600 font-bold text-[10px] uppercase tracking-wider rounded-full"><i class="ph ph-lock-key mr-1"></i> Selesai</span>';
                        }
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-6 py-4 text-slate-500 font-medium"><?php echo $no++; ?></td>
                    <td class="px-6 py-4 max-w-xs">
                        <h4 class="font-bold text-slate-800 text-sm truncate"><?php echo htmlspecialchars($data['judul_ujian']); ?></h4>
                        <p class="text-xs text-slate-500 font-medium mt-1"><i class="ph ph-hourglass-high mr-1"></i> Durasi: <b><?php echo $durasi; ?> Menit</b></p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1">
                            <span class="inline-flex w-fit items-center gap-1 px-2 py-0.5 bg-guru-50 text-guru-600 border border-guru-200 font-bold text-[10px] uppercase rounded">
                                <i class="ph ph-door"></i> <?php echo htmlspecialchars($data['nama_kelas']); ?>
                            </span>
                            <span class="text-slate-600 font-semibold text-xs"><?php echo htmlspecialchars($data['nama_mapel']); ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-slate-600 space-y-1">
                            <p><span class="inline-block w-12 font-semibold text-slate-400">Mulai</span> : <?php echo $mulai; ?></p>
                            <p><span class="inline-block w-12 font-semibold text-slate-400">Selesai</span> : <span class="font-bold text-slate-800"><?php echo $selesai; ?></span></p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php echo $status_badge; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="kelola_soal.php?id_ujian=<?php echo $data['id_ujian']; ?>" class="px-3 py-1.5 bg-guru-50 text-guru-700 hover:bg-guru-600 hover:text-white border border-guru-200 font-bold text-xs rounded-lg transition-colors tooltip" title="Susun Soal Ujian">
                                <i class="ph ph-list-numbers text-sm"></i> Soal
                            </a>
                            
                            <a href="hasil_ujian.php?id_ujian=<?php echo $data['id_ujian']; ?>" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors tooltip" title="Lihat Nilai Siswa">
                                <i class="ph ph-medal text-lg"></i>
                            </a>
                            
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity ml-1 border-l border-slate-200 pl-2">
                                <a href="edit_ujian.php?id=<?php echo $data['id_ujian']; ?>" class="p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Jadwal">
                                    <i class="ph ph-pencil-simple text-base"></i>
                                </a>
                                <a href="?hapus=<?php echo $data['id_ujian']; ?>" onclick="return confirm('Yakin menghapus ujian ini? Semua data soal dan nilai siswa terkait akan hilang!');" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Ujian">
                                    <i class="ph ph-trash text-base"></i>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="ph ph-exam text-4xl text-slate-300 mb-3"></i>
                            <?php if(!empty($kata_kunci)): ?>
                                <p class="font-medium text-slate-600">Pencarian "<b><?php echo htmlspecialchars($kata_kunci); ?></b>" tidak ditemukan.</p>
                                <a href="ujian.php" class="mt-2 text-sm text-guru-600 font-semibold hover:underline">Tampilkan semua ujian</a>
                            <?php else: ?>
                                <p class="font-medium text-slate-600">Belum ada jadwal ujian atau kuis.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Buat Ujian Baru" untuk menjadwalkan evaluasi.</p>
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