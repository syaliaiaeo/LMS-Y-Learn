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
// PROSES GANTI KATA SANDI
// ==========================================
if (isset($_POST['ganti'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi    = $_POST['konfirmasi'];

    // 1. Ambil data password lama dari database
    $query = mysqli_query($koneksi, "SELECT password FROM users WHERE id_user = '$id_siswa'");
    $data = mysqli_fetch_assoc($query);

    // 2. Verifikasi apakah password lama yang diketik COCOK dengan yang ada di database
    if (password_verify($password_lama, $data['password'])) {
        
        // 3. Cek apakah password baru dan konfirmasi sama
        if ($password_baru === $konfirmasi) {
            
            // 4. Enkripsi password baru sebelum disimpan
            $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            
            // 5. Update ke database
            $update = mysqli_query($koneksi, "UPDATE users SET password = '$password_hashed' WHERE id_user = '$id_siswa'");
            
            if ($update) {
                $_SESSION['sukses'] = "Kata sandi berhasil diperbarui! Silakan gunakan sandi baru untuk login selanjutnya.";
            } else {
                $error = "Terjadi kesalahan sistem. Gagal mengubah kata sandi.";
            }
        } else {
            $error = "Konfirmasi kata sandi baru tidak cocok!";
        }
    } else {
        $error = "Kata sandi lama yang Anda masukkan salah!";
    }
}

// Ganti 'includes/header.php' sesuai dengan path aslimu
include 'includes/header.php'; 
?>

<div class="mb-8">
    <a href="index.php" class="text-sm font-medium text-slate-500 hover:text-blue-600 flex items-center gap-2 w-fit transition-colors mb-4">
        <i class="ph ph-arrow-left text-lg"></i> Kembali ke Beranda
    </a>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Ganti Kata Sandi</h1>
    <p class="text-slate-500 text-sm mt-1">Pastikan Anda menggunakan kata sandi yang kuat dan mudah diingat.</p>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 md:p-8 max-w-2xl">
    
    <?php if (isset($_SESSION['sukses'])): ?>
        <div id="alert-sukses" class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-4 rounded-xl mb-6 flex items-start gap-3 shadow-sm">
            <i class="ph ph-check-circle text-xl mt-0.5"></i>
            <div>
                <p class="text-sm font-bold">Berhasil!</p>
                <p class="text-xs mt-1"><?php echo $_SESSION['sukses']; ?></p>
            </div>
            <button onclick="document.getElementById('alert-sukses').style.display='none'" class="ml-auto text-emerald-500 hover:text-emerald-700"><i class="ph ph-x text-lg font-bold"></i></button>
        </div>
    <?php unset($_SESSION['sukses']); endif; ?>

    <?php if(isset($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Kata Sandi Saat Ini <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="ph ph-lock-key-open text-slate-400 text-lg"></i>
                </div>
                <input type="password" name="password_lama" required placeholder="Masukkan kata sandi lama Anda" 
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <label class="block text-sm font-bold text-slate-700 mb-2">Kata Sandi Baru <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="ph ph-lock-key text-slate-400 text-lg"></i>
                </div>
                <input type="password" name="password_baru" required placeholder="Ketik kata sandi baru" minlength="6"
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Minimal 6 karakter. Kombinasikan huruf dan angka agar lebih aman.</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="ph ph-check-circle text-slate-400 text-lg"></i>
                </div>
                <input type="password" name="konfirmasi" required placeholder="Ketik ulang kata sandi baru" minlength="6"
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <div class="pt-8 flex justify-end gap-3">
            <button type="submit" name="ganti" class="px-6 py-3 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Kata Sandi
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>