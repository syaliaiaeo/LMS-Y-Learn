<?php
session_start();
if (isset($_SESSION['role'])) { header("Location: " . $_SESSION['role'] . "/index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - LMS Y-Learn</title>
    
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
        
        /* Animasi Orbs/Blobs Pastel di Background */
        .blob {
            position: absolute; filter: blur(90px); z-index: 0; opacity: 0.7;
            animation: float 12s infinite ease-in-out alternate;
        }
        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(-40px) scale(1.1); }
        }
        
        /* Efek Kaca (Glassmorphism) Terang */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.1);
        }

        /* Input kustom terang */
        .input-glow:focus {
            outline: none;
            border-color: #a5b4fc; /* indigo-300 */
            box-shadow: 0 0 0 4px rgba(165, 180, 252, 0.2);
            background: #ffffff;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-gradient-to-br from-indigo-50 via-white to-purple-50 text-slate-800">

    <div class="blob w-[32rem] h-[32rem] bg-indigo-200/50 rounded-full top-[-10%] left-[-10%] mix-blend-multiply"></div>
    <div class="blob w-[32rem] h-[32rem] bg-pink-200/50 rounded-full bottom-[-10%] right-[-10%] mix-blend-multiply" style="animation-delay: -6s;"></div>
    <div class="blob w-[24rem] h-[24rem] bg-sky-200/40 rounded-full bottom-[20%] left-[20%] mix-blend-multiply" style="animation-delay: -3s;"></div>

    <div class="w-full max-w-md p-8 relative z-10">
        <div class="glass-panel rounded-[2rem] p-10 relative overflow-hidden">
            
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-300 via-purple-300 to-pink-300"></div>

            <div class="flex justify-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center shadow-lg shadow-indigo-200/50">
                    <i class="ph ph-student text-3xl text-white"></i>
                </div>
            </div>

            <div class="text-center mb-10">
                <h2 class="text-3xl font-extrabold text-slate-800 mb-2 tracking-tight">Y-Learn</h2>
                <p class="text-slate-500 text-sm font-medium">Masuk untuk mengakses ruang belajar Anda</p>
            </div>

            <?php if(isset($_SESSION['error_login'])): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-600 text-sm p-4 rounded-xl mb-6 flex items-start shadow-sm">
                    <i class="ph ph-warning-circle text-lg mr-2 mt-0.5 shrink-0 font-bold"></i>
                    <span class="leading-relaxed font-semibold">
                        <?php 
                            echo $_SESSION['error_login']; 
                            unset($_SESSION['error_login']); 
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <form action="cek_login.php" method="POST" autocomplete="off" class="space-y-6">
                
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-600 ml-1">Username ID</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-user text-slate-400 text-lg"></i>
                        </div>
                        <input type="text" name="username" required autocomplete="off"
                            class="w-full pl-11 pr-4 py-3.5 bg-white/60 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 input-glow transition-all duration-300 font-medium"
                            placeholder="Ketik username Anda">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-sm font-bold text-slate-600">Kata Sandi</label>
                        <a href="lupa_sandi.php" class="text-xs font-semibold text-indigo-500 hover:text-indigo-600 transition-colors">Lupa sandi?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-lock-key text-slate-400 text-lg"></i>
                        </div>
                        <input type="password" name="password" required autocomplete="new-password"
                            class="w-full pl-11 pr-4 py-3.5 bg-white/60 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 input-glow transition-all duration-300 font-medium"
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" 
                    class="w-full py-4 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all duration-300 flex justify-center items-center group mt-8">
                    <span>Masuk Sistem</span>
                    <i class="ph ph-arrow-right text-lg ml-2 group-hover:translate-x-1.5 transition-transform"></i>
                </button>
            </form>

        </div>
        
        <p class="text-center text-slate-400 text-xs mt-8 font-semibold tracking-wide">
            &copy; 2026 SMA YAPAN Indonesia &bull; V.3.0
        </p>
    </div>

</body>
</html>