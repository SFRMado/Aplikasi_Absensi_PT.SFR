<?php
declare(strict_types=1);
session_start();

require_once 'config/koneksi.php';

// ================== CREATE ==================
if (isset($_POST['create'])) {
    $stmt = $pdo->prepare("
        INSERT INTO pabrik
        (nama_pabrik, jumlah_produksi, latitude, longitude, foto)
        VALUES (:nama, :produksi, :lat, :lng, :foto)
    ");

    $stmt->execute([
        'nama'     => $_POST['nama_pabrik'],
        'produksi' => (int)$_POST['jumlah_produksi'],
        'lat'      => $_POST['latitude'],
        'lng'      => $_POST['longitude'],
        'foto'     => $_POST['foto']
    ]);

    header("Location: pabrik.php?success=create");
    exit;
}

// ================== UPDATE ==================
if (isset($_POST['update'])) {
    $stmt = $pdo->prepare("
        UPDATE pabrik SET
            nama_pabrik = :nama,
            jumlah_produksi = :produksi,
            latitude = :lat,
            longitude = :lng,
            foto = :foto
        WHERE id_pabrik = :id
    ");

    $stmt->execute([
        'nama'     => $_POST['nama_pabrik'],
        'produksi' => (int)$_POST['jumlah_produksi'],
        'lat'      => $_POST['latitude'],
        'lng'      => $_POST['longitude'],
        'foto'     => $_POST['foto'],
        'id'       => (int)$_POST['id_pabrik']
    ]);

    header("Location: pabrik.php?success=update");
    exit;
}

// ================== DELETE ==================
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM pabrik WHERE id_pabrik = ?");
    $stmt->execute([(int)$_GET['delete']]);

    header("Location: pabrik.php?success=delete");
    exit;
}

// ================== READ ==================
$pabrik = $pdo->query("
    SELECT * FROM pabrik
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ================== EDIT DATA ==================
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pabrik WHERE id_pabrik = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>CRUD Pabrik</title>
<style>
/* ===== ROOT ===== */
:root {
    --primary: #0984e3;
    --secondary: #00cec9;
    --danger: #ff7675;
    --warning: #ffeaa7;
    --bg: #f4f7fb;
    --text: #2d3436;
}

/* ===== BASE ===== */
body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: linear-gradient(135deg, #eef2f7, #f8f9fc);
    padding: 32px;
    color: var(--text);
}

/* ===== CARD ===== */
.card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(0,0,0,.08);
    padding: 26px;
    margin-bottom: 30px;
}

/* ===== TITLE ===== */
h2 {
    margin-bottom: 18px;
    font-size: 22px;
    color: var(--primary);
}

/* ===== FORM ===== */
.form-group {
    margin-bottom: 16px;
}

label {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

input {
    width: 100%;
    padding: 11px 14px;
    border-radius: 12px;
    border: 1px solid #dfe6e9;
    font-size: 14px;
    transition: .25s ease;
}

input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(9,132,227,.18);
}

/* ===== BUTTON ===== */
button {
    padding: 12px 22px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: #fff;
    transition: .25s ease;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 26px rgba(9,132,227,.35);
}

/* ===== TABLE ===== */
.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: var(--primary);
    color: #fff;
}

th, td {
    padding: 14px;
    text-align: center;
    font-size: 14px;
}

tbody tr {
    transition: background .2s ease;
}

tbody tr:hover {
    background: #f1f7ff;
}

/* ===== FOTO ===== */
.foto-pabrik {
    width: 90px;
    height: 60px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0,0,0,.15);
    transition: transform .25s ease;
}

.foto-pabrik:hover {
    transform: scale(1.08);
}

.no-foto {
    font-size: 12px;
    color: #999;
    font-style: italic;
}

/* ===== ACTIONS ===== */
.action a {
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    margin: 0 2px;
    display: inline-block;
}

.action .edit {
    background: var(--warning);
    color: #2d3436;
}

.action .delete {
    background: var(--danger);
    color: #fff;
}

.action a:hover {
    opacity: .85;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    body { padding: 16px; }
    th, td { font-size: 13px; }
}
</style>
<div class="card">
    <h2><?= $editData ? '✏️ Edit Pabrik' : '➕ Tambah Pabrik' ?></h2>

    <form method="post">
        <input type="hidden" name="id_pabrik"
               value="<?= $editData['id_pabrik'] ?? '' ?>">

        <div class="form-group">
            <label>Nama Pabrik</label>
            <input type="text" name="nama_pabrik"
                   value="<?= htmlspecialchars($editData['nama_pabrik'] ?? '') ?>"
                   placeholder="Contoh: Pabrik Karawang"
                   required>
        </div>

        <div class="form-group">
            <label>Jumlah Produksi</label>
            <input type="number" name="jumlah_produksi"
                   value="<?= $editData['jumlah_produksi'] ?? 0 ?>"
                   required>
        </div>

        <div class="form-group">
            <label>Latitude</label>
            <input type="text" name="latitude"
                   value="<?= $editData['latitude'] ?? '' ?>"
                   placeholder="-6.4522823"
                   required>
        </div>

        <div class="form-group">
            <label>Longitude</label>
            <input type="text" name="longitude"
                   value="<?= $editData['longitude'] ?? '' ?>"
                   placeholder="107.0403940"
                   required>
        </div>

        <div class="form-group">
            <label>Path Foto</label>
            <input type="text" name="foto"
                   value="<?= htmlspecialchars($editData['foto'] ?? '') ?>"
                   placeholder="assets/img/pabrik1.jpg"
                   required>
        </div>

        <button type="submit" name="<?= $editData ? 'update' : 'create' ?>">
            <?= $editData ? 'Update Data' : 'Simpan Data' ?>
        </button>
    </form>
</div>

<div class="card">
    <h2>🏭 Data Pabrik</h2>

    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Produksi</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pabrik as $p): ?>
                <tr>
                    <td><?= $p['id_pabrik'] ?></td>
                    <td><?= htmlspecialchars($p['nama_pabrik']) ?></td>
                    <td><?= number_format($p['jumlah_produksi']) ?></td>
                    <td><?= $p['latitude'] ?></td>
                    <td><?= $p['longitude'] ?></td>
                    <td>
                        <?php if (!empty($p['foto']) && file_exists($p['foto'])): ?>
                            <img src="<?= htmlspecialchars($p['foto']) ?>"
                                 class="foto-pabrik"
                                 alt="Foto <?= htmlspecialchars($p['nama_pabrik']) ?>">
                        <?php else: ?>
                            <span class="no-foto">Tidak ada foto</span>
                        <?php endif; ?>
                    </td>
                    <td class="action">
                        <a href="?edit=<?= $p['id_pabrik'] ?>" class="edit">✏️ Edit</a>
                        <a href="?delete=<?= $p['id_pabrik'] ?>"
                           class="delete"
                           onclick="return confirm('Hapus pabrik ini?')">🗑 Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>