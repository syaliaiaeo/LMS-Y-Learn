<?php
session_start();
// Proteksi halaman guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user'];

// Tangkap tanggal aktif (default: hari ini)
$tanggal_aktif = date('Y-m-d');
if (isset($_GET['tanggal']) && $_GET['tanggal'] != "") {
    $tanggal_aktif = mysqli_real_escape_string($koneksi, $_GET['tanggal']);
}

// ==========================================
// 1. LOGIKA SIMPAN / UPDATE ABSENSI
// ==========================================
if (isset($_POST['simpan_absensi'])) {
    $tanggal_simpan = mysqli_real_escape_string($koneksi, $_POST['tanggal_absen']);
    
    if (isset($_POST['id_siswa']) && is_array($_POST['id_siswa'])) {
        foreach ($_POST['id_siswa'] as $id_siswa => $id_kelas_siswa) {
            // Jika checkbox dicentang maka Hadir, jika tidak maka Alpa
            $status = isset($_POST['kehadiran'][$id_siswa]) ? 'Hadir' : 'Alpa';
            
            // Cek apakah sudah ada rekaman absensi siswa pada tanggal tersebut oleh guru ini
            $cek_absen = mysqli_query($koneksi, "SELECT id_absensi FROM absensi WHERE id_siswa = '$id_siswa' AND tanggal = '$tanggal_simpan' AND id_guru = '$id_guru'");
            
            if (mysqli_num_rows($cek_absen) > 0) {
                // Jika sudah ada, lakukan Update
                mysqli_query($koneksi, "UPDATE absensi SET status = '$status', id_kelas = '$id_kelas_siswa' WHERE id_siswa = '$id_siswa' AND tanggal = '$tanggal_simpan' AND id_guru = '$id_guru'");
            } else {
                // Jika belum ada, lakukan Insert baru
                mysqli_query($koneksi, "INSERT INTO absensi (id_siswa, id_kelas, id_guru, tanggal, status) VALUES ('$id_siswa', '$id_kelas_siswa', '$id_guru', '$tanggal_simpan', '$status')");
            }
        }
        $kelas_redirect = isset($_GET['kelas']) ? $_GET['kelas'] : '';
        echo "<script>alert('Absensi tanggal $tanggal_simpan berhasil disimpan!'); window.location.href='data_siswa.php?kelas=$kelas_redirect&tanggal=$tanggal_simpan';</script>";
    }
}

// ==========================================
// 2. LOGIKA FILTER PENCARIAN & KELAS
// ==========================================
$kata_kunci = "";
$kelas_terpilih = "";

if (isset($_GET['cari'])) {
    $kata_kunci = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
}

if (isset($_GET['kelas']) && $_GET['kelas'] != "") {
    $kelas_terpilih = mysqli_real_escape_string($koneksi, $_GET['kelas']);
}

