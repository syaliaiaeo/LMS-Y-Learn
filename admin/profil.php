<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit; }

require_once '../config/koneksi.php';

// Ambil ID admin yang sedang login dari session (asumsi session menyimpan id_user)
// Jika session id_user belum ada, kita bisa mencarinya berdasarkan username yang login
$username_login = $_SESSION['username']; 
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username_login'");
$data_admin = mysqli_fetch_assoc($query);

if (isset($_POST['update_profil'])) {
    $nama_baru = trim(mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']));
    $pass_baru = $_POST['password_baru'];

    if (!empty($pass_baru)) {
        // Jika password diisi, update nama dan password
        $pass_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama_baru', password='$pass_hash' WHERE username='$username_login'");
    } else {
        // Jika password kosong, update nama saja
        $update = mysqli_query($koneksi, "UPDATE users SET nama_lengkap='$nama_baru' WHERE username='$username_login'");
    }

    if ($update) {
        $_SESSION['nama'] = $nama_baru; // Update session nama agar langsung berubah di sidebar
        header("Location: profil.php?pesan=sukses");
        exit;
    } else {
        $error = "Gagal memperbarui profil!";
    }
}

include '../includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Profil</h1>
    <p class="text-slate-500 text-sm mt-1">Kelola informasi pribadi dan keamanan akun Anda.</p>
</div>

<div class="max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
    
    <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'sukses'): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
            <i class="ph ph-check-circle text-lg"></i> Profil berhasil diperbarui!
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" required value="<?php echo htmlspecialchars($data_admin['nama_lengkap']); ?>"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-sekolah-500 focus:ring-4 focus:ring-sekolah-500/10">
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Username Login</label>
            <input type="text" value="<?php echo htmlspecialchars($data_admin['username']); ?>" disabled
                class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
            <p class="text-xs text-slate-400 mt-2">Username administrator bersifat tetap dan tidak dapat diubah.</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Ganti Kata Sandi (Opsional)</label>
            <input type="password" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah sandi"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-sekolah-500 focus:ring-4 focus:ring-sekolah-500/10">
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit" name="update_profil" class="px-6 py-3 bg-sekolah-600 text-white font-bold rounded-xl shadow-md hover:bg-sekolah-700 transition-all">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>