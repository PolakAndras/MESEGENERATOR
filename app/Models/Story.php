<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    // A user_id nincs a $fillable-ben: a mese mindig a bejelentkezett felhasználóhoz kerül
    // ($user->stories()->create()), a beküldött adat nem írhatja át (Backend Schema 5.).
    // user_id is not fillable: a story always belongs to the logged-in user
    // ($user->stories()->create()); submitted data cannot override it (Backend Schema 5.).
    protected $fillable = [
        'title',
        'child_name',
        'child_age',
        'child_gender',
        'child_hair_color',
        'context',
        'page_count',
    ];

    protected $casts = [
        'child_age' => 'integer',
        'page_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Rendezve, hogy az olvasó mindig oldalsorrendben kapja az oldalakat.
    // Ordered so the reader always receives the pages in page order.
    public function pages()
    {
        return $this->hasMany(StoryPage::class)->orderBy('page_number');
    }
}
