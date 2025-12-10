# Laporan Bug dan Isu - 2025-12-04

Berikut adalah daftar bug dan isu yang ditemukan selama pengujian aplikasi Nameless.POS di `localhost:8000` pada tanggal 4 Desember 2025.

## Daftar Bug dan Isu yang Ditemukan

1.  **Bug 1: Link "All Products" mengalami timeout.**
    *   **Deskripsi:** Saat mengklik tautan "All Products" dari halaman beranda, halaman tidak memuat dalam batas waktu yang ditentukan (5000ms). Ini menunjukkan masalah dengan navigasi melalui tautan tersebut, mungkin karena URL yang salah atau masalah pemrosesan sisi server.
    *   **Prioritas:** Tinggi (mempengaruhi navigasi dasar ke fitur penting).

2.  **Bug 2: Peringatan DataTables di halaman "All Products".**
    *   **Deskripsi:** Muncul dialog peringatan DataTables: `table id=product-table - Requested unknown parameter 'brand.brand_name' for row 0, column 2.`. Ini menunjukkan bahwa library DataTables mencoba mengakses parameter data (`brand.brand_name`) yang tidak ada dalam sumber data tabel, kemungkinan karena masalah struktur data yang dikirim dari server atau inisialisasi JavaScript yang salah.
    *   **Prioritas:** Sedang (mempengaruhi tampilan data produk dan indikasi data tidak lengkap).

3.  **Bug 3: Fungsi `maskMoney` tidak ditemukan di halaman "Create Product".**
    *   **Deskripsi:** Terdapat error JavaScript (`$(...).maskMoney is not a function`) yang menunjukkan bahwa fungsi `maskMoney` tidak terdefinisi atau library yang menyediakannya tidak dimuat dengan benar. Fungsi ini kemungkinan digunakan untuk memformat input mata uang, sehingga akan mencegah pengguna memasukkan nilai moneter dengan benar.
    *   **Prioritas:** Tinggi (mempengaruhi fungsionalitas inti pembuatan produk dengan harga).

4.  **Bug 4: Link "Create Sale" mengalami timeout.**
    *   **Deskripsi:** Saat mengklik tautan "Create Sale" dari sidebar, halaman tidak memuat dalam batas waktu yang ditentukan (5000ms). Mirip dengan Bug 1, menunjukkan masalah navigasi.
    *   **Prioritas:** Tinggi (mempengaruhi navigasi dasar ke fitur penting).

5.  **Isu 5: Peringatan "Scanner settings or search input not found" di halaman "Create Sale" dan "Create Purchase".**
    *   **Deskripsi:** Muncul peringatan `Scanner settings or search input not found. Defaulting to camera mode.` Ini menunjukkan bahwa pengaturan scanner atau elemen input pencarian tidak ditemukan, yang mungkin mengindikasikan konfigurasi yang tidak optimal atau elemen yang hilang untuk fitur pemindaian kode batang.
    *   **Prioritas:** Menengah (peringatan konfigurasi, dapat memengaruhi fungsionalitas scanner).

6.  **Isu 6: Penggunaan `<label for=FORM_ELEMENT>` yang salah di halaman "Create Sale", "Create Purchase", dan "General Settings".**
    *   **Deskripsi:** Terdapat beberapa isu (`Incorrect use of <label for=FORM_ELEMENT>`) yang terkait dengan penggunaan atribut `for` pada tag `<label>` yang tidak sesuai dengan elemen inputnya. Ini adalah masalah aksesibilitas dan sebaiknya diperbaiki untuk meningkatkan pengalaman pengguna, terutama bagi pengguna yang menggunakan teknologi bantu.
    *   **Prioritas:** Rendah (masalah aksesibilitas, tidak menghambat fungsionalitas secara langsung).

7.  **Bug 7: Link "Create Purchase" mengalami timeout.**
    *   **Deskripsi:** Saat mengklik tautan "Create Purchase" dari sidebar, halaman tidak memuat dalam batas waktu yang ditentukan (5000ms). Mirip dengan Bug 1 dan Bug 4, menunjukkan masalah navigasi.
    *   **Prioritas:** Tinggi (mempengaruhi navigasi dasar ke fitur penting).

