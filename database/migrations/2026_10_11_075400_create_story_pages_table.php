<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoryPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Időbélyeg nincs: az oldal a mesével együtt jön létre és törlődik (Backend Schema 3.6).
        // No timestamps: a page is created and deleted together with its story (Backend Schema 3.6).
        Schema::create('story_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('page_number');
            // restrict: a mentett mese hátterének mindig léteznie kell (BR-08, D-03).
            // restrict: the background of a saved story must always exist (BR-08, D-03).
            $table->foreignId('background_id')->constrained()->restrictOnDelete();
            $table->text('text');
            // Egy mesén belül egy oldalszám csak egyszer szerepelhet.
            // A page number may appear only once within a story.
            $table->unique(['story_id', 'page_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('story_pages');
    }
}
