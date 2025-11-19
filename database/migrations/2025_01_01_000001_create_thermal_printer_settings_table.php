<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('thermal_printer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Thermal Printer');
            $table->string('brand')->nullable(); // Eppos, Xprinter, Star, Epson, etc
            $table->string('model')->nullable(); // EP220II, XP-80C, TSP143, TM-T20, etc
            
            // Connection Settings
            $table->enum('connection_type', ['usb', 'ethernet', 'bluetooth', 'serial', 'wifi'])->default('usb');
            $table->string('ip_address')->nullable(); // untuk ethernet/wifi
            $table->integer('port')->default(9100); // untuk network printer
            $table->string('bluetooth_address')->nullable(); // untuk bluetooth
            $table->string('serial_port')->nullable(); // COM1, /dev/ttyUSB0, etc
            $table->integer('baud_rate')->default(115200); // untuk serial/bluetooth
            
            // Paper Settings
            $table->enum('paper_width', ['58', '80', '112'])->default('80'); // dalam mm
            $table->integer('paper_length')->default(0); // 0 = continuous
            $table->enum('paper_type', ['thermal', 'impact'])->default('thermal');
            
            // Print Settings
            $table->enum('print_speed', ['1', '2', '3', '4', '5'])->default('2'); // 1=fastest, 5=highest quality
            $table->enum('print_density', ['1', '2', '3', '4', '5'])->default('3'); // 1=lightest, 5=darkest
            $table->enum('character_set', ['PC437', 'PC850', 'PC852', 'PC858', 'PC866'])->default('PC437');
            $table->enum('font_size', ['small', 'normal', 'large'])->default('normal');
            $table->boolean('auto_cut')->default(true);
            $table->boolean('buzzer_enabled')->default(false);
            
            // ESC Commands Settings
            $table->json('esc_commands')->nullable(); // custom ESC commands untuk merk tertentu
            $table->string('init_command')->nullable(); // command untuk initialize printer
            $table->string('cut_command')->default('\x1B\x69'); // command untuk cut paper
            $table->string('cash_drawer_command')->nullable(); // command untuk buka cash drawer
            
            // Layout Settings
            $table->integer('margin_left')->default(0); // dalam mm
            $table->integer('margin_right')->default(0);
            $table->integer('margin_top')->default(0);
            $table->integer('margin_bottom')->default(0);
            $table->integer('line_spacing')->default(20); // ESC 3 parameter
            $table->integer('char_spacing')->default(0); // ESC SP parameter
            
            // Template Settings
            $table->boolean('print_logo')->default(false);
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->boolean('print_barcode')->default(true);
            $table->enum('barcode_position', ['top', 'bottom'])->default('bottom');
            
            // Status & Meta
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('capabilities')->nullable(); // what printer can do
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('thermal_printer_settings');
    }
};