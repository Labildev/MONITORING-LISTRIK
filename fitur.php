<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur - Izaz Power Monitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css?v=<?php echo time(); ?>">
    <style>
        .features-page-header {
            text-align: center;
            padding: 4rem 5% 2rem;
            background: #ffffff;
        }
        .features-page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 1rem;
        }
        .features-page-header p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .features-grid {
            margin-bottom: 4rem;
        }
        .features {
            padding-top: 2rem;
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo">
            <img src="assets/img/Logo_Politeknik_Negeri_Lhokseumawe.png" alt="Logo Poltek" style="height: 40px; margin-right: 8px;">
            Izaz <span>Power</span>
        </a>
        <div class="nav-links">
            <a href="index.php">Beranda</a>
            <a href="fitur.php" style="color: var(--primary); font-weight: 600;">Fitur</a>
            <a href="index.php#cara-kerja">Cara Kerja</a>
            <a href="index.php#profil">Profil</a>
        </div>
        <a href="login.php" class="btn-login">Login Admin</a>
    </nav>

    <div class="features-page-header">
        <h1>Fitur Lengkap Sistem</h1>
        <p>Jelajahi seluruh fitur yang membuat Izaz Power Monitor menjadi platform pengawasan energi paling responsif dan aman yang pernah Anda gunakan.</p>
    </div>

    <section id="fitur" class="features">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Real-time Analytics</h3>
                <p>Pantau tegangan, arus, daya aktif, dan energi (kWh) secara real-time. Grafik langsung bergerak seketika layaknya monitor di rumah sakit tanpa perlu refresh halaman web.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎛️</div>
                <h3>Kendali Jarak Jauh</h3>
                <p>Kendalikan hingga 4 perangkat listrik (relay) langsung dari dashboard di mana pun Anda berada. Respon sakelar instan dan hanya membutuhkan waktu sepersekon detik.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Keamanan Ekstra</h3>
                <p>Endpoint sistem dilindungi API Key dan antarmuka diamankan dengan autentikasi login terenkripsi. Tidak ada orang asing yang bisa mengambil alih kontrol kelistrikan Anda.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📈</div>
                <h3>Riwayat Data Permanen</h3>
                <p>Setiap 5 detik, semua data direkam secara permanen ke dalam Database MySQL di Hosting. Anda bisa menganalisa riwayat pemakaian energi kapan saja melalui tabel yang rapi.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Responsif & Mobile Friendly</h3>
                <p>Akses dashboard melalui smartphone, tablet, atau komputer dengan tampilan yang akan otomatis menyesuaikan ukuran layar tanpa mengurangi fungsionalitas dan estetika.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚙️</div>
                <h3>Pengaturan Nama Beban</h3>
                <p>Anda dapat memberikan nama kustom pada masing-masing relay (misalnya "Lampu Teras" atau "Pompa Air") langsung dari pengaturan dashboard tanpa memprogram ulang ESP32.</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Izaz Power Monitor - Politeknik Negeri Lhokseumawe. All Rights Reserved.</p>
    </footer>

</body>
</html>
