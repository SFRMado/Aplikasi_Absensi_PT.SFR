<div class="sidebar">
    <h3 class="sidebar-title">PT. SFR</h3>

    <nav class="sidebar-menu">
        <a href="dashboard.php"
           class="<?= ($currentPage ?? '') === 'dashboard.php' ? 'active' : '' ?>">
            📊 Dashboard
        </a>

        <a href="pabrik.php"
           class="<?= ($currentPage ?? '') === 'absensi.php' ? 'active' : '' ?>">
            🕒 Data Pabrik
        </a>
    </nav>
</div>
