<?php
session_start();
// Pastikan hanya admin yang bisa mengakses fitur cetak
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Siswa - SMA YAPAN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #ffffff; color: #000000; }
        /* Pengaturan khusus saat diprint ke kertas */
        @media print {
            @page { margin: 2cm; size: A4 portrait; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #334155; padding: 10px 12px; font-size: 12px; }
        th { background-color: #f1f5f9; font-weight: bold; text-align: left; text-transform: uppercase; }
    </style>
</head>
<body class="p-8 max-w-5xl mx-auto">

    <div class="mb-6 flex justify-end no-print">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white font-bold rounded shadow hover:bg-blue-700 text-sm">
            Cetak Sekarang
        </button>
    </div>

    <div class="border-b-[3px] border-slate-800 pb-4 mb-6 flex items-center gap-6">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center border border-slate-300 shrink-0">
            <span class="font-black text-2xl text-slate-800">YP</span>
        </div>
        <div>
            <h1 class="text-2xl font-black uppercase tracking-wide">SMA YAPAN INDONESIA</h1>
            <p class="text-sm font-medium mt-1">NPSN: 12345678 | Terakreditasi "A"</p>
            <p class="text-xs mt-1">Jl. Pendidikan No. 123, Kota Tangerang, Banten. Telp: (021) 555-1234</p>
        </div>
    </div>

    <div class="text-center mb-6">
        <h2 class="text-lg font-bold uppercase underline decoration-2 underline-offset-4">Laporan Data Peserta Didik</h2>
        <p class="text-sm font-medium mt-2">Tahun Ajaran 2025/2026</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="w-12 text-center">No</th>
                <th class="w-40">NISN (Username)</th>
                <th>Nama Lengkap Siswa</th>
                <th class="w-32 text-center">Ruang Kelas</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            // Ambil data siswa dan nama kelasnya
            $query = mysqli_query($koneksi, "
                SELECT users.nama_lengkap, users.username, kelas.nama_kelas 
                FROM users 
                LEFT JOIN kelas ON users.id_kelas = kelas.id_kelas 
                WHERE users.role = 'siswa' 
                ORDER BY kelas.nama_kelas ASC, users.nama_lengkap ASC
            ");

            if(mysqli_num_rows($query) > 0):
                while($siswa = mysqli_fetch_assoc($query)):
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td class="font-mono"><?php echo htmlspecialchars($siswa['username']); ?></td>
                <td class="font-semibold"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                <td class="text-center"><?php echo $siswa['nama_kelas'] ? htmlspecialchars($siswa['nama_kelas']) : '-'; ?></td>
            </tr>
            <?php 
                endwhile; 
            else: 
            ?>
            <tr>
                <td colspan="4" class="text-center py-4 italic">Belum ada data siswa terdaftar.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="mt-16 flex justify-end">
        <div class="text-center w-64">
            <p class="text-sm">Tangerang, <?php echo date('d F Y'); ?></p>
            <p class="text-sm font-bold mt-1">Kepala SMA YAPAN,</p>
            <br><br><br><br> <p class="text-sm font-bold underline">Dr. Budi Santoso, M.Pd.</p>
            <p class="text-xs mt-1">NIP. 19801231 200501 1 001</p>
        </div>
    </div>

    <script>
        // Saat halaman selesai dimuat, langsung panggil perintah print komputer
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>