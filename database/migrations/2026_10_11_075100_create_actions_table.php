<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            // restrict: amíg cselekvése van, a szereplő adatbázis-szinten sem törölhető (D-03).
            // restrict: a character cannot be deleted at database level while it has actions (D-03).
            $table->foreignId('character_id')->constrained()->restrictOnDelete();
            $table->string('name', 50);
            // Relatív útvonal az images tárolón, nem URL, hogy helyben és R2-n is működjön (NFR-11).
            // Relative path on the images disk, not a URL, so it works both locally and on R2 (NFR-11).
            $table->string('image_path');
            // Szereplőnként pontosan egy true; ezt a szolgáltatásréteg biztosítja, nem index (7.1, D-08).
            // Exactly one true per character; enforced by the service layer, not an index (7.1, D-08).
            $table->boolean('is_default')->default(false);
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
        Schema::dropIfExists('actions');
    }
}
