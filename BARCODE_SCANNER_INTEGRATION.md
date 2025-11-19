# Integrasi Scanner Barcode POS

## Ikhtisar

Dokumen ini menjelaskan solusi integrasi scanner barcode untuk aplikasi Point of Sale (POS) Anda. Solusi ini terdiri dari dua komponen utama:

1.  **Aplikasi Mobile Scanner (Flutter)**: Aplikasi Android yang memungkinkan pemindaian barcode menggunakan kamera perangkat, input scanner USB eksternal, atau Bluetooth.
2.  **POS Bridge Server (Python)**: Sebuah aplikasi server kecil yang berjalan di komputer kasir. Server ini menerima data barcode dari aplikasi mobile dan "mengetikkan" barcode tersebut ke aplikasi POS Anda, meniru input keyboard.

Arsitektur ini dirancang untuk menyediakan integrasi yang fleksibel dan stabil tanpa perlu memodifikasi kode inti aplikasi POS Laravel Anda.

## Komponen Solusi

### 1. Aplikasi Mobile Scanner (Flutter)

*   **Lokasi**: `pos_scanner/`
*   **Fungsionalitas**:
    *   Memindai barcode/QR code menggunakan kamera belakang perangkat.
    *   Menerima input dari scanner barcode USB yang terhubung (berfungsi sebagai keyboard HID).
    *   Mengirim data barcode yang dipindai ke POS Bridge Server melalui Wi-Fi (HTTP) atau Bluetooth Low Energy (BLE).
    *   Halaman pengaturan untuk mengonfigurasi jenis koneksi (Wi-Fi/Bluetooth), alamat IP server, port, dan pemilihan perangkat Bluetooth.
*   **Teknologi Utama**: Flutter, `mobile_scanner`, `permission_handler`, `provider`, `http`, `flutter_blue_plus`.

### 2. POS Bridge Server (Python)

*   **Lokasi**: `pos_bridge_server/`
*   **Fungsionalitas**:
    *   Berjalan sebagai proses latar belakang di komputer kasir.
    *   Menyediakan server HTTP untuk menerima data barcode melalui Wi-Fi.
    *   Menyediakan server Bluetooth LE untuk menerima data barcode melalui Bluetooth.
    *   Mensimulasikan input keyboard (mengetik barcode dan menekan Enter) ke aplikasi apa pun yang sedang fokus di layar komputer.
*   **Teknologi Utama**: Python 3, `Flask`, `bleak`, `pynput`.

## Panduan Setup

Ikuti langkah-langkah di bawah ini untuk menyiapkan seluruh sistem.

### Bagian A: Setup di Komputer Kasir (untuk POS Bridge Server)

