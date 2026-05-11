<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { header("Location: ../index.php"); exit; }

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user']; 
date_default_timezone_set('Asia/Jakarta');

// 1. TANGKAP ID TOPIK
if (!isset($_GET['id'])) { header("Location: forum.php"); exit; }
$id_topik = mysqli_real_escape_string($koneksi, $_GET['id']);

// 2. AMBIL DATA TOPIK INDUK
$query_topik = mysqli_query($koneksi, "
    SELECT forum_topik.*, users.nama_lengkap as nama_guru, kelas.nama_kelas, mapel.nama_mapel 
    FROM forum_topik 
    JOIN users ON forum_topik.id_guru = users.id_user 
    JOIN kelas ON forum_topik.id_kelas = kelas.id_kelas 
    JOIN mapel ON forum_topik.id_mapel = mapel.id_mapel 
    WHERE forum_topik.id_topik = '$id_topik'
");
if (mysqli_num_rows($query_topik) == 0) { header("Location: forum.php"); exit; }
$data_topik = mysqli_fetch_assoc($query_topik);

// 3. PROSES PENGIRIMAN PESAN/BALASAN BARU
if (isset($_POST['kirim'])) {
    // KITA TIDAK LAGI MENGHAPUS HTML AGAR GAMBAR & FORMAT TEKS BISA MASUK
    $isi_balasan = mysqli_real_escape_string($koneksi, trim($_POST['isi_balasan']));
    
    $parent_input = $_POST['parent_id'];
    $parent_id = (!empty($parent_input) && $parent_input !== 'main') ? "'" . mysqli_real_escape_string($koneksi, $parent_input) . "'" : "NULL";
    
    if (!empty($isi_balasan)) {
        $q_insert = "INSERT INTO forum_balasan (id_topik, parent_id, id_user, isi_balasan) VALUES ('$id_topik', $parent_id, '$id_guru', '$isi_balasan')";
        if (mysqli_query($koneksi, $q_insert)) {
            header("Location: detail_forum.php?id=$id_topik");
            exit;
        }
    }
}

include 'includes/header.php';

// ==========================================
// FUNGSI REKURSIF UNTUK MENGGAMBAR TREE CHAT
// ==========================================
function tampilkanBalasan($parent_id, $level, $balasan_tree, $id_guru) {
    if (!isset($balasan_tree[$parent_id])) return;

    foreach ($balasan_tree[$parent_id] as $chat) {
        $is_me = ($chat['id_user'] == $id_guru);
        $waktu_chat = date('l, d F Y \p\u\k\u\l H:i A', strtotime($chat['tgl_balas'])); 
        
        $inisial = '';
        $pecah_nama = explode(' ', $chat['nama_lengkap']);
        if (isset($pecah_nama[0])) $inisial .= strtoupper(substr($pecah_nama[0], 0, 1));
        if (isset($pecah_nama[1])) $inisial .= strtoupper(substr($pecah_nama[1], 0, 1));

        $margin_left = ($level > 0) ? 'ml-6 md:ml-12 border-l-2 border-slate-200 pl-4' : '';
        if($level > 3) $margin_left = 'ml-2 md:ml-4 border-l-2 border-slate-200 pl-4'; 

        $badge_role = $chat['role'] == 'guru' ? '<span class="px-2 py-0.5 bg-blue-500 text-white text-[10px] font-bold rounded-full">Guru</span>' : '<span class="px-2 py-0.5 bg-sky-400 text-white text-[10px] font-bold rounded-full">Mahasiswa/Siswa</span>';

        // NOTE: $chat['isi_balasan'] dicetak langsung tanpa htmlspecialchars agar format TinyMCE berjalan
        echo "
        <div class='flex flex-col mb-4 w-full {$margin_left}'>
            <div class='flex gap-4 w-full border border-slate-200 bg-white p-5 rounded-xl shadow-sm'>
                <div class='w-12 h-12 rounded-full shrink-0 flex items-center justify-center font-bold text-lg bg-slate-300 text-white shadow-inner mt-1'>
                    {$inisial}
                </div>
                
                <div class='flex-1'>
                    <div class='flex items-center gap-2 mb-0.5'>
                        <span class='text-sm font-bold text-slate-800 uppercase'>" . htmlspecialchars($chat['nama_lengkap']) . "</span>
                        {$badge_role}
                    </div>
                    <p class='text-[11px] text-slate-400 font-medium mb-3'>{$waktu_chat}</p>
                    
                    <div class='forum-content text-sm text-slate-700 leading-relaxed mb-4 overflow-hidden'>
                        " . $chat['isi_balasan'] . "
                    </div>
                    
                    <div class='flex justify-end border-t border-slate-50 pt-3 mt-2'>
                        <button type='button' onclick='toggleReplyForm({$chat['id_balasan']}, \"" . addslashes($chat['nama_lengkap']) . "\")' class='px-4 py-1.5 bg-[#4CAF50] hover:bg-[#45a049] text-white font-bold text-xs rounded transition-colors flex items-center gap-1.5 shadow-sm'>
                            <i class='ph ph-arrow-u-down-left text-sm'></i> REPLY
                        </button>
                    </div>

                    <div id='reply-form-{$chat['id_balasan']}' class='hidden mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl relative'>
                        <p class='text-xs font-bold text-slate-500 mb-2'>Membalas <span class='text-emerald-600'>{$chat['nama_lengkap']}</span></p>
                        
                        <form action='' method='POST' class='flex flex-col gap-2'>
                            <input type='hidden' name='parent_id' value='{$chat['id_balasan']}'>
                            
                            <textarea id='textarea-{$chat['id_balasan']}' name='isi_balasan' rows='3' placeholder='Tulis balasan Anda di sini...' class='w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-emerald-500 transition-all resize-y'></textarea>
                            
                            <div class='flex justify-between items-center w-full mt-2'>
                                <div class='flex gap-2'>
                                    <button type='submit' name='kirim' class='px-5 py-2 bg-[#1565C0] text-white text-xs font-bold rounded shadow-md hover:bg-[#0D47A1] transition-colors flex items-center gap-1'><i class='ph ph-paper-plane-right'></i> Post Balasan</button>
                                    <button type='button' onclick='closeReplyForm({$chat['id_balasan']})' class='px-4 py-2 border border-slate-300 text-slate-600 text-xs font-bold rounded hover:bg-slate-100 transition-colors'>CANCEL</button>
                                </div>
                                <button type='button' onclick='enableAdvanced(\"textarea-{$chat['id_balasan']}\", this)' class='px-4 py-2 bg-[#E0E0E0] text-slate-700 hover:bg-[#BDBDBD] font-bold text-xs rounded transition-colors flex items-center gap-1.5 shadow-sm'>
                                    <i class='ph ph-pencil'></i> ADVANCED
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        tampilkanBalasan($chat['id_balasan'], $level + 1, $balasan_tree, $id_guru);
    }
}
?>

<style>
    .forum-content ul { list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
    .forum-content ol { list-style-type: decimal; margin-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
    .forum-content a { color: #2563eb; text-decoration: underline; }
    .forum-content h1, .forum-content h2, .forum-content h3 { font-weight: bold; margin-top: 1rem; margin-bottom: 0.5rem; }
    .forum-content img { max-width: 100%; height: auto; border-radius: 8px; margin-top: 0.5rem; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin">

</script>
<div class="mb-6 flex justify-between items-center">
    <a href="forum.php" class="inline-flex items-center gap-2 text-sm font-semibold text-rose-500 hover:text-rose-600 border border-rose-500 hover:bg-rose-50 px-4 py-1.5 rounded-full transition-colors">
        <i class="ph ph-arrow-left"></i> BACK
    </a>
    <button onclick="window.location.reload()" class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-[#1565C0] hover:bg-[#0D47A1] px-4 py-1.5 rounded transition-colors shadow-sm">
        <i class="ph ph-arrows-clockwise"></i> REFRESH
    </button>
</div>

<div class="bg-transparent flex flex-col mb-8">
    
    <div class="bg-white border border-slate-200 px-8 py-6 rounded-xl shadow-sm mb-6 flex gap-4">
        <div class="w-14 h-14 rounded-full bg-[#0F9D58] text-white flex items-center justify-center font-bold text-2xl shrink-0 shadow-sm mt-1">
            <i class="ph ph-chalkboard-teacher"></i>
        </div>

        <div class="flex-1 w-full">
            <div class="flex items-center gap-2 mb-0.5">
                <span class="text-lg font-bold text-slate-800 uppercase"><?php echo htmlspecialchars($data_topik['nama_guru']); ?></span>
                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold uppercase rounded">Pembuat Topik</span>
            </div>
            <p class="text-[11px] text-slate-400 font-medium mb-4">Diterbitkan pada <?php echo date('d F Y, H:i', strtotime($data_topik['tgl_buat'])); ?></p>
            
            <h2 class="text-xl font-bold text-slate-800 mb-3"><?php echo htmlspecialchars($data_topik['judul_topik']); ?></h2>
            <div class="forum-content text-slate-700 text-sm leading-relaxed mb-4">
                <?php echo $data_topik['deskripsi']; ?>
            </div>

            <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">
                <button type="button" onclick="toggleReplyForm('main', 'Topik Utama')" class="px-4 py-1.5 bg-[#4CAF50] hover:bg-[#45a049] text-white font-bold text-xs rounded transition-colors flex items-center gap-1.5 shadow-sm">
                    <i class="ph ph-arrow-u-down-left text-sm"></i> REPLY
                </button>
            </div>

            <div id="reply-form-main" class="hidden mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl relative">
                <p class="text-xs font-bold text-slate-500 mb-2">Menambahkan balasan baru untuk diskusi ini</p>
                <form action="" method="POST" class="flex flex-col gap-2">
                    <input type="hidden" name="parent_id" value="main">
                    
                    <textarea id="textarea-main" name="isi_balasan" rows="3" placeholder="Tuliskan tanggapan Anda..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-[#4CAF50] transition-all resize-y"></textarea>
                    
                    <div class="flex justify-between items-center w-full mt-2">
                        <div class="flex gap-2">
                            <button type="submit" name="kirim" class="px-5 py-2 bg-[#1565C0] text-white text-xs font-bold rounded shadow-md hover:bg-[#0D47A1] transition-colors flex items-center gap-1"><i class="ph ph-paper-plane-right"></i> Post Balasan</button>
                            <button type="button" onclick="closeReplyForm('main')" class="px-4 py-2 border border-slate-300 text-slate-600 text-xs font-bold rounded hover:bg-slate-100 transition-colors">CANCEL</button>
                        </div>
                        
                        <button type="button" onclick="enableAdvanced('textarea-main', this)" class="px-4 py-2 bg-[#E0E0E0] text-slate-700 hover:bg-[#BDBDBD] font-bold text-xs rounded transition-colors flex items-center gap-1.5 shadow-sm">
                            <i class="ph ph-pencil"></i> ADVANCED
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="flex-1 rounded-xl">
        <?php
        $q_balasan = mysqli_query($koneksi, "
            SELECT fb.*, u.nama_lengkap, u.role 
            FROM forum_balasan fb 
            JOIN users u ON fb.id_user = u.id_user 
            WHERE fb.id_topik = '$id_topik' 
            ORDER BY fb.tgl_balas ASC
        ");

        $balasan_tree = [];
        while($row = mysqli_fetch_assoc($q_balasan)) {
            $parent = $row['parent_id'] ? $row['parent_id'] : 0;
            $balasan_tree[$parent][] = $row;
        }

        if(isset($balasan_tree[0])) {
            tampilkanBalasan(0, 0, $balasan_tree, $id_guru);
        } else {
            echo "<div class='text-center py-10 text-slate-400 font-medium bg-white rounded-xl border border-slate-200 border-dashed'>Belum ada partisipasi dalam diskusi ini. Tulis balasan pertama!</div>";
        }
        ?>
    </div>
</div>

<script>
    // Menyembunyikan form yang sedang terbuka
    function toggleReplyForm(id, nama) {
        document.querySelectorAll('[id^="reply-form-"]').forEach(form => {
            form.classList.add('hidden');
        });
        const formTarget = document.getElementById('reply-form-' + id);
        formTarget.classList.remove('hidden');
    }

    function closeReplyForm(id) {
        document.getElementById('reply-form-' + id).classList.add('hidden');
        
        // Opsional: Hancurkan instance TinyMCE jika dicancel (agar tidak menumpuk)
        if (tinymce.get('textarea-' + id)) {
            tinymce.get('textarea-' + id).remove();
            // Tampilkan kembali tombol advanced
            document.querySelector('#reply-form-' + id + ' .ph-pencil').parentNode.style.display = 'flex';
        }
    }

    // FUNGSI AJAIB: Menyulap Textarea biasa menjadi Rich Text Editor (Advanced)
    function enableAdvanced(textareaId, btnElement) {
        // Inisialisasi TinyMCE pada ID textarea yang spesifik
        tinymce.init({
            selector: '#' + textareaId,
            menubar: false,
            plugins: 'advlist autolink lists link image media table',
            toolbar_mode: 'floating',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table',
            height: 250,
            promotion: false,  // Menyembunyikan tombol upgrade premium
            branding: false,   // Menyembunyikan tulisan "Powered by TinyMCE"
            setup: function (editor) {
                // Saat editor berubah nilainya, otomatis salin ke textarea asli agar PHP bisa membacanya saat form di-submit
                editor.on('change', function () {
                    editor.save();
                });
            }
        });
        
        // Sembunyikan tombol "ADVANCED" setelah editor aktif agar tampilannya bersih
        btnElement.style.display = 'none';
    }
</script>

<?php include 'includes/footer.php'; ?>