<?php

namespace Modules\Scanner\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScannerSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'scanner_type',
        'camera_settings',
        'usb_settings', 
        'bluetooth_settings',
        'external_settings',
        'beep_sound',
        'vibration',
        'scan_mode',
        'scan_timeout',
        'auto_focus',
        'preferred_camera'
    ];

    protected $casts = [
        'camera_settings' => 'array',
        'usb_settings' => 'array',
        'bluetooth_settings' => 'array',
        'external_settings' => 'array',
        'beep_sound' => 'boolean',
        'vibration' => 'boolean',
        'auto_focus' => 'boolean'
    ];

    public static function getSettings()
    {
        return self::first() ?? self::create([
            'scanner_type' => 'camera',
            'beep_sound' => true,
            'vibration' => true,
            'scan_mode' => 'auto',
            'scan_timeout' => 30,
            'auto_focus' => true,
            'preferred_camera' => 'back'
        ]);
    }
}