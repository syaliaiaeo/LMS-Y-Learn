<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Siswa - LMS Y-Learn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; /* slate-50 */ }
        
        /* Modifikasi scrollbar agar lebih manis */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a9fafa; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800">

    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>

    <aside class="w-64 border-r border-slate-200 flex flex-col hidden md:flex shrink-0 z-20 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
        
        <div class="h-16 flex items-center px-8 bg-indigo-50/80 border-b border-indigo-100/50">
            <h2 class="text-2xl font-black tracking-tight"><span class="text-indigo-600">Y-</span><span class="text-slate-800">Learn</span></h2>
        </div>
        
        <div class="flex-1 overflow-y-auto py-8 px-4 space-y-1.5 bg-white">
            <p class="px-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4">Menu Utama</p>
            
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group <?php echo ($current_page == 'index.php') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                <i class="ph ph-squares-four text-xl transition-colors <?php echo ($current_page == 'index.php') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'; ?>"></i> Dashboard
            </a>

            <a href="kalender.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group <?php echo ($current_page == 'kalender.php') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                <i class="ph ph-calendar text-xl transition-colors <?php echo ($current_page == 'kalender.php') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'; ?>"></i> Kalender & Jadwal
            </a>

            <a href="absensi.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group <?php echo ($current_page == 'absensi.php') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                <i class="ph ph-calendar-check text-xl transition-colors <?php echo ($current_page == 'absensi.php') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'; ?>"></i> Absensi Saya
            </a>

            <a href="materi.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group <?php echo ($current_page == 'materi.php') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                <i class="ph ph-books text-xl transition-colors <?php echo ($current_page == 'materi.php') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'; ?>"></i> Materi Pelajaran
            </a>

            <a href="tugas.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group <?php echo ($current_page == 'tugas.php') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                <i class="ph ph-pencil-line text-xl transition-colors <?php echo ($current_page == 'tugas.php') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'; ?>"></i> Tugas Harian
            </a>

            <a href="ujian.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group <?php echo ($current_page == 'ujian.php') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                <i class="ph ph-exam text-xl transition-colors <?php echo ($current_page == 'ujian.php') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'; ?>"></i> Kuis & Ujian
            </a>

            <a href="forum.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group <?php echo ($current_page == 'forum.php') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                <i class="ph ph-chats-circle text-xl transition-colors <?php echo ($current_page == 'forum.php') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'; ?>"></i> Forum Diskusi
            </a>

            <a href="raport.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group <?php echo ($current_page == 'raport.php') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                <i class="ph ph-medal text-xl transition-colors <?php echo ($current_page == 'raport.php') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'; ?>"></i> Raport Nilai
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0 z-10 shadow-sm shadow-slate-100">
            <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest">
                <?php 
                    if($current_page == 'index.php') echo "Beranda Siswa";
                    else if($current_page == 'kalender.php') echo "Kalender Akademik";
                    else if($current_page == 'absensi.php') echo "Absensi Saya";
                    else echo str_replace('.php', '', ucfirst($current_page));
                ?>
            </h2>
            
            <div class="relative inline-block text-left">
                <button onclick="document.getElementById('dropdownProfilSiswa').classList.toggle('hidden')" class="flex items-center gap-3 focus:outline-none group hover:bg-slate-50 p-1.5 pr-3 rounded-full transition-colors border border-transparent hover:border-slate-200">
                    <div class="text-right hidden md:block ml-2">
                        <p class="text-sm font-extrabold text-slate-700 group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars(explode(' ', $_SESSION['nama'])[0]); ?></p>
                        <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Siswa</p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                        <i class="ph ph-user text-lg"></i>
                    </div>
                </button>

                <div id="dropdownProfilSiswa" class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl shadow-slate-200/50 py-2 z-50 transform origin-top-right transition-all">
                    <div class="px-4 py-3 border-b border-slate-100 mb-1">
                        <p class="text-xs text-slate-400 font-semibold mb-0.5">Masuk sebagai</p>
                        <p class="text-sm font-bold text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                    </div>
                    
                    <a href="ganti_password.php" class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500"><i class="ph ph-key text-base"></i></div>
                        Ganti Sandi
                    </a>
                    
                    <div class="border-t border-slate-100 my-1"></div>
                    
                    <a href="../logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                        <div class="w-7 h-7 rounded-lg bg-rose-100 flex items-center justify-center text-rose-500"><i class="ph ph-sign-out text-base"></i></div>
                        Keluar Akses
                    </a>
                </div>
            </div>
            
            <script>
                window.addEventListener('click', function(e) {
                    const dropdown = document.getElementById('dropdownProfilSiswa');
                    const button = dropdown.previousElementSibling;
                    if (dropdown && button && !button.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            </script>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 relative">