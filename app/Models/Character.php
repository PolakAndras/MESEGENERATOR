<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    use HasFactory;

    // Az érvényes értékek itt vannak, nem az adatbázisban (VARCHAR, nem ENUM); a Form Requestek ezeket használják (D-06).
    // Valid values live here, not in the database (VARCHAR, not ENUM); the Form Requests use them (D-06).
    public const TYPE_ANIMAL = 'animal';
    public const TYPE_CHILD_FIGURE = 'child_figure';
    public const GENDERS = ['girl', 'boy'];
    public const HAIR_COLORS = ['blond', 'brown', 'black', 'red'];

    protected $fillable = [
        'name',
        'type',
        'gender',
        'hair_color',
    ];

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    // hasOne szűréssel, mert szereplőnként pontosan egy alaphelyzet van (Backend Schema 7.1).
    // hasOne with a filter, because each character has exactly one default action (Backend Schema 7.1).
    public function defaultAction()
    {
        return $this->hasOne(Action::class)->where('is_default', true);
    }
}
