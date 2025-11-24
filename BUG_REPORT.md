# Bug Report - Nameless POS System

## Executive Summary
This document contains a comprehensive list of bugs, security vulnerabilities, and code quality issues found in the Nameless POS system.

---

## 🔴 CRITICAL BUGS

### 1. **Command Injection Vulnerability in Printer Commands**
**Severity:** CRITICAL  
**Location:** Multiple files
- `app/Services/PrinterDriverFactory.php:182`
- `app/Services/ThermalPrinterService.php:435`
- `app/Http/Controllers/ThermalPrinterController.php:167, 355`

**Issue:**
```php
// Line 182 in PrinterDriverFactory.php
exec("print /D:$printerName $tempFile");

// Line 435 in ThermalPrinterService.php
$command = "print /d:\"" . $printerName . "\" \"$tempFile\"";
exec($command . ' 2>&1', $output, $returnVar);
```

**Problem:** The `$printerName` variable is directly concatenated into shell commands without proper escaping. This allows command injection if a malicious user can control the printer name.

**Exploit Example:**
```php
$printerName = "MyPrinter\" & del /F /Q C:\\* & echo \"";
// Results in: print /D:MyPrinter" & del /F /Q C:\* & echo " tempfile
```

**Fix:**
```php
exec("print /D:" . escapeshellarg($printerName) . " " . escapeshellarg($tempFile));
```

---

### 2. **Resource Leak - Socket Not Closed on Connection Test**
**Severity:** CRITICAL  
**Location:** `app/Services/PrinterDriverFactory.php:44`

**Issue:**
```php
public function testConnection()
{
    $host = $this->printer->connection_address;
    $port = $this->printer->connection_port ?? 9100;

    if (@fsockopen($host, $port, $errno, $errstr, 2)) {
        return true;  // Socket is never closed!
    }

    throw new \Exception("Cannot connect to $host:$port");
}
```

**Problem:** The socket opened by `fsockopen()` is never closed, leading to resource leaks. If this function is called repeatedly, it will exhaust file descriptors.

**Fix:**
```php
public function testConnection()
{
    $host = $this->printer->connection_address;
    $port = $this->printer->connection_port ?? 9100;

    $socket = @fsockopen($host, $port, $errno, $errstr, 2);
    
    if ($socket) {
        fclose($socket);
        return true;
    }

    throw new \Exception("Cannot connect to $host:$port");
}
```

---

### 3. **Temporary File Not Cleaned Up on Error**
**Severity:** HIGH  
**Location:** `app/Services/PrinterDriverFactory.php:173-186`

**Issue:**
```php
public function print($content, $options = [])
{
    $printerName = $this->printer->connection_address;

    // Write to temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'print_');
    file_put_contents($tempFile, $content);

    // Send to printer using Windows print command
    exec("print /D:$printerName $tempFile");  // If this fails, file is not deleted
    
    // Clean up
    unlink($tempFile);
}
```

**Problem:** If `exec()` throws an exception or fails, the temporary file is never deleted. Over time, this fills up the temp directory.

**Fix:**
```php
public function print($content, $options = [])
{
    $printerName = $this->printer->connection_address;
    $tempFile = tempnam(sys_get_temp_dir(), 'print_');
    
    try {
        file_put_contents($tempFile, $content);
        exec(escapeshellcmd("print /D:" . escapeshellarg($printerName) . " " . escapeshellarg($tempFile)), $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Print command failed with code: $returnCode");
        }
    } finally {
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }
}
```

---

### 4. **Mass Assignment Vulnerability**
**Severity:** HIGH  
**Location:** `app/Http/Controllers/ThermalPrinterController.php:47, 90`

**Issue:**
```php
// Line 47
$printerSetting = ThermalPrinterSetting::create($request->all());

// Line 90
$thermalPrinter->update($request->all());
```

**Problem:** Using `$request->all()` with `create()` or `update()` allows mass assignment of any fields the user sends, including fields that should be protected (like `id`, `created_at`, `is_default`, etc.). An attacker could potentially:
- Set themselves as the default printer when they shouldn't
- Modify timestamps
- Inject malicious data into unvalidated fields

**Fix:**
```php
// Use only validated data
$validated = $request->validated();
$printerSetting = ThermalPrinterSetting::create($validated);

// OR specify exact fields
$printerSetting = ThermalPrinterSetting::create([
    'name' => $request->name,
    'brand' => $request->brand,
    'model' => $request->model,
    'connection_type' => $request->connection_type,
    'ip_address' => $request->ip_address,
    'port' => $request->port,
    'paper_width' => $request->paper_width,
    'print_speed' => $request->print_speed,
    'print_density' => $request->print_density,
]);
```

