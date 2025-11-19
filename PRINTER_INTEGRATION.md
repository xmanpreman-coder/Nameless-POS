Integrating Mike42 ESC/POS library

This project now includes a guarded integration for the Mike42 ESC/POS library (mike42/escpos-php).

1) Install the library

From the project root run:

```powershell
composer require mike42/escpos-php:^3.0
```

2) Configuration

`config/printer.php` contains options:
- `usb_device_path` - default `/dev/usb/lp0` for Unix-like systems
- `windows_print_method` - method used on Windows (default `print`)
- `allow_system_commands` - set to `false` to disable calls to `exec`/`shell_exec`
- `preferred_driver` - set to `mike42` or `native`. When `mike42` is available the code will prefer using it.

You can set these via `.env`:

```
PRINTER_USB_DEVICE_PATH=/dev/usb/lp0
PRINTER_WINDOWS_PRINT_METHOD=print
PRINTER_ALLOW_SYSTEM_COMMANDS=true
PRINTER_PREFERRED_DRIVER=mike42
```

3) Usage

The service `App\Services\PrinterManager` is a thin wrapper that will use the installed library to print to network/Windows/file connectors. `App\Services\ThermalPrinterService` will try `PrinterManager` first and fall back to native methods.

4) Testing

After installing the composer package, run a test print (from the UI or using Tinker):

```powershell
php artisan tinker --execute="$p = \App\Models\ThermalPrinterSetting::first(); print_r($p->testConnection());"
```

To actually print a test receipt, use the UI `Print Test` or call the `printTestPage()` method from Tinker.

5) Notes

- The integration is guarded: if the library isn't installed the code will continue to use existing native methods.
- Mike42 supports many connectors (network, Windows, file, serial) and provides ESC/POS helpers.
- If you need Bluetooth support, additional native platform bindings may be required.
