<?php
session_start();
require_once 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_pegawai = $_POST['id_pegawai'] ?? null;
    $awal = $_POST['periode_awal'] ?? null;
    $akhir = $_POST['periode_akhir'] ?? null;

    // VALIDASI DASAR
    if (!$id_pegawai || !$awal || !$akhir) {
        $_SESSION['success'] = 'Data tidak lengkap';
        header('Location: laporan.php');
        exit;
    }

    if ($awal > $akhir) {
        $_SESSION['success'] = 'Periode tanggal tidak valid';
        header('Location: laporan.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // CEK DUPLIKAT
        $cek = $pdo->prepare("
            SELECT COUNT(*) FROM laporan
            WHERE id_pegawai = ? 
            AND periode_awal = ? 
            AND periode_akhir = ?
        ");
        $cek->execute([$id_pegawai, $awal, $akhir]);

        if ($cek->fetchColumn() > 0) {
            $_SESSION['success'] = 'Laporan untuk periode ini sudah ada';
            header('Location: laporan.php');
            exit;
        }

        // HITUNG ABSENSI (LEBIH AMAN)
        $stmt = $pdo->prepare("
            SELECT
                SUM(CASE WHEN status_absensi = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status_absensi = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN status_absensi = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN status_absensi = 'tidak hadir' THEN 1 ELSE 0 END) AS tidak_hadir
            FROM absensi
            WHERE id_pegawai = ?
            AND tanggal BETWEEN ? AND ?
        ");
        $stmt->execute([$id_pegawai, $awal, $akhir]);
        $rekap = $stmt->fetch(PDO::FETCH_ASSOC);

        // OPSIONAL: CEK ABSENSI KOSONG
        if (
            ($rekap['hadir'] +
             $rekap['izin'] +
             $rekap['terlambat'] +
             $rekap['tidak_hadir']) == 0
        ) {
            $_SESSION['success'] = 'Tidak ada data absensi pada periode ini';
            header('Location: laporan.php');
            exit;
        }

        // INSERT LAPORAN
        $stmt = $pdo->prepare("
            INSERT INTO laporan
            (id_pegawai, periode_awal, periode_akhir,
             total_hadir, total_izin, total_terlambat, total_tidak_hadir, keterangan)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $id_pegawai,
            $awal,
            $akhir,
            $rekap['hadir'],
            $rekap['izin'],
            $rekap['terlambat'],
            $rekap['tidak_hadir'],
            'Laporan dibuat otomatis dari data absensi'
        ]);

        $pdo->commit();

        $_SESSION['success'] = 'Laporan berhasil dibuat otomatis';
        header('Location: laporan.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['success'] = 'Terjadi kesalahan sistem';
        header('Location: laporan.php');
        exit;
    }
}
/* ================== DELETE ================== */
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM laporan WHERE id_laporan = ?");
    $stmt->execute([$_GET['hapus']]);
    $_SESSION['success'] = 'Laporan berhasil dihapus';
    header('Location: laporan.php');
    exit;
}

/* ================== DATA ================== */
$pegawai = $pdo->query("
    SELECT id_pegawai, nama_lengkap
    FROM pegawai
    WHERE status_pegawai = 'aktif'
    ORDER BY nama_lengkap
")->fetchAll(PDO::FETCH_ASSOC);

$laporan = $pdo->query("
    SELECT l.*, p.nama_lengkap
    FROM laporan l
    JOIN pegawai p ON l.id_pegawai = p.id_pegawai
    ORDER BY l.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Absensi</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php include 'header.php'; ?>
<style>
.page-title {
    margin-bottom: 24px;
}

/* CARD */
.card {
    background: #fff;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
}

/* FORM */
.form-laporan {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    align-items: end;
}

.form-group label {
    display: block;
    font-size: 13px;
    margin-bottom: 6px;
    color: #4b5563;
}

.form-laporan select,
.form-laporan input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    font-size: 14px;
}

.form-action {
    display: flex;
    justify-content: flex-end;
}

.form-action button {
    background: #4f46e5;
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 12px 26px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}

.form-action button:hover {
    background: #4338ca;
}

/* TABLE */
.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 14px 16px;
    border-bottom: 1px solid #eee;
    text-align: center;
    font-size: 14px;
}

th {
    background: #4f46e5;
    color: #fff;
}

tr:hover td {
    background: #f9fafb;
}

/* DELETE BUTTON */
.btn-delete {
    color: #dc2626;
    text-decoration: none;
    font-size: 16px;
}

.btn-delete:hover {
    color: #b91c1c;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .form-laporan {
        grid-template-columns: 1fr;
    }

    .form-action {
        justify-content: stretch;
    }

    .form-action button {
        width: 100%;
    }
}
/* ================== LAYOUT UTAMA ================== */
.container {
    display: flex;
    min-height: 100vh;
}

/* AREA KONTEN (OFFSET HEADER & SIDEBAR) */
.content {
    flex: 1;
    margin-left: 260px;   /* LEBAR SIDEBAR */
    margin-top: 70px;     /* TINGGI HEADER */
    padding: 32px 36px;
    box-sizing: border-box;
}

/* WRAPPER HALAMAN */
.wrapper {
    max-width: 1200px;
    margin: 0 auto 40px;
}

</style>
</head>
<body>
<div class="container">
<main class="content">
<div class="wrapper">

<h2 class="page-title">📑 Laporan Absensi Pegawai</h2>

<?php if (!empty($_SESSION['success'])): ?>
<div class="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<!-- FORM CARD -->
<div class="card">
    <form method="post" class="form-laporan">

        <div class="form-group">
            <label>Silahkan Dipilih</label>
            <select name="id_pegawai" required>
                <option value="">Pegawai</option>
                <?php foreach ($pegawai as $p): ?>
                    <option value="<?= $p['id_pegawai'] ?>">
                        <?= htmlspecialchars($p['nama_lengkap']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Periode Awal</label>
            <input type="date" name="periode_awal" required>
        </div>

        <div class="form-group">
            <label>Periode Akhir</label>
            <input type="date" name="periode_akhir" required>
        </div>

        <div class="form-action">
            <button type="submit">📊 Buat Laporan</button>
        </div>

    </form>
</div>

<!-- TABLE CARD -->
<div class="card">
    <h3>Daftar Laporan</h3>

    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>Pegawai</th>
                <th>Periode</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Terlambat</th>
                <th>Tidak Hadir</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($laporan as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['nama_lengkap']) ?></td>
                <td><?= $l['periode_awal'] ?> s/d <?= $l['periode_akhir'] ?></td>
                <td><?= $l['total_hadir'] ?></td>
                <td><?= $l['total_izin'] ?></td>
                <td><?= $l['total_terlambat'] ?></td>
                <td><?= $l['total_tidak_hadir'] ?></td>
                <td><?= htmlspecialchars($l['keterangan'] ?? '-') ?></td>
                <td>
                    <a class="btn-delete"
                       href="?hapus=<?= $l['id_laporan'] ?>"
                       onclick="return confirm('Hapus laporan ini?')">🗑</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </main>
        </div>
    </div>
</div>
</div>
</body>
</html>
