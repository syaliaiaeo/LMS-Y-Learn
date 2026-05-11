<?php
session_start();
// Proteksi halaman admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// ==========================================
// 1. LOGIKA TAMBAH PENUGASAN BARU
// ==========================================
if (isset($_POST['simpan'])) {
    $id_guru = mysqli_real_escape_string($koneksi, $_POST['id_guru']);
    $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $id_tahun = mysqli_real_escape_string($koneksi, $_POST['id_tahun']);

    // Validasi: Cek apakah guru tersebut sudah ditugaskan di kelas yang sama pada tahun yang sama
    $cek_duplikat = mysqli_query($koneksi, "SELECT * FROM penugasan_guru WHERE id_guru='$id_guru' AND id_kelas='$id_kelas' AND id_tahun='$id_tahun'");
    
    if (mysqli_num_rows($cek_duplikat) > 0) {
        $_SESSION['error'] = "Gagal! Guru tersebut sudah ditugaskan di kelas ini pada tahun ajaran yang dipilih.";
    } else {
        $query_insert = "INSERT INTO penugasan_guru (id_guru, id_kelas, id_tahun) VALUES ('$id_guru', '$id_kelas', '$id_tahun')";
        if (mysqli_query($koneksi, $query_insert)) {
            $_SESSION['sukses'] = "Penugasan mengajar berhasil ditambahkan!";
            header("Location: penugasan_guru.php");
            exit;
        } else {
            $_SESSION['error'] = "Terjadi kesalahan sistem saat menyimpan data.";
        }
    }
}

// ==========================================
// 2. LOGIKA HAPUS PENUGASAN
// ==========================================
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    if (mysqli_query($koneksi, "DELETE FROM penugasan_guru WHERE id_penugasan='$id_hapus'")) {
        $_SESSION['sukses'] = "Jadwal penugasan berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus data penugasan.";
    }
    header("Location: penugasan_guru.php");
    exit;
}

