<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Background;
use App\Models\Character;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * A katalógus írása (admin, PRD US-17, US-18). Itt van az „egy alaphelyzet szereplőnként” szabály (Backend Schema
 * 7.1) és a képcsere (12.): új fájl feltöltése, mentés, majd a régi fájl törlése. Törlés nincs (D-03).
 * Writes to the catalog (admin, PRD US-17, US-18). Holds the "one default action per character" rule (Backend
 * Schema 7.1) and image replacement (12.): upload the new file, save, then delete the old file. No deletion (D-03).
 */
class CatalogService
{
    public function createCharacter(array $data, string $defaultName, UploadedFile $image): Character
    {
        $path = $image->store('actions', 'images');

        return DB::transaction(function () use ($data, $defaultName, $path) {
            $character = Character::create($this->characterAttributes($data));
            $character->actions()->create(['name' => $defaultName, 'image_path' => $path, 'is_default' => true]);

            return $character;
        });
    }

    public function updateCharacter(Character $character, array $data): void
    {
        $character->update($this->characterAttributes($data));
    }

    public function createAction(Character $character, string $name, UploadedFile $image): Action
    {
        // Új cselekvés mindig nem alaphelyzet (Backend Schema 7.1). / A new action is never the default (7.1).
        return $character->actions()->create([
            'name' => $name,
            'image_path' => $image->store('actions', 'images'),
            'is_default' => false,
        ]);
    }

    public function updateAction(Action $action, string $name, ?UploadedFile $image): void
    {
        $this->replaceImage($action, $image, 'actions', ['name' => $name]);
    }

    public function makeDefault(Action $action): void
    {
        DB::transaction(function () use ($action) {
            Action::where('character_id', $action->character_id)->update(['is_default' => false]);
            $action->update(['is_default' => true]);
        });
    }

    public function createBackground(string $name, UploadedFile $image): Background
    {
        return Background::create(['name' => $name, 'image_path' => $image->store('backgrounds', 'images')]);
    }

    public function updateBackground(Background $background, string $name, ?UploadedFile $image): void
    {
        $this->replaceImage($background, $image, 'backgrounds', ['name' => $name]);
    }

    public function actionLimit(Character $character): int
    {
        return (int) config('story.actions_max.'.$character->type, 10);
    }

    // Állatnál a nem és a hajszín NULL (Backend Schema 8.6). / For animals gender and hair colour are NULL (8.6).
    private function characterAttributes(array $data): array
    {
        $isFigure = $data['type'] === Character::TYPE_CHILD_FIGURE;

        return [
            'name' => $data['name'],
            'type' => $data['type'],
            'gender' => $isFigure ? $data['gender'] : null,
            'hair_color' => $isFigure ? $data['hair_color'] : null,
        ];
    }

    private function replaceImage($model, ?UploadedFile $image, string $folder, array $attributes): void
    {
        $old = $model->image_path;
        if ($image !== null) {
            $attributes['image_path'] = $image->store($folder, 'images');
        }
        $model->update($attributes);

        // A régi fájl csak sikeres mentés után törlődik; minden mese az azonosítóra hivatkozik (Backend Schema D-05).
        // The old file is deleted only after a successful save; every story refers to the id (Backend Schema D-05).
        if ($image !== null && $old !== $model->image_path) {
            Storage::disk('images')->delete($old);
        }
    }
}
