<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoryPagePlacementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // A gyerekfigura is ide kerül, nem külön oszlopba, így a 2. fázis fotója is elhelyezés lehet (D-07).
        // The child figure is stored here too, not in a separate column, so a phase-2 photo can also be a placement (D-07).
        Schema::create('story_page_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->restrictOnDelete();
            // Mindig kitöltve: választás nélkül az alaphelyzet azonosítója kerül ide (BR-09).
            // Always filled: without a choice the default action's id is stored (BR-09).
            $table->foreignId('action_id')->constrained()->restrictOnDelete();
            // DECIMAL(6,3), mert a szélsőpozíció 13,125 (Content Guidelines 8.4); a FLOAT kerekítene.
            // DECIMAL(6,3) because the edge position is 13.125 (Content Guidelines 8.4); FLOAT would round.
            $table->decimal('pos_x', 6, 3);
            $table->decimal('pos_y', 6, 3);
            $table->decimal('scale', 5, 2);
            $table->unsignedTinyInteger('z_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('story_page_placements');
    }
}
