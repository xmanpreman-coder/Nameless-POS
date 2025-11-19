# Panduan Konfigurasi Eppos EP220II untuk Nota 80mm

## 🔧 Pengaturan Driver Printer

### 1. **Pengaturan di Windows**
```
Control Panel > Devices and Printers > Eppos EP220II > Properties
```

#### **Tab General:**
- Paper Source: **Roll Paper**
- Paper Type: **Thermal Paper**

#### **Tab Advanced:**
- Paper Size: **80mm x Continuous**
- Print Quality: **Draft** (untuk kecepatan)
- TrueType Font: **Download as soft font**

#### **Tab Device Settings:**
- Form to Tray Assignment: **80mm Roll**
- Paper Feed Option: **Auto Cut**
- Character Set: **PC437** atau **PC850**
- Print Density: **Medium**

### 2. **Pengaturan Khusus Thermal (Registry)**
Buat file `.reg` untuk optimasi:

```registry
Windows Registry Editor Version 5.00

[HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\Print\Printers\Eppos EP220II\PrinterDriverData]
"ThermalMode"=dword:00000001
"PaperWidth"=dword:00000050
"LineSpacing"=dword:00000014
"CharSpacing"=dword:00000000
"PrintSpeed"=dword:00000002
"CutMode"=dword:00000001
```

## 📄 Pengaturan Aplikasi

### 1. **Browser Settings (Chrome/Edge)**
```
Print Settings:
- Paper size: Custom (80mm x 297mm)
- Margins: None
- Scale: 100% (Actual size)
- More settings:
  ✓ Background graphics
  ✓ Headers and footers: OFF
```

### 2. **CSS Print Optimizations**
Template sudah dioptimalkan dengan:
- Container width: 72mm (optimal untuk 80mm)
- Font: Courier New 9-10px
- Line height: 11-12px
- No page breaks
- ESC commands integration

## 🖥️ ESC Commands yang Digunakan

Berdasarkan manual programmer, aplikasi menggunakan:

| Command | Hex | Fungsi |
|---------|-----|--------|
| ESC @ | 1B 40 | Initialize printer |
| ESC 2 | 1B 32 | Default line spacing (3.75mm) |
| ESC M | 1B 4D | Select font A (12x24) |
| ESC ! | 1B 21 | Normal print mode |
| ESC SP | 1B 20 | Minimal character spacing |
| ESC 3 | 1B 33 | Set line spacing |
| ESC a | 1B 61 | Left justification |
| ESC i | 1B 69 | Partial cut |

## ⚡ Troubleshooting

### **Problem: Nota masih terpotong**
**Solusi:**
1. Set paper size di driver: **80mm x continuous**
2. Disable "Fit to page" di browser
3. Gunakan route `/sales/thermal/print/{id}` bukan `/sales/pos/print/{id}`

### **Problem: Font terlalu kecil**
**Solusi:**
1. Edit file `print-thermal-80mm.blade.php`
2. Ubah font-size dari 10px ke 11px:
```css
body {
    font-size: 11px; /* dari 10px */
    line-height: 13px; /* dari 12px */
}
```

### **Problem: Spacing terlalu besar**
**Solusi:**
1. Di browser print settings, pastikan scale = 100%
2. Check CSS line-height dan margins
3. Gunakan ESC 3 command untuk custom line spacing

### **Problem: Cut tidak berfungsi**
**Solusi:**
1. Enable auto-cut di driver printer
2. Check ESC i command (sudah diinclude)
3. Set cut mode di device settings

## 🎛️ Pengaturan Manual Printer

### **DIP Switch Settings (jika ada):**
- Switch 1: ON (Auto cut enable)
- Switch 2: OFF (Character set PC437)
- Switch 3: ON (80mm paper)
- Switch 4: OFF (Normal density)

### **Test Print:**
1. Hold FEED button saat power ON untuk test print
2. Check apakah output sesuai 80mm width
3. Verify character spacing dan cut function

## 📊 Performance Optimization

### **Print Speed Settings:**
```javascript
// Di thermal-printer-commands.js
setPrintSpeed: function(speed = 2) {
    // Speed 1-5: 1=fastest, 5=highest quality
    return '\x1D\x28\x4B\x02\x00\x30' + String.fromCharCode(speed);
}
```

### **Print Density:**
```javascript
// Adjust sesuai kualitas thermal paper
setPrintDensity: function(level = 2) {
    // Level 1-5: 1=lightest, 5=darkest
    return '\x1D\x7C\x00' + String.fromCharCode(level);
}
```

## 🔄 Backup & Recovery

### **Export Printer Settings:**
```batch
reg export "HKLM\SYSTEM\CurrentControlSet\Control\Print\Printers\Eppos EP220II" "eppos_backup.reg"
```

### **Reset to Factory:**
1. Delete printer dari Devices and Printers
2. Restart computer
3. Re-install dengan settings di atas

## 📱 Mobile Integration

Jika menggunakan dari mobile:
1. Install **PrinterShare** atau **Print to PDF**
2. Set paper size ke 80mm
3. Gunakan landscape orientation
4. Scale: Fit to width

## ✅ Verification Checklist

- [ ] Driver installed dengan settings di atas
- [ ] Paper size: 80mm x continuous
- [ ] Browser margins: None
- [ ] Template route: `/sales/thermal/print/{id}`
- [ ] ESC commands active (check console log)
- [ ] Auto cut enabled
- [ ] Test print successful

## 🆘 Support

Jika masih ada masalah:
1. Check printer manual untuk DIP switch settings
2. Update printer driver ke versi terbaru
3. Test dengan aplikasi thermal printer lainnya
4. Contact Eppos support untuk firmware update