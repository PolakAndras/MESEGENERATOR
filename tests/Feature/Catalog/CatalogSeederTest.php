<?php

namespace Tests\Feature\Catalog;

use App\Models\Action;
use App\Models\Background;
use App\Models\Character;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// A varázsló és minden későbbi teszt arra épít, hogy minden szereplőnek pontosan egy alaphelyzete van (IP F1-13).
// The wizard and every later test rely on each character having exactly one default action (IP F1-13).
class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_running_twice_creates_the_catalog_without_duplicates()
    {
        Storage::fake('images');

        $this->seed(CatalogSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->assertSame(10, Character::where('type', Character::TYPE_ANIMAL)->count());
        $this->assertSame(8, Character::where('type', Character::TYPE_CHILD_FIGURE)->count());
        $this->assertSame(90, Action::count());  // 18 alaphelyzet + 72 póz / 18 defaults + 72 poses
        $this->assertSame(4, Background::count());
    }

    public function test_every_character_has_exactly_one_default_action()
    {
        Storage::fake('images');

        $this->seed(CatalogSeeder::class);

        Character::withCount(['actions as default_count' => fn ($q) => $q->where('is_default', true)])
            ->get()
            ->each(fn ($character) => $this->assertSame(1, $character->default_count, $character->name));
    }

    public function test_every_image_path_exists_on_the_images_disk()
    {
        Storage::fake('images');

        $this->seed(CatalogSeeder::class);

        $paths = Action::pluck('image_path')->merge(Background::pluck('image_path'));
        foreach ($paths as $path) {
            Storage::disk('images')->assertExists($path);
        }
    }
}
