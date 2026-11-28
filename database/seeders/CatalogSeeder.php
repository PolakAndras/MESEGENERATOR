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

    // Állatonként 4 póz az alaphelyzet mellé (PRD US-23): fájlnév-utótag => cselekvés neve (ezt látja a szülő, és ez
    // alapján választ mondatot a mesegyűjtemény). / 4 poses per animal next to the default (PRD US-23): file suffix =>
    // action name (shown to the parent; the story collection picks sentences by it).
    public const ANIMAL_POSES = [
        'bear' => ['sit' => 'ül', 'run' => 'fut', 'sleep' => 'alszik', 'wave' => 'integet'],
        'deer' => ['graze' => 'legel', 'run' => 'fut', 'sleep' => 'alszik', 'jump' => 'ugrik'],
        'fox' => ['sit' => 'ül', 'run' => 'fut', 'sleep' => 'alszik', 'sniff' => 'szimatol'],
        'dog' => ['sit' => 'ül', 'run' => 'fut', 'sleep' => 'alszik', 'paw' => 'pacsit ad'],
        'lamb' => ['graze' => 'legel', 'run' => 'fut', 'sleep' => 'alszik', 'jump' => 'ugrál'],
        'cat' => ['sit' => 'ül', 'run' => 'fut', 'sleep' => 'alszik', 'wash' => 'mosakszik'],
        'rabbit' => ['eat' => 'répát eszik', 'run' => 'fut', 'sleep' => 'alszik', 'jump' => 'ugrik'],
        'hedgehog' => ['run' => 'fut', 'sleep' => 'alszik', 'curl' => 'összegömbölyödik', 'apple' => 'almát visz'],
        'mouse' => ['run' => 'fut', 'sleep' => 'alszik', 'cheese' => 'sajtot eszik', 'rear' => 'ágaskodik'],
        'frog' => ['jump' => 'ugrik', 'sleep' => 'alszik', 'croak' => 'brekeg', 'wave' => 'integet'],
    ];

    // A gyerekfigura 4 pózza mind a 8 változatnál (BR-06: alaphelyzet + 4). / The child figure's 4 poses (BR-06).
    public const CHILD_POSES = ['wave' => 'integet', 'run' => 'szalad', 'sit' => 'ül', 'sleep' => 'alszik'];

    public const BACKGROUNDS = [
        'Éjszakai erdő' => 'backgrounds/forest-night.svg',
        'Napos rét' => 'backgrounds/meadow-day.svg',
        'Esti hálószoba' => 'backgrounds/bedroom-evening.svg',
        // A téli mesékhez (PRD 1.6 US-24) / for the winter stories
        'Havas táj' => 'backgrounds/snowy-day.svg',
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
            $this->poses($character, $slug, self::ANIMAL_POSES[$slug]);
        }

        foreach (Character::GENDERS as $gender) {
            foreach (Character::HAIR_COLORS as $hair) {
                $character = Character::updateOrCreate(
                    ['type' => Character::TYPE_CHILD_FIGURE, 'name' => self::GENDER_NAMES[$gender].' – '.self::HAIR_NAMES[$hair]],
                    ['gender' => $gender, 'hair_color' => $hair]
                );
                $this->defaultAction($character, "actions/child-{$gender}-{$hair}-default.svg");
                $this->poses($character, "child-{$gender}-{$hair}", self::CHILD_POSES);
            }
        }

        foreach (self::BACKGROUNDS as $name => $path) {
            Background::updateOrCreate(['name' => $name], ['image_path' => $path]);
        }
    }

    // A repóban lévő képeket ugyanazzal a relatív úttal másolja az images tárolóra, így helyben és
    // a szerveren is ugyanazon az úton érhetők el (Tech Stack 4.3).
    // Copies the images from the repo to the images disk with the same relative path, so they are
    // reachable the same way locally and on the server (Tech Stack 4.3).
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

    // További pózok; a kulcs szereplő + név, így újrafuttatáskor sem duplikálódnak (IP F1-13).
    // Further poses; the key is character + name, so they are not duplicated when run again (IP F1-13).
    private function poses(Character $character, string $prefix, array $poses): void
    {
        foreach ($poses as $suffix => $name) {
            $action = Action::firstOrNew(['character_id' => $character->id, 'name' => $name, 'is_default' => false]);
            $action->character_id = $character->id;
            $action->fill(['image_path' => "actions/{$prefix}-{$suffix}.svg"]);
            $action->save();
        }
    }
}
