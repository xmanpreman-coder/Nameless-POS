<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('printer_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('printer_settings', 'printer_profiles')) {
                $table->json('printer_profiles')->nullable()->after('thermal_printer_commands');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printer_settings', function (Blueprint $table) {
            if (Schema::hasColumn('printer_settings', 'printer_profiles')) {
                $table->dropColumn('printer_profiles');
            }
        });
    }
};
