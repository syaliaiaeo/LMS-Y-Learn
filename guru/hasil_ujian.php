<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { header("Location: ../index.php"); exit; }

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 

if (!isset($_GET['id_ujian'])) { header("Location: ujian.php"); exit; }

$id_ujian = mysqli_real_escape_string($koneksi, $_GET['id_ujian']);

$query_ujian = mysqli_query($koneksi, "
    SELECT ujian.*, kelas.nama_kelas, mapel.nama_mapel 
    FROM ujian 
    LEFT JOIN kelas ON ujian.id_kelas = kelas.id_kelas
    LEFT JOIN mapel ON ujian.id_mapel = mapel.id_mapel
    WHERE ujian.id_ujian='$id_ujian' AND ujian.id_guru='$id_guru'
");

if (mysqli_num_rows($query_ujian) == 0) { header("Location: ujian.php"); exit; }
$data_ujian = mysqli_fetch_assoc($query_ujian);
$id_kelas_target = $data_ujian['id_kelas'];

$cek_essay = mysqli_query($koneksi, "SELECT id_soal FROM soal_ujian WHERE id_ujian='$id_ujian' AND jenis_soal='Essay'");
$ada_essay = (mysqli_num_rows($cek_essay) > 0) ? true : false;

include 'includes/header.php';
?>

<style>
    /* CSS ini hanya akan aktif saat tombol Print ditekan / Save as PDF */
    @media print {
        /* 1. Sembunyikan elemen UI Web (Sidebar, Header Navbar, Tombol) */
        aside, header, footer, .no-print { 
            display: none !important; 
        }
        /* 2. Hilangkan margin/padding bawaan web agar kertas penuh */
        main { 
            margin: 0 !important; 
            padding: 0 !important; 
            background-color: white !important;
        }
        .print-container {
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        /* 3. Tampilkan elemen rahasia (Kop Surat & Tanda Tangan) */
        .print-only { 
            display: block !important; 
        }
        /* 4. Format ulang tabel agar garis hitam tegas layaknya dokumen Excel */
        table { 
            width: 100% !important; 
            border-collapse: collapse !important; 
            margin-top: 15px !important;
        }
        th, td { 
            border: 1px solid #000 !important; 
            color: #000 !important; 
            padding: 8px !important;
        }
        /* Paksa browser mencetak warna background (jika user mengaktifkan background graphics) */
        * { 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
        }
    }

    /* Secara default di layar web, elemen cetak ini disembunyikan */
    .print-only { 
        display: none; 
    }
</style>

<div class="print-container mb-8">
    
    <div class="no-print mb-8">
        <a href="ujian.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-guru-600 transition-colors mb-4">
            <i class="ph ph-arrow-left"></i> Kembali ke Daftar Ujian
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Hasil Ujian & Rekapitulasi Nilai</h1>
    </div>

    <div class="print-only text-center border-b-4 border-double border-slate-900 pb-4 mb-6 pt-8">
        <h2 class="text-2xl font-extrabold uppercase tracking-widest text-slate-900">SMA YAPAN INDONESIA</h2>
        <p class="text-sm font-medium text-slate-700 mt-1">Jl. Raya Puspiptek, Buaran, Kec. Pamulang, Kota Tangerang Selatan, Banten 15310</p>
        <p class="text-xs text-slate-500 mt-1">Email: info@smayapan.sch.id | Website: www.smayapan.sch.id</p>
    </div>

    <div class="print-only text-center mb-6">
        <h3 class="text-lg font-bold uppercase underline">REKAPITULASI HASIL EVALUASI BELAJAR</h3>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:p-8 mb-8 flex flex-col md:flex-row justify-between gap-6 print-container print:mb-2">
        <div>
            <span class="no-print inline-block px-2 py-0.5 bg-indigo-50 text-indigo-600 border border-indigo-200 font-bold text-[10px] uppercase rounded mb-2">
                <?php echo $data_ujian['jenis_evaluasi']; ?>
            </span>
            <h2 class="text-xl font-bold text-slate-800 mb-1 print:text-lg print:uppercase"><?php echo htmlspecialchars($data_ujian['judul_ujian']); ?></h2>
            
            <div class="flex items-center gap-4 text-sm text-slate-500 font-medium mt-3 print:grid print:grid-cols-2 print:gap-x-12 print:gap-y-1 print:mt-4 print:text-slate-800">
                <span class="flex items-center gap-1.5"><i class="ph ph-door text-guru-500 no-print"></i> <b>Kelas:</b> <?php echo $data_ujian['nama_kelas']; ?></span>
                <span class="flex items-center gap-1.5"><i class="ph ph-book-open text-blue-500 no-print"></i> <b>Mata Pelajaran:</b> <?php echo $data_ujian['nama_mapel']; ?></span>
                <span class="print-only"><b>Guru Pengampu:</b> <?php echo $_SESSION['nama']; ?></span>
                <span class="print-only"><b>Tahun Ajaran:</b> <?php echo isset($_SESSION['nama_tahun_aktif']) ? $_SESSION['nama_tahun_aktif'] . ' (' . $_SESSION['semester_aktif'] . ')' : '-'; ?></span>
            </div>
        </div>
        
        <div class="flex items-end no-print">
            <button onclick="window.print()" class="px-5 py-2.5 bg-slate-800 text-white font-semibold rounded-xl shadow-md hover:bg-slate-900 transition-all flex items-center gap-2 text-sm">
                <i class="ph ph-printer text-lg"></i> Cetak Dokumen Resmi
            </button>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden print-container">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center no-print">
            <h3 class="font-bold text-slate-800"><i class="ph ph-users-three text-guru-500 mr-2"></i> Daftar Peserta & Nilai</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-widest print:bg-slate-200 print:text-slate-900">
                        <th class="px-6 py-4 font-bold w-16 text-center">No</th>
                        <th class="px-6 py-4 font-bold">Nama Siswa</th>
                        <th class="px-6 py-4 font-bold text-center">NIS</th>
                        <th class="px-6 py-4 font-bold text-center no-print">Status</th>
                        <th class="px-6 py-4 font-bold text-center">Nilai PG</th>
                        <th class="px-6 py-4 font-bold text-center">Nilai Essay</th>
                        <th class="px-6 py-4 font-bold text-center">Total Nilai</th>
                        <th class="px-6 py-4 font-bold text-center w-32 no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    
                    <?php
                    $no = 1;
                    $q_hasil = mysqli_query($koneksi, "
                        SELECT users.id_user, users.nama_lengkap, users.nis, 
                               nilai_ujian.waktu_selesai, nilai_ujian.nilai_pg, nilai_ujian.nilai_essay, nilai_ujian.status_koreksi
                        FROM users 
                        LEFT JOIN nilai_ujian ON users.id_user = nilai_ujian.id_siswa AND nilai_ujian.id_ujian = '$id_ujian'
                        WHERE users.id_kelas = '$id_kelas_target' AND users.role = 'siswa'
                        ORDER BY users.nama_lengkap ASC
                    ");

                    if(mysqli_num_rows($q_hasil) > 0):
                        while($siswa = mysqli_fetch_assoc($q_hasil)):
                            $nilai_pg = isset($siswa['nilai_pg']) ? $siswa['nilai_pg'] : 0;
                            $nilai_essay = isset($siswa['nilai_essay']) ? $siswa['nilai_essay'] : 0;
                            $total_nilai = $nilai_pg + $nilai_essay;
                            
                            $status_badge = "";
                            $sudah_mengerjakan = false;

                            if (is_null($siswa['waktu_selesai']) && !isset($siswa['status_koreksi'])) {
                                $status_badge = '<span class="px-2.5 py-1 bg-rose-50 text-rose-600 font-bold text-[10px] uppercase rounded">Belum</span>';
                            } elseif (is_null($siswa['waktu_selesai']) && isset($siswa['status_koreksi'])) {
                                $status_badge = '<span class="px-2.5 py-1 bg-amber-50 text-amber-600 font-bold text-[10px] uppercase rounded">Sedang Ujian</span>';
                            } else {
                                $sudah_mengerjakan = true;
                                $status_badge = '<span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 font-bold text-[10px] uppercase rounded">Selesai</span>';
                            }
                    ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-slate-500 font-medium text-center print:text-black"><?php echo $no++; ?></td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-800 text-sm print:font-medium"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></p>
                        </td>
                        <td class="px-6 py-4 text-center print:text-black">
                            <?php echo !empty($siswa['nis']) ? $siswa['nis'] : '-'; ?>
                        </td>
                        <td class="px-6 py-4 text-center no-print">
                            <?php echo $status_badge; ?>
                        </td>
                        <td class="px-6 py-4 text-center font-mono font-bold text-slate-700 print:font-sans print:text-black">
                            <?php echo $sudah_mengerjakan ? floatval($nilai_pg) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 text-center font-mono font-bold text-slate-700 print:font-sans print:text-black">
                            <?php 
                                if($sudah_mengerjakan) {
                                    if($ada_essay && $siswa['status_koreksi'] == 'Menunggu Koreksi Essay') {
                                        echo '<span class="text-amber-500 text-xs no-print"><i class="ph ph-clock mr-1"></i> Menunggu</span>';
                                        echo '<span class="print-only">-</span>';
                                    } else {
                                        echo floatval($nilai_essay);
                                    }
                                } else {
                                    echo '-';
                                }
                            ?>
                        </td>
                        <td class="px-6 py-4 text-center print:font-bold print:text-black">
                            <?php if($sudah_mengerjakan): ?>
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 border border-slate-200 text-slate-800 font-black text-sm print:border-none print:bg-transparent">
                                    <?php echo floatval($total_nilai); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-slate-300 print:text-black">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center no-print">
                            <?php if($sudah_mengerjakan && $ada_essay): ?>
                                <a href="#" class="px-3 py-1.5 bg-amber-100 text-amber-700 hover:bg-amber-200 font-bold text-xs rounded-lg transition-colors">Koreksi</a>
                            <?php elseif($sudah_mengerjakan && !$ada_essay): ?>
                                <span class="text-xs text-slate-400 italic">Otomatis</span>
                            <?php else: ?>
                                <span class="text-xs text-slate-300">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                            Tidak ada siswa yang terdaftar di kelas ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="print-only mt-12 w-full flex justify-end">
        <div class="text-center w-64 mr-8">
            <p class="text-sm font-medium mb-1">Tangerang, <?php echo date('d F Y'); ?></p>
            <p class="text-sm font-medium">Guru Mata Pelajaran,</p>
            <br><br><br><br>
            <p class="text-sm font-bold underline"><?php echo $_SESSION['nama']; ?></p>
            <p class="text-sm font-medium mt-1">NIP. <?php echo isset($_SESSION['nip']) ? $_SESSION['nip'] : '...............................'; ?></p>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>