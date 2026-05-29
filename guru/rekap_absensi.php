<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user'];

$kelas_terpilih = "";
if (isset($_GET['kelas']) && $_GET['kelas'] != "") {
    $kelas_terpilih = mysqli_real_escape_string($koneksi, $_GET['kelas']);
}

// Ambil daftar kelas
$q_list_kelas = mysqli_query($koneksi, "
    SELECT DISTINCT k.id_kelas, k.nama_kelas 
    FROM kelas k 
    JOIN penugasan_guru pg ON k.id_kelas = pg.id_kelas 
    WHERE pg.id_guru = '$id_guru' 
    ORDER BY k.nama_kelas ASC
");

include 'includes/header.php'; 
?>

<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Rekap Absensi Siswa</h1>
    <p class="text-slate-500 text-sm mt-1">Akumulasi persentase log kehadiran peserta didik secara menyeluruh.</p>
</div>

<form action="" method="GET" class="flex gap-3 items-center bg-white p-3 border border-slate-200 rounded-2xl shadow-sm mb-6 max-w-xs">
    <div class="relative w-full">
        <select name="kelas" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-blue-500 appearance-none cursor-pointer transition-all">
            <option value="">Pilih Ruang Kelas...</option>
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

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">
                    <th class="px-6 py-4 w-12 text-center">No</th>
                    <th class="px-6 py-4">Nama Lengkap</th>
                    <th class="px-6 py-4 text-center">Kelas</th>
                    <th class="px-6 py-4 text-center text-emerald-600">Total Hadir</th>
                    <th class="px-6 py-4 text-center text-rose-600">Total Alpa</th>
                    <th class="px-6 py-4 text-center">Persentase Kehadiran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php
                if(!empty($kelas_terpilih)) {
                    // Query agregasi menghitung rekap absensi per siswa
                    $query_rekap = "
                        SELECT u.id_user, u.nama_lengkap, k.nama_kelas,
                        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
                        SUM(CASE WHEN a.status = 'Alpa' THEN 1 ELSE 0 END) as total_alpa
                        FROM users u
                        JOIN kelas k ON u.id_kelas = k.id_kelas
                        LEFT JOIN absensi a ON u.id_user = a.id_siswa AND a.id_guru = '$id_guru'
                        WHERE u.role = 'siswa' AND u.id_kelas = '$kelas_terpilih'
                        GROUP BY u.id_user
                        ORDER BY u.nama_lengkap ASC
                    ";
                    $q_rekap = mysqli_query($koneksi, $query_rekap);
                    $no = 1;

                    if(mysqli_num_rows($q_rekap) > 0) {
                        while($row = mysqli_fetch_assoc($q_rekap)) {
                            $hadir = $row['total_hadir'] ? $row['total_hadir'] : 0;
                            $alpa = $row['total_alpa'] ? $row['total_alpa'] : 0;
                            $total_hari = $hadir + $alpa;
                            $persen = $total_hari > 0 ? round(($hadir / $total_hari) * 100) . '%' : '0%';
                ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-slate-500 text-center"><?php echo $no++; ?></td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-800"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td class="px-6 py-4 text-center text-sm text-slate-600"><?php echo htmlspecialchars($row['nama_kelas']); ?></td>
                    <td class="px-6 py-4 text-center text-sm font-bold text-emerald-600"><?php echo $hadir; ?> Hari</td>
                    <td class="px-6 py-4 text-center text-sm font-bold text-rose-600"><?php echo $alpa; ?> Hari</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">
                            <?php echo $persen; ?>
                        </span>
                    </td>
                </tr>
                <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='px-6 py-8 text-center text-slate-500'>Tidak ada data siswa di kelas ini.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='px-6 py-12 text-center text-slate-500'><i class='ph ph-info text-2xl mb-2 block text-slate-400'></i>Silakan tentukan kelas terlebih dahulu untuk meninjau rekap.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>