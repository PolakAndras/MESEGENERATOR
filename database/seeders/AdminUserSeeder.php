<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Az admin fiók a .env ADMIN_EMAIL és ADMIN_PASSWORD értékéből (PRD US-19, Backend Schema 11.1).
 * Ha bármelyik üres, csak figyelmeztet, így a seeder admin nélkül is lefut (pl. új gépen).
 *
 * The admin account from ADMIN_EMAIL and ADMIN_PASSWORD in .env (PRD US-19, Backend Schema 11.1).
 * If either is empty it only warns, so seeding still works without an admin (e.g. on a new machine).
 */
class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $email = config('story.admin.email');
        $password = config('story.admin.password');

        if (empty($email) || empty($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD üres, admin fiók nem jött létre. / Empty, no admin account created.');

            return;
        }

        // updateOrCreate: többszöri futtatás sem hoz létre második fiókot (US-19 AC1). A role és a
        // privacy_accepted_at nem fillable, ezért közvetlenül állítjuk.
        // updateOrCreate: running it again never creates a second account (US-19 AC1). role and
        // privacy_accepted_at are not fillable, so they are set directly.
        $user = User::firstOrNew(['email' => $email]);
        $user->fill(['name' => 'Admin', 'password' => Hash::make($password)]);
        $user->role = User::ROLE_ADMIN;
        $user->privacy_accepted_at = $user->privacy_accepted_at ?? now();
        $user->save();
    }
}
