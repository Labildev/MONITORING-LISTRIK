# 🚀 Panduan Deployment Sistem Monitoring Listrik (PHP Native)

Dokumen ini berisi panduan lengkap untuk meng-online-kan (deploy) project **Monitoring Listrik** berbasis **PHP Native** ke layanan **Shared Hosting (cPanel / hPanel)**.

---

## 📡 1. Konsep Alur Komunikasi (Wajib Paham)

Agar website dapat menerima data *real-time* dan tetap bisa menyimpan log tanpa membebani *shared hosting*, arsitekturnya dibuat sebagai berikut:

1. **ESP32** mengirim data listrik ke broker MQTT publik (`broker.hivemq.com`). Ini berfungsi agar dashboard (frontend) bisa memperbarui layar tanpa jeda (_real-time_).
2. **ESP32** JUGA mengirim HTTP POST berisi data sensor ke endpoint PHP Anda (misal: `http://domainanda.com/api/log_data.php`).
3. **PHP** menerima POST tersebut dan menyimpannya secara rapi ke database **MySQL**.

---

## ☁️ 2. Deploy ke Shared Hosting (cPanel / hPanel)

### Langkah 1: Persiapan File ZIP
1. Masuk ke folder proyek Anda di laptop.
2. *Block* semua file dan folder (kecuali folder `esp32` dan `.git`).
3. Klik kanan dan jadikan satu file `.zip`.

### Langkah 2: Upload ke Hosting
1. Login ke panel hosting Anda (cPanel / hPanel).
2. Buka **File Manager** ➔ buka folder `public_html`.
3. Klik tombol **Upload**, lalu pilih file `.zip` yang tadi dibuat.
4. Setelah ter-upload, **Extract** file zip tersebut di dalam `public_html`.

### Langkah 3: Setup Database MySQL
1. Di panel hosting, buka menu **MySQL Databases**.
2. Buat database baru (contoh: `u123456_voltmonitor`).
3. Buat User baru (contoh: `u123456_admin`) beserta password-nya.
4. **Add User To Database** dan centang *All Privileges*.
5. Buka menu **phpMyAdmin** di hosting Anda.
6. Pilih database yang baru dibuat, lalu klik tab **Import**.
7. Upload file `database.sql` dari proyek ini, lalu klik **Go**.

### Langkah 4: Konfigurasi Koneksi PHP
1. Kembali ke **File Manager** ➔ `public_html`.
2. Edit file `koneksi.php`.
3. Ubah bagian koneksi menyesuaikan database yang baru Anda buat:
   ```php
   $host = "localhost";
   $user = "u123456_admin"; // Ganti dengan username MySQL hosting Anda
   $pass = "password_rahasia"; // Ganti dengan password MySQL hosting Anda
   $db   = "u123456_voltmonitor"; // Ganti dengan nama database hosting Anda
   ```
4. Simpan file.

---

## 🛠 3. Konfigurasi Hardware (ESP32)

Agar ESP32 bisa menitipkan pesan ke database hosting Anda, Anda harus mengubah variabel *URL endpoint* di kode **Arduino IDE (`esp32/main/main.ino`)**.

1. Buka file `esp32/main/main.ino`.
2. Cari variabel konfigurasi PHP.
3. Ubah alamat *localhost* menjadi alamat domain asli Anda:

```cpp
// UBAH DARI (Mode Lokal):
// const char* php_server_url = "http://localhost/MONITORING-LISTRIK/api/log_data.php";

// MENJADI (Mode Online):
const char* php_server_url = "http://www.domainanda.com/api/log_data.php";
```

4. _Upload_ ulang kode ke modul ESP32 Anda.

Selesai! Website Monitoring Listrik Anda sekarang sudah online dan siap digunakan 🚀.
