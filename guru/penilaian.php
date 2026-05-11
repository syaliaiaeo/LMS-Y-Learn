<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { header("Location: ../index.php"); exit; }

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 

// PROSES SIMPAN NILAI DAN FEEDBACK
if (isset($_POST['simpan_nilai'])) {
    $id_tugas_post = $_POST['id_tugas'];
    $id_siswa_post = $_POST['id_siswa'];
    $nilai         = (int)$_POST['nilai'];
    $feedback      = mysqli_real_escape_string($koneksi, trim($_POST['feedback']));

    // Cek apakah sudah ada di tabel pengumpulan_tugas (Untuk jaga-jaga)
    $cek = mysqli_query($koneksi, "SELECT id_pengumpulan FROM pengumpulan_tugas WHERE id_tugas='$id_tugas_post' AND id_siswa='$id_siswa_post'");
    
    if(mysqli_num_rows($cek) > 0) {
        // Jika sudah ada (siswa sudah kumpul), update nilai dan feedback
        mysqli_query($koneksi, "UPDATE pengumpulan_tugas SET nilai='$nilai', feedback='$feedback' WHERE id_tugas='$id_tugas_post' AND id_siswa='$id_siswa_post'");
    } else {
        // Jika guru memberi nilai 0 pada siswa yang belum kumpul sama sekali
        mysqli_query($koneksi, "INSERT INTO pengumpulan_tugas (id_tugas, id_siswa, nilai, feedback) VALUES ('$id_tugas_post', '$id_siswa_post', '$nilai', '$feedback')");
    }
    
    header("Location: penilaian.php?id_tugas=$id_tugas_post&pesan=sukses");
    exit;
}

include 'includes/header.php';
?>

<?php if(isset($_GET['id_tugas'])): 
    $id_tugas = mysqli_real_escape_string($koneksi, $_GET['id_tugas']);
    
    // Ambil detail tugas
    $q_tugas = mysqli_query($koneksi, "SELECT tugas.*, kelas.nama_kelas, mapel.nama_mapel FROM tugas LEFT JOIN kelas ON tugas.id_kelas = kelas.id_kelas LEFT JOIN mapel ON tugas.id_mapel = mapel.id_mapel WHERE tugas.id_tugas='$id_tugas' AND tugas.id_guru='$id_guru'");
    if(mysqli_num_rows($q_tugas) == 0) { header("Location: penilaian.php"); exit; }
    $data_tugas = mysqli_fetch_assoc($q_tugas);
    $id_kelas = $data_tugas['id_kelas'];
?>

<div class="mb-8">
    <a href="penilaian.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-guru-600 transition-colors mb-4">
        <i class="ph ph-arrow-left"></i> Kembali ke Daftar Tugas
    </a>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Koreksi: <?php echo htmlspecialchars($data_tugas['judul_tugas']); ?></h1>
    <div class="flex gap-3 mt-2 text-sm text-slate-500 font-medium">
        <span class="flex items-center gap-1"><i class="ph ph-door"></i> Kelas <?php echo $data_tugas['nama_kelas']; ?></span>
        <span>&bull;</span>
        <span class="flex items-center gap-1"><i class="ph ph-calendar-blank"></i> Deadline: <?php echo date('d M Y, H:i', strtotime($data_tugas['tgl_selesai'])); ?></span>
    </div>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
        <i class="ph ph-check-circle text-xl"></i>
        <span class="text-sm font-semibold">Nilai dan feedback berhasil disimpan!</span>
    </div>
