<?php
// ================== SECURITY HEADERS ==================
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// ================== SESSION SETUP ==================
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', !empty($_SERVER['HTTPS']));
ini_set('session.use_only_cookies', 1);

session_start();

/**
 * ================== LOAD AUTH & DB ==================
 */
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/koneksi.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    exit('Koneksi database tidak tersedia.');
}

/**
 * ================== SAFE COUNT ==================
 */
function getCount(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * ================== DATA DASHBOARD (DINAMIS) ==================
 */

$hadir = getCount($pdo,
    "SELECT COUNT(DISTINCT id_pegawai)
     FROM absensi
     WHERE tanggal = CURDATE()
     AND status IN ('hadir','terlambat')"
);

$izin = getCount($pdo,
    "SELECT COUNT(DISTINCT id_pegawai)
     FROM absensi
     WHERE tanggal = CURDATE()
     AND status = 'izin'"
);

$total_pegawai = getCount($pdo,
    "SELECT COUNT(*) FROM pegawai WHERE status_pegawai = 'aktif'"
);

$tidak_hadir = max(0, $total_pegawai - $hadir - $izin);


// Tidak hadir = pegawai aktif - (hadir + izin)
$tidak_hadir = max(0, $total_pegawai - ($hadir + $izin));
?>
<!DOCTYPE html>
<html lang="id">
<?php include 'header.php'; ?>
<head>
<meta charset="UTF-8">
<title>Dashboard | PT. SFR</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
/* ================== GLOBAL ================== */
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    color: #2d3436;
}

/* ================== LAYOUT UTAMA ================== */
.container {
    display: flex;
    min-height: 100vh;
}

/* ================== CONTENT AREA ================== */
.content {
    flex: 1;
    padding: 32px 36px;
    margin-left: 260px; /* SESUAIKAN DENGAN LEBAR SIDEBAR */
    margin-top: 70px;   /* SESUAIKAN DENGAN TINGGI HEADER */
    box-sizing: border-box;
}

/* SECTION WRAPPER (AGAR TIDAK BERDEMPET) */
.section {
    margin-bottom: 40px;
}

/* ================== STATS ================== */
.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 28px;
    margin-bottom: 36px;
}

.stats .card {
    text-align: center;
    padding: 24px 20px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 6px 16px rgba(0,0,0,.08);
    transition: transform .25s ease, box-shadow .25s ease;
}

.stats .card span {
    font-size: 13px;
    color: #636e72;
}

.stats .card b {
    display: block;
    margin-top: 8px;
    font-size: 28px;
    font-weight: 600;
    color: #0984e3;
}

.stats .card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 28px rgba(0,0,0,.14);
}

/* ================== CARD UMUM ================== */
.card {
    background: #fff;
    padding: 24px 26px;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,.08);
    margin-bottom: 30px;
}

.card h3 {
    margin-top: 0;
    margin-bottom: 18px;
    font-size: 16px;
    font-weight: 600;
    color: #2d3436;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

/* ================== TABLE ================== */
.table-wrapper {
    overflow-x: auto;
    margin-top: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}

th, td {
    padding: 14px 16px;
    border-bottom: 1px solid #eaeaea;
    text-align: center;
    font-size: 14px;
    white-space: nowrap;
}

th {
    background: #0984e3;
    color: #fff;
    font-weight: 600;
}

tr:hover td {
    background: #f8f9fb;
}

/* ================== RESPONSIVE ================== */
@media (max-width: 1200px) {
    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .content {
        margin-left: 0;  /* Sidebar biasanya collapse */
        margin-top: 70px;
        padding: 24px;
    }
}

@media (max-width: 576px) {
    .stats {
        grid-template-columns: 1fr;
    }

    th, td {
        font-size: 13px;
        padding: 10px 12px;
    }

    .card {
        padding: 18px;
    }
}
</style>
</head>
<body>

<div class="container">

    <!-- CONTENT -->
    <main class="content">

        <!-- HEADER HALAMAN -->
        <div class="section">
            <h2>Dashboard Admin</h2>
        </div>

        <!-- STATISTIK -->
        <div class="section">
            <div class="stats">

                <div class="card">
                    <span>Total Pegawai</span>
                    <b><?= $total_pegawai ?></b>
                </div>

                <div class="card">
                    <span>Hadir</span>
                    <b><?= $hadir ?></b>
                </div>

                <div class="card">
                    <span>Izin</span>
                    <b><?= $izin ?></b>
                </div>

                <div class="card">
                    <span>Tidak Hadir</span>
                    <b><?= $tidak_hadir ?></b>
                </div>

            </div>
        </div>

        <!-- TABEL ABSENSI -->
        <div class="section">
            <div class="card">

                <h3>Absensi Hari Ini</h3>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Pegawai</th>
                                <th>Jam Absen</th>
                                <th>Status</th>
                                <th>Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT 
                                    p.nama_lengkap,
                                    a.jam_absen,
                                    a.status,
                                    a.device_info
                                FROM absensi a
                                JOIN pegawai p ON a.id_pegawai = p.id_pegawai
                                WHERE a.tanggal = CURDATE()
                                ORDER BY a.jam_absen ASC
                            ");
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) :
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['jam_absen']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                                <td><?= htmlspecialchars($row['device_info'] ?? '-') ?></td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>

                    </table>
                </div>

            </div>
        </div>

    </main>
    <!-- END CONTENT -->

</div>

</body>
</html>

