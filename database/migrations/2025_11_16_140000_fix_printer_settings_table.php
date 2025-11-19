<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table exists and add missing columns
        if (Schema::hasTable('printer_settings')) {
            Schema::table('printer_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('printer_settings', 'receipt_paper_size')) {
                    $table->string('receipt_paper_size')->default('80mm');
                }
                if (!Schema::hasColumn('printer_settings', 'auto_print_receipt')) {
                    $table->boolean('auto_print_receipt')->default(false);
                }
                if (!Schema::hasColumn('printer_settings', 'default_receipt_printer')) {
                    $table->string('default_receipt_printer')->nullable();
                }
                if (!Schema::hasColumn('printer_settings', 'print_customer_copy')) {
                    $table->boolean('print_customer_copy')->default(false);
                }
                if (!Schema::hasColumn('printer_settings', 'receipt_copies')) {
                    $table->integer('receipt_copies')->default(1);
                }
                if (!Schema::hasColumn('printer_settings', 'thermal_printer_commands')) {
                    $table->text('thermal_printer_commands')->nullable();
                }
            });
        } else {
            // Create table if it doesn't exist
            Schema::create('printer_settings', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_paper_size')->default('80mm');
                $table->boolean('auto_print_receipt')->default(false);
                $table->string('default_receipt_printer')->nullable();
                $table->boolean('print_customer_copy')->default(false);
                $table->integer('receipt_copies')->default(1);
                $table->text('thermal_printer_commands')->nullable();
                $table->json('printer_profiles')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Don't drop columns in down method to avoid data loss
    }
};