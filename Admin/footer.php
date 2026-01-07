<?php
/**
 * footer.php
 * Footer Aplikasi Absensi PT. SFR
 */
?>
<style>
/* =======================================================
   ADMIN FOOTER — RESPONSIVE (DESKTOP + MOBILE)
   ======================================================= */

.footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: #ffffff;
  padding: 12px 20px;
  border-top: 1px solid #e0e0e0;
  color: #636e72;
  font-size: 13px;
  margin-top: 30px;
  flex-wrap: wrap;
}

/* TEXT VERSION */
.footer-version {
  font-size: 12px;
  color: #b2bec3;
  white-space: nowrap;
}

/* =======================================================
   MOBILE / ANDROID
   ======================================================= */
@media (max-width: 576px) {
  .footer {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 14px 16px;
    font-size: 12.5px;
  }

  .footer-version {
    font-size: 11.5px;
  }
}

/* =======================================================
   SMALL DEVICES (≤ 360px)
   ======================================================= */
@media (max-width: 360px) {
  .footer {
    padding: 12px;
  }

  .footer-version {
    font-size: 11px;
  }
}

/* =======================================================
   TABLET
   ======================================================= */
@media (min-width: 577px) and (max-width: 1024px) {
  .footer {
    padding: 12px 18px;
  }
}

/* =======================================================
   LARGE DESKTOP
   ======================================================= */
@media (min-width: 1400px) {
  .footer {
    padding: 14px 26px;
  }
}
</style>
<footer class="admin-footer">
    <div class="footer-left">
        © <?= date('Y'); ?> PT. SFR — Aplikasi Absensi
    </div>

    <div class="footer-right">
        <span class="footer-version">
            v1.0.0
        </span>
    </div>
</footer>
