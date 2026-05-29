<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Guru - Y-Learn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        guru: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' }
                    }
                }
            }
        }
    </script>
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; } </style>
</head>
<body class="text-slate-800">

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<div class="flex min-h-screen">
    <aside class="w-72 bg-white border-r border-slate-200 hidden lg:flex flex-col sticky top-0 h-screen">
        <div class="p-8 flex items-center gap-3">
            <div class="w-10 h-10 bg-guru-600 rounded-xl flex items-center justify-center shadow-md shadow-guru-500/30">
                <i class="ph ph-chalkboard-teacher text-white text-2xl"></i>
            </div>
            <span class="text-xl font-extrabold tracking-tight text-slate-900">Guru</span>
        </div>

        <nav class="flex-1 px-4 space-y-2 mt-4">
            <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Aktivitas Mengajar</p>
            
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'index.php') ? 'text-guru-700 bg-guru-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-squares-four text-xl <?php echo ($current_page == 'index.php') ? 'text-guru-600' : 'text-slate-400'; ?>"></i> Dashboard
            </a>

            <a href="data_siswa.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'data_siswa.php') ? 'text-guru-700 bg-guru-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-users text-xl <?php echo ($current_page == 'data_siswa.php') ? 'text-guru-600' : 'text-slate-400'; ?>"></i> Data Siswa & Absensi
            </a>

            <a href="rekap_absensi.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'rekap_absensi.php') ? 'text-guru-700 bg-guru-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-chart-bar text-xl <?php echo ($current_page == 'rekap_absensi.php') ? 'text-guru-600' : 'text-slate-400'; ?>"></i> Rekap Keseluruhan Absen
            </a>
            
            <a href="materi.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'materi.php') ? 'text-guru-700 bg-guru-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-book-bookmark text-xl <?php echo ($current_page == 'materi.php') ? 'text-guru-600' : 'text-slate-400'; ?>"></i> Materi & Pembelajaran
            </a>
            
            <a href="tugas.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'tugas.php') ? 'text-guru-700 bg-guru-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-clipboard-text text-xl <?php echo ($current_page == 'tugas.php') ? 'text-guru-600' : 'text-slate-400'; ?>"></i> Kelola Tugas
            </a>
            
            <a href="ujian.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'ujian.php') ? 'text-guru-700 bg-guru-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-exam text-xl <?php echo ($current_page == 'ujian.php') ? 'text-guru-600' : 'text-slate-400'; ?>"></i> Kuis & Ujian
            </a>
            
            <a href="penilaian.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'penilaian.php') ? 'text-guru-700 bg-guru-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-medal text-xl <?php echo ($current_page == 'penilaian.php') ? 'text-guru-600' : 'text-slate-400'; ?>"></i> Nilai & Feedback
            </a>

            <a href="forum.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all <?php echo ($current_page == 'forum.php') ? 'text-guru-700 bg-guru-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>">
                <i class="ph ph-chats-teardrop text-xl <?php echo ($current_page == 'forum.php') ? 'text-guru-600' : 'text-slate-400'; ?>"></i> Forum Diskusi
            </a>
        </nav>

        <div class="p-6 border-t border-slate-100">
            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama']); ?>&background=16a34a&color=fff" class="w-10 h-10 rounded-full" alt="Avatar">
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-900 truncate"><?php echo $_SESSION['nama']; ?></p>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mt-0.5">Pengajar</p>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col">
        <header class="sticky top-0 z-30 px-8 py-4 flex items-center justify-between bg-white/90 backdrop-blur-md border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest">
                <?php 
                    if($current_page == 'index.php') echo "Beranda Guru";
                    else if($current_page == 'data_siswa.php') echo "Data Siswa & Absensi";
                    // Kondisi baru agar title barnya rapi saat di halaman rekap
                    else if($current_page == 'rekap_absensi.php') echo "Rekap Absensi Siswa";
                    else echo str_replace('.php', '', ucfirst($current_page));
                ?>
            </h2>
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col text-right mr-4 border-r border-slate-200 pr-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tahun Ajaran Aktif</span>
                    <span class="text-sm font-extrabold text-guru-600">
                        <?php echo isset($_SESSION['nama_tahun_aktif']) ? $_SESSION['nama_tahun_aktif'] . ' - ' . $_SESSION['semester_aktif'] : 'Belum Diatur'; ?>
                    </span>
                </div>
                <a href="../logout.php" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-600 rounded-xl text-xs font-bold transition-all">
                    <i class="ph ph-sign-out text-lg"></i> Keluar
                </a>
            </div>
        </header>
        <div class="p-8 max-w-7xl mx-auto w-full">