<?php
session_start();
// Proteksi halaman siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_siswa = $_SESSION['id_user'];

// ==========================================
// 1. HITUNG STATISTIK ABSENSI SISWA
// ==========================================
$q_stats = mysqli_query($koneksi, "
    SELECT 
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
        SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as total_alpa
    FROM absensi 
    WHERE id_siswa = '$id_siswa'
");
$stats = mysqli_fetch_assoc($q_stats);

$hadir = $stats['total_hadir'] ? $stats['total_hadir'] : 0;
$alpa = $stats['total_alpa'] ? $stats['total_alpa'] : 0;
$total_hari = $hadir + $alpa;

// Hitung persentase kehadiran
$persentase = $total_hari > 0 ? round(($hadir / $total_hari) * 100) : 0;

// ==========================================
// 2. AMBIL RIWAYAT ABSENSI DETAIL
// ==========================================
$q_riwayat = mysqli_query($koneksi, "
    SELECT a.*, u.nama_lengkap as nama_guru
    FROM absensi a
    JOIN users u ON a.id_guru = u.id_user
    WHERE a.id_siswa = '$id_siswa'
    ORDER BY a.tanggal DESC, a.waktu_absen DESC
");

include 'includes/header.php'; 
?>

<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Absensi Saya</h1>
    <p class="text-slate-500 text-sm mt-1">Pantau ringkasan kehadiran dan riwayat log absensi Anda selama semester aktif.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0">
            <i class="ph ph-chart-pie"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Persentase Kehadiran</p>
            <h3 class="text-2xl font-black text-slate-900 mt-1"><?php echo $persentase; ?>%</h3>
        </div>
    </div>

    <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shrink-0">
            <i class="ph ph-check-circle"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Hadir</p>
            <h3 class="text-2xl font-black text-slate-900 mt-1"><?php echo $hadir; ?> <span class="text-sm font-medium text-slate-500">Hari</span></h3>
        </div>
    </div>

    <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl shrink-0">
            <i class="ph ph-x-circle"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Alpa</p>
            <h3 class="text-2xl font-black text-slate-900 mt-1"><?php echo $alpa; ?> <span class="text-sm font-medium text-slate-500">Hari</span></h3>
        </div>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100">
        <h2 class="text-lg font-bold text-slate-800">Riwayat Kehadiran Harian</h2>
        <p class="text-xs text-slate-500 mt-0.5">Daftar kronologis pencatatan presensi oleh guru pengajar.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">
                    <th class="px-6 py-4 w-12 text-center">No</th>
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4">Guru Pengabsen</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Waktu Log</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php
                $no = 1;
                if (mysqli_num_rows($q_riwayat) > 0) {
                    while ($row = mysqli_fetch_assoc($q_riwayat)) {
                        // Atur badge status berdasarkan tipe kehadiran
                        if ($row['status'] == 'Hadir') {
                            $badge_class = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                            $icon = 'ph-check';
                        } else {
                            $badge_class = 'bg-rose-50 text-rose-700 border-rose-100';
                            $icon = 'ph-x';
                        }
                        
                        // Format tanggal indonesia sederhana
                        $tanggal_fmt = date('d M Y', strtotime($row['tanggal']));
                        $waktu_log = date('H:i', strtotime($row['waktu_absen']));
                ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-slate-500 text-center"><?php echo $no++; ?></td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <i class="ph ph-calendar text-slate-400 text-base"></i>
                            <?php echo $tanggal_fmt; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        <?php echo htmlspecialchars($row['nama_guru']); ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 border text-xs font-bold rounded-lg <?php echo $badge_class; ?>">
                            <i class="ph <?php echo $icon; ?> font-bold"></i> <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-xs text-slate-400 font-mono">
                        <?php echo $waktu_log; ?> WIB
                    </td>
                </tr>
                <?php
                    }
                } else {
                    ?>
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                <i class="ph ph-calendar-blank text-3xl text-slate-400"></i>
                            </div>
                            <h3 class="text-slate-800 font-bold text-lg mb-1">Belum Ada Data</h3>
                            <p class="text-sm">Guru belum menginput record kehadiran Anda pada sistem.</p>
                        </div>
                    </td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>