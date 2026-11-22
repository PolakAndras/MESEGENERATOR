<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleAndPrivacyToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Új migráció az alapprojekt users-migrációjának módosítása helyett, mert lefutott
        // migrációt nem írunk át (Backend Schema 3.1).
        // A new migration instead of editing the base users migration, because migrations
        // that have already run are never changed (Backend Schema 3.1).
        Schema::table('users', function (Blueprint $table) {
            // VARCHAR, nem ENUM: új szerepkör migráció nélkül felvehető (D-06, NFR-10).
            // VARCHAR, not ENUM: a new role can be added without a migration (D-06, NFR-10).
            $table->string('role', 20)->default('user')->after('password')->index();
            // Nullable, mert az elfogadás ideje csak regisztrációkor (vagy az admin seederében) kerül be.
            // Nullable because the acceptance time is only set at registration (or by the admin seeder).
            $table->timestamp('privacy_accepted_at')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'privacy_accepted_at']);
        });
    }
}
