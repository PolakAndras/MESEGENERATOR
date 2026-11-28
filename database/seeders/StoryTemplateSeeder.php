<?php

namespace Database\Seeders;

use App\Models\StoryScene;
use App\Models\StorySentence;
use App\Models\StoryTheme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * A mesegyűjtemény betöltése a database/seeders/data/story_templates.php fájlból (PRD 1.6 US-24).
 * Minden futáskor teljesen újratölt, egy tranzakcióban: a sablonokat csak ez a fájl módosítja, így frissítéskor
 * (php artisan db:seed --class=...) a szerkesztett szöveg azonnal érvényes lesz, és nem keletkezik duplikáció.
 *
 * Loads the story collection from database/seeders/data/story_templates.php (PRD 1.6 US-24).
 * Every run fully reloads it in one transaction: only this file edits the templates, so after an update the edited
 * text applies at once and nothing is duplicated.
 */
class StoryTemplateSeeder extends Seeder
{
    public function run()
    {
        $data = require database_path('seeders/data/story_templates.php');

        DB::transaction(function () use ($data) {
            // A jelenetek és a témához kötött mondatok a témával együtt törlődnek (cascade).
            // Scenes and theme-bound sentences are deleted together with the theme (cascade).
            StorySentence::query()->delete();
            StoryTheme::query()->delete();

            $sentences = [];
            $scenes = [];
            $sort = 0;
            foreach ($data['themes'] as $slug => $theme) {
                $themeId = StoryTheme::create(['slug' => $slug, 'name' => $theme['name'], 'sort' => ++$sort])->id;
                foreach ($theme['title'] as $text) {
                    $sentences[] = ['story_theme_id' => $themeId, 'kind' => 'title', 'key' => null, 'text' => $text];
                }
                foreach ($theme['plots'] as $plot => $steps) {
                    foreach ($steps as $i => [$weight, $place, $childPose, $friendPose, $extra, $textA, $textB]) {
                        $scenes[] = [
                            'story_theme_id' => $themeId,
                            'plot' => $plot + 1,
                            'step' => $i + 1,
                            'weight' => $weight,
                            'place' => $place,
                            'child_pose' => $childPose,
                            'friend_pose' => $friendPose,
                            'extra_animal' => $extra,
                            'texts' => json_encode([$textA, $textB], JSON_UNESCAPED_UNICODE),
                        ];
                    }
                }
            }

            foreach (['place', 'action'] as $kind) {
                foreach ($data[$kind] as $key => $variants) {
                    foreach ($variants as $text) {
                        $sentences[] = ['story_theme_id' => null, 'kind' => $kind, 'key' => $key === '' ? null : $key, 'text' => $text];
                    }
                }
            }

            foreach (array_chunk($sentences, 200) as $chunk) {
                StorySentence::insert($chunk);
            }
            foreach (array_chunk($scenes, 100) as $chunk) {
                StoryScene::insert($chunk);
            }
        });
    }
}
