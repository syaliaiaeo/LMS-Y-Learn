<?php
session_start();
// Proteksi halaman admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';

// 1. Ambil Data Siswa Lama
if (!isset($_GET['id'])) {
    header("Location: data_siswa.php");
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_GET['id']);
$query_lama = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user' AND role='siswa'");

if (mysqli_num_rows($query_lama) == 0) {
    header("Location: data_siswa.php");
    exit;
}
$data_lama = mysqli_fetch_assoc($query_lama);

// ==========================================
// 2. PROSES UPDATE DATA
// ==========================================
if (isset($_POST['update'])) {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nisn         = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $id_kelas     = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $status       = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Tangkap input password
    $password_baru = $_POST['password'];

    // LOGIKA ENKRIPSI KATA SANDI
    if (empty($password_baru)) {
        // Jika kolom password tidak diisi, UPDATE SEMUA KECUALI PASSWORD
        $query_update = "
            UPDATE users 
            SET nama_lengkap = '$nama_lengkap', 
                username = '$username', 
                nisn = '$nisn', 
                id_kelas = '$id_kelas', 
                status = '$status'
            WHERE id_user = '$id_user'
        ";
    } else {
        // JIKA KOLOM PASSWORD DIISI, ENKRIPSI (HASH) DULU SEBELUM DISIMPAN!
        $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
        
        $query_update = "
            UPDATE users 
            SET nama_lengkap = '$nama_lengkap', 
                username = '$username', 
                nisn = '$nisn', 
                id_kelas = '$id_kelas', 
                status = '$status',
                password = '$password_hashed'
            WHERE id_user = '$id_user'
        ";
    }

    if (mysqli_query($koneksi, $query_update)) {
        $_SESSION['sukses'] = "Data siswa berhasil diperbarui!";
        header("Location: data_siswa.php");
        exit;
    } else {
        $error = "Gagal mengupdate data: " . mysqli_error($koneksi);
    }
}

include '../includes/header.php';
?>

<div class="mb-8">
    <a href="data_siswa.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors mb-4">
        <i class="ph ph-arrow-left"></i> Kembali ke Data Siswa
    </a>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Edit Data Siswa</h1>
    <p class="text-slate-500 text-sm mt-1">Perbarui informasi profil, kelas, atau atur ulang kata sandi siswa.</p>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 md:p-8 max-w-4xl">
    
    <?php if(isset($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="ph ph-user text-slate-400 text-lg"></i></div>
                    <input type="text" name="nama_lengkap" required value="<?php echo htmlspecialchars($data_lama['nama_lengkap']); ?>" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">NISN</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="ph ph-identification-card text-slate-400 text-lg"></i></div>
                    <input type="text" name="nisn" value="<?php echo htmlspecialchars($data_lama['nisn'] ?? ''); ?>" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Username Login <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="ph ph-at text-slate-400 text-lg"></i></div>
                    <input type="text" name="username" required value="<?php echo htmlspecialchars($data_lama['username']); ?>" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Kata Sandi Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="ph ph-lock-key text-slate-400 text-lg"></i></div>
                    <input type="text" name="password" placeholder="Kosongkan jika tidak ingin diubah" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                </div>
                <p class="text-[11px] text-slate-500 mt-2"><i class="ph ph-info mr-1"></i>Hanya diisi jika siswa lupa kata sandi lamanya.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Penempatan Kelas <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select name="id_kelas" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none transition-all cursor-pointer">
                        <option value="">-- Pilih Kelas --</option>
                        <?php 
                        $q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                        while($k = mysqli_fetch_assoc($q_kelas)): 
                            $selected = ($k['id_kelas'] == $data_lama['id_kelas']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $k['id_kelas']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-slate-400"></i></div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Status Akun <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select name="status" required class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 appearance-none transition-all cursor-pointer">
                        <option value="aktif" <?php if($data_lama['status'] == 'aktif') echo 'selected'; ?>>Aktif</option>
                        <option value="nonaktif" <?php if($data_lama['status'] == 'nonaktif') echo 'selected'; ?>>Nonaktif</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="ph ph-caret-down text-slate-400"></i></div>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-slate-100 flex justify-end gap-3">
            <a href="data_siswa.php" class="px-6 py-3 border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-sm rounded-xl transition-colors">Batal</a>
            <button type="submit" name="update" class="px-6 py-3 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>