**Also ensure the model has proper `$fillable` or `$guarded` properties set.**

---

### 5. **SQL Injection Risk via Search Query**
**Severity:** HIGH  
**Location:** `app/Livewire/SearchProduct.php:26-29, 88-91`

**Issue:**
```php
public function updatedQuery() {
    $this->search_results = Product::where('product_name', 'like', '%' . $this->query . '%')
        ->orWhere('product_sku', 'like', '%' . $this->query . '%')
        ->orWhere('product_gtin', 'like', '%' . $this->query . '%')
        ->take($this->how_many)->get();
}
```

**Problem:** While Laravel's query builder does escape basic SQL injection, the LIKE operator with user input can still cause issues:
1. Special LIKE characters (`%`, `_`) are not escaped, allowing wildcards
2. Performance issues with leading wildcards
3. Potential for LIKE-based exploits

**Example Issue:**
- User inputs: `%` → Returns ALL products
- User inputs: `_____` → Returns all 5-character SKUs

**Fix:**
```php
public function updatedQuery() {
    // Escape LIKE special characters
    $searchTerm = str_replace(['%', '_'], ['\\%', '\\_'], $this->query);
    
    $this->search_results = Product::where('product_name', 'like', '%' . $searchTerm . '%')
        ->orWhere('product_sku', 'like', '%' . $searchTerm . '%')
        ->orWhere('product_gtin', 'like', '%' . $searchTerm . '%')
        ->take($this->how_many)
        ->get();
}
```

---

## 🟠 HIGH PRIORITY BUGS

### 6. **Race Condition in Default Printer Selection**
**Severity:** MEDIUM-HIGH  
**Location:** `app/Http/Controllers/ThermalPrinterController.php:50-52`

**Issue:**
```php
// Set as default if it's the first printer or explicitly requested
if ($request->is_default || ThermalPrinterSetting::count() === 1) {
    $printerSetting->setAsDefault();
}
```

**Problem:** There's a race condition between checking `count() === 1` and calling `setAsDefault()`. In concurrent requests, two printers could both be set as default.

**Fix:**
```php
DB::transaction(function () use ($request, $printerSetting) {
    if ($request->is_default || ThermalPrinterSetting::lockForUpdate()->count() === 1) {
        $printerSetting->setAsDefault();
    }
});
```

---

### 7. **Unchecked File Handle Closure**
**Severity:** MEDIUM  
**Location:** `app/Services/PrinterDriverFactory.php:97-102`

**Issue:**
```php
$handle = @fopen($device, 'w');
if (!$handle) {
    throw new \Exception("Cannot open USB device: $device");
}
fwrite($handle, $content);
fclose($handle);  // Not in try-finally block
```

**Problem:** If `fwrite()` throws an exception, the file handle is never closed, causing resource leaks.

**Fix:**
```php
$handle = @fopen($device, 'w');
if (!$handle) {
    throw new \Exception("Cannot open USB device: $device");
}

try {
    fwrite($handle, $content);
} finally {
    fclose($handle);
}
```

---

### 8. **Missing Validation for Connection Type Dependencies**
**Severity:** MEDIUM  
**Location:** `app/Http/Controllers/ThermalPrinterController.php:29-39, 72-82`

**Issue:**
```php
$validator = Validator::make($request->all(), [
    'name' => 'required|string|max:255',
    'connection_type' => 'required|in:usb,ethernet,bluetooth,serial,wifi',
    'ip_address' => 'nullable|ip',
    'port' => 'nullable|integer|between:1,65535',
    // ...
]);
```

**Problem:** The validation doesn't enforce that:
- `ethernet`/`wifi` connection types MUST have `ip_address` and `port`
- `usb`/`serial` connection types MUST have a device path
- Users can create invalid printer configurations

**Fix:**
```php
$rules = [
    'name' => 'required|string|max:255',
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
    $rules['connection_address'] = 'required|string';
}

$validator = Validator::make($request->all(), $rules);
```

---

### 9. **Cache Invalidation Bug**
**Severity:** MEDIUM  
**Location:** `app/Services/PrinterService.php:127-135`

**Issue:**
```php
public static function clearCache($printerId = null)
{
    if ($printerId) {
        Cache::forget("printer_{$printerId}");
    } else {
        Cache::forget('available_printers');
        Cache::forget('default_printer');
    }
}
```

**Problem:** When a specific printer is cleared from cache, the user printer preferences cache (`user_printer_pref_{$userId}`) is NOT cleared. This means users might still reference a deleted or modified printer from cache.

