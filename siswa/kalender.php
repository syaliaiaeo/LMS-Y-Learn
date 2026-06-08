<?php
session_start();
// Proteksi halaman siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') { 
    header("Location: ../index.php"); 
    exit; 
}

require_once '../config/koneksi.php';
$id_user = $_SESSION['id_user'];

// Ambil id_kelas siswa saat ini
$q_siswa = mysqli_query($koneksi, "SELECT id_kelas FROM users WHERE id_user = '$id_user'");
$data_siswa = mysqli_fetch_assoc($q_siswa);
$id_kelas = $data_siswa['id_kelas'];

// ==========================================
// 1. SIAPKAN DATA UNTUK FULLCALENDAR (JSON)
// ==========================================
$events = array();

// A. Jadwal Pelajaran Rutin Mingguan (Berdasarkan PDF Jadwal Resmi)
$hari_numeric_map = ['Minggu' => 0, 'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6];

if (!empty($id_kelas)) {
    $q_jadwal_rutin = mysqli_query($koneksi, "
        SELECT j.hari, j.jam_mulai, j.jam_selesai, m.nama_mapel 
        FROM jadwal_pelajaran j
        JOIN mapel m ON j.id_mapel = m.id_mapel
        WHERE j.id_kelas = '$id_kelas'
    ");
    while ($row = mysqli_fetch_assoc($q_jadwal_rutin)) {
        $day_num = $hari_numeric_map[$row['hari']];
        $events[] = array(
            'title' => $row['nama_mapel'],
            'startTime' => $row['jam_mulai'],
            'endTime' => $row['jam_selesai'],
            'daysOfWeek' => [$day_num], // Otomatis berulang setiap minggu di hari tersebut
            'backgroundColor' => '#e0e7ff', // Warna Biru/Indigo soft untuk KBM biasa
            'borderColor' => '#c7d2fe',
            'textColor' => '#4338ca'
        );
    }
}

// B. Data Tenggat Pengumpulan Tugas (Tenggat Waktu / tgl_selesai)
$q_tugas = mysqli_query($koneksi, "SELECT judul_tugas, tgl_selesai FROM tugas WHERE id_kelas = '$id_kelas'");
while($t = mysqli_fetch_assoc($q_tugas)){
    $events[] = array(
        'title' => '🔴 Deadline: ' . $t['judul_tugas'],
        'start' => $t['tgl_selesai'],
        'backgroundColor' => '#ffe4e6', // Warna Rose/Merah untuk tugas
        'borderColor' => '#fecdd3',
        'textColor' => '#e11d48'
    );
}

// C. Data Pelaksanaan Ujian / Kuis
$q_ujian = mysqli_query($koneksi, "SELECT judul_ujian, tgl_mulai, tgl_selesai FROM ujian WHERE id_kelas = '$id_kelas'");
while($u = mysqli_fetch_assoc($q_ujian)){
    $events[] = array(
        'title' => '🟣 Ujian: ' . $u['judul_ujian'],
        'start' => $u['tgl_mulai'],
        'end' => $u['tgl_selesai'],
        'backgroundColor' => '#f3e8ff', // Warna Violet/Ungu untuk ujian
        'borderColor' => '#e9d5ff',
        'textColor' => '#9333ea'
    );
}

$events_json = json_encode($events);

// ==========================================
// 2. AMBIL JADWAL MATA PELAJARAN HARI INI
// ==========================================
$hari_inggris = date('l');
$hari_indo_map = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];
$hari_ini = $hari_indo_map[$hari_inggris];
$tanggal_sekarang = date('d M Y');

$q_jadwal = mysqli_query($koneksi, "
    SELECT j.jam_mulai, j.jam_selesai, m.nama_mapel 
    FROM jadwal_pelajaran j
    JOIN mapel m ON j.id_mapel = m.id_mapel
    WHERE j.id_kelas = '$id_kelas' AND j.hari = '$hari_ini'
    ORDER BY j.jam_mulai ASC
");

include 'includes/header.php'; 
?>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Kalender Akademik & Jadwal</h1>
    <p class="text-slate-500 text-sm mt-1">Pantau jadwal mata pelajaran Anda hari ini serta tenggat waktu tugas dan jadwal ujian.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col h-full">
            <div class="p-6 border-b border-slate-100 bg-indigo-50/50">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="ph ph-clock"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Jadwal Hari Ini</h2>
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider"><?php echo $hari_ini . ', ' . $tanggal_sekarang; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 flex-1 bg-white">
                <div class="relative border-l-2 border-slate-100 ml-3 space-y-8">
                    
                    <?php 
                    if(!empty($id_kelas) && mysqli_num_rows($q_jadwal) > 0) {
                        while($j = mysqli_fetch_assoc($q_jadwal)) {
                            $jam_m = date('H:i', strtotime($j['jam_mulai']));
                            $jam_s = date('H:i', strtotime($j['jam_selesai']));
                    ?>
                    <div class="relative pl-6">
                        <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-4 border-indigo-500"></span>
                        <h3 class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($j['nama_mapel']); ?></h3>
                        <p class="text-xs font-mono text-slate-500 mt-0.5"><i class="ph ph-hourglass-high"></i> <?php echo $jam_m . ' - ' . $jam_s; ?> WIB</p>
                    </div>
                    <?php 
                        }
                    } else { 
                    ?>
                    <div class="text-center py-8">
                        <i class="ph ph-coffee text-4xl text-slate-300 mb-2 block"></i>
                        <p class="text-sm font-medium text-slate-500">Tidak ada jadwal pelajaran hari ini.</p>
                        <p class="text-xs text-slate-400 mt-1">Waktunya istirahat atau fokus menyelesaikan tugas mandiri!</p>
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
                    <span class="w-3 h-3 rounded-full bg-indigo-400"></span> Jadwal Pelajaran Rutin
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-rose-500"></span> Tenggat Tugas
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-violet-500"></span> Jadwal Ujian/Kuis
                </div>
            </div>
            
            <div id="calendar" class="h-[600px] w-full text-sm"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var kalenderEvents = <?php echo $events_json; ?>;

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
            events: kalenderEvents,
            eventTimeFormat: {
                hour: '2-digit', minute: '2-digit', hour12: false
            },
            height: '100%',
            eventDisplay: 'block',
            
            // PENGATURAN BATAS WAKTU FINAL (Penghilang Jam Malam)
            slotMinTime: '06:00:00', // Jam paling awal (06:00 Pagi)
            slotMaxTime: '17:00:00', // Jam paling akhir (17:00 Sore)
            allDaySlot: false,       // Menghilangkan baris "all-day" kosong di atas
            scrollTime: '06:00:00',  // Memaksa posisi scroll ke jam 6 pagi
            expandRows: true         // Merenggangkan tinggi baris agar penuh
        });

        calendar.render();
    });
</script>

<style>
    .fc-theme-standard td, .fc-theme-standard th { border-color: #f1f5f9; }
    .fc-col-header-cell { background-color: #f8fafc; padding: 8px 0; color: #64748b; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;}
    .fc .fc-button-primary { background-color: #4f46e5; border-color: #4f46e5; border-radius: 0.5rem; text-transform: capitalize; font-weight: 600;}
    .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #4338ca; border-color: #4338ca;}
    .fc .fc-button-primary:hover { background-color: #4338ca; border-color: #4338ca;}
    .fc .fc-today-button { background-color: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .fc .fc-today-button:hover { background-color: #e2e8f0; color: #334155; }
    .fc .fc-daygrid-day.fc-day-today { background-color: #eef2ff; }
    .fc-h-event { border: none; padding: 2px 4px; border-radius: 4px; font-weight: 600; font-size: 11px;}
    .fc-event-time { font-family: monospace; }
</style>

<?php include 'includes/footer.php'; ?>