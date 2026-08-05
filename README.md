# Izaz Power Monitor — IoT Power Analytics & Control Platform (PHP Native)

Izaz Power Monitor adalah sistem pemantauan telemetri dan pengendalian beban listrik secara *real-time* berbasis Internet of Things (IoT). Platform ini mengintegrasikan mikrokontroler **ESP32**, sensor **PZEM-004T**, protokol komunikasi **MQTT**, serta website berbasis **PHP Native** dan **MySQL**.

Aplikasi web ini sangat cocok untuk di-*deploy* di *Shared Hosting* konvensional (cPanel/Plesk) maupun dijalankan di localhost (XAMPP/Laragon).

---

## Struktur Direktori Proyek

```text
MONITORING-LISTRIK/
├── esp32/            # Firmware C++ (main.ino) untuk ESP32 (Arduino IDE)
├── api/              # Kumpulan Endpoint API PHP (log_data.php, get_data.php, dll)
├── assets/           # Folder penyimpanan CSS dan gambar
├── database.sql      # Skema tabel database MySQL
├── koneksi.php       # Konfigurasi koneksi database PDO
├── index.php         # Halaman Dashboard Utama (Real-time MQTT & Chart.js)
├── login.php         # Halaman Login Admin
└── README.md         # Dokumentasi resmi proyek
```

---

## Panduan Instalasi dan Menjalankan Aplikasi

### 1. Persiapan Database MySQL
1. Buat database baru di MySQL (misal: `izazmonitor`).
2. Import file `database.sql` ke dalam database yang baru dibuat.
3. Buka `koneksi.php` dan sesuaikan kredensial koneksi MySQL:
   ```php
   $host = "localhost";
   $user = "root"; // username db anda
   $pass = "";     // password db anda
   $db   = "izazmonitor";
   ```

> **Kredensial Default Login Admin:**
> - **Username**: `admin`
> - **Password**: `password`

### 2. Menjalankan Website (Localhost)
1. Pindahkan folder `MONITORING-LISTRIK` ke dalam folder root web server lokal Anda (`htdocs` pada XAMPP atau `www` pada Laragon).
2. Buka browser dan akses: `http://localhost/MONITORING-LISTRIK/`

### 3. Konfigurasi ESP32
1. Buka `esp32/main/main.ino` menggunakan Arduino IDE.
2. Pastikan library berikut sudah terinstal: `WiFi`, `PubSubClient`, `PZEM004Tv30`, `LiquidCrystal_I2C`, dan `HTTPClient`.
3. Sesuaikan konfigurasi WiFi, MQTT, dan URL PHP Server:
   ```cpp
   const char* ssid     = "NAMA_WIFI_ANDA";
   const char* password = "PASSWORD_WIFI_ANDA";
   
   const char* php_server_url = "http://localhost/MONITORING-LISTRIK/api/log_data.php"; // Ganti dengan URL domain hosting jika sudah online
   ```
4. Upload firmware ke ESP32.

---

## Skema Hardware & Pinout ESP32

- **PZEM-004T (HardwareSerial2)**: TX PZEM ➔ GPIO 16 (RX2), RX PZEM ➔ GPIO 17 (TX2)
- **LCD I2C (20x4 / 16x2)**: SDA ➔ GPIO 21, SCL ➔ GPIO 22
- **Modul Relay (Active LOW)**: GPIO 25, 26, 27, 14

---

## Arsitektur Data (Tanpa Node.js)
Berbeda dengan versi sebelumnya yang menggunakan Node.js:
1. **Real-time Web**: Frontend (Dashboard) terhubung *langsung* ke MQTT Broker (HiveMQ) via WebSocket Port 8000 dengan **Paho MQTT JavaScript**.
2. **Kendali Relay**: Dashboard men-publish ke topik MQTT `labil_listrik_123/control/relayX`.
3. **Penyimpanan Data (MySQL)**: ESP32 mengirim HTTP POST secara berkala ke endpoint `api/log_data.php` (sambil tetap mengirim publish MQTT agar dashboard bereaksi cepat tanpa delay).