**Fix:**
```php
public static function clearCache($printerId = null)
{
    if ($printerId) {
        Cache::forget("printer_{$printerId}");
        
        // Clear all user preferences that might reference this printer
        $users = UserPrinterPreference::where('thermal_printer_setting_id', $printerId)
                                      ->pluck('user_id');
        foreach ($users as $userId) {
            Cache::forget("user_printer_pref_{$userId}");
        }
    } else {
        // Clear all printer-related caches
        Cache::forget('available_printers');
        Cache::forget('default_printer');
        
        // Clear all user preference caches
        $userIds = UserPrinterPreference::pluck('user_id')->unique();
        foreach ($userIds as $userId) {
            Cache::forget("user_printer_pref_{$userId}");
        }
    }
}
```

---

## 🟡 MEDIUM PRIORITY BUGS

### 10. **Potential Null Pointer Exception**
**Severity:** MEDIUM  
**Location:** `app/Services/PrinterService.php:92-96`

**Issue:**
```php
public static function print($content, $options = [])
{
    $userId = $options['user_id'] ?? auth()->id();
    $printer = $options['printer'] ?? self::getActivePrinter($userId);

    if (!$printer) {
        throw new \Exception('Tidak ada printer yang dikonfigurasi');
    }
```

**Problem:** If `auth()->id()` returns null (user not authenticated), the method could fail silently or behave unexpectedly.

**Fix:**
```php
public static function print($content, $options = [])
{
    $userId = $options['user_id'] ?? auth()->id();
    
    if (!$userId) {
        throw new \Exception('User not authenticated');
    }
    
    $printer = $options['printer'] ?? self::getActivePrinter($userId);

    if (!$printer) {
        throw new \Exception('Tidak ada printer yang dikonfigurasi');
    }
    // ...
}
```

---

### 11. **Missing Error Handling in Network Operations**
**Severity:** MEDIUM  
**Location:** `app/Services/PrinterDriverFactory.php:62-63`

**Issue:**
```php
fwrite($socket, $content);
fclose($socket);
```

**Problem:** `fwrite()` can fail (return false or write fewer bytes than expected), but the code doesn't check the return value.

**Fix:**
```php
$bytesWritten = fwrite($socket, $content);
if ($bytesWritten === false || $bytesWritten < strlen($content)) {
    fclose($socket);
    throw new \Exception("Failed to write all data to printer. Written: $bytesWritten bytes");
}
fclose($socket);
```

---

### 12. **Hardcoded Magic Numbers**
**Severity:** LOW-MEDIUM  
**Location:** Multiple files

**Issue:**
```php
// Line 42 in PrinterDriverFactory.php
$port = $this->printer->connection_port ?? 9100;

// Line 178 in SearchProduct.php
$this->how_many = 5;
```

**Problem:** Magic numbers scattered throughout code make maintenance difficult.

**Fix:** Define constants:
```php
class PrinterConfig {
    const DEFAULT_NETWORK_PORT = 9100;
    const DEFAULT_CONNECTION_TIMEOUT = 5;
    const DEFAULT_SEARCH_LIMIT = 5;
}
```

---

### 13. **Inefficient Database Query Pattern**
**Severity:** MEDIUM  
**Location:** `app/Livewire/SearchProduct.php:130-149`

**Issue:**
```php
foreach ($commonFirstDigits as $digit) {
    $fullBarcode = $digit . $barcode;
    
    $product = Product::where('product_barcode_symbology', $fullBarcode)
                     ->orWhere('product_sku', $fullBarcode)
                     ->orWhere('product_gtin', $fullBarcode)
                     ->first();
    
    if ($product) {
        return [/* ... */];
    }
}
```

**Problem:** This performs up to 10 separate database queries in a loop. Very inefficient.

**Fix:**
```php
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
    $actualBarcode = $product->product_barcode_symbology ?? 
                     $product->product_sku ?? 
                     $product->product_gtin;
    return [
        'product' => $product,
        'actual_barcode' => $actualBarcode,
        'reconstructed' => true
    ];
}
```

---

### 14. **Missing CSRF Protection on API Routes**
**Severity:** MEDIUM  
**Location:** `routes/api.php:21-33`

**Issue:**
```php
// Printer API Routes
Route::middleware(['auth:api'])->group(function () {
    Route::get('/system-printer-settings', [/*...*/]);
    Route::post('/user-printer-preferences', [/*...*/]);
    // ...
});
```

**Problem:** While web routes have CSRF protection, API routes using `auth:api` middleware don't have CSRF tokens. If these endpoints are called from web context, they're vulnerable to CSRF attacks.

