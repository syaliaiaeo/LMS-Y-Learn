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
date_default_timezone_set('Asia/Jakarta');
$waktu_sekarang = date('Y-m-d H:i:s');

// 1. TANGKAP ID TUGAS & VALIDASI KELAS
if (!isset($_GET['id'])) { header("Location: tugas.php"); exit; }
$id_tugas = mysqli_real_escape_string($koneksi, $_GET['id']);

$q_tugas = mysqli_query($koneksi, "
    SELECT t.*, u.nama_lengkap AS nama_guru, mp.nama_mapel 
    FROM tugas t 
    JOIN users u ON t.id_guru = u.id_user 
    JOIN mapel mp ON t.id_mapel = mp.id_mapel 
    WHERE t.id_tugas = '$id_tugas' AND t.id_kelas = '$id_kelas_siswa'
");

if (mysqli_num_rows($q_tugas) == 0) { header("Location: tugas.php"); exit; }
$data_tugas = mysqli_fetch_assoc($q_tugas);

// 2. CEK STATUS PENGUMPULAN SISWA
$q_kumpul = mysqli_query($koneksi, "SELECT * FROM pengumpulan_tugas WHERE id_tugas='$id_tugas' AND id_siswa='$id_siswa'");
$data_kumpul = mysqli_fetch_assoc($q_kumpul);
$sudah_kumpul = mysqli_num_rows($q_kumpul) > 0;
$sudah_dinilai = ($sudah_kumpul && $data_kumpul['nilai'] !== null);

// Status Deadline
$terlambat = (!$sudah_kumpul && $waktu_sekarang > $data_tugas['tgl_selesai']);

// 3. PROSES PENGUMPULAN TUGAS BARU
if (isset($_POST['kumpul_tugas']) && !$sudah_kumpul) {
    $jawaban_teks = trim(mysqli_real_escape_string($koneksi, $_POST['jawaban_teks']));
    $nama_file_baru = NULL;
    $ada_file = false;

    if (isset($_FILES['file_jawaban']['name']) && $_FILES['file_jawaban']['error'] == 0) {
        $nama_file = $_FILES['file_jawaban']['name'];
        $ukuran_file = $_FILES['file_jawaban']['size'];
        $tmp_file = $_FILES['file_jawaban']['tmp_name'];
        $ext_diizinkan = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'jpg', 'jpeg', 'png');
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if (in_array($ekstensi, $ext_diizinkan)) {
            if ($ukuran_file <= 5048000) { 
                $nama_file_baru = time() . '_' . $id_siswa . '_' . str_replace(' ', '_', $nama_file);
                if(move_uploaded_file($tmp_file, '../uploads/tugas/jawaban/' . $nama_file_baru)) {
                    $ada_file = true;
                }
            } else { $error = "Ukuran file terlalu besar! Maksimal 5 MB."; }
        } else { $error = "Ekstensi file tidak diizinkan!"; }
    }

    $teks_kosong = empty(trim(strip_tags($_POST['jawaban_teks']))); 
    if (!$ada_file && $teks_kosong && !isset($error)) {
        $error = "Anda wajib melampirkan file ATAU mengetik jawaban!";
    }

    if (!isset($error)) {
        $file_sql = $ada_file ? "'$nama_file_baru'" : "NULL";
        $q_insert = "INSERT INTO pengumpulan_tugas (id_tugas, id_siswa, file_siswa, catatan_siswa, tgl_kumpul) 
                     VALUES ('$id_tugas', '$id_siswa', $file_sql, '$jawaban_teks', '$waktu_sekarang')";
        if (mysqli_query($koneksi, $q_insert)) {
            header("Location: detail_tugas.php?id=$id_tugas&pesan=sukses");
            exit;
        } else { $error = "Gagal menyimpan data: " . mysqli_error($koneksi); }
    }
}

