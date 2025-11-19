<?php
// database/migrations/[TIMESTAMP]_create_printer_schema.php
// Run: php artisan make:migration create_printer_schema

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
        // ============ 1. THERMAL PRINTER SETTINGS TABLE ============
        Schema::create('thermal_printer_settings', function (Blueprint $table) {
            $table->id();

            // === IDENTITAS PRINTER ===
            $table->string('name')->default('Default Thermal Printer');
            $table->string('brand')->nullable(); // Eppos, Xprinter, Star, Epson, Citizen
            $table->string('model')->nullable(); // EP220II, XP-80C, TSP143, TM-T20

            // === CONNECTION SETTINGS ===
            $table->enum('connection_type', ['usb', 'ethernet', 'bluetooth', 'serial', 'wifi'])
                ->default('usb');
            $table->string('ip_address')->nullable(); // Untuk ethernet/wifi
            $table->integer('port')->default(9100); // Standard ESC/POS port
            $table->string('bluetooth_address')->nullable(); // Format: 00:11:22:33:44:55
            $table->string('serial_port')->nullable(); // COM1, /dev/ttyUSB0, etc
            $table->integer('baud_rate')->default(115200); // Serial/Bluetooth speed

            // === PAPER SETTINGS ===
            $table->enum('paper_width', ['58', '80', '112'])->default('80'); // mm
            $table->integer('paper_length')->default(0); // 0 = continuous
            $table->enum('paper_type', ['thermal', 'impact'])->default('thermal');

            // === PRINT SETTINGS ===
            $table->enum('print_speed', ['1', '2', '3', '4', '5'])->default('2');
            // 1=fastest, 5=highest quality
            $table->enum('print_density', ['1', '2', '3', '4', '5'])->default('3');
            // 1=lightest, 5=darkest
            $table->string('character_set')->default('PC437');
            // PC437, PC850, PC852, PC858, PC866
            $table->enum('font_size', ['small', 'normal', 'large'])->default('normal');
            $table->boolean('auto_cut')->default(true);
            $table->boolean('buzzer_enabled')->default(false);

            // === ESC COMMANDS ===
            $table->json('esc_commands')->nullable(); // Custom ESC commands per brand
            $table->string('init_command')->nullable(); // Initialization command
            $table->string('cut_command')->default('\x1B\x69'); // Default cut command
            $table->string('cash_drawer_command')->nullable(); // Cash drawer open

            // === LAYOUT SETTINGS ===
            $table->integer('margin_left')->default(0);
            $table->integer('margin_right')->default(0);
            $table->integer('margin_top')->default(0);
            $table->integer('margin_bottom')->default(0);
            $table->integer('line_spacing')->default(20); // ESC 3 parameter
            $table->integer('char_spacing')->default(0); // ESC SP parameter

            // === TEMPLATE SETTINGS ===
            $table->boolean('print_logo')->default(false);
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->boolean('print_barcode')->default(true);
            $table->enum('barcode_position', ['top', 'bottom'])->default('bottom');

            // === STATUS & META ===
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('capabilities')->nullable(); // Printer capabilities
            $table->text('notes')->nullable();

            // === TIMESTAMPS ===
            $table->timestamps();

            // === INDEXES ===
            $table->index(['is_default', 'is_active']);
            $table->index('connection_type');
            $table->index('brand');
            $table->index('is_active');

            // === CONSTRAINTS ===
            // Ensure only one default printer
            $table->unique('is_default', 'unique_default')
                ->where('is_default', true);
        });

        // ============ 2. PRINTER SETTINGS TABLE (System-wide defaults) ============
        Schema::create('printer_settings', function (Blueprint $table) {
            $table->id();

            // === GLOBAL DEFAULTS ===
            $table->string('receipt_paper_size')->default('80mm');
            // 58mm, 80mm, letter, a4
            $table->string('default_receipt_printer')->nullable();
            // References thermal_printer_settings.name
            $table->boolean('auto_print_receipt')->default(false);
            $table->boolean('print_customer_copy')->default(false);
            $table->integer('receipt_copies')->default(1);

            // === ADVANCED ===
            $table->text('thermal_printer_commands')->nullable();
            $table->json('printer_profiles')->nullable();

            // === TIMESTAMPS ===
            $table->timestamps();
        });

        // ============ 3. USER PRINTER PREFERENCES TABLE ============
        Schema::create('user_printer_preferences', function (Blueprint $table) {
            $table->id();

            // === FOREIGN KEY ===
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // === USER'S PRINTER CHOICE ===
            $table->string('receipt_printer_name')->nullable();
            // References thermal_printer_settings.name
            $table->string('receipt_paper_size')->default('80mm');

            // === PRINT BEHAVIOR ===
            $table->boolean('auto_print_receipt')->default(false);
            $table->boolean('print_customer_copy')->default(false);

            // === ADVANCED SETTINGS ===
            $table->json('printer_settings')->nullable();
            // For user overrides and custom settings

            // === TIMESTAMPS ===
            $table->timestamps();

            // === INDEXES ===
            $table->unique('user_id');
            $table->index('receipt_printer_name');
        });

        // ============ 4. PRINTER CONNECTION LOGS TABLE (Optional) ============
        Schema::create('printer_connection_logs', function (Blueprint $table) {
            $table->id();

            // === FOREIGN KEY ===
            $table->foreignId('thermal_printer_setting_id')
                ->constrained('thermal_printer_settings')
                ->onDelete('cascade');

            // === LOG DATA ===
            $table->string('status'); // success, failed, timeout
            $table->string('message')->nullable();
            $table->integer('response_time')->nullable(); // milliseconds
            $table->json('details')->nullable();

            // === TIMESTAMPS ===
            $table->timestamp('tested_at');
            $table->timestamps();

            // === INDEXES ===
            $table->index(['thermal_printer_setting_id', 'tested_at']);
            $table->index('status');
        });

        // ============ 5. PRINT JOBS TABLE (Optional) ============
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();

            // === FOREIGN KEYS ===
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('thermal_printer_setting_id')
                ->nullable()
                ->constrained('thermal_printer_settings')
                ->onDelete('set null');

            // === JOB DETAILS ===
            $table->string('document_type'); // receipt, barcode, label, etc
            $table->longText('content'); // Print content (HTML/ESC commands)
            $table->string('status')->default('pending'); // pending, printing, completed, failed
            $table->string('error_message')->nullable();

            // === PRINT SETTINGS ===
            $table->integer('copies')->default(1);
            $table->boolean('auto_cut')->default(true);
            $table->boolean('open_drawer')->default(false);
            $table->json('settings')->nullable();

            // === TRACKING ===
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);

            // === TIMESTAMPS ===
            $table->timestamps();

            // === INDEXES ===
            $table->index(['user_id', 'status']);
            $table->index(['thermal_printer_setting_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order (due to foreign keys)
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('printer_connection_logs');
        Schema::dropIfExists('user_printer_preferences');
        Schema::dropIfExists('printer_settings');
        Schema::dropIfExists('thermal_printer_settings');
    }
};
