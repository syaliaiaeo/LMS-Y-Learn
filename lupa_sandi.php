<?php
session_start();
require_once 'config/koneksi.php'; // Pastikan path ini benar (berada di luar folder guru/siswa)

$user_found = false;
$id_user_reset = '';
$nama_user = '';
$sukses_reset = false;

// TAHAP 1: Cari Username
if (isset($_POST['cek_username'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $query = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM users WHERE username = '$username'");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $user_found = true;
        $id_user_reset = $data['id_user'];
        $nama_user = $data['nama_lengkap'];
    } else {
        $error = "Username tidak ditemukan di dalam sistem.";
    }
}

// TAHAP 2: Simpan Sandi Baru
if (isset($_POST['reset_password'])) {
    $id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    $nama_user = $_POST['nama_user']; // Membawa nama user agar tetap tampil jika error
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password_baru === $konfirmasi) {
        // Enkripsi kata sandi baru
        $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
        $query_update = "UPDATE users SET password = '$password_hashed' WHERE id_user = '$id_user'";

        if (mysqli_query($koneksi, $query_update)) {
            $sukses_reset = true;
        } else {
            $error = "Terjadi kesalahan. Gagal mereset kata sandi.";
            $user_found = true;
            $id_user_reset = $id_user;
        }
    } else {
        $error = "Kata sandi baru dan konfirmasi tidak cocok!";
        $user_found = true;
        $id_user_reset = $id_user;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Sandi - LMS Y-Learn</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }
        
        .blob {
            position: absolute; filter: blur(90px); z-index: 0; opacity: 0.7;
            animation: float 12s infinite ease-in-out alternate;
        }
        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(-40px) scale(1.1); }
        }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.1);
        }
        
        .input-glow:focus {
            outline: none; border-color: #a5b4fc; box-shadow: 0 0 0 4px rgba(165, 180, 252, 0.2); background: #ffffff;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-gradient-to-br from-indigo-50 via-white to-purple-50 text-slate-800">

    <div class="blob w-[32rem] h-[32rem] bg-indigo-200/50 rounded-full top-[-10%] left-[-10%] mix-blend-multiply"></div>
    <div class="blob w-[32rem] h-[32rem] bg-pink-200/50 rounded-full bottom-[-10%] right-[-10%] mix-blend-multiply" style="animation-delay: -6s;"></div>

    <div class="w-full max-w-md p-8 relative z-10">
        <div class="glass-panel rounded-[2rem] p-10 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-300 via-purple-300 to-pink-300"></div>

            <?php if ($sukses_reset): ?>
                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-emerald-100 border-4 border-white flex items-center justify-center shadow-lg shadow-emerald-200/50">
                        <i class="ph-fill ph-check-circle text-4xl text-emerald-500"></i>
                    </div>
                </div>
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-extrabold text-slate-800 mb-2 tracking-tight">Berhasil!</h2>
                    <p class="text-slate-500 text-sm font-medium">Kata sandi Anda telah berhasil diperbarui. Silakan masuk menggunakan kata sandi baru.</p>
                </div>
                <a href="index.php" class="w-full py-4 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all duration-300 flex justify-center items-center">
                    Masuk ke Sistem
                </a>

            <?php elseif ($user_found): ?>
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center shadow-lg shadow-indigo-200/50">
                        <i class="ph ph-shield-check text-3xl text-white"></i>
                    </div>
                </div>
                <div class="text-center mb-6">
                    <h2 class="text-xl font-extrabold text-slate-800 mb-1 tracking-tight">Buat Sandi Baru</h2>
                    <p class="text-slate-500 text-sm font-medium">Akun ditemukan untuk: <b class="text-indigo-600"><?php echo htmlspecialchars($nama_user); ?></b></p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="bg-rose-50 border border-rose-200 text-rose-600 text-xs p-3 rounded-xl mb-6 flex items-start shadow-sm font-semibold">
                        <i class="ph ph-warning-circle text-base mr-2 shrink-0"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" autocomplete="off" class="space-y-4">
                    <input type="hidden" name="id_user" value="<?php echo $id_user_reset; ?>">
                    <input type="hidden" name="nama_user" value="<?php echo htmlspecialchars($nama_user); ?>">
                    
                    <div>
                        <label class="text-xs font-bold text-slate-600 ml-1">Kata Sandi Baru</label>
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="ph ph-lock-key text-slate-400 text-lg"></i></div>
                            <input type="password" name="password_baru" required minlength="6" class="w-full pl-11 pr-4 py-3 bg-white/60 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 input-glow transition-all duration-300 font-medium text-sm" placeholder="Minimal 6 karakter">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-600 ml-1">Konfirmasi Kata Sandi</label>
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="ph ph-check-circle text-slate-400 text-lg"></i></div>
                            <input type="password" name="konfirmasi" required class="w-full pl-11 pr-4 py-3 bg-white/60 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 input-glow transition-all duration-300 font-medium text-sm" placeholder="Ketik ulang sandi baru">
                        </div>
                    </div>

                    <button type="submit" name="reset_password" class="w-full py-3.5 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all duration-300 flex justify-center items-center mt-6">
                        Simpan Sandi Baru
                    </button>
                    <a href="index.php" class="w-full py-3 bg-white hover:bg-slate-50 text-slate-500 font-bold border border-slate-200 rounded-xl transition-all duration-300 flex justify-center items-center mt-3 text-sm">
                        Batal
                    </a>
                </form>

            <?php else: ?>
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-white border border-indigo-100 flex items-center justify-center shadow-md shadow-indigo-100/50">
                        <i class="ph ph-magnifying-glass text-3xl text-indigo-500"></i>
                    </div>
                </div>
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-800 mb-2 tracking-tight">Cari Akun Anda</h2>
                    <p class="text-slate-500 text-sm font-medium">Masukkan Username ID untuk mengatur ulang kata sandi.</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="bg-rose-50 border border-rose-200 text-rose-600 text-xs p-3 rounded-xl mb-6 flex items-start shadow-sm font-semibold">
                        <i class="ph ph-warning-circle text-base mr-2 shrink-0"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" autocomplete="off" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-600 ml-1">Username ID</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ph ph-user text-slate-400 text-lg"></i>
                            </div>
                            <input type="text" name="username" required class="w-full pl-11 pr-4 py-3.5 bg-white/60 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 input-glow transition-all duration-300 font-medium" placeholder="Ketik username Anda">
                        </div>
                    </div>

                    <button type="submit" name="cek_username" class="w-full py-4 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all duration-300 flex justify-center items-center">
                        Lanjutkan
                    </button>
                    <a href="index.php" class="w-full py-3 bg-white hover:bg-slate-50 text-slate-600 font-bold border border-slate-200 rounded-xl transition-all duration-300 flex justify-center items-center">
                        <i class="ph ph-arrow-left text-lg mr-2"></i> Kembali ke Login
                    </a>
                </form>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>