// Filter Tahun untuk tabel (Opsional, agar tabel tidak terlalu panjang)
$filter_tahun = isset($_GET['tahun']) ? mysqli_real_escape_string($koneksi, $_GET['tahun']) : '';

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
    <a href="data_mapel.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Mata Pelajaran
    </a>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Penugasan Kelas</h1>
    <p class="text-slate-500 text-sm mt-1">Atur jadwal dan penempatan guru pengampu ke masing-masing ruang kelas.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-1">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 sticky top-6">
            <h2 class="text-lg font-extrabold text-slate-800 mb-6 flex items-center gap-2">
                <i class="ph ph-plus-circle text-blue-600"></i> Buat Penugasan Baru
            </h2>
            
            <form action="" method="POST" class="space-y-5">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Guru Pengampu</label>
                    <div class="relative">
                        <select name="id_guru" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                            <option value="">-- Pilih Guru --</option>
                            <?php 
                            $q_guru = mysqli_query($koneksi, "SELECT u.id_user, u.nama_lengkap, m.nama_mapel FROM users u LEFT JOIN mapel m ON u.id_mapel = m.id_mapel WHERE u.role='guru' AND u.status='aktif' ORDER BY u.nama_lengkap ASC");
                            while($g = mysqli_fetch_assoc($q_guru)): 
                                $mapel_info = !empty($g['nama_mapel']) ? " (".$g['nama_mapel'].")" : " (Belum ada mapel)";
                            ?>
                                <option value="<?php echo $g['id_user']; ?>"><?php echo htmlspecialchars($g['nama_lengkap']) . $mapel_info; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-slate-400"></i></div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Ruang Kelas</label>
                    <div class="relative">
                        <select name="id_kelas" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                            <option value="">-- Pilih Kelas --</option>
                            <?php 
                            $q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                            while($k = mysqli_fetch_assoc($q_kelas)): 
                            ?>
                                <option value="<?php echo $k['id_kelas']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-slate-400"></i></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Ajaran</label>
                    <div class="relative">
                        <select name="id_tahun" required class="w-full pl-4 pr-10 py-3 bg-blue-50 border border-blue-200 text-blue-900 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                            <?php 
                            $q_tahun = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC");
                            while($t = mysqli_fetch_assoc($q_tahun)): 
                                $is_active = ($t['status_aktif'] == 'Y') ? 'selected' : '';
                            ?>
                                <option value="<?php echo $t['nama_tahun']; ?>" <?php echo $is_active; ?>><?php echo $t['nama_tahun']; ?> <?php echo ($t['status_aktif'] == 'Y') ? '(Aktif)' : ''; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-blue-400"></i></div>
                    </div>
                </div>

                <button type="submit" name="simpan" class="w-full py-3 mt-4 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors shadow-md">
                    Tambahkan Penugasan
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        
        <div class="flex justify-end mb-4">
            <form action="" method="GET" class="flex items-center gap-2">
                <select name="tahun" onchange="this.form.submit()" class="pl-4 pr-10 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 focus:outline-none shadow-sm cursor-pointer">
                    <option value="">Semua Tahun Ajaran</option>
                    <?php 
                    mysqli_data_seek($q_tahun, 0);
                    while($t = mysqli_fetch_assoc($q_tahun)): 
                        $selected_filter = ($t['nama_tahun'] == $filter_tahun) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $t['nama_tahun']; ?>" <?php echo $selected_filter; ?>>Tahun <?php echo $t['nama_tahun']; ?></option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">
                            <th class="px-5 py-4 w-12 text-center">No</th>
                            <th class="px-5 py-4">Guru / Mapel</th>
                            <th class="px-5 py-4 text-center">Ruang Kelas</th>
                            <th class="px-5 py-4 text-center">Tahun Ajaran</th>
                            <th class="px-5 py-4 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        
                        <?php
                        // Query Super Cerdas: Menggabungkan 4 tabel sekaligus!
                        $sql_penugasan = "
                            SELECT pg.*, u.nama_lengkap, m.nama_mapel, k.nama_kelas 
                            FROM penugasan_guru pg
                            JOIN users u ON pg.id_guru = u.id_user
                            JOIN kelas k ON pg.id_kelas = k.id_kelas
                            LEFT JOIN mapel m ON u.id_mapel = m.id_mapel
                        ";
                        
                        if(!empty($filter_tahun)) {
                            $sql_penugasan .= " WHERE pg.id_tahun = '$filter_tahun'";
                        }
                        
                        $sql_penugasan .= " ORDER BY pg.id_tahun DESC, k.nama_kelas ASC, u.nama_lengkap ASC";
                        
                        $q_penugasan = mysqli_query($koneksi, $sql_penugasan);
                        $no = 1;

                        if(mysqli_num_rows($q_penugasan) > 0):
                            while($row = mysqli_fetch_assoc($q_penugasan)):
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-5 py-4 text-xs font-bold text-slate-500 text-center"><?php echo $no++; ?></td>
                            
                            <td class="px-5 py-4">
                                <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($row['nama_lengkap']); ?></p>
                                <span class="inline-flex mt-1 items-center gap-1 px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[10px] font-bold rounded">
                                    <i class="ph ph-book-open-text"></i> <?php echo !empty($row['nama_mapel']) ? htmlspecialchars($row['nama_mapel']) : 'Belum Atur Mapel'; ?>
                                </span>
                            </td>
                            
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold rounded-lg">
                                    <i class="ph ph-door"></i> <?php echo htmlspecialchars($row['nama_kelas']); ?>
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <p class="text-xs font-bold text-slate-600"><?php echo htmlspecialchars($row['id_tahun']); ?></p>
                            </td>
                            
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="?hapus=<?php echo $row['id_penugasan']; ?>" onclick="return confirm('Cabut tugas guru ini dari kelas <?php echo htmlspecialchars($row['nama_kelas']); ?>?')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors tooltip" title="Hapus Penugasan">
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
                            <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <i class="ph ph-calendar-blank text-2xl text-slate-400"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Belum Ada Penugasan</p>
                                    <p class="text-xs mt-1">Gunakan formulir di samping untuk mulai mengatur jadwal.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>