<?php
session_start();
// Proteksi: Hanya role 'siswa' yang boleh mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_siswa = $_SESSION['id_user']; 
$id_kelas_siswa = $_SESSION['id_kelas'];

// ==========================================
// LOGIKA PENGUMPULAN & PENGELOMPOKAN NILAI
// ==========================================
$raport = [];

// 1. Ambil Nilai Tugas
$q_tugas = mysqli_query($koneksi, "
    SELECT m.id_mapel, m.nama_mapel, t.judul_tugas, pt.nilai
    FROM pengumpulan_tugas pt
    JOIN tugas t ON pt.id_tugas = t.id_tugas
    JOIN mapel m ON t.id_mapel = m.id_mapel
    WHERE pt.id_siswa = '$id_siswa' AND pt.nilai IS NOT NULL AND t.id_kelas = '$id_kelas_siswa'
");

while($row = mysqli_fetch_assoc($q_tugas)) {
    $id_m = $row['id_mapel'];
    if(!isset($raport[$id_m])) {
        $raport[$id_m] = ['nama_mapel' => $row['nama_mapel'], 'tugas' => [], 'ujian' => []];
    }
    $raport[$id_m]['tugas'][] = ['judul' => $row['judul_tugas'], 'nilai' => $row['nilai']];
}

// 2. Ambil Nilai Ujian (Menggunakan kolom 'nilai' sesuai perbaikan kita sebelumnya)
$q_ujian = mysqli_query($koneksi, "
    SELECT m.id_mapel, m.nama_mapel, u.judul_ujian, nu.nilai
    FROM nilai_ujian nu
    JOIN ujian u ON nu.id_ujian = u.id_ujian
    JOIN mapel m ON u.id_mapel = m.id_mapel
    WHERE nu.id_siswa = '$id_siswa' AND nu.nilai IS NOT NULL AND u.id_kelas = '$id_kelas_siswa'
");

while($row = mysqli_fetch_assoc($q_ujian)) {
    $id_m = $row['id_mapel'];
    if(!isset($raport[$id_m])) {
        $raport[$id_m] = ['nama_mapel' => $row['nama_mapel'], 'tugas' => [], 'ujian' => []];
    }
    $raport[$id_m]['ujian'][] = ['judul' => $row['judul_ujian'], 'nilai' => $row['nilai']];
}

include 'includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Raport Nilai</h1>
    <p class="text-slate-500 text-sm mt-1">Ringkasan seluruh nilai tugas dan ujian Anda yang telah dikoreksi oleh guru.</p>
</div>

<?php if(empty($raport)): ?>
    <div class="bg-white border border-slate-200 border-dashed rounded-2xl p-12 text-center flex flex-col items-center justify-center">
        <i class="ph ph-medal text-5xl text-slate-300 mb-4"></i>
        <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Nilai</h3>
        <p class="text-slate-500 text-sm max-w-md mx-auto">Anda belum memiliki nilai yang masuk ke raport. Terus kerjakan tugas dan ujian dengan baik!</p>
    </div>
<?php else: ?>
    
    <div class="space-y-8">
        <?php 
        // LOOPING UNTUK SETIAP MATA PELAJARAN
        foreach($raport as $id_mapel => $data): 
            // Hitung Rata-rata Tugas
            $total_tugas = 0; $jml_tugas = count($data['tugas']);
            foreach($data['tugas'] as $t) { $total_tugas += $t['nilai']; }
            $rata_tugas = ($jml_tugas > 0) ? round($total_tugas / $jml_tugas, 1) : 0;

            // Hitung Rata-rata Ujian
            $total_ujian = 0; $jml_ujian = count($data['ujian']);
            foreach($data['ujian'] as $u) { $total_ujian += $u['nilai']; }
            $rata_ujian = ($jml_ujian > 0) ? round($total_ujian / $jml_ujian, 1) : 0;

            // Hitung Nilai Akhir (Asumsi bobot seimbang 50:50. Bisa disesuaikan)
            $nilai_akhir = 0;
            if($jml_tugas > 0 && $jml_ujian > 0) { $nilai_akhir = round(($rata_tugas + $rata_ujian) / 2, 1); }
            elseif($jml_tugas > 0) { $nilai_akhir = $rata_tugas; }
            elseif($jml_ujian > 0) { $nilai_akhir = $rata_ujian; }

            // Penentuan Predikat & Warna
            if($nilai_akhir >= 90) { $predikat = 'A'; $warna = 'emerald'; $teks = 'Sangat Baik'; }
            elseif($nilai_akhir >= 80) { $predikat = 'B'; $warna = 'blue'; $teks = 'Baik'; }
            elseif($nilai_akhir >= 70) { $predikat = 'C'; $warna = 'amber'; $teks = 'Cukup'; }
            else { $predikat = 'D'; $warna = 'rose'; $teks = 'Kurang'; }
        ?>
        
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i class="ph ph-books text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mata Pelajaran</p>
                        <h2 class="text-lg font-extrabold text-slate-800"><?php echo htmlspecialchars($data['nama_mapel']); ?></h2>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-right">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nilai Akhir</p>
                        <p class="text-xl font-black text-<?php echo $warna; ?>-600"><?php echo $nilai_akhir; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-<?php echo $warna; ?>-100 text-<?php echo $warna; ?>-600 flex flex-col items-center justify-center leading-none">
                        <span class="text-lg font-black"><?php echo $predikat; ?></span>
                    </div>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="flex items-center gap-2"><i class="ph ph-pencil-line text-indigo-500"></i> Riwayat Tugas</span>
                        <span class="text-xs text-slate-400 font-medium">Rata-rata: <b class="text-slate-700"><?php echo $rata_tugas; ?></b></span>
                    </h3>
                    <div class="space-y-3">
                        <?php if($jml_tugas > 0): foreach($data['tugas'] as $t): ?>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-600 truncate pr-4"><i class="ph ph-caret-right text-[10px] text-slate-300 mr-1"></i> <?php echo htmlspecialchars($t['judul']); ?></span>
                                <span class="font-bold text-slate-800"><?php echo $t['nilai']; ?></span>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="text-xs text-slate-400 italic">Belum ada nilai tugas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="flex items-center gap-2"><i class="ph ph-exam text-amber-500"></i> Riwayat Ujian</span>
                        <span class="text-xs text-slate-400 font-medium">Rata-rata: <b class="text-slate-700"><?php echo $rata_ujian; ?></b></span>
                    </h3>
                    <div class="space-y-3">
                        <?php if($jml_ujian > 0): foreach($data['ujian'] as $u): ?>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-600 truncate pr-4"><i class="ph ph-caret-right text-[10px] text-slate-300 mr-1"></i> <?php echo htmlspecialchars($u['judul']); ?></span>
                                <span class="font-bold text-slate-800"><?php echo $u['nilai']; ?></span>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="text-xs text-slate-400 italic">Belum ada nilai ujian.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="bg-<?php echo $warna; ?>-50/50 px-6 py-3 border-t border-slate-100 text-xs font-medium text-<?php echo $warna; ?>-700 flex items-center justify-center gap-2">
                <i class="ph ph-info"></i> Performa Anda di mata pelajaran ini dinilai <b><?php echo $teks; ?></b>.
            </div>
        </div>
        
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>