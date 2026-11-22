<?php

namespace Tests\Feature\Admin;

use App\Models\Action;
use App\Models\Background;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// F8: US-17 AC1 (törlés nélkül), AC3, AC5 · US-18 AC1 (törlés nélkül) · BR-05 · admin meselista.
// F8: US-17 AC1 (without deletion), AC3, AC5 · US-18 AC1 (without deletion) · BR-05 · admin story list.
class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('images');
        $this->admin = User::factory()->admin()->create();
    }

    private function svg(string $name = 'figura.svg'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"></svg>');
    }

    // Valódi 1×1-es PNG fejléccel; a GD bővítmény nem kell hozzá (UploadedFile::fake()->image() igényelné).
    // A real 1×1 PNG header; no GD extension needed (UploadedFile::fake()->image() would require it).
    private function png(string $name = 'kep.png', int $paddingKb = 0): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $png.str_repeat("\0", $paddingKb * 1024));
    }

    public function test_non_admins_get_403_and_guests_go_to_login()
    {
        $this->get(route('admin.characters.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.characters.index'))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('admin.stories.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.characters.index'))->assertOk();
    }

    public function test_admin_creates_an_animal_with_its_default_action()
    {
        $this->actingAs($this->admin)->post(route('admin.characters.store'), [
            'name' => 'Ló',
            'type' => 'animal',
            'gender' => 'girl',
            'default_action_name' => 'áll',
            'default_action_image' => $this->svg(),
        ])->assertRedirect();

        $horse = Character::where('name', 'Ló')->first();
        $this->assertNull($horse->gender);
        $this->assertTrue($horse->defaultAction->is_default);
        Storage::disk('images')->assertExists($horse->defaultAction->image_path);
    }

    public function test_admin_updates_a_character_and_there_is_no_delete_route()
    {
        $fox = Character::factory()->animal()->create(['name' => 'Róka']);

        $this->actingAs($this->admin)->put(route('admin.characters.update', $fox), ['name' => 'Vörös róka', 'type' => 'animal'])
            ->assertRedirect(route('admin.characters.edit', $fox));
        $this->assertSame('Vörös róka', $fox->fresh()->name);

        $this->actingAs($this->admin)->delete('/admin/characters/'.$fox->id)->assertStatus(405);
    }

    public function test_child_figure_gender_and_hair_must_be_unique()
    {
        Character::factory()->childFigure('girl', 'red')->create();

        $this->actingAs($this->admin)->post(route('admin.characters.store'), [
            'name' => 'Másik lány',
            'type' => 'child_figure',
            'gender' => 'girl',
            'hair_color' => 'red',
            'default_action_name' => 'áll',
            'default_action_image' => $this->svg(),
        ])->assertSessionHasErrors('gender');
    }

    public function test_image_format_and_size_are_checked()
    {
        $fox = Character::factory()->animal()->create();

        $this->actingAs($this->admin)->post(route('admin.characters.actions.store', $fox), [
            'name' => 'ugrik',
            'image' => UploadedFile::fake()->create('kep.gif', 10, 'image/gif'),
        ])->assertSessionHasErrors('image');

        $this->actingAs($this->admin)->post(route('admin.characters.actions.store', $fox), [
            'name' => 'ugrik',
            'image' => $this->png('kep.png', 3000),
        ])->assertSessionHasErrors('image');

        $this->actingAs($this->admin)->post(route('admin.characters.actions.store', $fox), [
            'name' => 'ugrik',
            'image' => $this->png(),
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, $fox->actions()->count());
    }

    public function test_action_limit_is_enforced_on_the_server()
    {
        $fox = Character::factory()->animal()->create();
        Action::factory()->count(config('story.actions_max.animal') - 1)->create(['character_id' => $fox->id]);

        $this->actingAs($this->admin)->post(route('admin.characters.actions.store', $fox), [
            'name' => 'tizenegyedik',
            'image' => $this->svg(),
        ])->assertRedirect(route('admin.characters.edit', $fox))->assertSessionHas('flash.type', 'error');

        $this->assertSame(10, $fox->actions()->count());
    }

    public function test_make_default_keeps_exactly_one_default()
    {
        $fox = Character::factory()->animal()->create();
        $sit = Action::factory()->create(['character_id' => $fox->id, 'name' => 'ül']);

        $this->actingAs($this->admin)->post(route('admin.actions.make-default', $sit))->assertRedirect();

        $this->assertSame(1, $fox->actions()->where('is_default', true)->count());
        $this->assertTrue($sit->fresh()->is_default);
    }

    public function test_replacing_an_image_deletes_the_old_file()
    {
        $old = $this->png('regi.png')->store('backgrounds', 'images');
        $background = Background::create(['name' => 'Rét', 'image_path' => $old]);

        $this->actingAs($this->admin)->put(route('admin.backgrounds.update', $background), [
            'name' => 'Napos rét',
            'image' => $this->png('uj.png'),
        ])->assertRedirect(route('admin.backgrounds.index'));

        $background->refresh();
        $this->assertSame('Napos rét', $background->name);
        Storage::disk('images')->assertMissing($old);
        Storage::disk('images')->assertExists($background->image_path);
    }

    public function test_admin_creates_a_background_and_there_is_no_delete_route()
    {
        $this->actingAs($this->admin)->post(route('admin.backgrounds.store'), [
            'name' => 'Tengerpart',
            'image' => $this->png('tenger.png'),
        ])->assertRedirect(route('admin.backgrounds.index'));

        $background = Background::where('name', 'Tengerpart')->first();
        $this->assertNotNull($background);
        $this->actingAs($this->admin)->delete('/admin/backgrounds/'.$background->id)->assertStatus(405);
    }

    public function test_admin_pages_render()
    {
        $fox = Character::factory()->animal()->create();
        $background = Background::factory()->create();

        foreach ([
            route('admin.characters.index'), route('admin.characters.create'), route('admin.characters.edit', $fox),
            route('admin.characters.actions.create', $fox), route('admin.actions.edit', $fox->defaultAction),
            route('admin.backgrounds.index'), route('admin.backgrounds.create'), route('admin.backgrounds.edit', $background),
            route('admin.stories.index'),
        ] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }
}
