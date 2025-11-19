# 🖨️ Global Thermal Printer System Documentation

## 📋 **Overview**

Sistem pengaturan printer thermal global yang telah diimplementasikan berdasarkan manual programmer 80MM THERMAL RECEIPT PRINTER dan driver tools yang lengkap. Sistem ini mendukung berbagai merk printer thermal dan dapat dikonfigurasi secara fleksibel.

## ✨ **Features Implemented**

### **1. Multi-Brand Printer Support**
- ✅ **Eppos EP220II** - Full ESC commands support
- ✅ **Xprinter XP-80C** - Extended barcode support  
- ✅ **Epson TM-T20** - Professional cash drawer integration
- ✅ **Star TSP143** - Star-specific cut commands
- ✅ **Generic 80mm** - Basic thermal printing
- ✅ **Custom configurations** - User-defined settings

### **2. Connection Types**
- ✅ **USB** - Direct USB connection
- ✅ **Ethernet** - Network IP printing
- ✅ **WiFi** - Wireless network printing
- ✅ **Bluetooth** - Mobile device integration
- ✅ **Serial** - COM port connection

### **3. Advanced Settings**
- ✅ **Paper Width**: 58mm, 80mm, 112mm
- ✅ **Print Speed**: 5 levels (1=fastest, 5=best quality)
- ✅ **Print Density**: 5 levels (1=lightest, 5=darkest)
- ✅ **Character Sets**: PC437, PC850, PC852, PC858, PC866
- ✅ **Font Sizes**: Small (Font B), Normal (Font A), Large
- ✅ **Auto Cut**: Configurable paper cutting
- ✅ **Cash Drawer**: Automatic drawer opening
- ✅ **Buzzer**: Sound notifications

## 🗂️ **File Structure**

```
Project/
├── database/migrations/
│   └── 2025_01_01_000001_create_thermal_printer_settings_table.php
├── app/
│   ├── Models/
│   │   └── ThermalPrinterSetting.php
│   ├── Http/Controllers/
│   │   ├── ThermalPrinterController.php
│   │   └── Api/ThermalPrintController.php
│   └── Services/
│       └── ThermalPrinterService.php
├── resources/views/thermal-printer/
│   ├── index.blade.php
│   └── create.blade.php
├── routes/
│   ├── web.php (thermal printer routes)
│   └── api.php (thermal API routes)
├── public/js/
│   ├── thermal-printer-commands.js
│   └── thermal-printer-test.js
└── Documentation/
    ├── GLOBAL_THERMAL_PRINTER_SYSTEM.md
    ├── EPPOS_EP220II_CONFIG_GUIDE.md
    └── SOLUSI_PRINTER_EPPOS_EP220II.md
```

## 🚀 **Installation & Setup**

### **1. Database Migration**
```bash
php artisan migrate
```

### **2. Create Default Printer**
```php
// Via Tinker
php artisan tinker
>>> $printer = App\Models\ThermalPrinterSetting::create([
    'name' => 'Eppos EP220II',
    'brand' => 'Eppos',
    'model' => 'EP220II',
    'connection_type' => 'usb',
    'paper_width' => '80',
    'is_default' => true
]);
```

### **3. Access Printer Settings**
Navigate to: `/thermal-printer` to configure printers.

## 📖 **Usage Guide**

### **1. Basic Configuration**

#### **Add New Printer:**
1. Go to **Settings > Thermal Printers**
2. Click **"Add New Printer"**
3. Select **Preset** or configure manually
4. Test connection and save

#### **Supported Presets:**
- **Eppos EP220II** - Auto ESC commands, auto-cut, cash drawer
- **Xprinter XP-80C** - Extended barcode support
- **Epson TM-T20** - Professional features
- **Star TSP143** - Star-specific commands
- **Generic 80mm** - Basic thermal printing

### **2. Connection Setup**

#### **USB Connection:**
```php
'connection_type' => 'usb',
'name' => 'Printer Name in System'  // Windows printer name
```

#### **Network Connection:**
```php
'connection_type' => 'ethernet', // or 'wifi'
'ip_address' => '192.168.1.100',
'port' => 9100
```

#### **Serial Connection:**
```php
'connection_type' => 'serial',
'serial_port' => 'COM1',        // Windows: COM1, Linux: /dev/ttyUSB0
'baud_rate' => 115200
```

#### **Bluetooth Connection:**
```php
'connection_type' => 'bluetooth',
'bluetooth_address' => '00:11:22:33:44:55',
'baud_rate' => 115200
```

### **3. ESC Commands Integration**

System menggunakan ESC commands berdasarkan **80MM THERMAL RECEIPT PRINTER PROGRAMMER MANUAL**:

