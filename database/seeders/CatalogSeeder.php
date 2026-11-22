<?php

namespace Database\Seeders;

use App\Models\Action;
use App\Models\Background;
use App\Models\Character;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * A katalógus (állatok, gyerekfigurák, alaphelyzetek, hátterek) és a kezdőképek (Backend Schema 11.2).
 * Többször is futtatható: minden sor updateOrCreate-tel jön létre, így nem duplikál.
 *
 * The catalog (animals, child figures, default actions, backgrounds) and the starter images (Backend Schema 11.2).
 * Safe to run repeatedly: every row is created with updateOrCreate, so nothing is duplicated.
 */
class CatalogSeeder extends Seeder
{
    // Név => slug; a slug adja a kezdőkép fájlnevét (CG 8.5). / Name => slug; the slug gives the image file name (CG 8.5).
    public const ANIMALS = [
        'Medve' => 'bear',
        'Szarvas' => 'deer',
        'Róka' => 'fox',
        'Kutya' => 'dog',
        'Bárány' => 'lamb',
        'Nyúl' => 'rabbit',
        'Macska' => 'cat',
        'Sün' => 'hedgehog',
        'Egér' => 'mouse',
        'Béka' => 'frog',
    ];

    public const BACKGROUNDS = [
        'Éjszakai erdő' => 'backgrounds/forest-night.svg',
        'Napos rét' => 'backgrounds/meadow-day.svg',
        'Esti hálószoba' => 'backgrounds/bedroom-evening.svg',
    ];

    private const GENDER_NAMES = ['girl' => 'Lány', 'boy' => 'Fiú'];

    private const HAIR_NAMES = ['blond' => 'szőke', 'brown' => 'barna', 'black' => 'fekete', 'red' => 'vörös'];

    public function run()
    {
        $this->copyImages();

        foreach (self::ANIMALS as $name => $slug) {
            $character = Character::updateOrCreate(
                ['type' => Character::TYPE_ANIMAL, 'name' => $name],
                ['gender' => null, 'hair_color' => null]
            );
            $this->defaultAction($character, "actions/{$slug}-default.svg");
        }

        foreach (Character::GENDERS as $gender) {
            foreach (Character::HAIR_COLORS as $hair) {
                $character = Character::updateOrCreate(
                    ['type' => Character::TYPE_CHILD_FIGURE, 'name' => self::GENDER_NAMES[$gender].' – '.self::HAIR_NAMES[$hair]],
                    ['gender' => $gender, 'hair_color' => $hair]
                );
                $this->defaultAction($character, "actions/child-{$gender}-{$hair}-default.svg");
            }
        }

        foreach (self::BACKGROUNDS as $name => $path) {
            Background::updateOrCreate(['name' => $name], ['image_path' => $path]);
        }
    }

    // A repóban lévő képeket ugyanazzal a relatív úttal tölti fel az images tárolóra, így helyben és
    // élesben (R2) is ugyanúgy érhetők el (Tech Stack 4.3).
    // Uploads the images from the repo to the images disk with the same relative path, so they are
    // reachable the same way locally and in production (R2) (Tech Stack 4.3).
    private function copyImages(): void
    {
        $source = database_path('seeders/images');

        foreach (File::allFiles($source) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            Storage::disk('images')->put($relative, $file->getContents());
        }
    }

    // A kulcs character_id + is_default, így szereplőnként mindig pontosan egy alaphelyzet marad (Backend Schema 7.1).
    // The key is character_id + is_default, so each character always keeps exactly one default action (Backend Schema 7.1).
    private function defaultAction(Character $character, string $path): void
    {
        $action = Action::firstOrNew(['character_id' => $character->id, 'is_default' => true]);
        $action->character_id = $character->id;
        $action->fill(['name' => 'áll', 'image_path' => $path, 'is_default' => true]);
        $action->save();
    }
}
