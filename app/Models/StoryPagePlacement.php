<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryPagePlacement extends Model
{
    use HasFactory;

    // A táblában nincs created_at / updated_at (Backend Schema 3.7).
    // The table has no created_at / updated_at (Backend Schema 3.7).
    public $timestamps = false;

    protected $fillable = [
        'character_id',
        'action_id',
        'pos_x',
        'pos_y',
        'scale',
        'z_index',
        'flipped',
    ];

    // A DECIMAL oszlopokat a PDO szövegként adja vissza; float-tá alakítva számolhatunk velük,
    // és a JSON-ban (Content Guidelines 11.3) is számként jelennek meg.
    // PDO returns DECIMAL columns as strings; casting to float lets us calculate with them,
    // and they appear as numbers in the JSON (Content Guidelines 11.3).
    protected $casts = [
        'pos_x' => 'float',
        'pos_y' => 'float',
        'scale' => 'float',
        'z_index' => 'integer',
        'flipped' => 'boolean',
    ];

    public function storyPage()
    {
        return $this->belongsTo(StoryPage::class);
    }

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function action()
    {
        return $this->belongsTo(Action::class);
    }
}