#### **Initialize Commands:**
- `ESC @` (1B 40) - Initialize printer
- `ESC 2` (1B 32) - Default line spacing
- `ESC M` (1B 4D) - Font selection
- `ESC !` (1B 21) - Print mode settings

#### **Print Commands:**
- `ESC a` (1B 61) - Text alignment
- `ESC !` (1B 21) - Emphasized/bold text
- `ESC 3` (1B 33) - Line spacing control
- `ESC SP` (1B 20) - Character spacing

#### **Cut Commands:**
- `ESC i` (1B 69) - Partial cut (Epson/Generic)
- `ESC d` (1B 64) - Star printers
- `ESC m` (1B 6D) - Citizen printers

## 🔧 **API Integration**

### **Print Sale Receipt:**
```javascript
fetch('/api/thermal/print-sale', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        sale_id: saleId,
        printer_id: printerId  // Optional, uses default if not specified
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Printed to:', data.printer_name);
    }
});
```

### **Test Print:**
```javascript
fetch('/api/thermal/print-test/' + printerId, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken }
})
```

### **Open Cash Drawer:**
```javascript
fetch('/api/thermal/open-cash-drawer/' + printerId, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken }
})
```

## 🎯 **Advanced Features**

### **1. Auto-Detection & Testing**
- Real-time connection testing
- Automatic printer discovery
- Performance monitoring
- Error logging

### **2. Template Customization**
- Header/footer text configuration
- Logo printing support
- Barcode positioning
- Custom ESC commands

### **3. Backup & Restore**
- Export printer settings (JSON)
- Import configurations
- Preset templates
- Bulk configuration

### **4. Multi-Location Support**
- Different printers per location
- Role-based printer access
- Default printer per user
- Printer groups

## 🔍 **Troubleshooting**

### **Common Issues:**

#### **1. Connection Failed**
```bash
# Test network connection
ping 192.168.1.100
telnet 192.168.1.100 9100

# Check USB device (Linux)
lsusb | grep -i printer

# Check serial port (Windows)
mode COM1
```

#### **2. Print Quality Issues**
- Adjust **Print Density** (1-5)
- Change **Print Speed** (1-5)
- Check paper quality
- Clean printer head

#### **3. ESC Commands Not Working**
- Verify printer compatibility
- Check manual for specific commands
- Test with different baud rate
- Update printer firmware

### **Debug Mode:**
```php
// Enable debug logging
config(['app.debug' => true]);

// Check logs
tail -f storage/logs/laravel.log | grep thermal
```

## 📊 **Performance Optimization**

### **1. Network Printing:**
- Use static IP addresses
- Configure optimal MTU size
- Monitor network latency
- Implement connection pooling

### **2. USB Printing:**
- Use dedicated USB ports
- Avoid USB hubs for printers
- Update USB drivers
- Check power requirements

### **3. Serial/Bluetooth:**
- Optimize baud rate settings
- Use quality cables
- Minimize interference
- Regular connection testing

## 🔒 **Security Considerations**

### **1. Network Printers:**
- Use VPN for remote printing
- Configure firewall rules
- Regular security updates
- Monitor printer access

### **2. Data Protection:**
- Encrypt sensitive print data
- Audit print activities
- Secure printer settings
- Regular backup verification

## 🚀 **Future Enhancements**

### **Planned Features:**
- [ ] Mobile app integration
- [ ] Cloud printing support
- [ ] Advanced barcode formats
- [ ] Multi-language support
- [ ] Print queue management
- [ ] Printer health monitoring
- [ ] Auto-discovery protocols
- [ ] Template designer UI

### **Integration Possibilities:**
- POS system integration
- Inventory management
- Customer display systems
- Kitchen printer routing
- Label printer support

## 💡 **Best Practices**

### **1. Configuration:**
- Test thoroughly before production
- Document all custom settings
- Regular backup configurations
- Monitor printer performance

### **2. Maintenance:**
- Clean printer heads regularly
- Replace paper rolls promptly
- Check connection stability
- Update drivers/firmware

### **3. User Training:**
- Provide clear documentation
- Train on troubleshooting
- Create standard procedures
- Maintain support contacts

---

## ✅ **System Status**

| Component | Status | Notes |
|-----------|--------|-------|
| Database Schema | ✅ Complete | Full migration ready |
| Model & Controllers | ✅ Complete | All CRUD operations |
| Web Interface | ✅ Complete | Full management UI |
| API Endpoints | ✅ Complete | REST API ready |
| ESC Commands | ✅ Complete | Manual-based implementation |
| Multi-Brand Support | ✅ Complete | 5+ printer brands |
| Connection Types | ✅ Complete | All major types |
| Documentation | ✅ Complete | Comprehensive guides |

**🎉 PRODUCTION READY - Full Global Thermal Printer System Implementation Complete!**