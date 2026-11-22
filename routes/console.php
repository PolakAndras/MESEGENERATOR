<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Éles induláskor (docker/entrypoint.sh) a katalógus csak üres adatbázisba töltődik, mert a Render gyakran újraindul,
// és a seeder minden indításnál felülírná az admin módosításait (például egy átnevezett állat újra létrejönne).
// On production start (docker/entrypoint.sh) the catalog is only seeded into an empty database, because Render
// restarts often and the seeder would overwrite the admin's changes on every start (a renamed animal would reappear).
Artisan::command('catalog:install', function () {
    if (\App\Models\Character::exists()) {
        $this->info('A katalógus már létezik, kihagyva. / Catalog already exists, skipped.');

        return;
    }
    $this->call('db:seed', ['--class' => \Database\Seeders\CatalogSeeder::class, '--force' => true]);
})->purpose('Seed the catalog only when the database has no characters yet');
