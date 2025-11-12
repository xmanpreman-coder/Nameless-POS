<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Entities\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Log::info('UnitSeeder: Running...');
        $count_before = Unit::count();
        Log::info("UnitSeeder: Count before seeding: " . $count_before);

        $units = [
            ['name' => 'Piece', 'short_name' => 'pc'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Liter', 'short_name' => 'lt'],
            ['name' => 'Pack', 'short_name' => 'pack'],
            ['name' => 'Set', 'short_name' => 'set'],
            ['name' => 'Dozen', 'short_name' => 'dz'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['name' => $unit['name']], $unit);
        }

        $count_after = Unit::count();
        Log::info("UnitSeeder: Count after seeding: " . $count_after);
        Log::info('UnitSeeder: Finished.');
    }
}
