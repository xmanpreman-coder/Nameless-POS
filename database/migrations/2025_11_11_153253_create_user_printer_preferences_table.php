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
        Schema::create('user_printer_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('receipt_printer_name')->nullable();
            $table->string('receipt_paper_size')->default('80mm');
            $table->boolean('auto_print_receipt')->default(false);
            $table->boolean('print_customer_copy')->default(false);
            $table->json('printer_settings')->nullable(); // For advanced printer settings
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_printer_preferences');
    }
};
