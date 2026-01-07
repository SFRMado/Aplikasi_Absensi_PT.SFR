<?php
session_start();
require_once 'config/koneksi.php';

/* ================== SIMPAN KOORDINAT ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE koordinat
        SET latitude = :lat,
            longitude = :lng,
            radius_meter = :radius
        WHERE id = 1
    ");
    $stmt->execute([
        'lat'    => $_POST['latitude'],
        'lng'    => $_POST['longitude'],
        'radius' => $_POST['radius']
    ]);

    $_SESSION['success'] = 'Koordinat staff berhasil diperbarui';
    header('Location: koordinat.php');
    exit;
}

/* ================== AMBIL DATA ================== */
$data = $pdo->query("
    SELECT * FROM koordinat WHERE id = 1
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pengaturan Titik Koordinat Staff</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php include 'header.php'; ?>
<style>
/* ================== GLOBAL ================== */
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;

    /* Lebih terang & soft */
    background: linear-gradient(120deg, #eef2ff, #f8fafc);
    color: #1f2937;
}

/* ================== WRAPPER UTAMA ================== */
.wrapper {
    max-width: 900px;

    /* JARAK AMAN DARI SIDEBAR & HEADER */
    margin: 120px auto 60px auto;

    background: #ffffff;
    border-radius: 20px;

    padding: 36px 40px;

    box-shadow:
        0 12px 30px rgba(0,0,0,.08),
        0 4px 12px rgba(0,0,0,.05);

    box-sizing: border-box;
}

/* ================== JUDUL ================== */
h2 {
    display: flex;
    align-items: center;
    gap: 12px;

    font-size: 22px;
    font-weight: 600;

    margin-bottom: 32px;
    padding-bottom: 14px;

    border-bottom: 1px solid #e5e7eb;
}

h2 span {
    font-size: 22px;
    color: #4f46e5;
}

/* ================== GRID FORM ================== */
.grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 28px;
    margin-bottom: 10px;
}

/* ================== LABEL ================== */
label {
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
    margin-bottom: 6px;
    display: block;
}

/* ================== INPUT ================== */
input {
    width: 100%;
    padding: 12px 14px;

    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: 12px;

    color: #111827;
    font-size: 14px;

    transition: .2s ease;
}

input:focus {
    outline: none;
    border-color: #6366f1;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(99,102,241,.15);
}

