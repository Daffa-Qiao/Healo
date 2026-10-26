<?php

namespace Tests\Feature;

use App\Models\HealthJournal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_a_journal_and_its_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/health-journals', [
            'title' => '<b>Kondisi pagi</b>',
            'journal_date' => now()->toDateString(),
            'mood' => 'good',
            'complaint' => 'Sedikit pusing',
            'symptoms' => "Pusing\nLelah",
            'notes' => 'Tidur tujuh jam',
            'weight_kg' => 71.5,
            'systolic' => 120,
            'diastolic' => 80,
            'sleep_hours' => 7.5,
            'water_liters' => 2.2,
            'exercise_minutes' => 30,
        ])->assertSessionHasNoErrors();

        $journal = $user->healthJournals()->with('entries')->firstOrFail();
        $this->assertSame('Kondisi pagi', $journal->title);
        $this->assertCount(4, $journal->entries);
        $this->assertSame('71.50', $journal->weight_kg);
        $this->assertSame(120, $journal->systolic);
        $this->assertSame(80, $journal->diastolic);

        $this->actingAs($user)->put('/health-journals/'.$journal->id, [
            'title' => 'Kondisi siang',
            'journal_date' => now()->toDateString(),
            'mood' => 'neutral',
            'notes' => 'Sudah lebih nyaman',
        ])->assertSessionHasNoErrors()->assertRedirect('/health-journals/'.$journal->id);

        $entry = $journal->refresh()->entries()->firstOrFail();
        $this->actingAs($user)->put('/health-journal-entries/'.$entry->id, [
            'type' => 'note',
            'content' => 'Catatan diperbarui',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Catatan diperbarui', $entry->refresh()->content);

        $this->actingAs($user)->delete('/health-journal-entries/'.$entry->id)->assertSessionHasNoErrors();
        $this->actingAs($user)->delete('/health-journals/'.$journal->id)->assertRedirect('/health-journals');
        $this->assertSoftDeleted('health_journals', ['id' => $journal->id]);
    }

    public function test_journal_content_is_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        $journal = HealthJournal::factory()->for($user)->create();
        $entry = $journal->entries()->create([
            'type' => 'complaint',
            'content' => 'Nyeri kepala rahasia',
            'sort_order' => 0,
        ]);

        $raw = DB::table('health_journal_entries')->where('id', $entry->id)->value('content');

        $this->assertStringNotContainsString('Nyeri kepala rahasia', $raw);
        $this->assertSame('Nyeri kepala rahasia', $entry->refresh()->content);
    }

    public function test_journal_requires_content_and_valid_metadata(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/health-journals', [
            'title' => '',
            'journal_date' => now()->addDay()->toDateString(),
            'mood' => 'invalid',
        ])->assertSessionHasErrors(['title', 'journal_date', 'mood', 'complaint', 'symptoms', 'notes']);
    }

    public function test_journal_dashboard_uses_owned_daily_metrics_for_score_and_charts(): void
    {
        $user = User::factory()->create();
        $user->healthProfile()->create([
            'height_cm' => 170,
            'weight_kg' => 70,
            'birth_date' => today()->subYears(30),
            'consented_at' => now(),
        ]);

        HealthJournal::factory()->for($user)->create([
            'journal_date' => today()->subDay(),
            'mood' => 'good',
            'weight_kg' => 71.5,
            'systolic' => 120,
            'diastolic' => 80,
            'sleep_hours' => 8,
            'water_liters' => 2,
            'exercise_minutes' => 30,
        ]);
        HealthJournal::factory()->for($user)->create([
            'journal_date' => today(),
            'mood' => 'very_good',
            'weight_kg' => 71,
            'systolic' => 118,
            'diastolic' => 78,
            'sleep_hours' => 8.5,
            'water_liters' => 2.5,
            'exercise_minutes' => 45,
        ]);

        $this->actingAs($user)->get('/health-journals')
            ->assertOk()
            ->assertViewIs('health-journals.dashboard')
            ->assertViewHas('dashboard', fn (array $dashboard): bool => $dashboard['days_analyzed'] === 2
                && $dashboard['score'] !== null
                && $dashboard['latest']['systolic'] === 118.0)
            ->assertSee('journal-weight-chart', false)
            ->assertSee('journal-bp-chart', false);
    }
}