1.  **Instalasi Python 3:**
    *   Jika belum terinstal, unduh dan instal Python 3 dari situs resmi: [python.org/downloads/](https://www.python.org/downloads/).
    *   **Penting**: Saat proses instalasi, pastikan Anda mencentang opsi **"Add Python to PATH"** agar Python dapat diakses dari Command Prompt.

2.  **Siapkan Folder Server:**
    *   Buat sebuah folder baru di komputer kasir Anda, misalnya `C:\POS_Bridge_Server`.
    *   Salin dua file berikut yang disediakan oleh Gemini CLI ke dalam folder tersebut:
        *   `pos_bridge_server.py`
        *   `requirements.txt`

3.  **Instalasi Library Python:**
    *   Buka **Command Prompt** (Cari "cmd" di Start Menu Windows).
    *   Masuk ke direktori folder server Anda:
        ```bash
        cd C:\POS_Bridge_Server
        ```
    *   Jalankan perintah berikut untuk menginstal semua library Python yang dibutuhkan:
        ```bash
        pip install -r requirements.txt
        ```
    *   Tunggu hingga proses instalasi selesai.

4.  **Temukan Alamat IP Lokal Komputer:**
    *   Masih di Command Prompt, ketik perintah berikut:
        ```bash
        ipconfig
        ```
    *   Cari bagian yang relevan (misalnya "Wireless LAN adapter Wi-Fi" atau "Ethernet adapter Ethernet").
    *   Catat nilai **`IPv4 Address`** (contoh: `192.168.1.100`). Alamat ini akan digunakan untuk konfigurasi Wi-Fi di aplikasi Flutter.

5.  **Nonaktifkan Mode Tidur (Sleep Mode):**
    *   Pastikan komputer Anda tidak masuk mode tidur (sleep) karena ini akan menghentikan server dan koneksi Bluetooth. Anda bisa mengubah pengaturan daya di Windows.

6.  **Jalankan POS Bridge Server:**
    *   Di Command Prompt yang sama, jalankan server dengan perintah:
        ```bash
        python pos_bridge_server.py
        ```
    *   Server akan mulai berjalan dan menampilkan pesan konfirmasi. **Jendela Command Prompt ini harus tetap terbuka** selama Anda ingin menggunakan fungsionalitas scanner.
    *   **Firewall**: Jika ada peringatan Firewall dari Windows, pilih **"Allow access"** untuk jaringan privat agar server dapat menerima koneksi.

### Bagian B: Setup dan Konfigurasi Aplikasi Mobile Scanner (Flutter)

1.  **Pastikan Flutter Terinstal:**
    *   Pastikan Anda memiliki Flutter SDK dan Android Studio (atau editor lain) yang terinstal dan dikonfigurasi di lingkungan pengembangan Anda.

2.  **Dapatkan Kode Aplikasi:**
    *   Direktori aplikasi scanner Flutter terletak di `pos_scanner/`.

3.  **Jalankan Aplikasi:**
    *   Hubungkan perangkat Android fisik Anda ke komputer pengembangan.
    *   Buka terminal di direktori root proyek Flutter (`pos_scanner/`).
    *   Jalankan aplikasi:
        ```bash
        flutter run
        ```
    *   Saat aplikasi pertama kali dibuka, ia akan meminta izin kamera dan Bluetooth. **Izinkan semua izin** tersebut.

4.  **Konfigurasi Koneksi di Aplikasi Flutter:**
    *   Setelah aplikasi berjalan di perangkat Anda, tekan ikon **Pengaturan** (ikon gerigi) di pojok kanan atas `AppBar` halaman utama.

    *   **Untuk Koneksi Wi-Fi:**
        *   Di halaman pengaturan, pilih **"Wi-Fi"** dari dropdown "Tipe Koneksi".
        *   Masukkan **Alamat IP** yang Anda catat dari `ipconfig` komputer kasir Anda.
        *   Masukkan **Port** server: `5000`.
        *   Tekan tombol **"Simpan"**.

    *   **Untuk Koneksi Bluetooth:**
        *   Pastikan Bluetooth di HP dan di Komputer Kasir Anda dalam keadaan aktif.
        *   Di halaman pengaturan, pilih **"Bluetooth"** dari dropdown "Tipe Koneksi".
        *   Tekan tombol **"Cari"**. Aplikasi akan membuka halaman pencarian perangkat Bluetooth.
        *   Tunggu hingga nama komputer kasir Anda muncul di daftar perangkat yang ditemukan (biasanya dengan nama PC Anda).
        *   Ketuk nama perangkat komputer Anda di daftar.
        *   Setelah kembali ke halaman pengaturan, tekan tombol **"Simpan"**.

## Panduan Penggunaan

1.  **Mulai POS Bridge Server**: Pastikan `pos_bridge_server.py` sedang berjalan di Command Prompt komputer kasir Anda (lihat **Bagian A, Langkah 6**).
2.  **Fokuskan Aplikasi POS**: Buka aplikasi POS Laravel Anda di komputer kasir dan pastikan **kolom input barcode (atau kolom teks lainnya yang sedang aktif)** memiliki fokus (kursor berkedip di dalamnya).
3.  **Gunakan Aplikasi Mobile Scanner**:
    *   Buka aplikasi **POS Scanner** di perangkat Android Anda.
    *   Pindai barcode menggunakan **kamera perangkat**, atau **scanner USB eksternal** yang terhubung ke HP, atau pastikan koneksi Bluetooth/Wi-Fi aktif.
    *   Setelah barcode dipindai dan berhasil dikirim, Anda akan melihat barcode tersebut secara otomatis "diketikkan" ke dalam aplikasi POS di komputer, diikuti dengan penekanan tombol Enter.
    *   Status pengiriman (Berhasil/Gagal) akan muncul sebentar di bagian bawah layar aplikasi Flutter.

## Catatan Penting & Pemecahan Masalah

*   **Firewall**: Pastikan firewall Windows Anda tidak memblokir koneksi masuk ke POS Bridge Server.
*   **IP Address**: Jika alamat IP komputer kasir Anda berubah (misalnya setelah restart router), Anda harus memperbarui alamat IP di pengaturan aplikasi Flutter.
*   **Bluetooth**: Jika koneksi Bluetooth bermasalah, pastikan Bluetooth di kedua perangkat aktif, dan coba ulangi proses pencarian & pemilihan perangkat.
*   **Fokus Aplikasi POS**: Jika barcode tidak muncul di aplikasi POS, pastikan aplikasi POS sedang dalam mode fokus dan memiliki kolom teks yang aktif.
*   **Debugging**: Pesan output di Command Prompt tempat Anda menjalankan `pos_bridge_server.py` dapat memberikan petunjuk jika ada masalah koneksi atau pengiriman.

---
Dibuat oleh Gemini.
