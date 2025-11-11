<?php

namespace Modules\Scanner\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Scanner\Entities\ScannerSetting;

class ScannerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Create default scanner settings
        ScannerSetting::firstOrCreate([], [
            'scanner_type' => 'camera',
            'beep_sound' => true,
            'vibration' => true,
            'scan_mode' => 'auto',
            'scan_timeout' => 30,
            'auto_focus' => true,
            'preferred_camera' => 'back',
            'camera_settings' => [
                'resolution' => '640x480',
                'frame_rate' => 30
            ],
            'usb_settings' => [],
            'bluetooth_settings' => []
        ]);
    }
}