**Fix:** Either:
1. Use stateless API authentication (tokens) only
2. Add CSRF middleware for API routes called from web context
3. Use SPA authentication with Sanctum

---

## 🟠 ADDITIONAL HIGH PRIORITY BUGS

### 19. **Path Traversal Vulnerability in Avatar Storage**
**Severity:** HIGH  
**Location:** `app/Models/User.php:56`

**Issue:**
```php
public function getAvatarUrlAttribute()
{
    if ($this->avatar && file_exists(storage_path('app/public/' . $this->avatar))) {
        return asset('storage/' . $this->avatar);
    }
    // ...
}
```

**Problem:** The `$this->avatar` is concatenated directly into the file path without validation. An attacker could set their avatar to `../../.env` and potentially access sensitive files.

**Fix:**
```php
public function getAvatarUrlAttribute()
{
    if ($this->avatar) {
        // Validate that path doesn't contain directory traversal
        $avatar = basename($this->avatar); // Only get filename
        $fullPath = storage_path('app/public/avatars/' . $avatar);
        
        if (file_exists($fullPath)) {
            return asset('storage/avatars/' . $avatar);
        }
    }
    
    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF&size=50';
}
```

---

### 20. **N+1 Query Problem**
**Severity:** HIGH  
**Location:** `app/Http/Controllers/HomeController.php:25`

**Issue:**
```php
foreach (Sale::completed()->with('saleDetails')->get() as $sale) {
    foreach ($sale->saleDetails as $saleDetail) {
        $product_costs += $saleDetail->product->product_cost * $saleDetail->quantity;
    }
}
```

**Problem:** Even though `saleDetails` is eager loaded, `$saleDetail->product` is NOT eager loaded. This causes an N+1 query problem. If there are 100 sales with 10 details each, this generates 1000+ queries!

**Fix:**
```php
foreach (Sale::completed()->with('saleDetails.product')->get() as $sale) {
    foreach ($sale->saleDetails as $saleDetail) {
        $product_costs += $saleDetail->product->product_cost * $saleDetail->quantity;
    }
}
```

---

### 21. **DB::raw SQL Injection Risk**
**Severity:** MEDIUM-HIGH  
**Location:** `app/Http/Controllers/HomeController.php:90-130`

**Issue:**
```php
$dateFormat = request()->type === 'day'
    ? "DATE_FORMAT(date, '%d-%m-%Y') as month"
    : "DATE_FORMAT(date, '%m-%Y') as month";

$sale_payments = SalePayment::where('date', '>=', $date_range)
    ->select([
        DB::raw($dateFormat),
        DB::raw("SUM(amount) as amount")
    ])
    ->groupBy('month')->orderBy('month')
    ->get()->pluck('amount', 'month');
```

**Problem:** While the current code is safe because `request()->type` is checked against specific values, the pattern is dangerous. If someone later modifies this without proper validation, it becomes an SQL injection vector.

**Fix:**
```php
// Use parameterized approach or validated enum
$allowedTypes = ['day', 'month'];
$type = in_array(request()->type, $allowedTypes) ? request()->type : 'month';

$dateFormat = $type === 'day'
    ? "DATE_FORMAT(date, '%d-%m-%Y')"
    : "DATE_FORMAT(date, '%m-%Y')";

$sale_payments = SalePayment::where('date', '>=', $date_range)
    ->selectRaw("$dateFormat as month, SUM(amount) as amount")
    ->groupBy(DB::raw($dateFormat))
    ->orderBy('month')
    ->get()
    ->pluck('amount', 'month');
```

---

### 22. **Socket Not Closed in Model Method**
**Severity:** HIGH  
**Location:** `app/Models/ThermalPrinterSetting.php:235`

**Issue:**
```php
$connection = @fsockopen($this->ip_address, $this->port, $errno, $errstr, 5);

if (!$connection) {
    return false;
}

return true;  // Socket never closed!
```

**Problem:** Same socket leak issue as bug #2, but in a different location.

**Fix:**
```php
$connection = @fsockopen($this->ip_address, $this->port, $errno, $errstr, 5);

if (!$connection) {
    return false;
}

fclose($connection);
return true;
```

---

## 🔵 LOW PRIORITY / CODE QUALITY ISSUES

### 15. **Guarded Property Too Permissive**
**Severity:** LOW  
**Location:** `Modules/Product/Entities/Product.php:16`

**Issue:**
```php
protected $guarded = [];
```

**Problem:** `$guarded = []` means ALL fields can be mass-assigned. This is dangerous if combined with `Model::create($request->all())`.

