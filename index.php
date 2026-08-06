<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izaz Power Monitor - IoT Smart Energy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css?v=<?php echo time(); ?>">
</head>
<body>

    <nav>
        <a href="#" class="logo">
            <img src="assets/img/Logo_Politeknik_Negeri_Lhokseumawe.png" alt="Logo Poltek" style="height: 40px; margin-right: 8px;">
            Izaz <span>Power</span>
        </a>
        <div class="nav-links">
            <a href="index.php">Beranda</a>
            <a href="fitur.php">Fitur</a>
            <a href="index.php#cara-kerja">Cara Kerja</a>
            <a href="index.php#profil">Profil</a>
        </div>
        <a href="login.php" class="btn-login">Login Admin</a>
    </nav>

    <section id="beranda" class="hero">
        <div class="hero-content">
            <div class="badge">Platform IoT Enterprise Terpercaya</div>
            <h1>Smart Energy Monitoring <br>& Control System</h1>
            <p>Sistem cerdas berbasis Internet of Things untuk memantau konsumsi daya listrik secara langsung (real-time) dan mengendalikan beban jarak jauh dengan aman dan instan.</p>
            <a href="login.php" class="btn-primary">Masuk ke Dashboard ➔</a>
        </div>
    </section>



    <section id="cara-kerja" class="how-it-works">
        <h2 class="section-title">Cara Kerja Sistem</h2>
        <p class="section-subtitle">Sistem menggunakan Arsitektur Hybrid canggih (MQTT + HTTP) untuk memadukan kecepatan <em>real-time</em> tanpa jeda dan keamanan penyimpanan jangka panjang yang andal.</p>

        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Pembacaan Sensor</h3>
                <p>Sensor listrik industrial grade PZEM-004T membaca aliran listrik (Tegangan, Arus, Daya Aktif) secara terus-menerus dan presisi.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Pemrosesan ESP32</h3>
                <p>Mikrokontroler ESP32 mengambil data dari sensor, membaca status relay, dan mengemasnya secara rapi menjadi paket JSON setiap 5 detik.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Transmisi Ganda</h3>
                <p>Data seketika dikirim lewat dua jalur: MQTT (untuk *update* layar super cepat tanpa jeda) dan HTTP POST (untuk ditumpuk ke dalam Database).</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Visualisasi Website</h3>
                <p>Data diterima oleh Browser pengguna, menggerakkan grafik seketika (DOM), atau dipanggil dari MySQL untuk disajikan pada tabel riwayat.</p>
            </div>
        </div>
    </section>

    <section id="profil" class="profile-section">
        <h2 class="section-title">Profil Pengembang</h2>
        <p class="section-subtitle">Sistem Izaz Power Monitor ini dikembangkan sepenuhnya untuk keperluan dan syarat kelulusan Tugas Akhir (TA).</p>

        <div class="profile-card">
            <img src="assets/img/Poto Izaz Abdul.jpeg" alt="Izaz Abdul" class="profile-img">
            <div class="profile-info">
                <h3>Izaz Abdul</h3>
                <p><strong>NIM</strong> : 2022203020007</p>
                <p><strong>ALAMAT</strong> : BIREUEN</p>
                <p><strong>JURUSAN</strong> : TEKNIK ELEKTRO</p>
                <p><strong>PRODI</strong> : TEKNOLOGI REKAYASA JARINGAN TELEKOMUNIKASI (TRJT)</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Izaz Power Monitor - Politeknik Negeri Lhokseumawe. All Rights Reserved.</p>
    </footer>

</body>
</html>
