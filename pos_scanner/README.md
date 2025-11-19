# Aplikasi POS Barcode Scanner (Flutter)

Aplikasi ini berfungsi sebagai klien scanner barcode untuk sistem POS Anda. Aplikasi ini dirancang untuk berjalan di perangkat Android, memungkinkan pengguna memindai barcode menggunakan kamera perangkat, scanner USB eksternal, atau koneksi Bluetooth, dan mengirimkan data tersebut ke sebuah "POS Bridge Server" yang berjalan di komputer kasir.

## Fitur Utama

*   **Pemindaian Barcode Cepat**: Menggunakan `mobile_scanner` untuk pemindaian barcode yang efisien melalui kamera perangkat.
*   **Dukungan Scanner USB**: Mampu menangkap input barcode dari scanner USB fisik yang terhubung (berfungsi sebagai keyboard HID).
*   **Opsi Konektivitas Fleksibel**: Mengirim data barcode melalui:
    *   **Wi-Fi (HTTP)**: Ke alamat IP dan port server yang dapat dikonfigurasi.
    *   **Bluetooth Low Energy (BLE)**: Ke perangkat Bluetooth yang dipilih dari daftar perangkat terdekat.
*   **Halaman Pengaturan**: Antarmuka pengguna untuk mengonfigurasi pengaturan koneksi (jenis koneksi, detail Wi-Fi, pemilihan perangkat Bluetooth).
*   **Penanganan Izin**: Mengelola izin kamera dan Bluetooth secara dinamis.
*   **Umpan Balik Pengiriman**: Memberikan umpan balik visual tentang status pengiriman barcode (berhasil/gagal).

## Struktur Proyek

Proyek ini mengikuti struktur folder berbasis fitur untuk memisahkan logika dan UI secara modular:

```
pos_scanner/
├── lib/
│   ├── src/
│   │   ├── core/               # Kode inti, model data, dan layanan abstrak
│   │   │   ├── models/         # Definisi model data (mis. AppSettings)
│   │   │   └── services/       # Layanan aplikasi (mis. SettingsService, CommunicationService)
│   │   ├── features/           # Modul fitur-fitur aplikasi
│   │   │   ├── home/           # Halaman utama aplikasi (HomePage)
│   │   │   ├── scanner/        # Komponen UI scanner (ScannerView)
│   │   │   └── settings/       # Halaman pengaturan (SettingsPage, BluetoothDiscoveryPage)
│   │   └── utils/              # (Saat ini kosong, untuk fungsi-fungsi bantuan umum)
│   └── main.dart               # Titik masuk utama aplikasi
├── android/            # Konfigurasi spesifik Android
├── ios/                # Konfigurasi spesifik iOS (saat ini tidak ditargetkan)
├── pubspec.yaml        # Definisi dependensi proyek
└── README.md           # Dokumentasi proyek ini
```

## Komponen Kunci

### `lib/main.dart`

*   Titik masuk aplikasi.
*   Menginisialisasi `MaterialApp` dan mendefinisikan tema aplikasi.
*   Menggunakan `ChangeNotifierProvider` dari paket `provider` untuk menyediakan `SettingsService` ke seluruh pohon widget, memungkinkan manajemen status terpusat untuk pengaturan aplikasi.

### `lib/src/features/home/home_page.dart`

*   Halaman utama aplikasi yang ditampilkan setelah startup.
*   Menangani permintaan izin kamera.
*   Mengimplementasikan `RawKeyboardListener` untuk mendeteksi input dari scanner USB eksternal.
*   Menampilkan `ScannerView` jika izin kamera diberikan, atau pesan permintaan izin jika tidak.
*   Berisi logika sentral `_handleBarcode` yang memproses barcode yang dipindai (dari kamera atau USB) dan memicu pengiriman melalui `CommunicationService`.
*   Menyediakan tombol ikon pengaturan di `AppBar` untuk navigasi ke `SettingsPage`.

### `lib/src/features/scanner/scanner_view.dart`

*   Widget UI yang bertanggung jawab untuk menampilkan pratinjau kamera dan melakukan pemindaian barcode.
*   Menggunakan `mobile_scanner` untuk fungsionalitas pemindaian.
*   Menerima callback `onBarcodeDetected` yang dipanggil ketika barcode berhasil dipindai.
*   Menampilkan *overlay* visual untuk area pemindaian.

### `lib/src/features/settings/settings_page.dart`

*   Halaman pengaturan aplikasi.
*   Memungkinkan pengguna memilih jenis koneksi (Wi-Fi atau Bluetooth).
*   Menyediakan input form untuk konfigurasi Wi-Fi (alamat IP dan port server).
*   Menyediakan opsi untuk membuka `BluetoothDiscoveryPage` guna memilih perangkat Bluetooth.
*   Menggunakan `SettingsService` untuk membaca dan menyimpan pengaturan aplikasi.

