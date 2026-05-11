<?php
session_start();
// Proteksi: Hanya guru yang boleh mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 

// PROSES SIMPAN TOPIK BARU
if (isset($_POST['simpan'])) {
    $judul_topik = trim(mysqli_real_escape_string($koneksi, $_POST['judul_topik']));
    $deskripsi   = trim(mysqli_real_escape_string($koneksi, $_POST['deskripsi']));
    $id_kelas    = $_POST['id_kelas'];
    $id_mapel    = $_POST['id_mapel'];

    if (empty($judul_topik) || empty($deskripsi) || empty($id_kelas) || empty($id_mapel)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        $query = "INSERT INTO forum_topik (judul_topik, deskripsi, id_kelas, id_mapel, id_guru) 
                  VALUES ('$judul_topik', '$deskripsi', '$id_kelas', '$id_mapel', '$id_guru')";
        
        if (mysqli_query($koneksi, $query)) {
            header("Location: forum.php?pesan=sukses");
            exit;
        } else {
            $error = "Gagal membuat topik diskusi: " . mysqli_error($koneksi);
        }
    }
}

include 'includes/header.php';
?>

<div class="mb-8">
    <a href="forum.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-guru-600 transition-colors mb-4">
        <i class="ph ph-arrow-left"></i> Kembali ke Daftar Forum
    </a>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buat Topik Diskusi Baru</h1>
    <p class="text-slate-500 text-sm mt-1">Buka ruang obrolan untuk membahas materi spesifik bersama siswa Anda.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
            
            <?php if(isset($error)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                    <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Judul Topik Diskusi <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-text-t text-slate-400 text-lg"></i>
                        </div>
                        <input type="text" name="judul_topik" required autocomplete="off" placeholder="Contoh: Tanya Jawab Bab 3 - Limit Fungsi"
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-guru-500 focus:ring-4 focus:ring-guru-500/10 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tujuan Kelas <span class="text-rose-500">*</span></label>
                        <select name="id_kelas" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-guru-500 focus:ring-4 focus:ring-guru-500/10 transition-all">
                            <option value="">-- Pilih Kelas --</option>
                            <?php
                            $q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                            while($k = mysqli_fetch_assoc($q_kelas)) echo "<option value='".$k['id_kelas']."'>Kelas ".$k['nama_kelas']."</option>";
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select name="id_mapel" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-guru-500 focus:ring-4 focus:ring-guru-500/10 transition-all">
                            <option value="">-- Pilih Mapel --</option>
                            <?php
                            // Filter agar hanya memunculkan mapel milik guru tersebut
                            $q_mapel = mysqli_query($koneksi, "SELECT * FROM mapel WHERE id_guru='$id_guru' ORDER BY nama_mapel ASC");
                            if(mysqli_num_rows($q_mapel) > 0) {
                                while($m = mysqli_fetch_assoc($q_mapel)) echo "<option value='".$m['id_mapel']."'>".$m['nama_mapel']."</option>";
                            } else {
                                echo "<option value=''>Belum ada mapel yang ditugaskan</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pertanyaan Pemantik / Deskripsi Singkat <span class="text-rose-500">*</span></label>
                    <textarea name="deskripsi" required rows="5" placeholder="Tuliskan pertanyaan awal untuk memancing diskusi siswa. Contoh: 'Bagaimana pendapat kalian mengenai dampak revolusi industri 4.0 di lingkungan sekitar kalian?'" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-guru-500 focus:ring-4 focus:ring-guru-500/10 transition-all"></textarea>
                    <p class="text-xs text-slate-400 mt-2 font-medium">Teks ini akan menjadi pesan pertama yang dilihat oleh siswa saat memasuki ruang diskusi.</p>
                </div>

                <hr class="border-slate-100">

                <div class="flex justify-end gap-3 pt-2">
                    <a href="forum.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Batal</a>
                    <button type="submit" name="simpan" class="px-6 py-3 bg-guru-600 text-white font-bold rounded-xl shadow-md hover:bg-guru-700 transition-all flex items-center gap-2">
                        <i class="ph ph-chats-circle text-lg"></i> Buka Ruang Diskusi
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div class="hidden lg:block">
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 relative overflow-hidden">
            <h3 class="font-bold text-blue-900 mb-4 flex items-center gap-2 relative z-10">
                <i class="ph ph-lightbulb text-blue-500 text-xl"></i> Tips Forum Interaktif
            </h3>
            <p class="text-sm text-blue-800 leading-relaxed relative z-10 mb-4">
                Forum diskusi adalah tempat yang tepat untuk mengasah daya kritis siswa.
            </p>
            <ul class="text-sm text-blue-700 space-y-4 relative z-10">
                <li class="flex items-start gap-2 border-b border-blue-200/50 pb-3">
                    <i class="ph ph-check-circle text-xl mt-0.5"></i>
                    <span>Gunakan <b>Pertanyaan Pemantik</b> yang sifatnya terbuka (Open-ended) agar siswa bisa mengemukakan pendapat, bukan sekadar jawaban Ya/Tidak.</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="ph ph-check-circle text-xl mt-0.5"></i>
                    <span>Hanya siswa dari <b>Target Kelas</b> yang Anda pilih yang dapat melihat dan bergabung ke dalam obrolan ini.</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>