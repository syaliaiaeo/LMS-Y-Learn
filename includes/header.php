<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Y-Learn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        sekolah: {
                            50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 
                            600: '#2563eb', 700: '#1d4ed8', 900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
<body class="text-slate-800">

<?php 
// Mendapatkan nama file yang sedang dibuka saat ini
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<div class="flex min-h-screen">
    <aside class="w-72 bg-white border-r border-slate-200 hidden lg:flex flex-col sticky top-0 h-screen">
        <div class="p-8 flex items-center gap-3">
            <div class="w-10 h-10 bg-sekolah-600 rounded-xl flex items-center justify-center shadow-md shadow-sekolah-500/30">
                <i class="ph ph-graduation-cap text-white text-2xl"></i>
            </div>
            <span class="text-xl font-extrabold tracking-tight text-slate-900">Y-Learn</span>
        </div>

        <nav class="flex-1 px-4 space-y-2 mt-4">
            <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Menu Utama</p>
            
            <a href="index.php" class="sidebar-item flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'index.php') ? 'text-sekolah-600 bg-sekolah-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-squares-four text-xl <?php echo ($current_page == 'index.php') ? 'text-sekolah-600' : 'text-slate-400'; ?>"></i> Dashboard
            </a>
            
            <a href="data_guru.php" class="sidebar-item flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'data_guru.php' || $current_page == 'tambah_guru.php') ? 'text-sekolah-600 bg-sekolah-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-chalkboard-teacher text-xl <?php echo ($current_page == 'data_guru.php' || $current_page == 'tambah_guru.php') ? 'text-sekolah-600' : 'text-slate-400'; ?>"></i> Manajemen Guru
            </a>
            
            <a href="data_siswa.php" class="sidebar-item flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'data_siswa.php' || $current_page == 'tambah_siswa.php') ? 'text-sekolah-600 bg-sekolah-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-users-three text-xl <?php echo ($current_page == 'data_siswa.php' || $current_page == 'tambah_siswa.php') ? 'text-sekolah-600' : 'text-slate-400'; ?>"></i> Data Siswa
            </a>
            
            <a href="data_kelas.php" class="sidebar-item flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'data_kelas.php') ? 'text-sekolah-600 bg-sekolah-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-door text-xl <?php echo ($current_page == 'data_kelas.php') ? 'text-sekolah-600' : 'text-slate-400'; ?>"></i> Ruang Kelas
            </a>
            
            <a href="data_mapel.php" class="sidebar-item flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'data_mapel.php') ? 'text-sekolah-600 bg-sekolah-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-book-open text-xl <?php echo ($current_page == 'data_mapel.php') ? 'text-sekolah-600' : 'text-slate-400'; ?>"></i> Mata Pelajaran
            </a>

            <a href="pengaturan_akademik.php" class="sidebar-item flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'pengaturan_akademik.php') ? 'text-sekolah-600 bg-sekolah-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-gear text-xl <?php echo ($current_page == 'pengaturan_akademik.php') ? 'text-sekolah-600' : 'text-slate-400'; ?>"></i> Pengaturan Akademik
            </a>
        </nav>

        <div class="p-6 border-t border-slate-100">
            <a href="profil.php" class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center gap-3 hover:bg-slate-100 hover:border-slate-200 hover:shadow-sm transition-all cursor-pointer group block">
                
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama']); ?>&background=2563eb&color=fff" class="w-10 h-10 rounded-full group-hover:scale-105 transition-transform" alt="Avatar">
                
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-900 truncate group-hover:text-sekolah-600 transition-colors"><?php echo $_SESSION['nama']; ?></p>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mt-0.5">Administrator</p>
                </div>
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col">
        <header class="glass-nav sticky top-0 z-30 px-8 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest">
                <?php 
                    if($current_page == 'index.php') echo "Dashboard Overview";
                    else if($current_page == 'data_guru.php' || $current_page == 'tambah_guru.php') echo "Modul Guru";
                    else if($current_page == 'data_siswa.php' || $current_page == 'import_siswa.php') echo "Modul Siswa";
                    else if($current_page == 'data_kelas.php') echo "Modul Kelas";
                    else if($current_page == 'data_mapel.php') echo "Modul Mapel";
                    else if($current_page == 'pengaturan_akademik.php') echo "Pengaturan Sistem";
                    else if($current_page == 'profil.php') echo "Profil Admin";
                ?>
            </h2>
            <div class="flex items-center gap-4">
                
                <div class="hidden md:flex flex-col text-right mr-4 border-r border-slate-200 pr-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tahun Ajaran Aktif</span>
                    <span class="text-sm font-extrabold text-sekolah-600">
                        <?php echo isset($_SESSION['nama_tahun_aktif']) ? $_SESSION['nama_tahun_aktif'] . ' - ' . $_SESSION['semester_aktif'] : 'Belum Diatur'; ?>
                    </span>
                </div>

                <div class="relative">
                    <button onclick="document.getElementById('notif-panel').classList.toggle('hidden')" class="p-2 text-slate-400 hover:text-sekolah-600 transition-colors relative">
                        <i class="ph ph-bell text-2xl"></i>
                        <span id="badge-notif" class="hidden absolute top-0 right-0 w-4 h-4 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center border-2 border-white">0</span>
                    </button>

                    <div id="notif-panel" class="hidden absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                        <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 text-sm">Pusat Notifikasi</h3>
                        </div>
                        
                        <div id="isi-notif" class="max-h-[400px] overflow-y-auto">
                            <div class="p-8 text-center text-slate-400 text-xs font-medium">
                                Memuat notifikasi...
                            </div>
                        </div>
                        
                        <div class="p-3 border-t border-slate-100 bg-slate-50 text-center">
                            <a href="#" class="text-xs font-bold text-sekolah-600 hover:text-sekolah-700">Lihat Semua Notifikasi &rarr;</a>
                        </div>
                    </div>
                </div>

                <a href="../logout.php" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-600 rounded-xl text-xs font-bold transition-all">
                    <i class="ph ph-sign-out text-lg"></i> Keluar
                </a>
            </div>
        </header>

        <div class="p-8 max-w-7xl mx-auto w-full">

        <script>
        function fetchNotifikasi() {
            // Memanggil file get_notifikasi.php secara otomatis
            fetch('get_notifikasi.php')
                .then(response => response.json())
                .then(data => {
                    // 1. Update Angka Badge Merah
                    const badge = document.getElementById('badge-notif');
                    if(data.jumlah_baru > 0) {
                        badge.innerText = data.jumlah_baru;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }

                    // 2. Render Daftar Notifikasi
                    const isiNotif = document.getElementById('isi-notif');
                    let html = '';

                    if(data.notifikasi.length === 0) {
                        html = `<div class="p-8 text-center text-slate-400 text-xs font-medium">Belum ada notifikasi.</div>`;
                    } else {
                        data.notifikasi.forEach(item => {
                            // Mewarnai ikon sesuai jenis
                            let colorClass = 'bg-blue-100 text-blue-600';
                            let iconClass = 'ph-info';
                            
                            if(item.jenis == 'success') { colorClass = 'bg-emerald-100 text-emerald-600'; iconClass = 'ph-check-circle'; }
                            else if(item.jenis == 'warning') { colorClass = 'bg-amber-100 text-amber-600'; iconClass = 'ph-warning-circle'; }
                            else if(item.jenis == 'system') { colorClass = 'bg-rose-100 text-rose-600'; iconClass = 'ph-hard-drives'; }

                            let bgUnread = item.status == 'belum_dibaca' ? 'bg-slate-50' : 'bg-white';

                            html += `
                            <div class="p-4 flex gap-3 items-start border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer ${bgUnread}">
                                <div class="w-8 h-8 rounded-full ${colorClass} flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="ph ${iconClass} font-bold text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">${item.judul}</p>
                                    <p class="text-xs text-slate-600 mt-0.5">${item.pesan}</p>
                                    <p class="text-[10px] text-slate-400 mt-1.5 font-medium">${item.waktu}</p>
                                </div>
                            </div>`;
                        });
                    }
                    isiNotif.innerHTML = html;
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        // Panggil saat halaman pertama kali dibuka
        fetchNotifikasi();

        // Ulangi proses tarikan data setiap 5 detik (5000ms)
        setInterval(fetchNotifikasi, 5000);
        </script>