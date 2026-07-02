<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ImmunizationService;
use App\Models\Immunization;
use App\Models\ChildRecord;
use App\Models\Vaccine;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendVaccinationReminderJob;
use Carbon\Carbon;

class ImmunizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ImmunizationService $service;
    protected User $user;
    protected Patient $mother;
    protected ChildRecord $child;
    protected Vaccine $vaccine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ImmunizationService::class);

        // Create test user
        $this->user = User::factory()->create([
            'role' => 'midwife',
            'is_active' => true,
        ]);
        $this->actingAs($this->user);

        // Create test mother/patient
        $this->mother = Patient::factory()->create([
            'contact' => '09123456789',
        ]);

        // Create test child
        $this->child = ChildRecord::factory()->create([
            'mother_id' => $this->mother->id,
        ]);

        // Create test vaccine with stock
        $this->vaccine = Vaccine::factory()->create([
            'name' => 'BCG',
            'current_stock' => 10,
        ]);
    }

    /** @test */
    public function it_creates_immunization_with_upcoming_status()
    {
        $data = [
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'dose' => '1st Dose',
            'schedule_date' => Carbon::now()->addDays(7)->toDateString(),
            'schedule_time' => '08:00',
        ];

        $immunization = $this->service->createImmunization($data);

        $this->assertInstanceOf(Immunization::class, $immunization);
        $this->assertEquals('Upcoming', $immunization->status);
        $this->assertEquals($this->child->id, $immunization->child_record_id);
        $this->assertEquals($this->vaccine->id, $immunization->vaccine_id);
    }

    /** @test */
    public function it_dispatches_sms_job_when_creating_immunization()
    {
        Queue::fake();

        $data = [
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'dose' => '1st Dose',
            'schedule_date' => Carbon::now()->addDays(7)->toDateString(),
            'schedule_time' => '08:00',
        ];

        $this->service->createImmunization($data);

        Queue::assertPushed(SendVaccinationReminderJob::class);
    }

    /** @test */
    public function it_throws_exception_when_vaccine_out_of_stock()
    {
        $this->vaccine->update(['current_stock' => 0]);

        $data = [
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'dose' => '1st Dose',
            'schedule_date' => Carbon::now()->addDays(7)->toDateString(),
            'schedule_time' => '08:00',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('out of stock');

        $this->service->createImmunization($data);
    }

    /** @test */
    public function it_prevents_multiple_upcoming_immunizations_for_same_child()
    {
        // Create first immunization
        Immunization::factory()->create([
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'status' => 'Upcoming',
        ]);

        // Try to create second
        $data = [
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'dose' => '1st Dose',
            'schedule_date' => Carbon::now()->addDays(7)->toDateString(),
            'schedule_time' => '08:00',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already has an upcoming immunization');

        $this->service->createImmunization($data);
    }

    /** @test */
    public function it_calculates_next_due_date_correctly()
    {
        $currentDate = '2024-01-01';

        // Test Hepatitis B 1st dose (30 days)
        $nextDate = $this->service->calculateNextDueDate('Hepatitis B', '1st Dose', $currentDate);
        $this->assertEquals('2024-01-31', $nextDate);

        // Test Hepatitis B 2nd dose (150 days)
        $nextDate = $this->service->calculateNextDueDate('Hepatitis B', '2nd Dose', $currentDate);
        $this->assertEquals('2024-05-30', $nextDate);

        // Test BCG (no next dose)
        $nextDate = $this->service->calculateNextDueDate('BCG', '1st Dose', $currentDate);
        $this->assertNull($nextDate);
    }

    /** @test */
    public function it_updates_immunization_status_to_done()
    {
        $immunization = Immunization::factory()->create([
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'status' => 'Upcoming',
        ]);

        $data = [
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'dose' => '1st Dose',
            'schedule_date' => Carbon::now()->toDateString(),
            'schedule_time' => '08:00',
            'status' => 'Done',
        ];

        $updated = $this->service->updateImmunization($immunization, $data);

        $this->assertEquals('Done', $updated->status);
    }

    /** @test */
    public function it_consumes_vaccine_stock_when_marked_done()
    {
        $initialStock = $this->vaccine->current_stock;

        $immunization = Immunization::factory()->create([
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'status' => 'Upcoming',
        ]);

        $data = [
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'dose' => '1st Dose',
            'schedule_date' => Carbon::now()->toDateString(),
            'schedule_time' => '08:00',
            'status' => 'Done',
        ];

        $this->service->updateImmunization($immunization, $data);

        $this->vaccine->refresh();
        $this->assertEquals($initialStock - 1, $this->vaccine->current_stock);
    }

    /** @test */
    public function it_marks_status_as_missed()
    {
        $immunization = Immunization::factory()->create([
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'status' => 'Upcoming',
        ]);

        $updated = $this->service->markStatus($immunization, 'Missed');

        $this->assertEquals('Missed', $updated->status);
    }

    /** @test */
    public function it_throws_exception_for_invalid_status()
    {
        $immunization = Immunization::factory()->create([
            'child_record_id' => $this->child->id,
            'vaccine_id' => $this->vaccine->id,
            'status' => 'Upcoming',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid status');

        $this->service->markStatus($immunization, 'Invalid');
    }
}
