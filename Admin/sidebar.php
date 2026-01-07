<div class="sidebar">
    <h3 class="sidebar-title">PT. SFR</h3>

    <nav class="sidebar-menu">
        <a href="dashboard.php"
           class="<?= ($currentPage ?? '') === 'dashboard.php' ? 'active' : '' ?>">
            📊 Dashboard
        </a>

        <a href="absensi.php"
           class="<?= ($currentPage ?? '') === 'absensi.php' ? 'active' : '' ?>">
            🕒 Data Absensi
        </a>

        <a href="laporan.php"
           class="<?= ($currentPage ?? '') === 'laporan.php' ? 'active' : '' ?>">
            📑 Laporan
        </a>
        <a href="users.php"
           class="<?= ($currentPage ?? '') === 'users.php' ? 'active' : '' ?>">
        👥 Data Staff
        </a>
        <a href="koordinat.php"
           class="<?= ($currentPage ?? '') === 'koordinat.php' ? 'active' : '' ?>">
         📍 Koordinat
        </a>
    </nav>
</div>
