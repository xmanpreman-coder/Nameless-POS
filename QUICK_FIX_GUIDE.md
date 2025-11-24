# Quick Fix Guide - Priority Bugs

This document provides copy-paste ready code fixes for the most critical bugs.

---

## 🔥 CRITICAL FIX #1: Command Injection

### File: `app/Services/PrinterDriverFactory.php`

**Replace lines 173-186:**

```php
// BEFORE (VULNERABLE)
public function print($content, $options = [])
{
    $printerName = $this->printer->connection_address;

    // Write to temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'print_');
    file_put_contents($tempFile, $content);

    // Send to printer using Windows print command
    exec("print /D:$printerName $tempFile");
    
    // Clean up
    unlink($tempFile);
}
```

```php
// AFTER (SECURE)
public function print($content, $options = [])
{
    $printerName = $this->printer->connection_address;
    $tempFile = tempnam(sys_get_temp_dir(), 'print_');
    
    try {
        file_put_contents($tempFile, $content);
        
        // Properly escape shell arguments
        $command = sprintf(
            "print /D:%s %s",
            escapeshellarg($printerName),
            escapeshellarg($tempFile)
        );
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Print command failed: " . implode("\n", $output));
        }
    } finally {
        // Always clean up temp file
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }
}
```

---

## 🔥 CRITICAL FIX #2: Socket Resource Leak

### File: `app/Services/PrinterDriverFactory.php`

**Replace lines 39-49:**

```php
// BEFORE (RESOURCE LEAK)
public function testConnection()
{
    $host = $this->printer->connection_address;
    $port = $this->printer->connection_port ?? 9100;

    if (@fsockopen($host, $port, $errno, $errstr, 2)) {
        return true;
    }

    throw new \Exception("Cannot connect to $host:$port");
}
```

```php
// AFTER (FIXED)
public function testConnection()
{
    $host = $this->printer->connection_address;
    $port = $this->printer->connection_port ?? 9100;

    $socket = @fsockopen($host, $port, $errno, $errstr, 2);
    
    if ($socket) {
        fclose($socket); // Always close the socket!
        return true;
    }

    throw new \Exception("Cannot connect to $host:$port - $errstr");
}
```

**Replace lines 51-64:**

```php
// BEFORE (NO ERROR HANDLING)
public function print($content, $options = [])
{
    $host = $this->printer->connection_address;
    $port = $this->printer->connection_port ?? 9100;

    $socket = @fsockopen($host, $port, $errno, $errstr, 5);
    
    if (!$socket) {
        throw new \Exception("Connection failed: $errstr ($errno)");
    }

    fwrite($socket, $content);
    fclose($socket);
}
```

```php
// AFTER (WITH PROPER ERROR HANDLING)
public function print($content, $options = [])
{
    $host = $this->printer->connection_address;
    $port = $this->printer->connection_port ?? 9100;

    $socket = @fsockopen($host, $port, $errno, $errstr, 5);
    
    if (!$socket) {
        throw new \Exception("Connection failed: $errstr ($errno)");
    }

    try {
        $bytesWritten = fwrite($socket, $content);
        
        if ($bytesWritten === false || $bytesWritten < strlen($content)) {
            throw new \Exception("Failed to write all data to printer");
        }
    } finally {
        fclose($socket); // Always close in finally block
    }
}
```

---

## 🔥 CRITICAL FIX #3: Socket Leak in Model

### File: `app/Models/ThermalPrinterSetting.php`

**Find and replace the testConnection method (around line 235):**

```php
// BEFORE
$connection = @fsockopen($this->ip_address, $this->port, $errno, $errstr, 5);

if (!$connection) {
    return false;
}

return true;
```

```php
// AFTER
$connection = @fsockopen($this->ip_address, $this->port, $errno, $errstr, 5);

if (!$connection) {
    return false;
}

fclose($connection); // Close the socket!
return true;
```

---

## 🔥 CRITICAL FIX #4: Mass Assignment Vulnerability

### File: `app/Http/Controllers/ThermalPrinterController.php`

**Replace line 47:**

```php
// BEFORE (VULNERABLE)
$printerSetting = ThermalPrinterSetting::create($request->all());
```

```php
// AFTER (SECURE)
$printerSetting = ThermalPrinterSetting::create([
    'name' => $request->name,
    'brand' => $request->brand,
    'model' => $request->model,
    'connection_type' => $request->connection_type,
    'connection_address' => $request->connection_address,
    'ip_address' => $request->ip_address,
    'port' => $request->port,
    'paper_width' => $request->paper_width,
    'print_speed' => $request->print_speed,
    'print_density' => $request->print_density,
]);
```

**Replace line 90:**

```php
// BEFORE (VULNERABLE)
$thermalPrinter->update($request->all());
```

