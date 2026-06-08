<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_guru = $_SESSION['id_user'];

// Ambil data mapel yang diampu oleh guru ini
$q_guru_info = mysqli_query($koneksi, "SELECT id_mapel FROM users WHERE id_user = '$id_guru'");
$guru_info = mysqli_fetch_assoc($q_guru_info);
$id_mapel_guru = $guru_info['id_mapel'];

// ==========================================
// 1. QUERY JADWAL MENGAJAR HARI INI
// ==========================================
$hari_inggris = date('l');
$hari_indo_map = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];
$hari_ini = $hari_indo_map[$hari_inggris];
$tanggal_sekarang = date('d M Y');

$q_jadwal_hari_ini = mysqli_query($koneksi, "
    SELECT j.jam_mulai, j.jam_selesai, k.nama_kelas, m.nama_mapel 
    FROM jadwal_pelajaran j
    JOIN kelas k ON j.id_kelas = k.id_kelas
    JOIN mapel m ON j.id_mapel = m.id_mapel
    WHERE j.id_mapel = '$id_mapel_guru' AND j.hari = '$hari_ini'
    ORDER BY j.jam_mulai ASC
");

// ==========================================
// 2. FORMULASI DATA UNTUK FULLCALENDAR (JSON)
// ==========================================
$events = array();

// A. Pola Jadwal Mengajar Rutin Mingguan (Menggunakan fitur daysOfWeek FullCalendar)
$hari_numeric_map = ['Minggu' => 0, 'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6];

$q_jadwal_rutin = mysqli_query($koneksi, "
    SELECT j.hari, j.jam_mulai, j.jam_selesai, k.nama_kelas, m.nama_mapel 
    FROM jadwal_pelajaran j
    JOIN kelas k ON j.id_kelas = k.id_kelas
    JOIN mapel m ON j.id_mapel = m.id_mapel
    WHERE j.id_mapel = '$id_mapel_guru'
");

while ($row = mysqli_fetch_assoc($q_jadwal_rutin)) {
    $day_num = $hari_numeric_map[$row['hari']];
    $events[] = array(
        'title' => $row['nama_mapel'] . ' (' . $row['nama_kelas'] . ')',
        'startTime' => $row['jam_mulai'],
        'endTime' => $row['jam_selesai'],
        'daysOfWeek' => [$day_num], // Berulang setiap minggu pada hari tersebut
        'backgroundColor' => '#10b981', // Hijau Emerald untuk jadwal mengajar rutin
        'borderColor' => '#10b981',
        'textColor' => '#ffffff'
    );
}

// B. Data Tenggat Pengumpulan Tugas yang Dibuat oleh Guru Ini
$q_tugas = mysqli_query($koneksi, "
    SELECT t.judul_tugas, t.tgl_selesai, k.nama_kelas 
    FROM tugas t
    JOIN kelas k ON t.id_kelas = k.id_kelas
    WHERE t.id_guru = '$id_guru'
");
while ($t = mysqli_fetch_assoc($q_tugas)) {
    $events[] = array(
        'title' => 'Deadline Tugas: ' . $t['judul_tugas'] . ' [' . $t['nama_kelas'] . ']',
        'start' => $t['tgl_selesai'],
        'backgroundColor' => '#f43f5e', // Merah untuk batas waktu tugas
        'borderColor' => '#f43f5e',
        'textColor' => '#ffffff'
    );
}

// C. Data Pelaksanaan Ujian / Kuis yang Dibuat oleh Guru Ini
$q_ujian = mysqli_query($koneksi, "
    SELECT u.judul_ujian, u.tgl_mulai, u.tgl_selesai, k.nama_kelas 
    FROM ujian u
    JOIN kelas k ON u.id_kelas = k.id_kelas
    WHERE u.id_guru = '$id_guru'
");
while ($u = mysqli_fetch_assoc($q_ujian)) {
    $events[] = array(
        'title' => 'Pelaksanaan Ujian: ' . $u['judul_ujian'] . ' [' . $u['nama_kelas'] . ']',
        'start' => $u['tgl_mulai'],
        'end' => $u['tgl_selesai'],
        'backgroundColor' => '#8b5cf6', // Ungu untuk Ujian
        'borderColor' => '#8b5cf6',
        'textColor' => '#ffffff'
    );
}

$events_json = json_encode($events);

include 'includes/header.php'; 
?>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Kalender Mengajar & Agenda</h1>
    <p class="text-slate-500 text-sm mt-1">Pantau agenda mengajar harian, batas pengumpulan tugas siswa, serta jadwal evaluasi kuis.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-1">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col h-full">
            <div class="p-6 border-b border-slate-100 bg-guru-50">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-guru-100 text-guru-700 flex items-center justify-center text-xl">
                        <i class="ph ph-chalkboard text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Jadwal Hari Ini</h2>
                        <p class="text-xs font-bold text-guru-600 uppercase tracking-wider"><?php echo $hari_ini . ', ' . $tanggal_sekarang; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 flex-1 bg-white">
                <div class="relative border-l-2 border-slate-100 ml-3 space-y-8">
                    <?php 
                    if (mysqli_num_rows($q_jadwal_hari_ini) > 0) {
                        while ($j = mysqli_fetch_assoc($q_jadwal_hari_ini)) {
                            $jam_m = date('H:i', strtotime($j['jam_mulai']));
                            $jam_s = date('H:i', strtotime($j['jam_selesai']));
                    ?>
                    <div class="relative pl-6">
                        <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-4 border-guru-600"></span>
                        <h3 class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($j['nama_mapel']); ?></h3>
                        <p class="text-xs font-semibold text-indigo-600 mt-0.5">Ruang Kelas: <?php echo htmlspecialchars($j['nama_kelas']); ?></p>
                        <p class="text-[11px] font-mono text-slate-400 mt-1"><i class="ph ph-clock"></i> <?php echo $jam_m . ' - ' . $jam_s; ?> WIB</p>
                    </div>
                    <?php 
                        }
                    } else { 
                    ?>
                    <div class="text-center py-12">
                        <i class="ph ph-calendar-blank text-4xl text-slate-300 mb-2 block"></i>
                        <p class="text-sm font-medium text-slate-500">Tidak ada jadwal daring hari ini.</p>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6">
            <div class="flex flex-wrap items-center gap-4 mb-4 pb-4 border-b border-slate-100 text-xs font-semibold text-slate-600">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Jadwal Rutin Mengajar
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-rose-500"></span> Batas Akhir Tugas
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-violet-500"></span> Agenda Kuis & Ujian
                </div>
            </div>
            
            <div id="calendar" class="h-[600px] w-full text-sm"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var jsonEvents = <?php echo $events_json; ?>;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            locale: 'id',
            buttonText: {
                today: 'Hari Ini', month: 'Bulan', week: 'Minggu', list: 'Agenda'
            },
            events: jsonEvents,
            eventTimeFormat: {
                hour: '2-digit', minute: '2-digit', hour12: false
            },
            height: '100%',
            eventDisplay: 'block'
        });

        calendar.render();
    });
</script>

<style>
    .fc-theme-standard td, .fc-theme-standard th { border-color: #f1f5f9; }
    .fc-col-header-cell { background-color: #f8fafc; padding: 8px 0; color: #64748b; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;}
    .fc .fc-button-primary { background-color: #16a34a; border-color: #16a34a; border-radius: 0.5rem; font-weight: 600;}
    .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #15803d; border-color: #15803d;}
    .fc .fc-button-primary:hover { background-color: #15803d; border-color: #15803d;}
    .fc .fc-today-button { background-color: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .fc .fc-daygrid-day.fc-day-today { background-color: #f0fdf4; }
    .fc-h-event { border: none; padding: 2px 4px; border-radius: 4px; font-weight: 600; font-size: 11px;}
</style>

<?php include 'includes/footer.php'; ?>