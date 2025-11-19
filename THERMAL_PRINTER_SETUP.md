# Pengaturan Printer Thermal untuk Eppos EP220II

## Masalah yang Diperbaiki

Sebelumnya, printer thermal Eppos EP220II dengan kertas 80mm mengalami masalah:
- ❌ Nota tercetak terlalu panjang
- ❌ Terpecah menjadi 2 halaman
- ❌ Format tidak optimal untuk thermal printer

## Solusi yang Diterapkan

### 1. Template Baru untuk Thermal 80mm
- ✅ Dibuat template khusus: `print-thermal-80mm.blade.php`
- ✅ Optimized CSS untuk kertas thermal 80mm
- ✅ Font monospace (Courier New) untuk hasil konsisten
- ✅ Ukuran font yang tepat (9-10px)
- ✅ Margin minimal untuk thermal paper

### 2. CSS Print Settings yang Diperbaiki
```css
@page {
    size: 80mm auto; /* 80mm width, auto height */
    margin: 0mm; /* No margins untuk thermal */
}

.thermal-receipt {
    width: 72mm; /* 80mm paper dengan margin 4mm */
    font-family: 'Courier New', monospace;
    font-size: 10px;
    line-height: 12px;
}
```

### 3. Route Baru
- Route baru: `/sales/thermal/print/{id}`
- Tombol "Thermal 80mm" di halaman detail sale

## Cara Menggunakan

### 1. Melalui Halaman Detail Sale
1. Buka halaman detail sale
2. Klik tombol **"Thermal 80mm"** (hijau)
3. Window baru akan terbuka dengan format thermal
4. Printer dialog akan otomatis muncul

### 2. Akses Langsung
```
https://your-domain.com/sales/thermal/print/{sale_id}
```

### 3. Pengaturan Printer

#### Di Windows:
1. Buka **Control Panel > Devices and Printers**
2. Klik kanan printer Eppos EP220II
3. Pilih **"Printer properties"**
4. Tab **"Advanced"** → Set paper size ke **"80mm"**
5. Tab **"Device Settings"** → Form to Tray Assignment: **"80mm Roll"**

#### Di Browser:
1. Saat print dialog muncul
2. Pilih printer: **"EPPOS EP220II"**
3. Paper size: **"80mm x continuous"** atau **"Custom 80mm"**
4. Margins: **"Minimum"** atau **"None"**
5. Scale: **"100%"** (jangan shrink to fit)

## Perbedaan Template

| Feature | Template Lama | Template Thermal Baru |
|---------|---------------|----------------------|
| Lebar Container | 400px | 72mm (optimal untuk 80mm) |
| Font Size | 12px | 9-10px |
| Line Height | 18px | 11-12px |
| Font Family | Ubuntu, sans-serif | Courier New, monospace |
| Page Break | Bermasalah | Dihilangkan |
| Margins | Default | 0mm untuk thermal |

## Tips Optimasi

### 1. Pengaturan Printer Driver
```
Paper Size: 80mm x continuous
Print Quality: Draft (untuk kecepatan)
Paper Type: Thermal Paper
Density: Medium (untuk ketajaman)
```

### 2. Pengaturan Browser
- Chrome: Print > More settings > Paper size: Custom 80mm
- Firefox: Page Setup > Custom size: 80mm width
- Edge: Print > More settings > Custom paper size

### 3. Troubleshooting

#### Jika masih terpotong:
1. Cek ukuran kertas di printer driver
2. Pastikan margins di browser = 0
3. Pilih "Actual size" bukan "Fit to page"

#### Jika font terlalu kecil:
1. Sesuaikan setting di `print-thermal-80mm.blade.php`
2. Ubah font-size dari 10px ke 11px jika perlu

#### Jika masih 2 halaman:
1. Pastikan menggunakan route `/thermal/print/` bukan `/pos/print/`
2. Cek setting page break di CSS

## File yang Diubah/Dibuat

### File Baru:
- `Modules/Sale/Resources/views/print-thermal-80mm.blade.php`
- `THERMAL_PRINTER_SETUP.md`

### File yang Dimodifikasi:
- `Modules/Sale/Resources/views/print-pos.blade.php` (CSS diperbaiki)
- `Modules/Sale/Resources/views/show.blade.php` (tambah tombol)
- `Modules/Sale/Routes/web.php` (tambah route)

## Rekomendasi Printer Thermal Lainnya

Template ini juga kompatibel dengan:
- Epson TM-T20, TM-T82, TM-T88
- Star TSP143, TSP654
- Citizen CT-S310, CT-S2000
- POS-80 series
- Xprinter XP-80C, XP-58

## Support

Jika masih ada masalah:
1. Cek pengaturan printer driver
2. Test dengan printer lain untuk isolasi masalah
3. Sesuaikan CSS sesuai kebutuhan spesifik printer