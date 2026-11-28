<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Egy kiegészítő mondatsablon a mesegyűjteményből (PRD 1.6 US-24): cím (témához kötve), helyszín- és pózmondat.
 * Helyőrzők: :name, :friend (cím), :Who / :who és :action (póz). A szereplők mindig alanyesetben állnak.
 * One supplementary sentence template of the story collection (PRD 1.6 US-24): title (per theme), place and pose
 * sentences. Placeholders: :name, :friend (title), :Who / :who and :action (pose). Characters stay in the nominative.
 */
class StorySentence extends Model
{
    public $timestamps = false;

    protected $fillable = ['story_theme_id', 'kind', 'key', 'text'];
}
