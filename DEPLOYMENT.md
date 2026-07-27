# 🚀 Panduan Deployment Sistem Monitoring Listrik (Khusus Niagahoster / hPanel)

Dokumen ini berisi panduan lengkap untuk meng-online-kan (deploy) project **Monitoring Listrik (Full-Stack IoT)** secara spesifik ke layanan **Niagahoster / Hostinger (hPanel)** menggunakan fitur **Import dari GitHub**.

---

## 📡 1. Konsep Alur Komunikasi (Wajib Paham)

Karena layanan web hosting seperti Niagahoster memiliki pelindung keamanan (_firewall_) yang ketat, mereka **memblokir port MQTT (1883)** dari luar. Akibatnya, alat ESP32 Anda tidak bisa mengirim data langsung menembus masuk ke Niagahoster.

**Solusinya (Jalan Pintas):**
Sistem ini menggunakan **HiveMQ** sebagai "Kantor Pos Pusat" penengah.

1. **ESP32** mengirim data listrik ke server publik `broker.hivemq.com`.
2. **Backend Node.js** (yang sudah hidup di Niagahoster) diam-diam memantau HiveMQ.
3. Begitu paket data dari ESP32 masuk ke HiveMQ, **Backend langsung menangkapnya**, menyimpannya ke _Database_, dan menampilkannya di _Dashboard_ Anda.

_Catatan: Jika Anda menjalankan aplikasi ini di laptop secara lokal (`npm run dev:full`), sistem otomatis kembali menggunakan MQTT Broker Lokal (Aedes) dan tidak memakai HiveMQ._

---

## 🛠 2. Konfigurasi Hardware (ESP32)

Agar ESP32 bisa menitipkan pesan ke HiveMQ, Anda harus mengubah 1 baris kode di **Arduino IDE (`esp32/main.ino`)** sebelum Anda mempresentasikan/menjalankan alatnya.

1. Buka file kode Arduino Anda.
2. Cari variabel konfigurasi MQTT server.
3. Ubah alamat IP laptop Anda menjadi alamat broker HiveMQ:

```cpp
// UBAH DARI (Mode Lokal):
// const char* mqtt_server = "192.168.1.10";

// MENJADI (Mode Online/Skripsi):
const char* mqtt_server = "broker.hivemq.com";
```

4. _Upload_ ulang kode ke modul ESP32 Anda.

---

## ☁️ 3. Deploy ke Niagahoster (Via hPanel Import GitHub)

Pastikan semua kode terbaru di VS Code sudah Anda _Push_ ke repository GitHub Anda. Setelah itu, ikuti langkah berikut di halaman hPanel:

### Isian Formulir "Periksa Pengaturan Build"

Isi layar konfigurasi Niagahoster Anda secara persis seperti di bawah ini:

1. **Preset framework**: Pilih `Other`
2. **Branch**: Pilih `main`
3. **Versi node**: Pilih `22.x` _(atau `20.x` jika tersedia)_
4. **Root directory**: Biarkan `./`
5. **Pengaturan build dan output**:
   Klik tombol **Ubah**, lalu isi persis seperti ini:
   - **Build command**: `npm run build`
   - **Package manager**: `npm`
   - **Direktori output**: _(Biarkan kosong, atau jika wajib diisi ketik `.`)_
   - **Entry file**: `backend/server.js`
     _(Penjelasan: Entry file adalah kunci utama agar Niagahoster tahu file mana yang harus dihidupkan 24 jam)._
6. **Variabel environment**:
   Klik tombol **Tambahkan**, lalu ketik:
   - Kolom **Nama (Key)**: `NODE_ENV`
   - Kolom **Nilai (Value)**: `production`
     _(Penjelasan: Variabel ini yang memberi tahu Backend Anda untuk otomatis mencari data ke HiveMQ, bukan ke localhost)._
7. Terakhir, klik tombol biru **Deploy**.

Tunggu sekitar 3-5 menit sampai proses _build_ dan instalasi modul di server Niagahoster selesai.

Jika proses sukses dan indikator sudah hijau, web _Dashboard_ Anda siap diakses (misalnya lewat `monitoringlistrik.tanggapdata.com`) dan akan langsung terhubung secara _real-time_ dengan alat ESP32 Anda melalui HiveMQ! 🚀
