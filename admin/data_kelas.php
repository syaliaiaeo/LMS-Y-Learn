<?php
session_start();
// Proteksi halaman admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
include '../includes/header.php';
?>

<?php if (isset($_SESSION['sukses'])): ?>
    <div id="alert-sukses" class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="ph ph-check-circle text-xl"></i>
            <span class="text-sm font-bold"><?php echo $_SESSION['sukses']; ?></span>
        </div>
        <button onclick="document.getElementById('alert-sukses').style.display='none'" class="text-emerald-500 hover:text-emerald-700"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php unset($_SESSION['sukses']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div id="alert-error" class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="ph ph-warning-circle text-xl"></i>
            <span class="text-sm font-bold"><?php echo $_SESSION['error']; ?></span>
        </div>
        <button onclick="document.getElementById('alert-error').style.display='none'" class="text-rose-500 hover:text-rose-700"><i class="ph ph-x text-lg font-bold"></i></button>
    </div>
<?php unset($_SESSION['error']); endif; ?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Ruang Kelas</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola data kelas yang aktif pada tahun ajaran ini.</p>
    </div>
    <a href="tambah_kelas.php" class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 shadow-md w-fit">
        <i class="ph ph-plus-circle text-lg"></i> Tambah Kelas
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden max-w-5xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] font-extrabold uppercase tracking-widest">
                    <th class="px-6 py-4 w-20 text-center">No</th>
                    <th class="px-6 py-4">Nama Ruang Kelas</th>
                    <th class="px-6 py-4 text-center">Jumlah Siswa</th>
                    <th class="px-6 py-4 text-center w-48">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                
                <?php
                $no = 1;
                // Mengambil data kelas DAN MENGHITUNG jumlah siswa di dalamnya
                $query_kelas = "
                    SELECT k.*, 
                    (SELECT COUNT(id_user) FROM users WHERE role='siswa' AND id_kelas = k.id_kelas) as jml_siswa 
                    FROM kelas k 
                    ORDER BY k.nama_kelas ASC
                ";
                $query = mysqli_query($koneksi, $query_kelas);
                
                if(mysqli_num_rows($query) > 0):
                    while($data = mysqli_fetch_assoc($query)):
                ?>
                <tr class="hover:bg-slate-50/80 transition-colors group">
                    <td class="px-6 py-4 text-slate-500 font-bold text-center"><?php echo $no++; ?></td>
                    
                    <td class="px-6 py-4">
                        <a href="detail_kelas.php?id=<?php echo $data['id_kelas']; ?>" class="flex items-center gap-3 group/link w-fit">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center font-bold transition-colors group-hover/link:bg-indigo-600 group-hover/link:text-white shrink-0">
                                <i class="ph ph-door text-xl"></i>
                            </div>
                            <div>
                                <span class="font-extrabold text-slate-800 text-base group-hover/link:text-indigo-600 transition-colors"><?php echo htmlspecialchars($data['nama_kelas']); ?></span>
                                <p class="text-[10px] font-medium text-slate-400 mt-0.5">Klik untuk lihat daftar hadir</p>
                            </div>
                        </a>
                    </td>
                    
                    <td class="px-6 py-4 text-center">
                        <?php if($data['jml_siswa'] > 0): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold rounded-full">
                                <i class="ph ph-users text-sm"></i> <?php echo $data['jml_siswa']; ?> Siswa
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-500 border border-slate-200 text-xs font-bold rounded-full">
                                Kosong
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            
                            <a href="detail_kelas.php?id=<?php echo $data['id_kelas']; ?>" class="px-3 py-1.5 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-colors font-bold text-xs flex items-center gap-1 tooltip" title="Lihat Daftar Hadir">
                                <i class="ph ph-list-magnifying-glass text-sm"></i> Detail
                            </a>

                            <a href="edit_kelas.php?id=<?php echo $data['id_kelas']; ?>" class="w-8 h-8 flex items-center justify-center text-amber-500 bg-amber-50 hover:bg-amber-500 hover:text-white rounded-lg transition-colors tooltip" title="Edit Kelas">
                                <i class="ph ph-pencil-simple text-base"></i>
                            </a>
                            
                            <a href="hapus.php?jenis=kelas&id=<?php echo $data['id_kelas']; ?>" onclick="return confirm('Menghapus kelas akan berdampak pada data siswa di kelas tersebut. Yakin?');" class="w-8 h-8 flex items-center justify-center text-rose-500 bg-rose-50 hover:bg-rose-500 hover:text-white rounded-lg transition-colors tooltip" title="Hapus Kelas">
                                <i class="ph ph-trash text-base"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                <i class="ph ph-door text-3xl text-slate-400"></i>
                            </div>
                            <h3 class="text-slate-800 font-bold text-lg mb-1">Belum Ada Kelas</h3>
                            <p class="text-sm">Anda belum menambahkan data ruang kelas ke dalam sistem.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>