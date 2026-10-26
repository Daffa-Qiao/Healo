<?php

namespace Tests\Feature;

use App\Models\BmiRecord;
use App\Models\HealthJournal;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseTwoAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_private_records_on_web(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $bmi = BmiRecord::factory()->for($owner)->create();
        $journal = HealthJournal::factory()->for($owner)->create();
        $entry = $journal->entries()->create(['type' => 'note', 'content' => 'Private', 'sort_order' => 0]);

        $this->actingAs($intruder)->get('/health-journals/'.$journal->id)->assertNotFound();
        $this->actingAs($intruder)->put('/health-journals/'.$journal->id, $this->journalPayload())->assertNotFound();
        $this->actingAs($intruder)->delete('/health-journals/'.$journal->id)->assertNotFound();
        $this->actingAs($intruder)->delete('/bmi/'.$bmi->id)->assertNotFound();
        $this->actingAs($intruder)->put('/health-journal-entries/'.$entry->id, [
            'type' => 'note', 'content' => 'Stolen',
        ])->assertNotFound();
    }

    public function test_api_scopes_every_private_record_to_authenticated_owner(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $bmi = BmiRecord::factory()->for($owner)->create();
        $journal = HealthJournal::factory()->for($owner)->create();
        $entry = $journal->entries()->create(['type' => 'note', 'content' => 'Private', 'sort_order' => 0]);
        Sanctum::actingAs($intruder);

        $this->getJson('/api/v1/bmi-records/'.$bmi->id)->assertNotFound();
        $this->deleteJson('/api/v1/bmi-records/'.$bmi->id)->assertNotFound();
        $this->getJson('/api/v1/health-journals/'.$journal->id)->assertNotFound();
        $this->putJson('/api/v1/health-journal-entries/'.$entry->id, [
            'type' => 'note', 'content' => 'Stolen',
        ])->assertNotFound();
        $this->postJson('/api/v1/health-journals/'.$journal->id.'/entries', [
            'type' => 'note', 'content' => 'Stolen',
        ])->assertNotFound();
    }

    public function test_policies_do_not_give_admins_a_health_data_bypass(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $profile = HealthProfile::factory()->for($owner)->create();
        $bmi = BmiRecord::factory()->for($owner)->create();
        $journal = HealthJournal::factory()->for($owner)->create();
        $entry = $journal->entries()->create(['type' => 'note', 'content' => 'Private', 'sort_order' => 0]);

        $this->actingAs($admin);

        $this->assertFalse(Gate::allows('view', $profile));
        $this->assertFalse(Gate::allows('view', $bmi));
        $this->assertFalse(Gate::allows('view', $journal));
        $this->assertFalse(Gate::allows('view', $entry));
    }

    private function journalPayload(): array
    {
        return [
            'title' => 'Unauthorized',
            'journal_date' => now()->toDateString(),
            'notes' => 'Unauthorized',
        ];
    }
}
