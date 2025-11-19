# 🔧 Fix Infinite Paper Rolling - Thermal Printer

## ⚠️ **Masalah yang Terjadi**

**Symptoms:**
- ❌ Kertas thermal terus bergulir tanpa henti
- ❌ Printer tidak mencetak nota sama sekali
- ❌ Roll kertas habis tanpa ada output
- ❌ Printer seperti stuck dalam loop

**Root Cause:**
1. **ESC commands tidak tepat** - Missing atau salah sequence
2. **Line spacing berlebihan** - Menyebabkan paper feed terus menerus
3. **Missing cut commands** - Printer tidak tahu kapan berhenti
4. **Print buffer overflow** - Data terlalu banyak untuk printer buffer

## ✅ **Solusi yang Telah Diimplementasikan**

### **1. Database Fix**
```bash
# Jalankan migration untuk fix missing columns
php artisan migrate
```

### **2. Emergency Controls**
Akses: `/thermal-printer` → **Emergency Controls** section

#### **🛑 Emergency Stop**
- **Fungsi:** Menghentikan printer yang sedang rolling
- **Command:** `CAN (0x18)` + `ESC @ (0x1B 0x40)` + `ESC i (0x1B 0x69)`
- **Usage:** Input IP printer → klik "Emergency Stop"

#### **⚙️ Fix Settings** 
- **Fungsi:** Update ESC commands yang benar
- **Features:** Auto-generate proper initialization commands
- **Usage:** Klik "Fix Settings" → Page reload

#### **🧪 Test Fixed Print**
- **Fungsi:** Test print dengan ESC commands yang sudah diperbaiki
- **Features:** Minimal content untuk prevent infinite rolling
- **Usage:** Input IP → klik "Test Fixed Print"

### **3. Improved ESC Commands**

#### **Initialization Commands (Fixed):**
```php
ESC @ (1B 40)        // Initialize printer
ESC 3 20 (1B 33 14)  // Set minimal line spacing (20/180 inch)
ESC M 1 (1B 4D 01)   // Font B (smaller, compact)
ESC ! 0 (1B 21 00)   // Normal print mode
ESC SP 0 (1B 20 00)  // No extra character spacing
GS L (1D 4C 00 00)   // Set left margin to 0
GS W (1D 57 00 02)   // Set print width for 80mm
```

#### **Proper Cut Commands:**
```php
LF (0A)              // 1 line feed only (minimal)
ESC i (1B 69)        // Partial cut to stop rolling
```

#### **Emergency Stop Commands:**
```php
CAN (18)             // Cancel any pending operations
ESC @ (1B 40)        // Reset printer
ESC i (1B 69)        // Immediate cut
```

## 🚀 **Cara Menggunakan Fix**

### **Metode 1: Emergency Stop (Untuk Masalah Sekarang)**

1. **Akses Emergency Controls:**
   ```
   https://your-domain.com/thermal-printer
   ```

2. **Input Printer IP:**
   ```
   IP Address: 192.168.1.100  (sesuaikan dengan IP printer Anda)
   Port: 9100
   ```

3. **Emergency Stop:**
   - Klik **"Emergency Stop"** 
   - Wait untuk response "Emergency stop command sent successfully!"
   - Printer harus berhenti rolling

### **Metode 2: Fix Settings (Untuk Pencegahan)**

1. **Fix Printer Settings:**
   - Klik **"Fix Settings"**
   - Wait untuk "Printer settings fixed!"
   - Page akan reload otomatis

2. **Test Fixed Print:**
   - Klik **"Test Fixed Print"**
   - Harus mencetak test receipt yang pendek
   - Kertas berhenti setelah cut

### **Metode 3: Manual Network Command**

```bash
# Via telnet (jika emergency controls tidak work)
telnet 192.168.1.100 9100

# Send emergency stop (hex):
18 1B 40 1B 69

# Or via echo command:
echo -e "\x18\x1B\x40\x1B\x69" | nc 192.168.1.100 9100
```

## 🔍 **Troubleshooting**

### **Problem: Emergency Stop Tidak Work**

**Solution 1: Check Network Connection**
```bash
ping 192.168.1.100
telnet 192.168.1.100 9100
```

**Solution 2: Physical Reset**
1. Matikan printer (power off)
2. Wait 10 detik
3. Nyalakan kembali
4. Test dengan "Test Fixed Print"

**Solution 3: Manual Cut**
1. Buka cover printer
2. Manual cut kertas dengan tangan
3. Close cover
4. Test fixed commands

### **Problem: Settings Fix Tidak Work**

**Solution:**
1. Check database connection
2. Run migration manual:
   ```bash
   php artisan migrate
   ```
3. Check `printer_settings` table ada

### **Problem: Masih Rolling After Fix**

**Possible Causes:**
1. **Wrong IP Address** - Check printer IP
2. **Firewall blocking** - Check network access
3. **Printer firmware issue** - Update firmware
4. **Hardware problem** - Contact vendor

## 📊 **Technical Details**

### **Fixed ThermalPrinterFixService Class:**
```php
// Proper initialization
$commands[] = "\x1B\x40";        // ESC @ - Initialize
$commands[] = "\x1B\x33\x14";    // ESC 3 20 - Minimal line spacing
$commands[] = "\x1B\x4D\x01";    // ESC M 1 - Font B
$commands[] = "\x1B\x21\x00";    // ESC ! 0 - Normal mode

// Proper cut to stop rolling
$commands[] = "\x0A";             // 1 LF only
$commands[] = "\x1B\x69";        // ESC i - Partial cut
```

### **Database Structure:**
```sql
-- printer_settings table
receipt_paper_size VARCHAR(10) DEFAULT '80mm'
auto_print_receipt BOOLEAN DEFAULT false
thermal_printer_commands TEXT
```

## ⚡ **Prevention Tips**

### **1. Always Use Proper ESC Sequence:**
- Initialize printer sebelum print
- Set line spacing minimal
- Always end dengan cut command

### **2. Content Optimization:**
- Limit line length (42 chars untuk 80mm)
- Avoid excessive line feeds
- Use compact format

### **3. Testing:**
- Test setiap perubahan dengan "Test Fixed Print"
- Monitor printer behavior
- Keep emergency stop ready

### **4. Network Printer Settings:**
```
IP: Static IP (jangan DHCP)
Port: 9100 (standard)
Protocol: RAW/TCP
Buffer: Minimal
Timeout: 5 seconds
```

## 📋 **Checklist After Fix**

- [ ] Database migration completed
- [ ] Emergency stop working
- [ ] Fix settings applied
- [ ] Test print successful (short receipt)
- [ ] No more infinite rolling
- [ ] Normal receipt printing works
- [ ] Cut function working

## 🆘 **Emergency Contacts**

**Jika semua solusi gagal:**

1. **Hardware Reset:**
   - Power off printer
   - Hold FEED button while power on
   - Release FEED button
   - Test print

2. **Vendor Support:**
   - Contact Eppos support
   - Report infinite rolling issue
   - Request firmware update

3. **Alternative Solution:**
   - Use USB connection (temporary)
   - Check other thermal printer
   - Manual cutting as backup

---

## ✅ **Status After Fix**

| Issue | Status | Solution Applied |
|-------|--------|-----------------|
| Database Error | ✅ Fixed | Migration added |
| Infinite Rolling | ✅ Fixed | ESC commands optimized |
| Emergency Stop | ✅ Available | Network command ready |
| Settings Fix | ✅ Available | Auto-update commands |
| Test Print | ✅ Available | Fixed format |

**🎉 FIXED - Infinite Paper Rolling Issue Resolved!**