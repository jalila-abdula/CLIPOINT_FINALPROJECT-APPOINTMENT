<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppointmentSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_a_client_cannot_be_booked_twice_on_the_same_day(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 30, 9, 0, 0, config('app.timezone')));

        $receptionist = User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $client = Client::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Lopez',
            'phone' => '09123456789',
        ]);

        Appointment::query()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'service_type' => 'Consultation',
            'appointment_date' => '2026-05-02',
            'appointment_time' => '09:00:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $receptionist->id,
        ]);

        $response = $this
            ->actingAs($receptionist)
            ->post(route('appointments.store'), [
                'client_id' => $client->id,
                'staff_id' => $staff->id,
                'service_type' => 'Follow-up',
                'appointment_date' => '2026-05-02',
                'appointment_time' => '11:00',
            ]);

        $response
            ->assertSessionHasErrors('appointment_date')
            ->assertRedirect();

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_appointments_cannot_be_created_in_the_past(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 30, 10, 0, 0, config('app.timezone')));

        $receptionist = User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $client = Client::query()->create([
            'first_name' => 'Morgan',
            'last_name' => 'Reyes',
            'phone' => '09987654321',
        ]);

        $response = $this
            ->actingAs($receptionist)
            ->post(route('appointments.store'), [
                'client_id' => $client->id,
                'staff_id' => $staff->id,
                'service_type' => 'Checkup',
                'appointment_date' => '2026-04-30',
                'appointment_time' => '09:59',
            ]);

        $response
            ->assertSessionHasErrors('appointment_time')
            ->assertRedirect();

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_only_appointments_before_today_are_marked_as_no_show(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 30, 14, 0, 0, config('app.timezone')));

        $creator = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $client = Client::query()->create([
            'first_name' => 'Taylor',
            'last_name' => 'Santos',
            'phone' => '09001112222',
        ]);

        $missedScheduled = Appointment::query()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'service_type' => 'Cleaning',
            'appointment_date' => '2026-04-29',
            'appointment_time' => '13:00:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $creator->id,
        ]);

        $missedConfirmed = Appointment::query()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'service_type' => 'Consultation',
            'appointment_date' => '2026-04-29',
            'appointment_time' => '16:00:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'created_by' => $creator->id,
        ]);

        $sameDayPastTimeAppointment = Appointment::query()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'service_type' => 'Follow-up',
            'appointment_date' => '2026-04-30',
            'appointment_time' => '09:00:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $creator->id,
        ]);

        $futureAppointment = Appointment::query()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'service_type' => 'Checkup',
            'appointment_date' => '2026-05-01',
            'appointment_time' => '15:00:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $creator->id,
        ]);

        $completedAppointment = Appointment::query()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'service_type' => 'Therapy',
            'appointment_date' => '2026-04-30',
            'appointment_time' => '12:00:00',
            'status' => Appointment::STATUS_COMPLETED,
            'created_by' => $creator->id,
        ]);

        $this->artisan('appointments:mark-no-shows')->assertExitCode(0);

        $this->assertSame(Appointment::STATUS_NO_SHOW, $missedScheduled->fresh()->status);
        $this->assertSame(Appointment::STATUS_NO_SHOW, $missedConfirmed->fresh()->status);
        $this->assertSame(Appointment::STATUS_SCHEDULED, $sameDayPastTimeAppointment->fresh()->status);
        $this->assertSame(Appointment::STATUS_SCHEDULED, $futureAppointment->fresh()->status);
        $this->assertSame(Appointment::STATUS_COMPLETED, $completedAppointment->fresh()->status);
    }
}
