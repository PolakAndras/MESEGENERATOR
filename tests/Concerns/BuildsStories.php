<?php

namespace Tests\Concerns;

use App\Models\Background;
use App\Models\Character;
use Illuminate\Support\Facades\Storage;

/**
 * Közös tesztsegéd: kis katalógus factoryval és a varázsló végigjárása HTTP-kérésekkel, ahogy a böngésző tenné.
 * Így minden teszt a valódi útvonalakon, validáción és munkameneten megy át.
 * Shared test helper: a small catalog from factories and walking the wizard via HTTP requests, as a browser would.
 * Every test thus goes through the real routes, validation and session.
 */
trait BuildsStories
{
    protected Character $figure;

    protected Character $fox;

    protected Character $rabbit;

    protected Background $forest;

    protected function makeCatalog(): void
    {
        Storage::fake('images');
        $this->figure = Character::factory()->childFigure('girl', 'red')->create(['name' => 'Lány – vörös']);
        $this->fox = Character::factory()->animal()->create(['name' => 'Róka']);
        $this->rabbit = Character::factory()->animal()->create(['name' => 'Nyúl']);
        $this->forest = Background::factory()->create(['name' => 'Éjszakai erdő']);
    }

    protected function childData(array $overrides = []): array
    {
        return array_merge([
            'child_name' => 'Panni',
            'child_age' => 5,
            'child_gender' => 'girl',
            'child_hair_color' => 'red',
        ], $overrides);
    }

    protected function pageData(array $overrides = []): array
    {
        return array_merge([
            'target' => 'next',
            'background_id' => $this->forest->id,
            'child_enabled' => 1,
            'child' => ['action_id' => null, 'pos_x' => 50, 'pos_y' => 90],
            'animals' => [
                ['character_id' => $this->fox->id, 'action_id' => null, 'pos_x' => 20, 'pos_y' => 90, 'slot' => 1],
            ],
        ], $overrides);
    }

    protected function completeSteps(int $pageCount = 8): void
    {
        $this->post(route('stories.create.child.store'), $this->childData());
        $this->post(route('stories.create.settings.store'), ['context' => 'barátság', 'page_count' => $pageCount]);
    }

    protected function fillAllPages(int $pageCount = 8): void
    {
        for ($n = 1; $n < $pageCount; $n++) {
            $this->post(route('stories.create.page.store', $n), $this->pageData());
        }
    }

    // A teljes vendég-folyamat a generálásig (demo generátorral). / The whole guest flow up to generation (demo generator).
    protected function generateStory(int $pageCount = 8)
    {
        $this->completeSteps($pageCount);
        $this->fillAllPages($pageCount);

        return $this->post(route('stories.generate'), $this->pageData(['target' => null]));
    }
}