```php
// AFTER (SECURE)
$thermalPrinter->update([
    'name' => $request->name,
    'brand' => $request->brand,
    'model' => $request->model,
    'connection_type' => $request->connection_type,
    'connection_address' => $request->connection_address,
    'ip_address' => $request->ip_address,
    'port' => $request->port,
    'paper_width' => $request->paper_width,
    'print_speed' => $request->print_speed,
    'print_density' => $request->print_density,
]);
```

---

## 🔥 CRITICAL FIX #5: LIKE Wildcard Injection

### File: `app/Livewire/SearchProduct.php`

**Replace lines 25-30:**

```php
// BEFORE (VULNERABLE)
public function updatedQuery() {
    $this->search_results = Product::where('product_name', 'like', '%' . $this->query . '%')
        ->orWhere('product_sku', 'like', '%' . $this->query . '%')
        ->orWhere('product_gtin', 'like', '%' . $this->query . '%')
        ->take($this->how_many)->get();
}
```

```php
// AFTER (SECURE)
public function updatedQuery() {
    // Escape LIKE special characters
    $searchTerm = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $this->query);
    
    $this->search_results = Product::where('product_name', 'like', '%' . $searchTerm . '%')
        ->orWhere('product_sku', 'like', '%' . $searchTerm . '%')
        ->orWhere('product_gtin', 'like', '%' . $searchTerm . '%')
        ->take($this->how_many)
        ->get();
}
```

---

## 🚨 HIGH PRIORITY FIX #6: Path Traversal

### File: `app/Models/User.php`

**Replace lines 54-62:**

```php
// BEFORE (VULNERABLE)
public function getAvatarUrlAttribute()
{
    if ($this->avatar && file_exists(storage_path('app/public/' . $this->avatar))) {
        return asset('storage/' . $this->avatar);
    }
    
    // Return initials as fallback
    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF&size=50';
}
```

```php
// AFTER (SECURE)
public function getAvatarUrlAttribute()
{
    if ($this->avatar) {
        // Only use the filename, remove any directory traversal attempts
        $avatarFile = basename($this->avatar);
        
        // Construct safe path
        $fullPath = storage_path('app/public/avatars/' . $avatarFile);
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return asset('storage/avatars/' . $avatarFile);
        }
    }
    
    // Return initials as fallback
    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF&size=50';
}
```

---

## 🚨 HIGH PRIORITY FIX #7: N+1 Query Problem

### File: `app/Http/Controllers/HomeController.php`

**Find the index method (around line 25) and replace:**

```php
// BEFORE (N+1 PROBLEM)
foreach (Sale::completed()->with('saleDetails')->get() as $sale) {
    foreach ($sale->saleDetails as $saleDetail) {
        $product_costs += $saleDetail->product->product_cost * $saleDetail->quantity;
    }
}
```

```php
// AFTER (OPTIMIZED)
foreach (Sale::completed()->with('saleDetails.product')->get() as $sale) {
    foreach ($sale->saleDetails as $saleDetail) {
        $product_costs += $saleDetail->product->product_cost * $saleDetail->quantity;
    }
}
```

---

## 🚨 HIGH PRIORITY FIX #8: File Handle Leak

### File: `app/Services/PrinterDriverFactory.php`

**Replace lines 88-104 (USBPrinterDriver print method):**

```php
// BEFORE (RESOURCE LEAK)
public function print($content, $options = [])
{
    $device = $this->printer->connection_address;

    if (PHP_OS_FAMILY === 'Windows') {
        throw new \Exception("Use network printer for Windows USB devices");
    } else {
        // Linux: write directly to device
        $handle = @fopen($device, 'w');
        if (!$handle) {
            throw new \Exception("Cannot open USB device: $device");
        }
        fwrite($handle, $content);
        fclose($handle);
    }
}
```

```php
// AFTER (FIXED)
public function print($content, $options = [])
{
    $device = $this->printer->connection_address;

    if (PHP_OS_FAMILY === 'Windows') {
        throw new \Exception("Use network printer for Windows USB devices");
    } else {
        // Linux: write directly to device
        $handle = @fopen($device, 'w');
        if (!$handle) {
            throw new \Exception("Cannot open USB device: $device");
        }
        
        try {
            $bytesWritten = fwrite($handle, $content);
            if ($bytesWritten === false) {
                throw new \Exception("Failed to write to USB device");
            }
        } finally {
            fclose($handle); // Always close in finally block
        }
    }
}
```

---

## 🚨 HIGH PRIORITY FIX #9: Connection Validation

### File: `app/Http/Controllers/ThermalPrinterController.php`

**Replace the store method validation (lines 27-39):**

```php
// BEFORE (INCOMPLETE VALIDATION)
$validator = Validator::make($request->all(), [
    'name' => 'required|string|max:255',
    'brand' => 'nullable|string|max:100',
    'model' => 'nullable|string|max:100',
    'connection_type' => 'required|in:usb,ethernet,bluetooth,serial,wifi',
    'ip_address' => 'nullable|ip',
    'port' => 'nullable|integer|between:1,65535',
    'paper_width' => 'required|in:58,80,112',
    'print_speed' => 'required|in:1,2,3,4,5',
    'print_density' => 'required|in:1,2,3,4,5',
]);
```

