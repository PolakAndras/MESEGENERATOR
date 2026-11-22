<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tükrözött figura (PRD US-22, Backend Schema 1.1): a kép vízszintesen tükrözve jelenik meg, hogy a szereplő a
 * másik irányba nézzen. Új, additív migráció, mert a lefutott migrációt nem módosítjuk (NFR-10).
 * Flipped figure (PRD US-22, Backend Schema 1.1): the image is mirrored horizontally so the character faces the
 * other way. A new, additive migration, because migrations that already ran are never changed (NFR-10).
 */
class AddFlippedToStoryPagePlacementsTable extends Migration
{
    public function up()
    {
        Schema::table('story_page_placements', function (Blueprint $table) {
            $table->boolean('flipped')->default(false)->after('z_index');
        });
    }

    public function down()
    {
        Schema::table('story_page_placements', function (Blueprint $table) {
            $table->dropColumn('flipped');
        });
    }
}
