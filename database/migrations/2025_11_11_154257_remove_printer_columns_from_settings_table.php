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
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_paper_size', 
                'auto_print_receipt', 
                'default_receipt_printer', 
                'printer_profiles'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('receipt_paper_size')->default('80mm')->after('login_logo');
            $table->boolean('auto_print_receipt')->default(false)->after('receipt_paper_size');
            $table->string('default_receipt_printer')->nullable()->after('auto_print_receipt');
            $table->text('printer_profiles')->nullable()->after('default_receipt_printer');
        });
    }
};
