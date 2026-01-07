<?php
require_once 'config/koneksi.php';

/* ================== CREATE / UPDATE ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama   = trim($_POST['nama_lengkap']);
    $status = $_POST['status'];

    if (isset($_POST['create'])) {
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, nama_lengkap, status, role)
            VALUES (?, ?, ?, ?, 'admin')
        ");
        $stmt->execute([$username, $password, $nama, $status]);
    }

    if (isset($_POST['update'])) {
        $id = (int)$_POST['id_user'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users 
                SET nama_lengkap=?, status=?, password=? 
                WHERE id_user=?
            ");
            $stmt->execute([$nama, $status, $password, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET nama_lengkap=?, status=? 
                WHERE id_user=?
            ");
            $stmt->execute([$nama, $status, $id]);
        }
    }

    header('Location: users.php');
    exit;
}

/* ================== DELETE ================== */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE id_user=?")->execute([$id]);
    header('Location: users.php');
    exit;
}

/* ================== READ ================== */
$stmt = $pdo->query("
    SELECT id_user, nama_lengkap, username, status, qr_code
    FROM users
    ORDER BY nama_lengkap ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'header.php'; ?>
<style>
/* ================== PAGE WRAPPER ================== */
.page-users {
    padding: 28px 32px;
    margin-left: 260px; /* sesuaikan lebar sidebar */
    margin-top: 70px;   /* sesuaikan tinggi header */
}

/* ================== CARD CONTAINER ================== */
.users-card {
    background: #ffffff;
    padding: 22px 24px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,.08);
}

/* ================== TABLE ================== */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    margin-top: 18px;
}

th, td {
    padding: 14px 12px;
    border-bottom: 1px solid #eaeaea;
    text-align: center;
    font-size: 14px;
}

th {
    background: #0984e3;
    color: #fff;
    font-weight: 600;
}

tr:hover td {
    background: #f8f9fb;
}

/* ================== FORM ================== */
form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

input, select {
    padding: 7px 10px;
    border-radius: 8px;
    border: 1px solid #dcdde1;
    font-size: 13px;
    outline: none;
}

input:focus, select:focus {
    border-color: #0984e3;
}

/* Form di dalam tabel */
td form {
    justify-content: center;
}

/* ================== BUTTON ================== */
.btn-generate,
.btn-exists {
    padding: 7px 16px;
    border-radius: 8px;
    border: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: .25s ease;
}

.btn-generate {
    background: #0984e3;
    color: #fff;
}
.btn-generate:hover {
    background: #0873c4;
    transform: translateY(-1px);
}

.btn-exists {
    background: #636e72;
    color: #fff;
}
.btn-exists:hover {
    background: #4b5559;
}

/* ================== HEADER TITLE ================== */
.users-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 18px;
    color: #2d3436;
}

/* ================== POPUP ================== */
.popup {
    position: fixed;
    top: 24px;
    right: 24px;
    background: #d63031;
    color: #fff;
    padding: 14px 22px;
    border-radius: 12px;
    opacity: 0;
    transform: translateY(-15px);
    transition: .35s ease;
    z-index: 3000;
    box-shadow: 0 10px 24px rgba(0,0,0,.25);
    font-size: 14px;
}
.popup.show {
    opacity: 1;
    transform: translateY(0);
}

/* ================== RESPONSIVE ================== */
@media (max-width: 992px) {
    .page-users {
        margin-left: 0;
        padding: 22px;
    }

    form {
        flex-direction: column;
        align-items: stretch;
    }

    th, td {
        font-size: 13px;
    }
}
</style>
<div class="users-card" style="margin-bottom:32px;">
    <h3 class="users-title">Tambah User</h3>

    <form method="post">
        <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <select name="status">
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select>

        <button type="submit" name="create" class="btn-generate">
            ➕ Tambah User
        </button>
    </form>
</div>
<div class="users-card">
    <h3 class="users-title">Data Pegawai & Admin</h3>

    <table>
        <thead>
        <tr>
            <th>Nama</th>
            <th>Username</th>
            <th>Status</th>
            <th>QR Code</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($users as $u): ?>
        <tr>
            <td>
                <form method="post">
                    <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                    <input type="text" name="nama_lengkap"
                           value="<?= htmlspecialchars($u['nama_lengkap']) ?>">
            </td>

            <td><?= htmlspecialchars($u['username']) ?></td>

            <td>
                <select name="status">
                    <option value="aktif" <?= $u['status']=='aktif'?'selected':'' ?>>Aktif</option>
                    <option value="nonaktif" <?= $u['status']=='nonaktif'?'selected':'' ?>>Nonaktif</option>
                </select>
            </td>

            <td>
                <?php if ($u['qr_code']): ?>
                    <button type="button" class="btn-exists" onclick="showPopup()">Sudah Ada</button>
                <?php else: ?>
                    <button type="button" class="btn-generate"
                            onclick="generateQR(<?= $u['id_user'] ?>)">
                        Buat QR
                    </button>
                <?php endif; ?>
            </td>

            <td>
                <input type="password" name="password" placeholder="Password baru">

                <button type="submit" name="update" class="btn-generate">
                    💾 Update
                </button>

                <a href="?delete=<?= $u['id_user'] ?>"
                   onclick="return confirm('Hapus user ini?')"
                   class="btn-exists">
                    🗑 Hapus
                </a>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>
<?php if (isset($_GET['qr']) && $_GET['qr'] === 'exists'): ?>
<script>
    showPopup();
</script>
<?php endif; ?>
<?php if (isset($_GET['qr']) && $_GET['qr'] === 'success'): ?>
<script>
    alert('QR Code berhasil dibuat!');
</script>
<?php endif; ?>

<!-- POPUP -->
<div id="popup" class="popup">
    ⚠️ User ini sudah memiliki QR Code
</div>

<script>
function showPopup() {
    const popup = document.getElementById('popup');
    popup.classList.add('show');

    setTimeout(() => {
        popup.classList.remove('show');
    }, 2500);
}

function generateQR(idUser) {
    window.location.href = 'qr-code.php?id_user=' + idUser;
}
</script>
