<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { header("Location: ../index.php"); exit; }

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 

// Validasi apakah id_ujian ada di URL
if (!isset($_GET['id_ujian'])) {
    header("Location: ujian.php");
    exit;
}

$id_ujian = mysqli_real_escape_string($koneksi, $_GET['id_ujian']);

// Cek apakah ujian ini valid dan milik guru yang sedang login
$cek_ujian = mysqli_query($koneksi, "SELECT judul_ujian, jenis_evaluasi FROM ujian WHERE id_ujian='$id_ujian' AND id_guru='$id_guru'");
if (mysqli_num_rows($cek_ujian) == 0) {
    header("Location: ujian.php"); // Tendang jika bukan miliknya
    exit;
}
$data_ujian = mysqli_fetch_assoc($cek_ujian);

// PROSES SIMPAN SOAL BARU
if (isset($_POST['simpan_soal'])) {
    $jenis_soal = $_POST['jenis_soal'];
    $pertanyaan = mysqli_real_escape_string($koneksi, trim($_POST['pertanyaan']));
    $bobot      = (int)$_POST['bobot'];

    if ($jenis_soal == 'Pilihan Ganda') {
        $opsi_a = mysqli_real_escape_string($koneksi, trim($_POST['opsi_a']));
        $opsi_b = mysqli_real_escape_string($koneksi, trim($_POST['opsi_b']));
        $opsi_c = mysqli_real_escape_string($koneksi, trim($_POST['opsi_c']));
        $opsi_d = mysqli_real_escape_string($koneksi, trim($_POST['opsi_d']));
        $opsi_e = mysqli_real_escape_string($koneksi, trim($_POST['opsi_e']));
        $kunci  = mysqli_real_escape_string($koneksi, $_POST['kunci_pg']);
        
        $query = "INSERT INTO soal_ujian (id_ujian, jenis_soal, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, kunci_jawaban, bobot) 
                  VALUES ('$id_ujian', '$jenis_soal', '$pertanyaan', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$opsi_e', '$kunci', '$bobot')";
    } else {
        // Jika Essay, opsi A-E dikosongkan
        $kunci_essay = mysqli_real_escape_string($koneksi, trim($_POST['kunci_essay']));
        $query = "INSERT INTO soal_ujian (id_ujian, jenis_soal, pertanyaan, kunci_jawaban, bobot) 
                  VALUES ('$id_ujian', '$jenis_soal', '$pertanyaan', '$kunci_essay', '$bobot')";
    }

    if (mysqli_query($koneksi, $query)) {
        header("Location: kelola_soal.php?id_ujian=$id_ujian&pesan=sukses");
        exit;
    } else {
        $error = "Gagal menyimpan soal: " . mysqli_error($koneksi);
    }
}

// PROSES HAPUS SOAL
if (isset($_GET['hapus'])) {
    $id_soal = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM soal_ujian WHERE id_soal='$id_soal' AND id_ujian='$id_ujian'");
    header("Location: kelola_soal.php?id_ujian=$id_ujian&pesan=dihapus");
    exit;
}

include 'includes/header.php';
?>

<div class="mb-8">
    <a href="ujian.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-guru-600 transition-colors mb-4">
        <i class="ph ph-arrow-left"></i> Kembali ke Daftar Ujian
    </a>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola Soal: <?php echo htmlspecialchars($data_ujian['judul_ujian']); ?></h1>
    <p class="text-slate-500 text-sm mt-1">
        <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-600 border border-indigo-200 font-bold text-[10px] uppercase rounded mr-1">
            <?php echo $data_ujian['jenis_evaluasi']; ?>
        </span>
        Tambahkan butir soal Pilihan Ganda atau Essay ke dalam evaluasi ini.
    </p>
</div>

<?php if(isset($_GET['pesan'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
        <i class="ph ph-check-circle text-xl"></i>
        <span class="text-sm font-semibold">
            <?php 
                if($_GET['pesan'] == 'sukses') echo "Butir soal berhasil ditambahkan!";
                if($_GET['pesan'] == 'dihapus') echo "Soal berhasil dihapus.";
            ?>
        </span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <div class="xl:col-span-1">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sticky top-24">
            <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="ph ph-plus-circle text-guru-500 text-xl"></i> Tambah Soal Baru
            </h3>

            <?php if(isset($error)): ?>
                <div class="text-rose-500 text-xs font-bold mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Jenis Soal</label>
                    <div class="relative">
                        <select name="jenis_soal" id="jenis_soal" onchange="toggleTipeSoal()" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-guru-500 transition-all appearance-none">
                            <option value="Pilihan Ganda">Pilihan Ganda (A-E)</option>
                            <option value="Essay">Essay (Uraian)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="ph ph-caret-down text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Bobot Nilai (Poin)</label>
                    <input type="number" name="bobot" required value="10" min="1" class="w-24 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-guru-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Teks Pertanyaan</label>
                    <textarea name="pertanyaan" required rows="4" placeholder="Tulis soal di sini..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-guru-500 transition-all"></textarea>
                </div>

                <div id="block_pg" class="space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <label class="block text-xs font-bold text-slate-700">Opsi Jawaban</label>
                    
                    <div class="flex items-center gap-2">
                        <span class="w-6 text-center font-bold text-slate-500">A</span>
                        <input type="text" name="opsi_a" id="opsi_a" placeholder="Pilihan A" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-guru-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 text-center font-bold text-slate-500">B</span>
                        <input type="text" name="opsi_b" id="opsi_b" placeholder="Pilihan B" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-guru-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 text-center font-bold text-slate-500">C</span>
                        <input type="text" name="opsi_c" id="opsi_c" placeholder="Pilihan C" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-guru-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 text-center font-bold text-slate-500">D</span>
                        <input type="text" name="opsi_d" id="opsi_d" placeholder="Pilihan D" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-guru-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 text-center font-bold text-slate-500">E</span>
                        <input type="text" name="opsi_e" id="opsi_e" placeholder="Pilihan E" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-guru-500">
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200">
                        <label class="block text-xs font-bold text-slate-700 mb-2">Kunci Jawaban PG</label>
                        <select name="kunci_pg" id="kunci_pg" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-guru-500">
                            <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option><option value="E">E</option>
                        </select>
                    </div>
                </div>

                <div id="block_essay" class="hidden space-y-3 p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <div>
                        <label class="block text-xs font-bold text-amber-900 mb-2">Referensi Jawaban / Rubrik (Opsional)</label>
                        <textarea name="kunci_essay" id="kunci_essay" rows="3" placeholder="Kata kunci yang diharapkan dari jawaban siswa..." class="w-full px-4 py-3 bg-white border border-amber-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-amber-500 transition-all"></textarea>
                        <p class="text-[10px] text-amber-700 mt-1">Hanya akan dilihat oleh Guru sebagai acuan saat mengoreksi.</p>
                    </div>
                </div>

                <button type="submit" name="simpan_soal" class="w-full py-3 bg-guru-600 text-white font-bold rounded-xl hover:bg-guru-700 transition-colors text-sm flex justify-center items-center gap-2">
                    <i class="ph ph-floppy-disk text-lg"></i> Simpan Soal
                </button>
            </form>
        </div>
    </div>

    <div class="xl:col-span-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:p-8">
            <h3 class="font-bold text-slate-800 mb-6 pb-3 border-b border-slate-100">Daftar Butir Soal</h3>
            
            <div class="space-y-6">
                <?php
                $q_soal = mysqli_query($koneksi, "SELECT * FROM soal_ujian WHERE id_ujian='$id_ujian' ORDER BY id_soal ASC");
                $no = 1;
                $total_bobot = 0;

                if(mysqli_num_rows($q_soal) > 0):
                    while($s = mysqli_fetch_assoc($q_soal)):
                        $total_bobot += $s['bobot'];
                ?>
                
                <div class="p-5 border border-slate-100 rounded-xl hover:border-slate-200 hover:shadow-sm transition-all group">
                    <div class="flex justify-between items-start gap-4 mb-3">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm"><?php echo $no++; ?></span>
                            <?php if($s['jenis_soal'] == 'Pilihan Ganda'): ?>
                                <span class="px-2 py-1 bg-blue-50 text-blue-600 font-bold text-[10px] uppercase rounded">Pilihan Ganda</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-amber-50 text-amber-600 font-bold text-[10px] uppercase rounded">Essay</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-slate-400">Bobot: <?php echo $s['bobot']; ?></span>
                            <a href="?id_ujian=<?php echo $id_ujian; ?>&hapus=<?php echo $s['id_soal']; ?>" onclick="return confirm('Hapus soal ini?');" class="text-slate-400 hover:text-rose-500 transition-colors">
                                <i class="ph ph-trash text-lg"></i>
                            </a>
                        </div>
                    </div>

                    <p class="text-sm font-semibold text-slate-800 mb-4 whitespace-pre-wrap leading-relaxed"><?php echo htmlspecialchars($s['pertanyaan']); ?></p>

                    <?php if($s['jenis_soal'] == 'Pilihan Ganda'): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-slate-600 ml-11">
                            <div class="flex gap-2 p-2 rounded-lg <?php echo ($s['kunci_jawaban'] == 'A') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'bg-slate-50 border border-transparent'; ?>">
                                <span>A.</span> <?php echo htmlspecialchars($s['opsi_a']); ?>
                            </div>
                            <div class="flex gap-2 p-2 rounded-lg <?php echo ($s['kunci_jawaban'] == 'B') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'bg-slate-50 border border-transparent'; ?>">
                                <span>B.</span> <?php echo htmlspecialchars($s['opsi_b']); ?>
                            </div>
                            <div class="flex gap-2 p-2 rounded-lg <?php echo ($s['kunci_jawaban'] == 'C') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'bg-slate-50 border border-transparent'; ?>">
                                <span>C.</span> <?php echo htmlspecialchars($s['opsi_c']); ?>
                            </div>
                            <div class="flex gap-2 p-2 rounded-lg <?php echo ($s['kunci_jawaban'] == 'D') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'bg-slate-50 border border-transparent'; ?>">
                                <span>D.</span> <?php echo htmlspecialchars($s['opsi_d']); ?>
                            </div>
                            <?php if(!empty($s['opsi_e'])): ?>
                            <div class="flex gap-2 p-2 rounded-lg md:col-span-2 <?php echo ($s['kunci_jawaban'] == 'E') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'bg-slate-50 border border-transparent'; ?>">
                                <span>E.</span> <?php echo htmlspecialchars($s['opsi_e']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php if(!empty($s['kunci_jawaban'])): ?>
                            <div class="ml-11 p-3 bg-amber-50/50 border border-amber-100 rounded-lg">
                                <span class="block text-[10px] font-bold text-amber-600 uppercase mb-1">Referensi Jawaban:</span>
                                <p class="text-xs text-amber-900 leading-relaxed"><?php echo nl2br(htmlspecialchars($s['kunci_jawaban'])); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php 
                    endwhile; 
                ?>
                    <div class="mt-6 pt-4 border-t border-slate-200 flex justify-end">
                        <span class="px-4 py-2 bg-slate-100 text-slate-700 font-bold text-sm rounded-xl border border-slate-200">
                            Total Bobot Nilai Sementara: <span class="<?php echo ($total_bobot == 100) ? 'text-emerald-600' : 'text-amber-600'; ?>"><?php echo $total_bobot; ?></span>
                        </span>
                    </div>
                <?php
                else:
                ?>
                    <div class="text-center py-12">
                        <i class="ph ph-list-numbers text-5xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500 font-medium">Belum ada butir soal yang ditambahkan.</p>
                        <p class="text-xs text-slate-400 mt-1">Silakan gunakan formulir di samping kiri untuk mulai menyusun soal.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
function toggleTipeSoal() {
    var jenis = document.getElementById("jenis_soal").value;
    var blockPG = document.getElementById("block_pg");
    var blockEssay = document.getElementById("block_essay");
    
    // Input Pilihan Ganda
    var inputA = document.getElementById("opsi_a");
    var inputB = document.getElementById("opsi_b");
    
    if (jenis === "Pilihan Ganda") {
        // Tampilkan blok PG, sembunyikan blok Essay
        blockPG.classList.remove("hidden");
        blockEssay.classList.add("hidden");
        
        // Wajibkan minimal A dan B diisi untuk pilihan ganda
        inputA.setAttribute("required", "required");
        inputB.setAttribute("required", "required");
    } else {
        // Tampilkan blok Essay, sembunyikan blok PG
        blockPG.classList.add("hidden");
        blockEssay.classList.remove("hidden");
        
        // Hapus kewajiban mengisi A dan B agar form bisa disubmit
        inputA.removeAttribute("required");
        inputB.removeAttribute("required");
    }
}

// Jalankan fungsi sekali saat halaman dimuat untuk memastikan status awal
window.onload = toggleTipeSoal;
</script>

<?php include 'includes/footer.php'; ?>