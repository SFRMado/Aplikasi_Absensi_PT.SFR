<?php
// opsi-login.php - Pilihan Login Admin / Pegawai
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilihan Login | PT. SFR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            overflow: hidden;
            background: radial-gradient(circle at top, #1b2735, #090a0f);
            color: #fff;
        }

        /* Canvas Katana Slash */
        #butterflyCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 700px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(14px);
            border-radius: 24px;
            padding: 50px 60px;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            animation: fadeUp 1.2s ease;
        }

        .login-card h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(135deg, #00eaff, #ffffff);
            background-clip: text; /* standard */
            -webkit-background-clip: text;
            color: transparent;
        }

        .login-card p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
        }

        .option {
            padding: 35px 25px;
            border-radius: 20px;
            background: rgba(255,255,255,0.08);
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .option::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,0.15), transparent);
            opacity: 0;
            transition: 0.4s;
        }

        .option:hover::before {
            opacity: 1;
        }

        .option:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
        }

        .option h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .option p {
            font-size: 0.95rem;
            opacity: 0.85;
        }

        .admin {
            border: 1px solid rgba(255, 80, 80, 0.5);
        }

        .pegawai {
            border: 1px solid rgba(80, 200, 255, 0.5);
        }

        footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 0.85rem;
            opacity: 0.7;
            z-index: 2;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<canvas id="butterflyCanvas"></canvas>

<div class="container">
    <div class="login-card">
        <h1>Pilih Akses Login</h1>
        <p>Silakan pilih peran Anda untuk melanjutkan ke sistem absensi PT. SFR</p>

        <div class="options">
            <div class="option admin" onclick="location.href='Admin/login.php'">
                <h3>🛡 Login Admin</h3>
                <p>Mengakses pengelolaan Data Karyawan, Data Laporan, dan Data Sistem.</p>
            </div>
            <div class="option pegawai" onclick="location.href='Pegawai/login.php'">
                <h3>👨‍💼 Login Pegawai</h3>
                <p>Absensi Harian, Riwayat Kehadiran, dan Informasi Pribadi.</p>
            </div>
        </div>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> PT. SFR. All rights reserved.
</footer>

<script>
(function () {
    var canvas = document.getElementById('butterflyCanvas');
    var ctx = canvas.getContext('2d');

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    var colors = ['#ff3b3b', '#3bff6f', '#3b8cff'];
    var slashes = [];

    function KatanaSlash() {
        this.reset();
    }

    KatanaSlash.prototype.reset = function () {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.length = Math.random() * 120 + 60;
        this.width = Math.random() * 2 + 1;
        this.speed = Math.random() * 6 + 4;
        this.angle = Math.random() * Math.PI * 2;
        this.life = 80 + Math.random() * 40;
        this.color = colors[Math.floor(Math.random() * colors.length)];
    };

    KatanaSlash.prototype.update = function () {
        this.x += Math.cos(this.angle) * this.speed;
        this.y += Math.sin(this.angle) * this.speed;
        this.life--;
        if (this.life <= 0) this.reset();
    };

    KatanaSlash.prototype.draw = function () {
        ctx.save();
        ctx.translate(this.x, this.y);
        ctx.rotate(this.angle);

        // Glow api
        ctx.shadowBlur = 20;
        ctx.shadowColor = this.color;

        var gradient = ctx.createLinearGradient(0, 0, this.length, 0);
        gradient.addColorStop(0, 'rgba(255,255,255,0)');
        gradient.addColorStop(0.4, this.color);
        gradient.addColorStop(0.6, '#ffaa33');
        gradient.addColorStop(1, 'rgba(255,255,255,0)');

        ctx.strokeStyle = gradient;
        ctx.lineWidth = this.width;
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.lineTo(this.length, 0);
        ctx.stroke();

        // Partikel api kecil
        for (var i = 0; i < 3; i++) {
            ctx.beginPath();
            ctx.fillStyle = 'rgba(255,140,0,0.6)';
            ctx.arc(Math.random() * this.length, (Math.random() - 0.5) * 8, 2, 0, Math.PI * 2);
            ctx.fill();
        }

        ctx.restore();
    };

    function initSlashes(count) {
        slashes = [];
        for (var i = 0; i < count; i++) {
            slashes.push(new KatanaSlash());
        }
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (var i = 0; i < slashes.length; i++) {
            slashes[i].update();
            slashes[i].draw();
        }
        requestAnimationFrame(animate);
    }

    initSlashes(25);
    animate();
})();
</script>

</body>
</html>
