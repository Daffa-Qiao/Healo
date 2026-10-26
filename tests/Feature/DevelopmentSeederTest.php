<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_development_accounts_and_demo_health_data_are_seeded_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@healo.test')->firstOrFail();
        $demo = User::where('email', 'demo@healo.test')->firstOrFail();

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertSame(UserRole::User, $demo->role);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertNotNull($demo->healthProfile);
        $this->assertCount(3, $demo->bmiRecords);
        $this->assertCount(1, $demo->healthJournals);
        $this->assertCount(3, $demo->healthJournals->first()->entries);
        $this->assertNotNull($demo->nutritionProfile);
        $this->assertGreaterThanOrEqual(25, $demo->meals()->count());
        $this->assertDatabaseCount('healthy_menus', 6);
        $this->assertCount(1, $demo->healthConversations);
        $this->assertCount(2, $demo->healthConversations->first()->messages);
        $this->assertCount(1, $demo->symptomAssessments);
        $this->assertCount(2, $demo->healthGoals);
        $this->assertCount(1, $demo->healthPlans);
        $this->assertGreaterThan(0, $demo->healthPlans->first()->tasks()->count());
        $this->assertSame(2, User::count());
    }
}
