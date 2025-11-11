<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterSetting extends Model
{
    protected $fillable = [
        'receipt_paper_size',
        'auto_print_receipt',
        'default_receipt_printer',
        'print_customer_copy',
        'receipt_copies',
        'thermal_printer_commands',
        'printer_profiles'
    ];

    protected $casts = [
        'auto_print_receipt' => 'boolean',
        'print_customer_copy' => 'boolean',
        'receipt_copies' => 'integer',
        'printer_profiles' => 'array'
    ];

    public static function getInstance()
    {
        return self::firstOrCreate([]);
    }
}