// Ambil daftar kelas yang diampu guru
$q_list_kelas = mysqli_query($koneksi, "
    SELECT DISTINCT k.id_kelas, k.nama_kelas 
    FROM kelas k 
    JOIN penugasan_guru pg ON k.id_kelas = pg.id_kelas 
    WHERE pg.id_guru = '$id_guru' 
    ORDER BY k.nama_kelas ASC
");

// ==========================================
// 3. QUERY MENGAMBIL DATA SISWA
// ==========================================
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
$query_sql .= " GROUP BY u.id_user ORDER BY k.nama_kelas ASC, u.nama_lengkap ASC";

$q_siswa = mysqli_query($koneksi, $query_sql);
$jumlah_siswa = mysqli_num_rows($q_siswa);

include 'includes/header.php'; 
?>

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <a href="index.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-2">
            <i class="ph ph-arrow-left text-lg"></i> Kembali ke Beranda
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Peserta Didik Saya</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola data siswa sekaligus peninjauan absensi harian.</p>
        <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-xl text-sm font-bold border border-blue-100">
            <i class="ph ph-users-three text-lg"></i> Total: <?php echo $jumlah_siswa; ?> Siswa Diampu
        </div>
    </div>
</div>

<form action="" method="GET" class="flex flex-col md:flex-row gap-3 items-center bg-white p-3 border border-slate-200 rounded-2xl shadow-sm mb-6">
    <input type="hidden" name="tanggal" value="<?php echo $tanggal_aktif; ?>">
    <div class="relative flex-1 w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400 text-lg"></i>
        </div>
        <input type="text" name="cari" value="<?php echo htmlspecialchars($kata_kunci); ?>" placeholder="Cari nama, NISN, atau NIS..." 
            class="w-full pl-11 pr-12 py-3 bg-slate-50 hover:bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 transition-all">
    </div>

    <div class="relative w-full md:w-56 shrink-0">
        <select name="kelas" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-blue-500 appearance-none cursor-pointer transition-all">
            <option value="">Semua Kelas Saya</option>
            <?php 
            while($kls = mysqli_fetch_assoc($q_list_kelas)) {
                $selected = ($kls['id_kelas'] == $kelas_terpilih) ? "selected" : "";
                echo "<option value='{$kls['id_kelas']}' {$selected}>{$kls['nama_kelas']}</option>";
            }
            ?>
        </select>
        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
            <i class="ph ph-caret-down text-slate-400"></i>
        </div>
    </div>
</form>

<form action="" method="POST" autocomplete="off" id="formAbsensi">
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 p-4 bg-white border border-slate-200 rounded-2xl shadow-sm gap-4">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <label class="text-sm font-bold text-slate-700 shrink-0"><i class="ph ph-calendar-blank text-lg align-middle mb-1"></i> Tanggal Absen:</label>
            <input type="date" name="tanggal_absen" value="<?php echo $tanggal_aktif; ?>" 
                onchange="let url = new URL(window.location.href); url.searchParams.set('tanggal', this.value); window.location.href = url.href;" 
                required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 cursor-pointer">
        </div>
        <button type="submit" name="simpan_absensi" class="px-6 py-2 bg-guru-600 hover:bg-guru-700 text-white rounded-xl font-bold transition-colors w-full md:w-auto flex justify-center items-center gap-2 shadow-md shadow-guru-500/20">
            <i class="ph ph-floppy-disk text-lg"></i> Simpan
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4">Profil Siswa</th>
                        <th class="px-6 py-4 text-center">Kelas</th>
                        
                        <th class="px-6 py-4 text-center w-32">
                            <div class="flex flex-col items-center justify-center gap-1.5">
                                <span>Hadir Semua?</span>
                                <input type="checkbox" id="checkAll" onclick="toggleCheckboxes(this)" class="w-4 h-4 text-guru-600 border-slate-300 rounded focus:ring-guru-500 cursor-pointer transition-all tooltip" title="Centang/Hilangkan Semua" autocomplete="off">
                                <script>document.getElementById('checkAll').checked = false;</script>
                            </div>
                        </th>
                        
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $no = 1;
                    if($jumlah_siswa > 0):
                        mysqli_data_seek($q_siswa, 0); 
                        while($siswa = mysqli_fetch_assoc($q_siswa)):
                            $inisial = strtoupper(substr($siswa['nama_lengkap'], 0, 2));
                            
                            // Cek status absensi siswa di database
                            $id_s = $siswa['id_user'];
                            $q_status = mysqli_query($koneksi, "SELECT status FROM absensi WHERE id_siswa = '$id_s' AND tanggal = '$tanggal_aktif' AND id_guru = '$id_guru'");
                            $data_status = mysqli_fetch_assoc($q_status);
                            
                            $is_hadir = ($data_status && $data_status['status'] == 'Hadir') ? true : false;
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
                                    <p class="text-[11px] font-mono text-slate-500 mt-0.5"><i class="ph ph-identification-card"></i> NISN: <?php echo !empty($siswa['nisn']) ? htmlspecialchars($siswa['nisn']) : '-'; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 text-xs font-bold rounded-lg">
                                <i class="ph ph-door"></i> <?php echo htmlspecialchars($siswa['nama_kelas']); ?>
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 text-center">
                            <input type="hidden" name="id_siswa[<?php echo $siswa['id_user']; ?>]" value="<?php echo $siswa['id_kelas']; ?>">
                            <label class="inline-flex items-center justify-center cursor-pointer p-2">
                                <input type="checkbox" id="chk_siswa_<?php echo $siswa['id_user']; ?>" name="kehadiran[<?php echo $siswa['id_user']; ?>]" value="Hadir" 
                                    <?php echo $is_hadir ? 'checked' : ''; ?> 
                                    class="chk-hadir w-5 h-5 text-guru-600 border-slate-300 rounded focus:ring-guru-500 shadow-sm cursor-pointer transition-all" autocomplete="off">
                                    
                                <script>document.getElementById('chk_siswa_<?php echo $siswa['id_user']; ?>').checked = <?php echo $is_hadir ? 'true' : 'false'; ?>;</script>
                            </label>
                        </td>
                        
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="penilaian.php?siswa=<?php echo $siswa['id_user']; ?>" class="px-3 py-1.5 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-colors font-bold text-xs flex items-center gap-1">
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
                        <td colspan="5" class="px-6 py-16 text-center text-slate-500">Data tidak ditemukan.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<script>
    function toggleCheckboxes(source) {
        const checkboxes = document.querySelectorAll('.chk-hadir');
        for (let i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = document.querySelectorAll('.chk-hadir');
        const masterCheck = document.getElementById('checkAll');
        
        // Logika agar master-check lepas jika salah satu anak di-uncheck
        checkboxes.forEach(function(chk) {
            chk.addEventListener('change', function() {
                if (!this.checked && masterCheck) {
                    masterCheck.checked = false;
                } else if (masterCheck) {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    masterCheck.checked = allChecked;
                }
            });
        });

        // Cek kondisi awal saat dimuat
        if (masterCheck && checkboxes.length > 0) {
            const allChecked = Array.from(checkboxes).every(c => c.checked);
            masterCheck.checked = allChecked;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>