input[disabled] {
    background: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

/* ================== ACTION ================== */
.actions {
    margin-top: 34px;
    display: flex;
    justify-content: flex-end;
}

/* ================== BUTTON ================== */
.btn {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border: none;

    padding: 12px 26px;
    border-radius: 14px;

    color: #fff;
    font-weight: 600;
    font-size: 14px;

    cursor: pointer;
    transition: all .25s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 26px rgba(79,70,229,.35);
}

/* ================== ALERT ================== */
.alert {
    background: #ecfdf5;
    color: #047857;

    padding: 14px 18px;
    border-radius: 14px;

    margin-bottom: 28px;
    font-size: 14px;

    border: 1px solid #a7f3d0;
}

/* ================== NOTE ================== */
.note {
    margin-top: 26px;
    padding-top: 14px;

    border-top: 1px dashed #d1d5db;

    font-size: 13px;
    color: #6b7280;
}

/* ================== RESPONSIVE ================== */
@media (max-width: 768px) {
    .wrapper {
        margin: 100px 20px 40px;
        padding: 28px 24px;
    }

    .grid {
        grid-template-columns: 1fr;
    }
}
/* =======================================================
   GLOBAL MOBILE SAFETY
   ======================================================= */
html, body {
  width: 100%;
  overflow-x: hidden;
  -webkit-text-size-adjust: 100%;
  touch-action: manipulation;
}

/* =======================================================
   WRAPPER RESPONSIVE
   ======================================================= */
@media (max-width: 1024px) {
  .wrapper {
    max-width: 100%;
  }
}

/* =======================================================
   TABLET
   ======================================================= */
@media (max-width: 768px) {
  .wrapper {
    margin: 96px 18px 36px;
    padding: 28px 22px;
    border-radius: 18px;
  }

  h2,
  h2 span {
    font-size: 20px;
  }

  .grid {
    gap: 22px;
  }
}

/* =======================================================
   MOBILE / ANDROID
   ======================================================= */
@media (max-width: 576px) {
  .wrapper {
    margin: 88px 14px 32px;
    padding: 24px 18px;
    border-radius: 16px;
  }

  h2 {
    gap: 10px;
    font-size: 18px;
  }

  h2 span {
    font-size: 18px;
  }

  label {
    font-size: 12.5px;
  }

  input {
    padding: 12px;
    font-size: 13.5px;
    border-radius: 10px;
  }

  .actions {
    justify-content: center;
  }

  .btn {
    width: 100%;
    padding: 13px;
    font-size: 13.5px;
    border-radius: 12px;
  }

  .alert {
    font-size: 13px;
    padding: 13px 16px;
  }

  .note {
    font-size: 12.5px;
  }
}

/* =======================================================
   EXTRA SMALL DEVICES (≤ 360px)
   ======================================================= */
@media (max-width: 360px) {
  .wrapper {
    margin: 84px 10px 28px;
    padding: 20px 14px;
  }

  h2 {
    font-size: 17px;
  }

  .btn {
    font-size: 13px;
  }
}

/* =======================================================
   LARGE DESKTOP STABILITY
   ======================================================= */
@media (min-width: 1400px) {
  .wrapper {
    max-width: 960px;
  }
}

</style>
</head>
<body>

<div class="wrapper">

    <h2><span>📍</span> Pengaturan Titik Koordinat Staff</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <div class="grid">
            <div>
                <label>Latitude</label>
                <input type="text" name="latitude"
                       value="<?= htmlspecialchars($data['latitude']) ?>" required>
            </div>

            <div>
                <label>Longitude</label>
                <input type="text" name="longitude"
                       value="<?= htmlspecialchars($data['longitude']) ?>" required>
            </div>

            <div>
                <label>Radius Absensi (meter)</label>
                <input type="number" name="radius"
                       value="<?= (int)$data['radius_meter'] ?>" min="10">
            </div>

            <div>
                <label>Lokasi</label>
                <input type="text" value="AREA STAFF" disabled>
            </div>
        </div>

        <div class="actions">
            <button class="btn">Simpan Koordinat</button>
        </div>

    </form>
    <div id="map"></div>
    <div class="note">
        Koordinat ini digunakan sebagai pusat validasi radius absensi staff.
    </div>

</div>
<script>
/* ================== DATA AWAL ================== */
const latInput = document.querySelector('[name="latitude"]');
const lngInput = document.querySelector('[name="longitude"]');
const radiusInput = document.querySelector('[name="radius"]');

let lat = parseFloat(latInput.value);
let lng = parseFloat(lngInput.value);
let radius = parseInt(radiusInput.value);

/* ================== BASE LAYERS ================== */
const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
});

const osmDark = L.tileLayer(
    'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
    { attribution: '© OpenStreetMap © CARTO' }
);

/* ================== MAP INIT ================== */
const map = L.map('map', {
    center: [lat, lng],
    zoom: 16,
    layers: [osm]
});

/* ================== LAYER GROUPS ================== */
const markerLayer = L.layerGroup().addTo(map);
const radiusLayer = L.layerGroup().addTo(map);

/* ================== MARKER & RADIUS ================== */
let marker = L.marker([lat, lng], { draggable: false })
    .bindPopup('Titik Koordinat Staff')
    .openPopup()
    .addTo(markerLayer);

let circle = L.circle([lat, lng], {
    radius: radius,
    color: '#4f46e5',
    fillColor: '#6366f1',
    fillOpacity: 0.2
}).addTo(radiusLayer);

/* ================== LAYER CONTROL ================== */
const baseLayers = {
    "OpenStreetMap": osm,
    "Dark Mode": osmDark
};

const overlays = {
    "Marker Lokasi": markerLayer,
    "Radius Absensi": radiusLayer
};

L.control.layers(baseLayers, overlays).addTo(map);

/* ================== CLICK MAP ================== */
map.on('click', function (e) {
    const { lat, lng } = e.latlng;

    // Update input
    latInput.value = lat.toFixed(6);
    lngInput.value = lng.toFixed(6);

    // Update marker
    marker.setLatLng([lat, lng]);

    // Update circle
    circle.setLatLng([lat, lng]);

    map.panTo([lat, lng]);
});

/* ================== UPDATE RADIUS REALTIME ================== */
radiusInput.addEventListener('input', () => {
    circle.setRadius(parseInt(radiusInput.value));
});
</script>

</body>
</html>
