<?php
session_start();
require_once 'config/koneksi.php';

/* ================== CREATE & UPDATE ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_absensi = $_POST['id_absensi'] ?? null;

    if ($id_absensi) {
        // UPDATE
        $stmt = $pdo->prepare("
            UPDATE absensi SET
                id_pegawai = :id_pegawai,
                tanggal    = :tanggal,
                jam_absen  = :jam_absen,
                status     = :status
            WHERE id_absensi = :id
        ");
        $stmt->execute([
            'id_pegawai' => $_POST['id_pegawai'],
            'tanggal'    => $_POST['tanggal'],
            'jam_absen'  => $_POST['jam_absen'],
            'status'     => $_POST['status'],
            'id'         => $id_absensi
        ]);
        $_SESSION['success'] = 'Data absensi berhasil diperbarui';

    } else {
        // CREATE
        $stmt = $pdo->prepare("
            INSERT INTO absensi
            (id_pegawai, tanggal, jam_absen, status, device_info)
            VALUES
            (:id_pegawai, :tanggal, :jam_absen, :status, 'Manual Admin')
        ");
        $stmt->execute([
            'id_pegawai' => $_POST['id_pegawai'],
            'tanggal'    => $_POST['tanggal'],
            'jam_absen'  => $_POST['jam_absen'],
            'status'     => $_POST['status']
        ]);
        $_SESSION['success'] = 'Data absensi berhasil ditambahkan';
    }

    header('Location: absensi.php');
    exit;
}

/* ================== DELETE ================== */
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM absensi WHERE id_absensi = ?");
    $stmt->execute([$_GET['hapus']]);
    $_SESSION['success'] = 'Data absensi berhasil dihapus';
    header('Location: absensi.php');
    exit;
}

/* ================== DATA ================== */
$pegawai = $pdo->query("
    SELECT id_pegawai, nama_lengkap
    FROM pegawai
    WHERE status_pegawai = 'aktif'
    ORDER BY nama_lengkap
")->fetchAll(PDO::FETCH_ASSOC);

$absensi = $pdo->query("
    SELECT a.*, p.nama_lengkap
    FROM absensi a
    JOIN pegawai p ON a.id_pegawai = p.id_pegawai
    ORDER BY a.tanggal DESC, a.jam_absen DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Absensi</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php include 'header.php'; ?>
<style>
body {
    background: #f4f6f9;
    font-family: 'Segoe UI', sans-serif;
}

.wrapper {
    max-width: 1100px;
    margin: 110px auto 40px;
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 10px 24px rgba(0,0,0,.08);
}

h2 {
    margin-bottom: 24px;
}

.alert {
    background: #ecfdf5;
    color: #047857;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
}

form {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

select, input {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
}

button {
    background: #4f46e5;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px;
    cursor: pointer;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 14px;
    border-bottom: 1px solid #eee;
    text-align: center;
}

th {
    background: #4f46e5;
    color: #fff;
}
</style>
</head>
<body>

<div class="wrapper">

<h2>📋 Data Absensi</h2>

<?php if (!empty($_SESSION['success'])): ?>
<div class="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<!-- FORM -->
<form method="post">
    <select name="id_pegawai" required>
        <option value="">-- Pegawai --</option>
        <?php foreach ($pegawai as $p): ?>
            <option value="<?= $p['id_pegawai'] ?>"><?= $p['nama_lengkap'] ?></option>
        <?php endforeach; ?>
    </select>

    <input type="date" name="tanggal" required>
    <input type="time" name="jam_absen" required>

    <select name="status" required>
        <option value="hadir">Hadir</option>
        <option value="terlambat">Terlambat</option>
        <option value="izin">Izin</option>
        <option value="tidak hadir">Tidak Hadir</option>
    </select>

    <button>Simpan</button>
</form>

<!-- TABLE -->
<table>
<thead>
<tr>
    <th>Nama Pegawai</th>
    <th>Tanggal</th>
    <th>Jam</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php foreach ($absensi as $a): ?>
<tr>
    <td><?= htmlspecialchars($a['nama_lengkap']) ?></td>
    <td><?= $a['tanggal'] ?></td>
    <td><?= $a['jam_absen'] ?></td>
    <td><?= ucfirst($a['status']) ?></td>
    <td>
        <a href="?hapus=<?= $a['id_absensi'] ?>"
           onclick="return confirm('Hapus data ini?')">🗑</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>

</body>
</html>
