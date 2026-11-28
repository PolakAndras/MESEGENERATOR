<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A mesegyűjtemény egy jelenete (PRD 1.6 US-24): szövegváltozatok és a kép jelölése. Helyőrzők: :name, :kid,
 * :friend/:Friend; mind alanyesetben vagy névutóval áll, így nincs ragozási hiba. A seeder tölti fel, időbélyeg nélkül.
 * One scene of the story collection (PRD 1.6 US-24): text variants and picture hints. Placeholders: :name, :kid,
 * :friend/:Friend, always nominative or with a postposition, so no inflection can go wrong. Filled by the seeder.
 */
class StoryScene extends Model
{
    public $timestamps = false;

    protected $fillable = ['story_theme_id', 'plot', 'step', 'weight', 'place', 'child_pose', 'friend_pose', 'extra_animal', 'texts'];

    protected $casts = ['texts' => 'array', 'extra_animal' => 'boolean'];
}