<?php endif; ?>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-widest">
                    <th class="px-6 py-4 font-bold w-16">No</th>
                    <th class="px-6 py-4 font-bold">Nama Siswa</th>
                    <th class="px-6 py-4 font-bold text-center">Waktu Kumpul</th>
                    <th class="px-6 py-4 font-bold text-center">File Jawaban</th>
                    <th class="px-6 py-4 font-bold text-center w-64">Form Penilaian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php
                $no = 1;
                // Ambil semua siswa di kelas tersebut beserta data pengumpulannya
                $q_siswa = mysqli_query($koneksi, "
                    SELECT users.id_user, users.nama_lengkap, users.nis, 
                           pt.id_pengumpulan, pt.file_siswa, pt.catatan_siswa, pt.tgl_kumpul, pt.nilai, pt.feedback
                    FROM users 
                    LEFT JOIN pengumpulan_tugas pt ON users.id_user = pt.id_siswa AND pt.id_tugas = '$id_tugas'
                    WHERE users.id_kelas = '$id_kelas' AND users.role = 'siswa'
                    ORDER BY users.nama_lengkap ASC
                ");

                while($siswa = mysqli_fetch_assoc($q_siswa)):
                    $sudah_kumpul = !empty($siswa['tgl_kumpul']);
                    $terlambat = false;
                    if($sudah_kumpul && $siswa['tgl_kumpul'] > $data_tugas['tgl_selesai']) {
                        $terlambat = true;
                    }
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4 text-slate-500 font-medium"><?php echo $no++; ?></td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></p>
                        <p class="text-[11px] text-slate-500 mt-0.5">NIS: <?php echo !empty($siswa['nis']) ? $siswa['nis'] : '-'; ?></p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if($sudah_kumpul): ?>
                            <div class="flex flex-col gap-1 items-center">
                                <span class="text-xs font-bold text-slate-700"><?php echo date('d/m/Y H:i', strtotime($siswa['tgl_kumpul'])); ?></span>
                                <?php if($terlambat): ?>
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-600 text-[10px] font-bold uppercase rounded">Terlambat</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[10px] font-bold uppercase rounded">Tepat Waktu</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-400 font-bold text-[10px] uppercase rounded">Belum Kumpul</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if(!empty($siswa['file_siswa'])): ?>
                            <a href="../uploads/tugas/jawaban/<?php echo $siswa['file_siswa']; ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold text-xs rounded-lg transition-colors">
                                <i class="ph ph-download-simple text-sm"></i> Unduh File
                            </a>
                        <?php else: ?>
                            <span class="text-slate-300">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <form action="" method="POST" class="flex flex-col gap-2">
                            <input type="hidden" name="id_tugas" value="<?php echo $id_tugas; ?>">
                            <input type="hidden" name="id_siswa" value="<?php echo $siswa['id_user']; ?>">
                            
                            <div class="flex items-center gap-2">
                                <input type="number" name="nilai" min="0" max="100" placeholder="0-100" value="<?php echo isset($siswa['nilai']) ? $siswa['nilai'] : ''; ?>" required class="w-20 px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-sm text-center focus:border-guru-500 focus:outline-none font-bold">
                                <input type="text" name="feedback" placeholder="Catatan/Feedback..." value="<?php echo htmlspecialchars($siswa['feedback'] ?? ''); ?>" class="flex-1 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs focus:border-guru-500 focus:outline-none">
                            </div>
                            <button type="submit" name="simpan_nilai" class="w-full py-1.5 bg-guru-50 text-guru-600 hover:bg-guru-600 hover:text-white border border-guru-200 font-bold text-[10px] uppercase tracking-wider rounded-lg transition-colors">
                                <?php echo isset($siswa['nilai']) ? 'Perbarui Nilai' : 'Simpan Nilai'; ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>


<?php else: ?>

<div class="mb-8">
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Nilai & Feedback Tugas</h1>
    <p class="text-slate-500 text-sm mt-1">Pilih tugas di bawah ini untuk mulai mengoreksi dan memberikan nilai kepada siswa.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <?php
    // Ambil semua tugas milik guru ini
    $q_list_tugas = mysqli_query($koneksi, "
        SELECT tugas.id_tugas, tugas.judul_tugas, tugas.tgl_selesai, kelas.nama_kelas, kelas.id_kelas,
        (SELECT COUNT(*) FROM users WHERE users.id_kelas = tugas.id_kelas AND users.role='siswa') as total_siswa,
        (SELECT COUNT(*) FROM pengumpulan_tugas WHERE pengumpulan_tugas.id_tugas = tugas.id_tugas) as sudah_kumpul,
        (SELECT COUNT(*) FROM pengumpulan_tugas WHERE pengumpulan_tugas.id_tugas = tugas.id_tugas AND nilai IS NULL) as belum_dinilai
        FROM tugas 
        LEFT JOIN kelas ON tugas.id_kelas = kelas.id_kelas
        WHERE tugas.id_guru = '$id_guru'
        ORDER BY tugas.tgl_buat DESC
    ");

    if(mysqli_num_rows($q_list_tugas) > 0):
        while($t = mysqli_fetch_assoc($q_list_tugas)):
            $persentase = ($t['total_siswa'] > 0) ? round(($t['sudah_kumpul'] / $t['total_siswa']) * 100) : 0;
    ?>
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group flex flex-col">
        <div class="flex justify-between items-start mb-4">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-600 font-bold text-[10px] uppercase rounded">
                <i class="ph ph-door"></i> Kelas <?php echo $t['nama_kelas']; ?>
            </span>
            <?php if($t['belum_dinilai'] > 0): ?>
                <span class="flex h-3 w-3 relative tooltip" title="Ada <?php echo $t['belum_dinilai']; ?> tugas menunggu dikoreksi">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
            <?php endif; ?>
        </div>
        
        <h3 class="font-bold text-slate-800 text-lg mb-1 group-hover:text-guru-600 transition-colors"><?php echo htmlspecialchars($t['judul_tugas']); ?></h3>
        <p class="text-xs text-slate-500 mb-6"><i class="ph ph-calendar-blank mr-1"></i> Deadline: <?php echo date('d M Y', strtotime($t['tgl_selesai'])); ?></p>
        
        <div class="mt-auto space-y-4">
            <div>
                <div class="flex justify-between text-xs font-bold text-slate-500 mb-1.5">
                    <span>Progres Pengumpulan</span>
                    <span><?php echo $t['sudah_kumpul']; ?> dari <?php echo $t['total_siswa']; ?> Siswa</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-guru-500 h-2 rounded-full" style="width: <?php echo $persentase; ?>%"></div>
                </div>
            </div>
            
            <a href="penilaian.php?id_tugas=<?php echo $t['id_tugas']; ?>" class="block text-center w-full py-2.5 bg-slate-50 text-slate-700 hover:bg-guru-600 hover:text-white hover:border-guru-600 border border-slate-200 font-bold rounded-xl text-sm transition-all">
                Mulai Koreksi
            </a>
        </div>
    </div>
    <?php 
        endwhile;
    else:
    ?>
    <div class="col-span-full py-16 text-center text-slate-500 bg-white border border-slate-200 rounded-2xl border-dashed">
        <i class="ph ph-check-square-offset text-4xl text-slate-300 mb-3 block"></i>
        <p class="font-medium">Anda belum membuat tugas apa pun.</p>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>