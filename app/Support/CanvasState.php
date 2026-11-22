<?php

namespace App\Support;

use App\Models\Action;
use App\Models\Background;
use App\Models\Character;
use App\Models\StoryPage;
use Illuminate\Support\Facades\Storage;

/**
 * A vászon (K-06) adatai a CG 11.3 szerinti szerkezetben, a munkamenet-vázlatból vagy a mentett oldalból.
 * Ugyanezt kapja a Blade-komponens (kirendereléshez) és a JS (szerkesztéshez), később egy React-komponens is.
 *
 * Canvas (K-06) data in the CG 11.3 shape, built from the session draft or a saved page.
 * The Blade component (rendering), the JS (editing) and later a React component all receive the same structure.
 */
class CanvasState
{
    public static function fromDraftPage(array $page): array
    {
        $catalog = self::catalog();
        $background = $catalog['backgrounds'][$page['background_id']] ?? null;
        $placements = [];

        foreach (PlacementRules::withZIndex(array_values($page['placements'])) as $order => $p) {
            $character = $catalog['characters'][$p['character_id']] ?? null;
            $action = $catalog['actions'][$p['action_id']] ?? null;
            if ($character === null || $action === null) {
                continue;
            }
            // Az order a hozzáadás sorrendje: a JS ezzel dönti el a rétegsorrendet egyenlő pos_y-nál (CG 8.4).
            // order is the insertion order: the JS uses it to break layer-order ties on equal pos_y (CG 8.4).
            $placements[] = self::placement($character, $action, $p) + ['slot' => $p['slot'] ?? null, 'order' => $order];
        }

        return [
            'background' => $background ? self::background($background) : null,
            'placements' => self::sortByZ($placements),
        ];
    }

    public static function fromStoryPage(StoryPage $page): array
    {
        $placements = [];
        foreach ($page->placements as $p) {
            $placements[] = self::placement($p->character, $p->action, $p->toArray());
        }

        return [
            'background' => self::background($page->background),
            'placements' => self::sortByZ($placements),
        ];
    }

    public static function imageUrl(string $path): string
    {
        return Storage::disk('images')->url($path);
    }

    // A katalógus kicsi, ezért kérésenként egyszer töltjük be (az alkalmazás-konténerben, így tesztenként friss),
    // és oldalanként, figuránként nincs külön lekérdezés.
    // The catalog is small, so it is loaded once per request (in the app container, so it is fresh per test),
    // with no query per page or per figure.
    private static function catalog(): array
    {
        if (! app()->bound('canvas.catalog')) {
            app()->instance('canvas.catalog', [
                'backgrounds' => Background::all()->keyBy('id')->all(),
                'characters' => Character::all()->keyBy('id')->all(),
                'actions' => Action::all()->keyBy('id')->all(),
            ]);
        }

        return app('canvas.catalog');
    }

    private static function background(Background $background): array
    {
        return ['id' => $background->id, 'name' => $background->name, 'url' => self::imageUrl($background->image_path)];
    }

    private static function placement(Character $character, Action $action, array $p): array
    {
        return [
            'character_id' => $character->id,
            'action_id' => $action->id,
            'character_name' => $character->name,
            'action_name' => $action->name,
            'type' => $character->type,
            'url' => self::imageUrl($action->image_path),
            'pos_x' => (float) $p['pos_x'],
            'pos_y' => (float) $p['pos_y'],
            'scale' => (float) $p['scale'],
            'z_index' => (int) $p['z_index'],
            // Tükrözött figura: a CSS vízszintesen fordítja meg (PRD US-22). / Flipped figure: mirrored by CSS (US-22).
            'flipped' => (bool) ($p['flipped'] ?? false),
        ];
    }

    private static function sortByZ(array $placements): array
    {
        usort($placements, fn ($a, $b) => $a['z_index'] <=> $b['z_index']);

        return $placements;
    }
}
