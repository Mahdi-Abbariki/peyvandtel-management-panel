<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->callOnce([
            PeyvandtelAdminSeeder::class,
            ServicesSeeder::class
        ]);
    }
}
