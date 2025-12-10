<?php

return [
    // Authentication
    'user_not_authenticated' => 'User not authenticated',
    
    // Printer Configuration
    'no_printer_configured' => 'No printer configured',
    
    // Connection Errors
    'cannot_connect' => 'Cannot connect to :host::port - :error',
    'connection_failed' => 'Connection failed: :error (:code)',
    
    // Write Errors
    'write_failed' => 'Failed to write data to printer',
    'partial_write' => 'Partial write: wrote :written of :total bytes',
    'usb_write_failed' => 'Failed to write to USB device',
    
    // USB Errors
    'windows_usb_not_supported' => 'Use network printer for Windows USB devices',
    'cannot_open_usb' => 'Cannot open USB device: :device',
    
    // Serial Port Errors
    'serial_not_supported' => 'Serial printer support requires additional library installation',
    
    // Bluetooth Errors
    'bluetooth_not_supported' => 'Bluetooth printing requires mobile app implementation',
    
    // Windows Printer
    'windows_only' => 'Windows printer driver only works on Windows',
    'print_command_failed' => 'Print command failed: :output',
    
    // General
    'unsupported_connection_type' => 'Unsupported connection type: :type',
];
