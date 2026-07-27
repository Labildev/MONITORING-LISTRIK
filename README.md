# VoltMonitor — IoT Power Analytics & Control Platform

VoltMonitor adalah sistem pemantauan telemetri dan pengendalian beban listrik secara *real-time* berbasis Internet of Things (IoT). Platform ini mengintegrasikan mikrokontroler **ESP32**, sensor **PZEM-004T**, protokol komunikasi **MQTT**, backend **Node.js (Express & Socket.IO)**, serta antarmuka web modern berbahan **React (Vite)** berstandar *enterprise*.

Project ini dikelola menggunakan **NPM Workspaces (Monorepo)** untuk mempermudah eksekusi frontend dan backend dalam satu repositori.

---

## Struktur Direktori Proyek

```text
MONITORING-LISTRIK/
├── esp32/            # Firmware C++ (main.ino) untuk ESP32 (Arduino IDE)
├── backend/          # Server Node.js (Express API, Socket.IO, SQLite3 DB, Seeder)
├── frontend/         # Dashboard Web React (Vite, Recharts, Lucide Icons, Clean Design)
├── simulator/        # Script simulasi telemetri MQTT untuk pengujian tanpa alat
├── package.json      # Root configuration & NPM Workspaces scripts
└── README.md         # Dokumentasi resmi proyek
```

---

## Panduan Menjalankan Aplikasi

### 1. Instalasi Dependensi
Jalankan perintah ini pada folder utama (*root*) untuk memasang seluruh paket frontend dan backend sekaligus:
```bash
npm install
```

### 2. Inisialisasi Database & Seeder User
Sebelum menjalankan server pertama kali, pastikan database diisi akun administrator:
```bash
# Inisialisasi user bawaan (admin / password)
npm run seed -w backend
```

> **Kredensial Default Login:**
> - **Username**: `admin`
> - **Password**: `password`

### 3. Menjalankan Aplikasi
Jalankan backend dan frontend secara bersamaan dalam satu terminal:
```bash
npm run dev
```

- **Frontend Web**: `http://localhost:5173` (di-proxy otomatis ke backend)
- **Backend API**: `http://localhost:3001`

### 4. Menjalankan dengan Simulator Telemetri
Jika perangkat keras ESP32 belum terpasang, Anda dapat menguji data daya real-time dengan simulator:
```bash
npm run dev:full
```

---

## Daftar Perintah Maintenance & Scripts

| Perintah | Deskripsi |
| :--- | :--- |
| `npm run dev` | Menjalankan Frontend & Backend secara bersamaan |
| `npm run dev:full` | Menjalankan Frontend, Backend, dan Simulator MQTT |
| `npm run backend` | Menjalankan Backend Express Server saja |
| `npm run frontend` | Menjalankan Frontend Vite Server saja |
| `npm run simulator` | Menjalankan script pengirim data dummy MQTT |
| `npm run seed -w backend` | Mengisi ulang akun admin bawaan ke database |
| `npm run reset -w backend` | Membersihkan riwayat data sensor tanpa menghapus user |

---

## Skema Hardware & Pinout ESP32

1. Buka `esp32/main.ino` di Arduino IDE.
2. Library yang dibutuhkan: `WiFi`, `PubSubClient`, `PZEM004Tv30`, `LiquidCrystal_I2C`.
3. Konfigurasi Wiring Hardware:
   - **PZEM-004T (HardwareSerial2)**: TX PZEM ➔ GPIO 16 (RX2), RX PZEM ➔ GPIO 17 (TX2)
   - **LCD I2C (20x4 / 16x2)**: SDA ➔ GPIO 21, SCL ➔ GPIO 22
   - **Modul Relay (Active LOW)**: GPIO 25, 26, 27, 14
4. Broker MQTT Public bawaan: `broker.hivemq.com` (Port `1883`).

---

## Stack Teknologi

- **Hardware & Firmware**: ESP32, Sensor PZEM-004T v3.0, LCD I2C, Modul Relay.
- **Komunikasi Data**: MQTT Protocol, Socket.IO WebSockets.
- **Backend**: Node.js, Express.js, SQLite3, BCrypt.js Password Hashing.
- **Frontend**: React.js, Vite, Recharts, Lucide Icons, Clean Industrial Design System (Inter & JetBrains Mono typography).
