<style>
/* ===== FIXED FOOTER ===== */
.footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 12px 28px;
    background: linear-gradient(135deg, #0984e3, #00cec9);
    color: #ffffff;

    font-size: 13px;
    font-weight: 500;

    box-shadow: 0 -6px 18px rgba(0,0,0,.15);
    z-index: 1500;
}

/* ===== TEXT LEFT ===== */
.footer span {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ===== VERSION ===== */
.footer-version {
    font-size: 12px;
    opacity: .85;
}

/* ===== ICON EFFECT ===== */
.footer i {
    font-size: 14px;
    opacity: .9;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .admin-footer {
        flex-direction: column;
        gap: 4px;
        text-align: center;
        padding: 10px 14px;
    }
}
</style>
<footer class="footer">
    <span>
        © <?= date('Y') ?> PT. SFR • Aplikasi Absensi PT.SFR
    </span>

    <span class="footer-version">
        v1.0.0
    </span>
</footer>

