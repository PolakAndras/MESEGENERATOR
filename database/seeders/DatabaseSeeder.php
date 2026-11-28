<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Sorrend a Backend Schema 11.3 szerint; mindkét seeder többször is futtatható.
     * Order per Backend Schema 11.3; both seeders are safe to run repeatedly.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminUserSeeder::class,
            CatalogSeeder::class,
            StoryTemplateSeeder::class,
        ]);
    }
}
