<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_preferred_locale(): void
    {
        $user = User::factory()->create(['locale' => 'id']);

        $this->actingAs($user)
            ->patch('/locale', ['locale' => 'en'])
            ->assertRedirect();

        $this->assertSame('en', $user->refresh()->locale);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/locale', ['locale' => 'xx'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors('locale');
    }
}