8.  **Bug 8: Link "General Settings" mengalami timeout.**
    *   **Deskripsi:** Saat mengklik tautan "General Settings" dari sidebar, halaman tidak memuat dalam batas waktu yang ditentukan (5000ms). Mirip dengan Bug 1, Bug 4, dan Bug 7, menunjukkan masalah navigasi.
    *   **Prioritas:** Tinggi (mempengaruhi navigasi dasar ke fitur penting).

9.  **Bug 9: Gagal memuat sumber daya di halaman "General Settings" (404 Not Found).**
    *   **Deskripsi:** Terdapat error `Failed to load resource: the server responded with a status of 404 (Not Found)` yang mengindikasikan beberapa sumber daya (kemungkinan gambar logo `site_logo_1762858186.png` dan `login_logo_1762858186.png`) tidak ditemukan oleh server. Ini dapat menyebabkan elemen UI rusak atau tampilan yang tidak lengkap.
    *   **Prioritas:** Sedang (mempengaruhi tampilan UI dan pengalaman pengguna).

## Task List Perbaikan Bug

- [ ] **Perbaiki Bug 1: Link "All Products" mengalami timeout.**
    - [ ] Investigasi mengapa tautan tidak memuat halaman atau mengapa terjadi timeout.
    - [ ] Pastikan URL tautan benar dan penanganan rute sisi server berfungsi.
- [ ] **Perbaiki Bug 2: Peringatan DataTables di halaman "All Products".**
    - [ ] Periksa data yang dikirim ke DataTables untuk memastikan bahwa properti `brand.brand_name` ada atau tangani secara graceful jika tidak ada.
    - [ ] Periksa inisialisasi DataTables untuk memastikan konfigurasi kolom sesuai dengan data yang tersedia.
- [ ] **Perbaiki Bug 3: Fungsi `maskMoney` tidak ditemukan di halaman "Create Product".**
    - [ ] Pastikan library `maskMoney` (atau yang setara) dimuat dengan benar sebelum digunakan.
    - [ ] Periksa dependensi JavaScript dan urutan pemuatan skrip.
- [ ] **Perbaiki Bug 4: Link "Create Sale" mengalami timeout.**
    - [ ] Investigasi mengapa tautan tidak memuat halaman atau mengapa terjadi timeout.
    - [ ] Pastikan URL tautan benar dan penanganan rute sisi server berfungsi.
- [ ] **Perbaiki Isu 5: Peringatan "Scanner settings or search input not found".**
    - [ ] Periksa konfigurasi scanner dan pastikan elemen input yang diperlukan ada.
    - [ ] Implementasikan penanganan kesalahan yang lebih baik untuk konfigurasi scanner.
- [ ] **Perbaiki Isu 6: Penggunaan `<label for=FORM_ELEMENT>` yang salah.**
    - [ ] Koreksi atribut `for` pada semua tag `<label>` agar sesuai dengan `id` elemen input yang relevan di halaman yang terpengaruh.
- [ ] **Perbaiki Bug 7: Link "Create Purchase" mengalami timeout.**
    - [ ] Investigasi mengapa tautan tidak memuat halaman atau mengapa terjadi timeout.
    - [ ] Pastikan URL tautan benar dan penanganan rute sisi server berfungsi.
- [ ] **Perbaiki Bug 8: Link "General Settings" mengalami timeout.**
    - [ ] Investigasi mengapa tautan tidak memuat halaman atau mengapa terjadi timeout.
    - [ ] Pastikan URL tautan benar dan penanganan rute sisi server berfungsi.
- [ ] **Perbaiki Bug 9: Gagal memuat sumber daya di halaman "General Settings" (404 Not Found).**
    - [ ] Periksa jalur ke file gambar logo yang hilang.
    - [ ] Pastikan file gambar ada di lokasi yang benar di server.
    - [ ] Periksa hak akses file dan konfigurasi web server (misalnya, untuk Laravel, pastikan `storage:link` sudah dijalankan jika menggunakan symbolic link).
