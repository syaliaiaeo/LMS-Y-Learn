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
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    
    // Enkripsi password agar bisa dipakai login
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    $tahun_masuk = mysqli_real_escape_string($koneksi, $_POST['tahun_masuk']);
    $tahun_keluar = mysqli_real_escape_string($koneksi, $_POST['tahun_keluar']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Tangkap data spesialisasi mapel
    $id_mapel = !empty($_POST['id_mapel']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['id_mapel']) . "'" : "NULL";

    $query_insert = "
        INSERT INTO users (nama_lengkap, nip, username, password, role, tahun_masuk, tahun_keluar, status, id_mapel) 
        VALUES ('$nama', '$nip', '$username', '$password', 'guru', '$tahun_masuk', '$tahun_keluar', '$status', $id_mapel)
    ";

    // Menggunakan Session untuk notifikasi modern
    if (mysqli_query($koneksi, $query_insert)) {
        $_SESSION['sukses'] = "Data Guru dan Spesialisasi Mapel berhasil ditambahkan!";
        header("Location: data_guru.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan data guru!";
    }
}

// Ambil daftar tahun ajaran untuk combo box
$q_tahun = mysqli_query($koneksi, "SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC");

// Ambil daftar mapel untuk combo box spesialisasi
$q_mapel = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY nama_mapel ASC");

include '../includes/header.php'; 
?>

<div class="mb-8 flex items-center gap-4">
    <a href="data_guru.php" class="w-10 h-10 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors shadow-sm">
        <i class="ph ph-arrow-left text-xl"></i>
    </a>
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Guru Baru</h1>
        <p class="text-slate-500 text-sm mt-1">Isi formulir di bawah ini untuk menambahkan akun pengajar.</p>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 max-w-4xl">
    <form action="" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap (beserta Gelar)</label>
                    <input type="text" name="nama_lengkap" required placeholder="Contoh: Budi Santoso, S.Pd" 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">NIP (Opsional)</label>
                    <input type="text" name="nip" placeholder="Masukkan NIP jika ada..." 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Mata Pelajaran (Spesialisasi)</label>
                    <div class="relative">
                        <select name="id_mapel" required class="w-full pl-4 pr-10 py-2.5 bg-indigo-50/30 border border-indigo-200 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:bg-white appearance-none transition-all">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php 
                            mysqli_data_seek($q_mapel, 0);
                            while($mp = mysqli_fetch_assoc($q_mapel)): 
                            ?>
                                <option value="<?php echo $mp['id_mapel']; ?>"><?php echo htmlspecialchars($mp['nama_mapel']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="ph ph-books text-indigo-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Username Login</label>
                    <input type="text" name="username" required placeholder="Contoh: guru_budi" 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Password Akun</label>
                    <input type="password" name="password" required placeholder="Buat password untuk guru..." 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Mulai Mengajar</label>
                    <div class="relative">
                        <select name="tahun_masuk" required class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                            <option value="">-- Pilih Tahun Masuk --</option>
                            <?php 
                            mysqli_data_seek($q_tahun, 0);
                            while($thn = mysqli_fetch_assoc($q_tahun)): 
                            ?>
                                <option value="<?php echo $thn['nama_tahun']; ?>"><?php echo $thn['nama_tahun']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="ph ph-caret-down text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Keluar / Berakhir</label>
                    <div class="relative">
                        <select name="tahun_keluar" required class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:bg-white appearance-none transition-all">
                            <option value="Sekarang" class="font-bold text-emerald-600">Sekarang (Masih Aktif Mengajar)</option>
                            <?php 
                            mysqli_data_seek($q_tahun, 0);
                            while($thn = mysqli_fetch_assoc($q_tahun)): 
                            ?>
                                <option value="<?php echo $thn['nama_tahun']; ?>"><?php echo $thn['nama_tahun']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="ph ph-caret-down text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Status Akun</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="aktif" checked class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="text-sm text-slate-700 font-medium">Aktif</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="nonaktif" class="w-4 h-4 text-rose-600 border-gray-300 focus:ring-rose-500">
                            <span class="text-sm text-slate-700 font-medium">Nonaktif</span>
                        </label>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end gap-3">
            <a href="data_guru.php" class="px-6 py-2.5 border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold text-sm rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" name="simpan" class="px-6 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Data Guru
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>