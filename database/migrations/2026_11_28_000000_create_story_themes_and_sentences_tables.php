<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A mesegyűjtemény (PRD 1.5 US-24, Backend Schema 1.2): 15 téma és a mondatsablonok, amelyekből a TemplateStoryGenerator
 * AI nélkül állítja össze a mese szövegét. A sablonokat seeder tölti fel (StoryTemplateSeeder), a felület nem módosítja.
 *
 * The story collection (PRD 1.5 US-24, Backend Schema 1.2): 15 themes and the sentence templates from which
 * TemplateStoryGenerator assembles the story text without AI. A seeder fills them (StoryTemplateSeeder); the UI never edits them.
 */
class CreateStoryThemesAndSentencesTables extends Migration
{
    public function up()
    {
        Schema::create('story_themes', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->unsignedTinyInteger('sort')->default(0);
        });

        Schema::create('story_sentences', function (Blueprint $table) {
            $table->id();
            // NULL = témától független mondat (helyszín, cselekvés …) / NULL = theme-independent sentence (scene, action …)
            $table->foreignId('story_theme_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('kind', 20);             // title | opening | middle | ending | scene | action | together | favorite | quiet
            $table->string('key', 50)->nullable();  // middle: szakasz 1–8; action: cselekvés neve; scene: háttér neve
            $table->string('text', 500);
            $table->index(['story_theme_id', 'kind']);
            $table->index(['kind', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('story_sentences');
        Schema::dropIfExists('story_themes');
    }
}