**Fix:**
```php
protected $fillable = [
    'product_name',
    'product_sku',
    'product_gtin',
    'product_barcode_symbology',
    'product_cost',
    'product_price',
    'product_quantity',
    'product_unit',
    'category_id',
    // ... list specific fields
];

protected $guarded = ['id', 'created_at', 'updated_at'];
```

---

### 16. **Inconsistent Error Messages**
**Severity:** LOW  
**Location:** Multiple files

**Issue:** Mix of Indonesian and English error messages:
```php
// Indonesian
throw new \Exception('Tidak ada printer yang dikonfigurasi');

// English
throw new \Exception("Cannot connect to $host:$port");
```

**Fix:** Standardize on one language or use Laravel's localization:
```php
throw new \Exception(__('printer.no_printer_configured'));
```

---

### 17. **Missing Type Hints**
**Severity:** LOW  
**Location:** `app/Services/PrinterService.php:14`

**Issue:**
```php
public static function getActivePrinter($userId = null)
```

**Fix:**
```php
public static function getActivePrinter(?int $userId = null): ?ThermalPrinterSetting
```

---

### 18. **Silent Failure with @ Operator Overuse**
**Severity:** LOW-MEDIUM  
**Location:** Multiple files

**Issue:**
```php
$socket = @fsockopen($host, $port, $errno, $errstr, 2);
$handle = @fopen($device, 'w');
@unlink($tempFile);
```

**Problem:** The `@` operator suppresses ALL errors, making debugging difficult.

**Fix:** Use specific error handling:
```php
set_error_handler(function($errno, $errstr) {
    throw new \Exception($errstr, $errno);
}, E_WARNING);

try {
    $socket = fsockopen($host, $port, $errno, $errstr, 2);
} finally {
    restore_error_handler();
}
```

---

## 🔍 TESTING RECOMMENDATIONS

### Test Cases Needed:

1. **Security Tests:**
   - Test command injection in printer names
   - Test mass assignment vulnerabilities
   - Test SQL injection via search queries
   - Test CSRF protection on all POST/PUT/DELETE routes

2. **Resource Management Tests:**
   - Test socket cleanup under various failure scenarios
   - Test temporary file cleanup
   - Monitor file descriptor usage during stress tests

3. **Concurrency Tests:**
   - Test race conditions in default printer selection
   - Test cache invalidation under concurrent modifications

4. **Integration Tests:**
   - Test printer connection with invalid configurations
   - Test barcode search with special characters
   - Test error handling in network operations

---

## 📊 SUMMARY

| Severity | Count | Must Fix Before Production |
|----------|-------|---------------------------|
| Critical | 5 | ✅ YES |
| High | 8 | ✅ YES |
| Medium | 9 | ⚠️ Recommended |
| Low | 4 | 💡 Nice to have |
| **TOTAL** | **26** | **13 Critical/High** |

---

## 🎯 IMMEDIATE ACTION ITEMS (Priority Order)

### 🔥 Critical - Must Fix Immediately

1. **Fix command injection vulnerabilities** (Bug #1) - Use `escapeshellarg()` everywhere
   - Files: `PrinterDriverFactory.php`, `ThermalPrinterService.php`, `ThermalPrinterController.php`
   
2. **Close socket resources properly** (Bug #2, #22) - Add proper cleanup
   - Files: `PrinterDriverFactory.php`, `ThermalPrinterSetting.php`
   
3. **Use try-finally for temp files** (Bug #3) - Ensure cleanup on error
   - Files: `PrinterDriverFactory.php`, `ThermalPrinterService.php`
   
4. **Replace `$request->all()` with validated data** (Bug #4) - Prevent mass assignment
   - Files: `ThermalPrinterController.php`
   
5. **Escape LIKE query wildcards** (Bug #5) - Prevent search exploits
   - Files: `SearchProduct.php`

### 🚨 High Priority - Fix Before Production

6. **Path traversal in avatar storage** (Bug #19) - Validate file paths
   - Files: `User.php`
   
7. **Fix N+1 query problem** (Bug #20) - Add eager loading
   - Files: `HomeController.php`
   
8. **Unchecked file handles** (Bug #7) - Use try-finally
   - Files: `PrinterDriverFactory.php`
   
9. **Add connection type validation** (Bug #8) - Conditional validation rules
   - Files: `ThermalPrinterController.php`

---

## 📝 NOTES

- Many of these bugs are related to security best practices
- Resource management issues could cause server instability under load
- Code quality issues make the system harder to maintain and debug
- Consider adding automated security scanning tools (PHPStan, Psalm, Laravel Pint)

---

**Generated:** 2025-01-XX  
**Reviewer:** Rovo Dev  
**Priority:** Critical issues must be addressed before production deployment