// 4. PROSES EDIT TUGAS YANG SUDAH DIKUMPULKAN
if (isset($_POST['edit_kumpul']) && $sudah_kumpul && !$sudah_dinilai) {
    $jawaban_teks = trim(mysqli_real_escape_string($koneksi, $_POST['jawaban_teks']));
    $nama_file_lama = $data_kumpul['file_siswa'];
    $nama_file_baru = $nama_file_lama; // Secara default pertahankan file lama

    if (isset($_FILES['file_jawaban']['name']) && $_FILES['file_jawaban']['error'] == 0) {
        $nama_file = $_FILES['file_jawaban']['name'];
        $ukuran_file = $_FILES['file_jawaban']['size'];
        $tmp_file = $_FILES['file_jawaban']['tmp_name'];
        $ext_diizinkan = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'jpg', 'jpeg', 'png');
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if (in_array($ekstensi, $ext_diizinkan)) {
            if ($ukuran_file <= 5048000) { 
                $nama_file_upload = time() . '_EDIT_' . $id_siswa . '_' . str_replace(' ', '_', $nama_file);
                if(move_uploaded_file($tmp_file, '../uploads/tugas/jawaban/' . $nama_file_upload)) {
                    $nama_file_baru = $nama_file_upload;
                    // Hapus file lama jika ada
                    if(!empty($nama_file_lama)) { @unlink('../uploads/tugas/jawaban/' . $nama_file_lama); }
                }
            } else { $error = "Ukuran file terlalu besar! Maksimal 5 MB."; }
        } else { $error = "Ekstensi file tidak diizinkan!"; }
    }

    $teks_kosong = empty(trim(strip_tags($_POST['jawaban_teks']))); 
    if (empty($nama_file_baru) && $teks_kosong && !isset($error)) {
        $error = "Jawaban tidak boleh kosong sepenuhnya!";
    }

    if (!isset($error)) {
        $file_sql = $nama_file_baru ? "'$nama_file_baru'" : "NULL";
        $q_update = "UPDATE pengumpulan_tugas SET file_siswa = $file_sql, catatan_siswa = '$jawaban_teks', tgl_kumpul = '$waktu_sekarang' WHERE id_pengumpulan = '" . $data_kumpul['id_pengumpulan'] . "'";
        if (mysqli_query($koneksi, $q_update)) {
            header("Location: detail_tugas.php?id=$id_tugas&pesan=diedit");
            exit;
        } else { $error = "Gagal memperbarui data: " . mysqli_error($koneksi); }
    }
}

// 5. PROSES BATAL KUMPUL (UNSUBMIT)
if (isset($_POST['batal_kumpul']) && $sudah_kumpul && !$sudah_dinilai) {
    if (!empty($data_kumpul['file_siswa'])) {
        $path_hapus = '../uploads/tugas/jawaban/' . $data_kumpul['file_siswa'];
        if (file_exists($path_hapus)) { unlink($path_hapus); }
    }
    mysqli_query($koneksi, "DELETE FROM pengumpulan_tugas WHERE id_pengumpulan='" . $data_kumpul['id_pengumpulan'] . "'");
    header("Location: detail_tugas.php?id=$id_tugas&pesan=batal");
    exit;
}

