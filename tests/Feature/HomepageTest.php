<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_homepage_renders_the_public_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('landing.hero_title'))
            ->assertSee(__('landing.feature_title'))
            ->assertSee(__('landing.why_title'))
            ->assertSee('images/healo-hero-wellness.png', false)
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false);
    }

    public function test_authenticated_homepage_points_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee(route('dashboard'), false)
            ->assertDontSee(route('register'), false);
    }
}
