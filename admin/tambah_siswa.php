<?php
session_start();
// Proteksi halaman admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// Proses penyimpanan data jika tombol simpan ditekan
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nisn = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Tangkap data tahun masuk dan keluar
    $tahun_masuk = mysqli_real_escape_string($koneksi, $_POST['tahun_masuk']);
    $tahun_keluar = mysqli_real_escape_string($koneksi, $_POST['tahun_keluar']);

    // Query insert (Role otomatis 'siswa')
    $query_insert = "
        INSERT INTO users (nama_lengkap, nisn, nis, id_kelas, username, password, role, status, tahun_masuk, tahun_keluar) 
        VALUES ('$nama', '$nisn', '$nis', '$id_kelas', '$username', '$password', 'siswa', '$status', '$tahun_masuk', '$tahun_keluar')
    ";

    if (mysqli_query($koneksi, $query_insert)) {
        // Menggunakan session untuk flash message yang modern
        $_SESSION['sukses'] = "Data Siswa baru berhasil ditambahkan!";
        header("Location: data_siswa.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan data siswa!";
    }
}

// Ambil daftar Kelas untuk dropdown
$q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");

// Ambil daftar Tahun Ajaran untuk dropdown
$q_tahun = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC");

include '../includes/header.php'; 
?>

<div class="mb-6">
    <a href="data_siswa.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Data Siswa
    </a>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Siswa Baru</h1>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div id="alert-error" class="mb-6 px-5 py-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl flex items-center justify-between shadow-sm max-w-4xl">
        <div class="flex items-center gap-3 font-bold text-sm">
            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0"><i class="ph ph-warning-circle text-xl"></i></div>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <button onclick="document.getElementById('alert-error').style.display='none'" class="text-rose-500 hover:text-rose-700"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php endif; ?>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 md:p-8 max-w-4xl">
    <form action="" method="POST">
        
        <div class="mb-6">
            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Siswa</label>
            <input type="text" name="nama_lengkap" required placeholder="Nama sesuai akta" 
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">NISN</label>
                <input type="text" name="nisn" required placeholder="10 digit nomor" 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">NIS</label>
                <input type="text" name="nis" placeholder="Nomor Induk Siswa" 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Kelas</label>
                <div class="relative">
                    <select name="id_kelas" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                        <option value="">-- Pilih Kelas --</option>
                        <?php while($kls = mysqli_fetch_assoc($q_kelas)): ?>
                            <option value="<?php echo $kls['id_kelas']; ?>"><?php echo htmlspecialchars($kls['nama_kelas']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-slate-400"></i></div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Status Siswa</label>
                <div class="relative">
                    <select name="status" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-slate-400"></i></div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Username (NISN)</label>
                <input type="text" name="username" required placeholder="Gunakan NISN" 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Sandi Default</label>
                <input type="text" name="password" required value="siswa123" placeholder="siswa123" 
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Masuk <span class="text-blue-500">*</span></label>
                <div class="relative">
                    <select name="tahun_masuk" required class="w-full pl-4 pr-10 py-3 bg-blue-50/50 border border-blue-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                        <option value="">-- Pilih Tahun Masuk --</option>
                        <?php 
                        mysqli_data_seek($q_tahun, 0);
                        while($thn = mysqli_fetch_assoc($q_tahun)): 
                        ?>
                            <option value="<?php echo $thn['nama_tahun']; ?>"><?php echo $thn['nama_tahun']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-calendar-plus text-blue-400"></i></div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Selesai / Lulus <span class="text-blue-500">*</span></label>
                <div class="relative">
                    <select name="tahun_keluar" required class="w-full pl-4 pr-10 py-3 bg-blue-50/50 border border-blue-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                        <option value="Sekarang" class="font-bold text-emerald-600">Sekarang (Masih Aktif)</option>
                        <?php 
                        mysqli_data_seek($q_tahun, 0);
                        while($thn = mysqli_fetch_assoc($q_tahun)): 
                        ?>
                            <option value="<?php echo $thn['nama_tahun']; ?>"><?php echo $thn['nama_tahun']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-graduation-cap text-blue-400"></i></div>
                </div>
            </div>

        </div> <div class="mt-10 pt-6 border-t border-slate-100 flex justify-end gap-3">
            <a href="data_siswa.php" class="px-6 py-3 border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-sm rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" name="simpan" class="px-6 py-3 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Data Siswa
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>