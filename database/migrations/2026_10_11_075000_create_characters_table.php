<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Nincs softDeletes(): katalóguselem nem törölhető (Backend Schema D-03).
        // No softDeletes(): catalogue items cannot be deleted (Backend Schema D-03).
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            // animal | child_figure; VARCHAR, hogy a 2. fázis child_photo típusa migráció nélkül jöhessen.
            // animal | child_figure; VARCHAR so the phase-2 child_photo type needs no migration.
            $table->string('type', 20)->index();
            // Csak gyerekfiguránál kitöltve, állatnál NULL; az egyediséget a Form Request ellenőrzi (D-08).
            // Only filled for child figures, NULL for animals; uniqueness is checked by the Form Request (D-08).
            $table->string('gender', 10)->nullable();
            $table->string('hair_color', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('characters');
    }
}
