<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_reports_page_displays_the_new_summary_sections(): void
    {
        $admin = $this->seedReportFixtures();

        $this->actingAs($admin);
        request()->setUserResolver(fn () => $admin);

        $view = app(ReportController::class)->index();
        $data = $view->getData();

        $this->assertSame('reports.index', $view->name());
        $this->assertSame(1, $data['reports']['daily']);
        $this->assertSame(1, $data['reports']['completed']);
        $this->assertSame(1, $data['reports']['cancelled']);
        $this->assertCount(1, $data['dailyAppointments']);
        $this->assertCount(1, $data['completedAppointments']);
        $this->assertCount(1, $data['cancelledAppointments']);
        $this->assertNotEmpty($data['staffActivity']);
        $this->assertNotEmpty($data['clientVisitSummary']);
        $this->assertSame('Jamie Lopez', $data['clientVisitSummary']->first()->full_name);
    }

    public function test_reports_page_can_export_csv(): void
    {
        $admin = $this->seedReportFixtures();

        $response = $this->actingAs($admin)->get(route('reports.export.csv'));

        $response->assertOk();
        $response->assertDownload();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Appointment Reports Export', $content);
        $this->assertStringContainsString('Summary', $content);
        $this->assertStringContainsString('Jamie Lopez', $content);
        $this->assertStringContainsString('Alex Staff', $content);
    }

    public function test_reports_page_can_export_pdf(): void
    {
        $admin = $this->seedReportFixtures();

        $response = $this->actingAs($admin)->get(route('reports.export.pdf'));

        $response->assertOk();
        $response->assertDownload();
        $response->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    protected function seedReportFixtures(): User
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 11, 10, 0, 0, config('app.timezone')));

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'name' => 'Alex Staff']);

        $clientOne = Client::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Lopez',
            'phone' => '09123456789',
        ]);

        $clientTwo = Client::query()->create([
            'first_name' => 'Morgan',
            'last_name' => 'Reyes',
            'phone' => '09987654321',
        ]);

        Appointment::query()->create([
            'client_id' => $clientOne->id,
            'staff_id' => $staff->id,
            'service_type' => 'Consultation',
            'appointment_date' => '2026-05-11',
            'appointment_time' => '13:00:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $admin->id,
        ]);

        Appointment::query()->create([
            'client_id' => $clientOne->id,
            'staff_id' => $staff->id,
            'service_type' => 'Training',
            'appointment_date' => '2026-05-10',
            'appointment_time' => '09:00:00',
            'status' => Appointment::STATUS_COMPLETED,
            'created_by' => $admin->id,
        ]);

        Appointment::query()->create([
            'client_id' => $clientTwo->id,
            'staff_id' => $staff->id,
            'service_type' => 'Technical',
            'appointment_date' => '2026-05-09',
            'appointment_time' => '15:00:00',
            'status' => Appointment::STATUS_CANCELLED,
            'created_by' => $admin->id,
        ]);

        return $admin;
    }
}