### `lib/src/features/settings/bluetooth_discovery_page.dart`

*   Halaman yang didedikasikan untuk mencari dan menampilkan perangkat Bluetooth Low Energy (BLE) yang tersedia di sekitar.
*   Menggunakan `flutter_blue_plus` untuk melakukan pemindaian BLE.
*   Menampilkan daftar perangkat yang ditemukan.
*   Memungkinkan pengguna untuk memilih perangkat, yang kemudian akan mengembalikan `BluetoothDevice` ke `SettingsPage`.

### `lib/src/core/models/settings_model.dart`

*   Mendefinisikan enum `ConnectionType` (Wi-Fi, Bluetooth).
*   Mendefinisikan kelas `AppSettings` sebagai model data untuk menyimpan semua pengaturan koneksi aplikasi (IP Wi-Fi, port, ID perangkat Bluetooth yang dipilih, dll.).

### `lib/src/core/services/settings_service.dart`

*   Kelas `ChangeNotifier` yang bertindak sebagai penyedia pengaturan aplikasi.
*   Menyimpan instance tunggal `AppSettings` dan menyediakan metode untuk memperbarui dan memberi tahu listener tentang perubahan.

### `lib/src/core/services/communication_service.dart`

*   **`abstract class CommunicationService`**: Mendefinisikan antarmuka (kontrak) untuk semua layanan komunikasi, dengan metode `sendBarcode(String barcode)`.
*   **`class WifiCommunicationService`**: Implementasi `CommunicationService` untuk mengirim data barcode melalui HTTP POST menggunakan paket `http`.
*   **`class BluetoothCommunicationService`**: Implementasi `CommunicationService` untuk mengirim data barcode melalui Bluetooth LE menggunakan `flutter_blue_plus`. **Penting:** UUID layanan dan karakteristik yang digunakan di sini harus sesuai dengan yang di-advertise oleh POS Bridge Server Python.
*   **`class CommunicationServiceFactory`**: Sebuah kelas pembantu untuk membuat instance `CommunicationService` yang tepat berdasarkan `AppSettings` yang sedang aktif.

## Dependensi

Proyek ini menggunakan dependensi berikut, yang didefinisikan dalam `pubspec.yaml`:

*   `flutter_blue_plus`: Untuk fungsionalitas Bluetooth LE.
*   `http`: Untuk membuat permintaan HTTP (koneksi Wi-Fi).
*   `mobile_scanner`: Untuk pemindaian barcode menggunakan kamera.
*   `permission_handler`: Untuk manajemen izin platform (kamera, Bluetooth).
*   `provider`: Untuk manajemen status aplikasi.

## Setup & Menjalankan Aplikasi (Untuk Developer)

1.  **Clone Repositori**: Jika Anda mendapatkan kode ini dari repositori Git, lakukan clone terlebih dahulu.
2.  **Masuk ke Direktori Proyek**: Buka terminal dan navigasi ke direktori `pos_scanner/`.
3.  **Dapatkan Dependensi**: Jalankan perintah berikut untuk mengunduh semua paket Dart/Flutter yang dibutuhkan:
    ```bash
    flutter pub get
    ```
4.  **Konfigurasi Android (Wajib!):**
    *   Buka `android/app/build.gradle.kts` dan pastikan `minSdk` diatur ke `21` atau lebih tinggi.
    *   Buka `android/app/src/main/AndroidManifest.xml` dan pastikan izin `CAMERA`, `BLUETOOTH_SCAN`, `BLUETOOTH_CONNECT`, dan `ACCESS_FINE_LOCATION` telah ditambahkan di luar tag `<application>`.
5.  **Jalankan Aplikasi**: Sambungkan perangkat Android fisik (atau emulator) Anda.
    ```bash
    flutter run
    ```
6.  **Izin Awal**: Aplikasi akan meminta izin Kamera dan Bluetooth saat pertama kali dijalankan. Pastikan untuk mengizinkannya.

## Catatan Penting

*   **POS Bridge Server Diperlukan**: Aplikasi ini membutuhkan **POS Bridge Server (Python)** yang berjalan di komputer kasir Anda agar dapat mengirim data barcode. Pastikan server tersebut sudah disiapkan dan berjalan sesuai dokumentasinya.
*   **Sinkronisasi UUID Bluetooth**: UUID layanan dan karakteristik Bluetooth yang digunakan dalam `BluetoothCommunicationService` telah disinkronkan dengan `pos_bridge_server.py`. Jika Anda mengubah UUID di server Python, Anda juga harus memperbarui di sini.
*   **Koneksi Jaringan**: Untuk koneksi Wi-Fi, pastikan perangkat Android Anda dan komputer kasir berada dalam jaringan lokal yang sama dan dapat saling berkomunikasi.

---
Dibuat oleh Gemini.