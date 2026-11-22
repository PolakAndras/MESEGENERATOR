<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            // cascade: fióktörléskor a mesék is véglegesen törlődnek (US-13 AC2).
            // cascade: deleting the account permanently deletes its stories too (US-13 AC2).
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            // A gyerek adatai a mesénél vannak, mert mentés után már nem a munkamenetből jönnek (US-14 AC1).
            // The child's data lives on the story, because after saving it no longer comes from the session (US-14 AC1).
            $table->string('child_name', 50);
            $table->unsignedTinyInteger('child_age');
            $table->string('child_gender', 10);
            $table->string('child_hair_color', 10);
            $table->string('context', 1000);
            $table->unsignedTinyInteger('page_count');
            $table->timestamps();
            // A Meséim lista a legújabbal kezd (US-15 AC1), ezért a created_at indexelt.
            // The My stories list starts with the newest (US-15 AC1), so created_at is indexed.
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stories');
    }
}
