<?php

return [
    // Authentication
    'user_not_authenticated' => 'Pengguna tidak terautentikasi',
    
    // Printer Configuration
    'no_printer_configured' => 'Tidak ada printer yang dikonfigurasi',
    
    // Connection Errors
    'cannot_connect' => 'Tidak dapat terhubung ke :host::port - :error',
    'connection_failed' => 'Koneksi gagal: :error (:code)',
    
    // Write Errors
    'write_failed' => 'Gagal menulis data ke printer',
    'partial_write' => 'Penulisan sebagian: menulis :written dari :total bytes',
    'usb_write_failed' => 'Gagal menulis ke perangkat USB',
    
    // USB Errors
    'windows_usb_not_supported' => 'Gunakan network printer untuk perangkat USB Windows',
    'cannot_open_usb' => 'Tidak dapat membuka perangkat USB: :device',
    
    // Serial Port Errors
    'serial_not_supported' => 'Dukungan serial printer memerlukan instalasi library tambahan',
    
    // Bluetooth Errors
    'bluetooth_not_supported' => 'Pencetakan Bluetooth memerlukan implementasi aplikasi mobile',
    
    // Windows Printer
    'windows_only' => 'Driver printer Windows hanya bekerja di Windows',
    'print_command_failed' => 'Perintah cetak gagal: :output',
    
    // General
    'unsupported_connection_type' => 'Tipe koneksi tidak didukung: :type',
];
