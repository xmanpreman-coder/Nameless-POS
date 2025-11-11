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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_settings');
    }
};
