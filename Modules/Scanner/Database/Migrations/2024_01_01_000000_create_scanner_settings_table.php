<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScannerSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scanner_settings', function (Blueprint $table) {
            $table->id();
            $table->string('scanner_type')->default('camera'); // camera, usb, bluetooth
            $table->json('camera_settings')->nullable(); // camera specific settings
            $table->json('usb_settings')->nullable(); // USB scanner settings
            $table->json('bluetooth_settings')->nullable(); // Bluetooth scanner settings
            $table->boolean('beep_sound')->default(true);
            $table->boolean('vibration')->default(true);
            $table->string('scan_mode')->default('auto'); // auto, manual
            $table->integer('scan_timeout')->default(30); // seconds
            $table->boolean('auto_focus')->default(true);
            $table->string('preferred_camera')->default('back'); // back, front
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scanner_settings');
    }
}