<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryPage extends Model
{
    use HasFactory;

    // A táblában nincs created_at / updated_at (Backend Schema 3.6).
    // The table has no created_at / updated_at (Backend Schema 3.6).
    public $timestamps = false;

    protected $fillable = [
        'page_number',
        'background_id',
        'text',
    ];

    protected $casts = [
        'page_number' => 'integer',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function background()
    {
        return $this->belongsTo(Background::class);
    }

    // z_index szerint, így a vászon a helyes rétegsorrendben rajzolja ki a figurákat (US-06 AC5).
    // Ordered by z_index so the canvas renders the figures in the correct layer order (US-06 AC5).
    public function placements()
    {
        return $this->hasMany(StoryPagePlacement::class)->orderBy('z_index');
    }
}
