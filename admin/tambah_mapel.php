<?php
session_start();
// Proteksi halaman: Hanya admin yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// PROSES SIMPAN DATA
if (isset($_POST['simpan'])) {
    $nama_mapel = trim(mysqli_real_escape_string($koneksi, $_POST['nama_mapel']));

    if (empty($nama_mapel)) {
        $error = "Nama Mata Pelajaran wajib diisi!";
    } else {
        // Query BARU: Hanya memasukkan nama_mapel saja
        $query = "INSERT INTO mapel (nama_mapel) VALUES ('$nama_mapel')";
        
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['sukses'] = "Mata Pelajaran baru berhasil ditambahkan!";
            header("Location: data_mapel.php");
            exit;
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    }
}

include '../includes/header.php';
?>

<div class="mb-8">
    <a href="data_mapel.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-sekolah-600 transition-colors mb-4">
        <i class="ph ph-arrow-left"></i> Kembali ke Data Mapel
    </a>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Mata Pelajaran</h1>
    <p class="text-slate-500 text-sm mt-1">Daftarkan mata pelajaran baru ke dalam sistem.</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
        
        <?php if(isset($error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Mata Pelajaran</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="ph ph-book-open text-slate-400 text-lg"></i>
                    </div>
                    <input type="text" name="nama_mapel" required autocomplete="off" placeholder="Contoh: Matematika Wajib"
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sekolah-500 focus:ring-4 focus:ring-sekolah-500/10 transition-all">
                </div>
            </div>

            <hr class="border-slate-100">

            <div class="flex justify-end gap-3 pt-2">
                <a href="data_mapel.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Batal</a>
                <button type="submit" name="simpan" class="px-6 py-3 bg-sekolah-600 text-white font-bold rounded-xl shadow-md shadow-sekolah-500/30 hover:bg-sekolah-700 transition-all flex items-center gap-2">
                    <i class="ph ph-floppy-disk text-lg"></i> Simpan Mata Pelajaran
                </button>
            </div>

        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>