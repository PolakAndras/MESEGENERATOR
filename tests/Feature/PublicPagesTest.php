<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// A két nyilvános oldal (S-01, S-02) betölt, magyarul szól, és a láblécből elérhető a tájékoztató (IP F1-14, US-20 AC1).
// The two public pages (S-01, S-02) load, are in Hungarian, and the footer links to the privacy notice (IP F1-14, US-20 AC1).
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_in_hungarian_with_privacy_link()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lang="hu"', false)
            ->assertSee('href="'.route('privacy').'"', false);
    }

    public function test_privacy_page_loads()
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('lang="hu"', false)
            ->assertSee(__('privacy.title'));
    }
}