```php
// AFTER (COMPLETE VALIDATION)
$rules = [
    'name' => 'required|string|max:255',
    'brand' => 'nullable|string|max:100',
    'model' => 'nullable|string|max:100',
    'connection_type' => 'required|in:usb,ethernet,bluetooth,serial,wifi',
    'paper_width' => 'required|in:58,80,112',
    'print_speed' => 'required|in:1,2,3,4,5',
    'print_density' => 'required|in:1,2,3,4,5',
];

// Add conditional validation based on connection type
if (in_array($request->connection_type, ['ethernet', 'wifi'])) {
    $rules['ip_address'] = 'required|ip';
    $rules['port'] = 'required|integer|between:1,65535';
} elseif (in_array($request->connection_type, ['usb', 'serial'])) {
    $rules['connection_address'] = 'required|string|max:255';
}

$validator = Validator::make($request->all(), $rules);
```

**Apply the same fix to the update method (lines 70-82).**

---

## 🚨 HIGH PRIORITY FIX #10: Inefficient Query Loop

### File: `app/Livewire/SearchProduct.php`

**Replace lines 130-149:**

```php
// BEFORE (MULTIPLE QUERIES IN LOOP)
foreach ($commonFirstDigits as $digit) {
    $fullBarcode = $digit . $barcode;
    
    $product = Product::where('product_barcode_symbology', $fullBarcode)
                     ->orWhere('product_sku', $fullBarcode)
                     ->orWhere('product_gtin', $fullBarcode)
                     ->first();
    
    if ($product) {
        return [
            'product' => $product,
            'actual_barcode' => $fullBarcode,
            'reconstructed' => true
        ];
    }
}

return null;
```

```php
// AFTER (SINGLE OPTIMIZED QUERY)
// Build all possible barcodes
$possibleBarcodes = array_map(fn($digit) => $digit . $barcode, $commonFirstDigits);

// Single query with whereIn
$product = Product::where(function($query) use ($possibleBarcodes) {
    $query->whereIn('product_barcode_symbology', $possibleBarcodes)
          ->orWhereIn('product_sku', $possibleBarcodes)
          ->orWhereIn('product_gtin', $possibleBarcodes);
})->first();

if ($product) {
    // Determine which barcode matched
    $actualBarcode = in_array($product->product_barcode_symbology, $possibleBarcodes) 
        ? $product->product_barcode_symbology
        : (in_array($product->product_sku, $possibleBarcodes) 
            ? $product->product_sku 
            : $product->product_gtin);
    
    return [
        'product' => $product,
        'actual_barcode' => $actualBarcode,
        'reconstructed' => true
    ];
}

return null;
```

---

## ✅ Verification Commands

After applying fixes, run these commands to verify:

```bash
# 1. Run tests
php artisan test

# 2. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Run static analysis (if installed)
./vendor/bin/phpstan analyse
./vendor/bin/psalm

# 4. Check for remaining security issues
grep -r "exec(" app/
grep -r "\$request->all()" app/
grep -r "@fsockopen" app/
grep -r "->all()" app/Http/Controllers/
```

---

## 📝 Testing Checklist

After applying fixes:

- [ ] Test printer creation with special characters in name
- [ ] Test printer connection multiple times (check for leaks)
- [ ] Test product search with `%` and `_` characters
- [ ] Test avatar upload with `../../` in filename
- [ ] Monitor `/tmp` directory for orphaned files
- [ ] Check database query count on dashboard load
- [ ] Test concurrent printer operations
- [ ] Verify validation errors for incomplete printer configs

---

## 🔄 Git Workflow

Suggested approach for applying fixes:

```bash
# Create feature branch
git checkout -b fix/critical-security-issues

# Apply fixes one category at a time
git add app/Services/PrinterDriverFactory.php
git commit -m "Fix: Command injection and resource leaks in PrinterDriverFactory"

git add app/Http/Controllers/ThermalPrinterController.php
git commit -m "Fix: Mass assignment vulnerability in ThermalPrinterController"

git add app/Livewire/SearchProduct.php
git commit -m "Fix: SQL injection in search and optimize queries"

git add app/Models/User.php
git commit -m "Fix: Path traversal vulnerability in User avatar"

git add app/Http/Controllers/HomeController.php
git commit -m "Fix: N+1 query problem in HomeController"

# Push and create PR
git push origin fix/critical-security-issues
```

---

## 📞 Support

If you encounter issues while applying these fixes:

1. Check the detailed `BUG_REPORT.md` for context
2. Review Laravel documentation for best practices
3. Test each fix individually before moving to the next
4. Use Laravel Debugbar to verify query counts
5. Monitor logs for any new errors

---

**⚠️ IMPORTANT:** 
- Test thoroughly in a development environment first
- Apply fixes in the order listed (Critical → High → Medium)
- Don't skip the verification steps
- Create backups before making changes

---

*Quick Fix Guide - Generated by Rovo Dev*
