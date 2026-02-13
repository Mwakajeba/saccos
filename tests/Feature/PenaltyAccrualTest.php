<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\AccruedPenalty;
use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\GlTransaction;
use App\Jobs\AccruePenaltyJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class PenaltyAccrualTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_accrues_penalties_and_posts_to_gl_and_journal()
    {
        // Arrange: create a loan and overdue schedule
        $loan = Loan::factory()->create();
        $schedule = LoanSchedule::factory()->create([
            'loan_id' => $loan->id,
            'due_date' => now()->subDays(10),
            'paid' => false,
            'penalty_amount' => 0,
            'grace_period' => 5,
        ]);

        // Act: run the penalty accrual job
        (new AccruePenaltyJob())->handle();

        // Assert: penalty is accrued
        $schedule->refresh();
        $this->assertGreaterThan(0, $schedule->penalty_amount, 'Penalty should be accrued');

        // Assert: accrued penalty record exists
        $this->assertDatabaseHas('accrued_penalties', [
            'loan_schedule_id' => $schedule->id,
            'loan_id' => $loan->id,
        ]);

        // Assert: journal and GL entries are created
        $this->assertDatabaseHas('journals', [
            'loan_id' => $loan->id,
            'type' => 'penalty_accrual',
        ]);
        $this->assertDatabaseHas('journal_items', [
            'loan_id' => $loan->id,
        ]);
        $this->assertDatabaseHas('gl_transactions', [
            'loan_id' => $loan->id,
            'type' => 'penalty_accrual',
        ]);
    }
}
