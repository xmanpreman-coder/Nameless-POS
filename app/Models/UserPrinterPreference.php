<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrinterPreference extends Model
{
    protected $fillable = [
        'user_id',
        'thermal_printer_setting_id',
        'receipt_printer_name',
        'receipt_paper_size',
        'auto_print_receipt',
        'print_customer_copy',
        'printer_settings'
    ];

    protected $casts = [
        'auto_print_receipt' => 'boolean',
        'print_customer_copy' => 'boolean',
        'printer_settings' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(ThermalPrinterSetting::class, 'thermal_printer_setting_id');
    }
}