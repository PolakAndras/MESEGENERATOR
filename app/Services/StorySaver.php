<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Background;
use App\Models\Character;
use App\Models\Story;
use App\Models\User;
use App\Support\PlacementRules;
use App\Support\StoryLimits;
use Illuminate\Support\Facades\DB;

/**
 * Munkamenet → adatbázis (Backend Schema 9.4). Egyetlen tranzakcióban menti a mesét, oldalait és elhelyezéseit,
 * így félig mentett mese nem maradhat. A mese egyszer menthető: csak „generated” állapotból (US-14 AC2).
 *
 * Session → database (Backend Schema 9.4). Saves the story, its pages and placements in one transaction,
 * so a half-saved story can never remain. A story can be saved once: only from the "generated" state (US-14 AC2).
 */
class StorySaver
{
    /**
     * @return Story|null null, ha a vázlat nem menthető / null when the draft cannot be saved
     */
    public function save(StoryDraftService $drafts, User $user): ?Story
    {
        if ($drafts->status() !== StoryDraftService::STATUS_GENERATED || ! $this->isValid($drafts, $user)) {
            return null;
        }

        $child = $drafts->child();
        $settings = $drafts->settings();
        $generated = $drafts->generated();

        $story = DB::transaction(function () use ($drafts, $user, $child, $settings, $generated) {
            $story = $user->stories()->create([
                'title' => $generated['title'],
                'child_name' => $child['name'],
                'child_age' => $child['age'],
                'child_gender' => $child['gender'],
                'child_hair_color' => $child['hair_color'],
                'context' => $settings['context'],
                'page_count' => $settings['page_count'],
            ]);

            foreach ($drafts->pages() as $number => $page) {
                $storyPage = $story->pages()->create([
                    'page_number' => $number,
                    'background_id' => $page['background_id'],
                    'text' => $generated['pages'][$number],
                ]);
                foreach (PlacementRules::withZIndex($page['placements']) as $p) {
                    $storyPage->placements()->create([
                        'character_id' => $p['character_id'],
                        'action_id' => $p['action_id'],
                        'pos_x' => $p['pos_x'],
                        'pos_y' => $p['pos_y'],
                        'scale' => PlacementRules::scale(),
                        'z_index' => $p['z_index'],
                        'flipped' => (bool) ($p['flipped'] ?? false),
                    ]);
                }
            }

            return $story;
        });

        $drafts->markSaved($story->id);

        return $story;
    }

    // A teljes vázlat a „user” korlátai szerint, minden oldalon háttérrel, létező hivatkozásokkal (9.4/3.).
    // The whole draft within the "user" limits, every page with a background, all references existing (9.4/3.).
    private function isValid(StoryDraftService $drafts, User $user): bool
    {
        $limits = StoryLimits::for($user);
        $pages = $drafts->pages();
        $generated = $drafts->generated();

        if (count($pages) < $limits['pages_min'] || count($pages) > $limits['pages_max']) {
            return false;
        }

        foreach ($pages as $number => $page) {
            if ($page['background_id'] === null || ! Background::whereKey($page['background_id'])->exists()) {
                return false;
            }
            if (trim($generated['pages'][$number] ?? '') === '') {
                return false;
            }
            $animals = 0;
            foreach ($page['placements'] as $p) {
                $character = Character::find($p['character_id']);
                if ($character === null || ! Action::where('id', $p['action_id'])->where('character_id', $character->id)->exists()) {
                    return false;
                }
                $animals += $character->type === Character::TYPE_ANIMAL ? 1 : 0;
            }
            if ($animals > $limits['animals_per_page']) {
                return false;
            }
        }

        return true;
    }
}
