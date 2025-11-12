<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExternalSettingsToScannerSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scanner_settings', function (Blueprint $table) {
            $table->json('external_settings')->nullable()->after('bluetooth_settings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scanner_settings', function (Blueprint $table) {
            $table->dropColumn('external_settings');
        });
    }
}