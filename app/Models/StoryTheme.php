<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A mese témája a 2. lépés lenyíló listájában (PRD US-24). A seeder tölti fel, időbélyeg nélkül.
 * The story theme in the step 2 drop-down (PRD US-24). Filled by the seeder, no timestamps.
 */
class StoryTheme extends Model
{
    public $timestamps = false;

    protected $fillable = ['slug', 'name', 'sort'];

    public function sentences()
    {
        return $this->hasMany(StorySentence::class);
    }

    public function scenes()
    {
        return $this->hasMany(StoryScene::class);
    }
}
