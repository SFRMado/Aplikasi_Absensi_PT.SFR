<?php
declare(strict_types=1);

// ================== SESSION & AUTH ==================
session_start();
require_once __DIR__ . '/config/auth.php';
requirePegawaiLogin();

// ================== DATA STATIS ==================
$jumlahUnit = 3;   // ⬅️ HARDCODE
$totalProduksi = 0;

// ================== LOAD GEOJSON (OPSIONAL) ==================
$geojsonPath = __DIR__ . '/assets/js/PT.SFR.geojson';

if (is_readable($geojsonPath)) {
    $geojson = json_decode(file_get_contents($geojsonPath), true);

    if (
        json_last_error() === JSON_ERROR_NONE &&
        !empty($geojson['features'])
    ) {
        foreach ($geojson['features'] as $feature) {
            $props = $feature['properties'] ?? [];

            if (isset($props['produksi']) && is_numeric($props['produksi'])) {
                $totalProduksi += (int) $props['produksi'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Pegawai | PT. SFR</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php include 'header.php'; ?>

<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    color: #2d3436;
}

.container {
    display: flex;
    min-height: 100vh;
}

.content {
    flex: 1;
    padding: 32px 36px;
    margin-left: 260px;
    margin-top: 70px;
}

.section {
    margin-bottom: 40px;
}

/* ===== STATS ===== */
.stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 28px;
}

.card {
    text-align: center;
    padding: 28px 20px;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 6px 18px rgba(0,0,0,.1);
    transition: .3s ease;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(0,0,0,.18);
}

.card span {
    font-size: 14px;
    color: #636e72;
}

.card b {
    display: block;
    margin-top: 10px;
    font-size: 32px;
    font-weight: 600;
    color: #0984e3;
}

@media (max-width: 992px) {
    .content {
        margin-left: 0;
        padding: 24px;
    }

    .stats {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="container">

    <main class="content">

        <div class="section">
            <h2>Dashboard Pegawai</h2>
            <p style="opacity:.7">Ringkasan data produksi PT. SFR</p>
        </div>

        <div class="section">
            <div class="stats">

                <div class="card">
                    <span>Jumlah Unit</span>
                    <b><?= number_format($jumlahUnit) ?></b>
                </div>

                <div class="card">
                    <span>Total Produksi</span>
                    <b><?= number_format($totalProduksi) ?></b>
                </div>

            </div>
        </div>

    </main>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
