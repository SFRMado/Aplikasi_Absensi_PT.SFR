<?php
// index.php - Aplikasi Absensi PT. SFR
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Absensi | PT. SFR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
        }

        header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 3px;
            background: linear-gradient(135deg, #00eaff, #ffffff);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }

        header h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -6px;
            width: 50%;
            height: 2px;
            background: linear-gradient(135deg, #00eaff, transparent);
        }

        header a {
            padding: 10px 28px;
            background: linear-gradient(135deg, #00eaff, #00b3ff);
            color: #002b36;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s ease;
        }

        header a:hover {
            box-shadow: 0 8px 20px rgba(0,234,255,0.4);
            transform: translateY(-2px);
        }

        /* Canvas Partikel */
        #particleCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Container Utama */
        .container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 120px 20px 40px;
        }

        .card {
            max-width: 900px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 50px 60px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            animation: fadeIn 1.2s ease-in-out;
        }

        .card h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .card h1 span {
            color: #00eaff;
        }

        .card p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 14px 36px;
            background: linear-gradient(135deg, #00eaff, #00b3ff);
            color: #002b36;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,234,255,0.4);
        }

        footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 0.85rem;
            opacity: 0.7;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<canvas id="particleCanvas"></canvas>

<header>
    <h2>PT. SFR</h2>
    <a href="opsi-login.php">Login</a>
</header>

<div class="container">
    <div class="card">
        <h1>Aplikasi Absensi <span>PT. SFR</span></h1>
        <p>Sistem absensi digital modern yang dirancang untuk meningkatkan efisiensi, akurasi, dan transparansi kehadiran karyawan secara real-time.</p>

        <div class="features" style="margin:40px 0; display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:25px;">
            <div style="display:flex; flex-direction:column; align-items:center; text-align:center;">
                <h3 style="color:#00eaff; margin-bottom:10px;">⏱ Absensi Real-Time</h3>
                <p style="font-size:0.95rem; opacity:0.9;">Pencatatan kehadiran langsung tersimpan otomatis dan akurat.</p>
            </div>
            <div style="display:flex; flex-direction:column; align-items:center; text-align:center;">
                <h3 style="color:#00eaff; margin-bottom:10px;">📍 Lokasi Terkontrol</h3>
                <p style="font-size:0.95rem; opacity:0.9;">Validasi lokasi untuk memastikan absensi dilakukan di area kerja.</p>
            </div>
            <div style="display:flex; flex-direction:column; align-items:center; text-align:center;">
                <h3 style="color:#00eaff; margin-bottom:10px;">📊 Laporan Otomatis</h3>
                <p style="font-size:0.95rem; opacity:0.9;">Rekap absensi harian, bulanan, dan tahunan siap digunakan.</p>
            </div>
            <div style="display:flex; flex-direction:column; align-items:center; text-align:center;">
                <h3 style="color:#00eaff; margin-bottom:10px;">🔐 Aman & Terintegrasi</h3>
                <p style="font-size:0.95rem; opacity:0.9;">Sistem login aman dan mudah diintegrasikan dengan HR.</p>
            </div>
        </div>

        
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> PT. SFR. All rights reserved.
</footer>

<script>
    const canvas = document.getElementById('particleCanvas');
    const ctx = canvas.getContext('2d');

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let particlesArray = [];
    const particleCount = 100;

    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });

    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2 + 0.5;
            this.speedX = (Math.random() - 0.5) * 0.8;
            this.speedY = (Math.random() - 0.5) * 0.8;
            this.opacity = Math.random();
        }

        update() {
            this.x += this.speedX;
            this.y += this.speedY;

            if (this.x > canvas.width) this.x = 0;
            if (this.x < 0) this.x = canvas.width;
            if (this.y > canvas.height) this.y = 0;
            if (this.y < 0) this.y = canvas.height;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(0, 234, 255, ${this.opacity})`;
            ctx.fill();
        }
    }

    function initParticles() {
        particlesArray = [];
        for (let i = 0; i < particleCount; i++) {
            particlesArray.push(new Particle());
        }
    }

    function animateParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particlesArray.forEach(particle => {
            particle.update();
            particle.draw();
        });
        requestAnimationFrame(animateParticles);
    }

    initParticles();
    animateParticles();
</script>

</body>
</html>
