<?php

return [
    // Default USB device path for Unix-like systems. Can be overridden per-deployment.
    'usb_device_path' => env('PRINTER_USB_DEVICE_PATH', '/dev/usb/lp0'),

    // Windows print method. Keep as 'print' by default but can be changed to use PowerShell approach.
    'windows_print_method' => env('PRINTER_WINDOWS_PRINT_METHOD', 'print'),

    // Whether to allow using shell_exec/exec for system printing. If false, code will return informative errors.
    'allow_system_commands' => env('PRINTER_ALLOW_SYSTEM_COMMANDS', true),

    // Preferred printer driver/library. Options: 'mike42' or 'native'.
    'preferred_driver' => env('PRINTER_PREFERRED_DRIVER', 'mike42'),
];
