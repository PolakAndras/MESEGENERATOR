<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A mesegyűjtemény jelenetei (PRD 1.6 US-24, Backend Schema 1.3): témánként két kézzel írt cselekmény, 10–10 jelenettel.
 * A jelenet a szövegváltozatok mellett a képi jelölést is tárolja (helyszín, a gyerek és a társ póztípusa,
 * mellékszereplő), így véletlen módban a kép ugyanazt meséli, mint a szöveg. Új tábla, mert a korábbi, már lefutott
 * migrációt nem módosítjuk; a story_sentences a címeket, helyszín- és pózmondatokat tárolja tovább.
 *
 * The story collection's scenes (PRD 1.6 US-24, Backend Schema 1.3): two hand-written plots per theme, 10 scenes each.
 * Next to the text variants a scene stores its picture hints (place, the child's and the companion's pose kind, extra
 * animal), so in random mode the picture tells the same thing as the text. A new table because the earlier, already
 * run migration is not edited; story_sentences keeps the titles, place and pose sentences.
 */
class CreateStoryScenesTable extends Migration
{
    public function up()
    {
        Schema::create('story_scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_theme_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('plot');            // a téma cselekményének sorszáma / plot number within the theme
            $table->unsignedTinyInteger('step');            // 1–10, időrendben / in time order
            $table->unsignedTinyInteger('weight');          // 3 = mag, 2 = fontos, 1 = színesítő / core, important, colour
            $table->string('place', 10);                    // home | day | night | snow (config story.scenes.background_kinds)
            $table->string('child_pose', 10);               // stand | move | rest | sleep | happy (config story.scenes.pose_kinds)
            $table->string('friend_pose', 10);
            $table->boolean('extra_animal')->default(false);
            $table->json('texts');                          // a szövegváltozatok / the text variants
            $table->unique(['story_theme_id', 'plot', 'step']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('story_scenes');
    }
}
