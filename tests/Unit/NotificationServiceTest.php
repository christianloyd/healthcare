<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Patient;
use App\Models\PrenatalCheckup;
use App\Models\PrenatalRecord;
use App\Models\ChildRecord;
use App\Models\Vaccine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\HealthcareNotification;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $midwife;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->midwife = User::factory()->create([
            'role' => 'midwife',
            'is_active' => true,
        ]);

        $this->patient = Patient::factory()->create([
            'contact' => '09123456789',
        ]);
    }

    /** @test */
    public function it_sends_appointment_confirmation()
    {
        NotificationFacade::fake();

        $prenatalRecord = PrenatalRecord::create([
            'patient_id' => $this->patient->id,
            'last_menstrual_period' => now()->subDays(60),
            'status' => 'normal',
        ]);

        $checkup = PrenatalCheckup::create([
            'prenatal_record_id' => $prenatalRecord->id,
            'patient_id' => $this->patient->id,
            'checkup_date' => now(),
            'checkup_time' => '08:00:00',
            'next_visit_date' => now()->addDays(30),
            'status' => 'upcoming',
        ]);

        NotificationService::sendAppointmentConfirmation($checkup);

        NotificationFacade::assertSentTo(
            $this->patient,
            HealthcareNotification::class
        );
    }

    /** @test */
    public function it_sends_appointment_reminder()
    {
        NotificationFacade::fake();

        $prenatalRecord = PrenatalRecord::create([
            'patient_id' => $this->patient->id,
            'last_menstrual_period' => now()->subDays(60),
            'status' => 'normal',
        ]);

        $checkup = PrenatalCheckup::create([
            'prenatal_record_id' => $prenatalRecord->id,
            'patient_id' => $this->patient->id,
            'checkup_date' => now(),
            'checkup_time' => '08:00:00',
            'next_visit_date' => now()->addDay(),
            'status' => 'upcoming',
        ]);

        NotificationService::sendAppointmentReminder($checkup);

        NotificationFacade::assertSentTo(
            $this->patient,
            HealthcareNotification::class
        );
    }

    /** @test */
    public function it_sends_vaccination_reminder()
    {
        NotificationFacade::fake();

        $child = ChildRecord::factory()->create([
            'mother_id' => $this->patient->id,
        ]);

        NotificationService::sendVaccinationReminder($child);

        NotificationFacade::assertSentTo(
            [$this->midwife, $this->patient],
            HealthcareNotification::class
        );
    }

    /** @test */
    public function it_sends_low_stock_alert_to_midwives()
    {
        NotificationFacade::fake();

        $vaccine = Vaccine::factory()->create([
            'current_stock' => 5,
            'min_stock' => 10,
        ]);

        NotificationService::sendLowStockAlert($vaccine);

        NotificationFacade::assertSentTo(
            $this->midwife,
            HealthcareNotification::class,
            function ($notification) use ($vaccine) {
                return str_contains($notification->title, 'Low Vaccine Stock');
            }
        );
    }

    /** @test */
    public function it_sends_new_patient_notification()
    {
        NotificationFacade::fake();

        NotificationService::sendNewPatientNotification($this->patient);

        NotificationFacade::assertSentTo(
            $this->midwife,
            HealthcareNotification::class,
            function ($notification) {
                return str_contains($notification->title, 'New Patient');
            }
        );
    }

    /** @test */
    public function it_clears_notification_cache_after_sending()
    {
        $cacheKey = "unread_notifications_count_{$this->midwife->id}";

        cache()->put($cacheKey, 5, 3600);

        $this->assertEquals(5, cache()->get($cacheKey));

        NotificationService::sendNewPatientNotification($this->patient);

        $this->assertNull(cache()->get($cacheKey));
    }
}
