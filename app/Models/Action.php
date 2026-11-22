<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    // A character_id nincs a $fillable-ben: a cselekvés a szereplő kapcsolatán át jön létre
    // ($character->actions()->create()), és utólag nem költözhet másik szereplőhöz (Backend Schema 3.3, 5.).
    // character_id is not fillable: an action is created through the character relation
    // ($character->actions()->create()) and cannot move to another character later (Backend Schema 3.3, 5.).
    protected $fillable = [
        'name',
        'image_path',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class);
    }
}