include 'includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<style>
    .jawaban-konten ul { list-style-type: disc; margin-left: 1.5rem; }
    .jawaban-konten ol { list-style-type: decimal; margin-left: 1.5rem; }
    .jawaban-konten a { color: #4f46e5; text-decoration: underline; }
</style>

<div class="mb-6">
    <a href="tugas.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-600 transition-colors mb-4">
        <i class="ph ph-arrow-left"></i> Kembali ke Daftar Tugas
    </a>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
        <i class="ph ph-check-circle text-xl"></i>
        <span class="text-sm font-semibold">
            <?php 
                if($_GET['pesan'] == 'sukses') echo "Tugas berhasil dikumpulkan!";
                if($_GET['pesan'] == 'batal') echo "Pengumpulan tugas dibatalkan.";
                if($_GET['pesan'] == 'diedit') echo "Jawaban tugas berhasil diperbarui!";
            ?>
        </span>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 flex items-center gap-3 text-sm font-semibold animate-pulse">
        <i class="ph ph-warning-circle text-xl"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-3 mb-4 text-xs font-bold text-slate-500">
                <span class="px-2.5 py-1 bg-rose-50 text-rose-600 rounded flex items-center gap-1"><i class="ph ph-book-bookmark"></i> <?php echo htmlspecialchars($data_tugas['nama_mapel']); ?></span>
                <span>Oleh: <span class="text-slate-800"><?php echo htmlspecialchars($data_tugas['nama_guru']); ?></span></span>
            </div>
            
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight mb-6">
                <?php echo htmlspecialchars($data_tugas['judul_tugas']); ?>
            </h1>
            
            <div class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl border border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-lg <?php echo $terlambat ? 'bg-rose-100 text-rose-600' : 'bg-slate-200 text-slate-600'; ?> flex items-center justify-center shrink-0">
                    <i class="ph ph-calendar-blank text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Batas Waktu (Deadline)</p>
                    <p class="text-sm font-bold <?php echo $terlambat ? 'text-rose-600' : 'text-slate-800'; ?>">
                        <?php echo date('l, d F Y - H:i', strtotime($data_tugas['tgl_selesai'])); ?> WIB
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="ph ph-article text-indigo-500 text-lg"></i> Instruksi Tugas
                </h3>
                <div class="text-slate-700 text-sm leading-relaxed whitespace-pre-wrap">
                    <?php echo htmlspecialchars($data_tugas['deskripsi']); ?>
                </div>
            </div>
            
            <?php if(!empty($data_tugas['file_tugas'])): ?>
            <div class="mt-8 border-t border-slate-100 pt-6">
                <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="ph ph-paperclip text-indigo-500 text-lg"></i> Lampiran dari Guru
                </h3>
                <a href="../uploads/tugas/<?php echo $data_tugas['file_tugas']; ?>" download class="inline-flex items-center gap-3 p-3 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-xl transition-colors group w-full sm:w-auto">
                    <div class="w-10 h-10 bg-white text-blue-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                        <i class="ph ph-file-text text-xl"></i>
                    </div>
                    <div class="pr-4">
                        <p class="text-sm font-bold text-blue-800 line-clamp-1"><?php echo htmlspecialchars($data_tugas['file_tugas']); ?></p>
                        <p class="text-[10px] font-medium text-blue-500 uppercase">Klik untuk mengunduh</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>


    <div class="xl:col-span-1">
        <div class="bg-white border <?php echo $sudah_kumpul ? ($sudah_dinilai ? 'border-emerald-200' : 'border-indigo-200') : 'border-slate-200 shadow-sm'; ?> rounded-2xl p-6 sticky top-8">
            
            <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-5">
                <h2 class="font-extrabold text-slate-800 text-lg">Pekerjaan Anda</h2>
                <?php if($sudah_dinilai): ?>
                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase rounded">Dinilai</span>
                <?php elseif($sudah_kumpul): ?>
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase rounded">Diserahkan</span>
                <?php elseif($terlambat): ?>
                    <span class="px-2 py-1 bg-rose-100 text-rose-700 text-[10px] font-bold uppercase rounded">Terlambat</span>
                <?php else: ?>
                    <span class="px-2 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold uppercase rounded">Ditugaskan</span>
                <?php endif; ?>
            </div>

            <?php if($sudah_dinilai): ?>
                <div class="text-center mb-6">
                    <div class="inline-flex w-24 h-24 rounded-full border-4 border-emerald-100 items-center justify-center mb-3">
                        <span class="text-4xl font-black text-emerald-600"><?php echo $data_kumpul['nilai']; ?></span>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Poin Nilai</p>
                </div>
                
                <?php if(!empty($data_kumpul['feedback'])): ?>
                    <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl mb-6">
                        <p class="text-[10px] font-bold text-amber-700 uppercase mb-1 flex items-center gap-1"><i class="ph ph-chat-teardrop-text"></i> Catatan Guru:</p>
                        <p class="text-sm text-amber-900 font-medium"><?php echo htmlspecialchars($data_kumpul['feedback']); ?></p>
                    </div>
                <?php endif; ?>

            <?php elseif($sudah_kumpul): ?>
                
                <div id="view-mode" class="block">
                    <?php if(!empty($data_kumpul['catatan_siswa'])): ?>
                        <div class="mb-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Jawaban Teks:</p>
                            <div class="jawaban-konten p-4 bg-slate-50 border border-slate-100 rounded-xl text-sm text-slate-700 overflow-hidden">
                                <?php echo $data_kumpul['catatan_siswa']; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($data_kumpul['file_siswa'])): ?>
                        <div class="mb-6 space-y-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 mt-4">File Terlampir:</p>
                            <div class="p-3 border border-indigo-100 bg-indigo-50/50 rounded-xl flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                                    <i class="ph ph-file-arrow-up text-xl"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-xs font-bold text-indigo-900 truncate"><?php echo htmlspecialchars($data_kumpul['file_siswa']); ?></p>
                                    <p class="text-[10px] text-indigo-500 mt-0.5">Dikirim: <?php echo date('d/m/Y H:i', strtotime($data_kumpul['tgl_kumpul'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex flex-col gap-3 mt-6">
                        <button type="button" onclick="toggleEditMode()" class="w-full py-3 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white font-bold text-sm rounded-xl transition-all flex justify-center items-center gap-2 border border-indigo-100">
                            <i class="ph ph-pencil-simple text-lg"></i> Edit Jawaban
                        </button>
                        
                        <form action="" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengumpulan? File Anda akan dihapus permanen.');">
                            <button type="submit" name="batal_kumpul" class="w-full py-3 bg-white border border-slate-200 text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 font-bold text-sm rounded-xl transition-all flex justify-center items-center gap-2">
                                <i class="ph ph-trash text-lg"></i> Hapus / Batal Kumpul
                            </button>
                        </form>
                    </div>
                </div>

                <div id="edit-mode" class="hidden">
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-indigo-600 uppercase tracking-widest mb-2 flex items-center gap-1">
                                <i class="ph ph-pencil text-lg"></i> Perbarui Jawaban Teks
                            </label>
                            <textarea id="editor-jawaban" name="jawaban_teks" rows="6" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-indigo-500 transition-all"><?php echo htmlspecialchars($data_kumpul['catatan_siswa']); ?></textarea>
                        </div>

                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2 flex items-center gap-1">
                                Ganti File Lampiran (Opsional)
                            </label>
                            <?php if(!empty($data_kumpul['file_siswa'])): ?>
                                <p class="text-[10px] text-slate-500 mb-2">File saat ini: <b class="text-indigo-600"><?php echo htmlspecialchars($data_kumpul['file_siswa']); ?></b></p>
                            <?php endif; ?>
                            <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png" class="block w-full text-[10px] text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer text-center mx-auto bg-white border border-slate-200 rounded-lg p-1">
                            <p class="text-[9px] text-slate-400 mt-2 text-center">Kosongkan jika tidak ingin mengganti file lama.</p>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" onclick="toggleEditMode()" class="px-4 py-3 bg-white border border-slate-200 text-slate-600 font-bold text-xs rounded-xl hover:bg-slate-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit" name="edit_kumpul" class="flex-1 py-3 bg-indigo-600 text-white font-bold text-sm rounded-xl shadow-md hover:bg-indigo-700 transition-colors flex justify-center items-center gap-2">
                                <i class="ph ph-floppy-disk text-lg"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>


            <?php else: ?>
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2 flex items-center gap-1">
                            <i class="ph ph-text-aa text-indigo-500 text-lg"></i> Ketik Jawaban Langsung
                        </label>
                        <textarea id="editor-jawaban" name="jawaban_teks" rows="8" placeholder="Ketik jawaban, lampirkan link Google Drive, atau paste teks di sini..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-indigo-500 transition-all"></textarea>
                    </div>

                    <div class="flex items-center gap-4 my-2">
                        <hr class="flex-1 border-slate-200">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">ATAU / DAN</span>
                        <hr class="flex-1 border-slate-200">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2 flex items-center gap-1">
                            <i class="ph ph-upload-simple text-indigo-500 text-lg"></i> Unggah File Lampiran
                        </label>
                        <div class="border-2 border-dashed border-indigo-200 rounded-xl p-5 text-center bg-indigo-50/30 hover:bg-indigo-50 transition-colors">
                            <div class="w-10 h-10 bg-white text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm border border-indigo-100">
                                <i class="ph ph-file-arrow-up text-xl font-bold"></i>
                            </div>
                            <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer text-center mx-auto">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5 text-center">Format: PDF, Word, Excel, ZIP, Gambar (Max 5MB).</p>
                    </div>

                    <button type="submit" name="kumpul_tugas" class="w-full py-3.5 bg-indigo-600 text-white font-bold text-sm rounded-xl shadow-md shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex justify-center items-center gap-2">
                        <i class="ph ph-paper-plane-right text-lg"></i> Serahkan Tugas
                    </button>
                </form>
            <?php endif; ?>

            <?php if(!$sudah_dinilai): ?>
            <script>
                tinymce.init({
                    selector: '#editor-jawaban',
                    menubar: false,
                    plugins: 'lists link',
                    toolbar: 'bold italic underline | bullist numlist | link',
                    height: 250,
                    branding: false,
                    promotion: false
                });

                function toggleEditMode() {
                    const viewMode = document.getElementById('view-mode');
                    const editMode = document.getElementById('edit-mode');
                    if(viewMode.classList.contains('hidden')) {
                        viewMode.classList.remove('hidden');
                        editMode.classList.add('hidden');
                    } else {
                        viewMode.classList.add('hidden');
                        editMode.classList.remove('hidden');
                    }
                }
            </script>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>