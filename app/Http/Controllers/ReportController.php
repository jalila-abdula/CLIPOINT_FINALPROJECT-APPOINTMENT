<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', $this->reportData($this->authenticatedUser()));
    }

    public function exportCsv(): StreamedResponse
    {
        $data = $this->reportData($this->authenticatedUser());
        $sections = $this->exportSections($data);
        $filename = 'reports-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($data, $sections) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Appointment Reports Export']);
            fputcsv($handle, ['Generated At', now()->format('M d, Y h:i A')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Metric', 'Value']);
            foreach ($this->summaryRows($data['reports']) as $row) {
                fputcsv($handle, $row);
            }

            foreach ($sections as $section) {
                fputcsv($handle, []);
                fputcsv($handle, [$section['title']]);
                fputcsv($handle, $section['headers']);

                if ($section['rows'] === []) {
                    fputcsv($handle, ['No data available for this section.']);
                    continue;
                }

                foreach ($section['rows'] as $row) {
                    fputcsv($handle, $row);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(): Response
    {
        $data = $this->reportData($this->authenticatedUser());

        return response($this->buildStyledPdfDocument($data, $this->exportSections($data)), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reports-' . now()->format('Y-m-d-His') . '.pdf"',
        ]);
    }

    protected function reportData(Authenticatable $user): array
    {
        Appointment::markReadyForNoShow();

        /** @var User $user */
        $appointments = Appointment::query()->visibleTo($user);

        $reports = [
            'total' => (clone $appointments)->count(),
            'daily' => (clone $appointments)->whereDate('appointment_date', today())->count(),
            'completed' => (clone $appointments)->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled' => (clone $appointments)->where('status', Appointment::STATUS_CANCELLED)->count(),
            'no_show' => (clone $appointments)->where('status', Appointment::STATUS_NO_SHOW)->count(),
            'clients' => $user->isStaff()
                ? (clone $appointments)->distinct('client_id')->count('client_id')
                : Client::count(),
        ];

        $staffActivity = User::query()
            ->where('role', User::ROLE_STAFF)
            ->withCount([
                'assignedAppointments',
                'serviceRecords',
                'assignedAppointments as completed_appointments_count' => fn ($query) => $query
                    ->where('status', Appointment::STATUS_COMPLETED),
            ])
            ->orderByDesc('assigned_appointments_count')
            ->get();

        $trendMonths = collect(range(5, 1))
            ->map(fn (int $monthsAgo) => Carbon::now()->subMonths($monthsAgo)->startOfMonth())
            ->push(Carbon::now()->startOfMonth());

        $monthlyTrend = $trendMonths->map(function (Carbon $month) use ($appointments) {
            return [
                'label' => $month->format('M'),
                'count' => (clone $appointments)
                    ->whereYear('appointment_date', $month->year)
                    ->whereMonth('appointment_date', $month->month)
                    ->count(),
            ];
        });

        $statusDistribution = collect(Appointment::STATUSES)->map(function (string $status) use ($appointments) {
            return [
                'label' => $status,
                'count' => (clone $appointments)->where('status', $status)->count(),
            ];
        });

        $dailyAppointments = (clone $appointments)
            ->with(['client', 'staff'])
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->get();

        $completedAppointments = (clone $appointments)
            ->with(['client', 'staff'])
            ->where('status', Appointment::STATUS_COMPLETED)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->limit(8)
            ->get();

        $cancelledAppointments = (clone $appointments)
            ->with(['client', 'staff'])
            ->where('status', Appointment::STATUS_CANCELLED)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->limit(8)
            ->get();

        $clientVisitSummary = (clone $appointments)
            ->select([
                'appointments.client_id',
                DB::raw('COUNT(*) as total_visits'),
                DB::raw("SUM(CASE WHEN appointments.status = '" . Appointment::STATUS_COMPLETED . "' THEN 1 ELSE 0 END) as completed_visits"),
                DB::raw("SUM(CASE WHEN appointments.status = '" . Appointment::STATUS_CANCELLED . "' THEN 1 ELSE 0 END) as cancelled_visits"),
                DB::raw('MAX(appointments.appointment_date) as latest_visit_date'),
            ])
            ->join('clients', 'clients.id', '=', 'appointments.client_id')
            ->addSelect([
                'clients.first_name',
                'clients.last_name',
                'clients.phone',
            ])
            ->groupBy('appointments.client_id', 'clients.first_name', 'clients.last_name', 'clients.phone')
            ->orderByDesc('total_visits')
            ->orderBy('clients.last_name')
            ->limit(8)
            ->get()
            ->map(function ($clientSummary) {
                $clientSummary->full_name = trim($clientSummary->first_name . ' ' . $clientSummary->last_name);

                return $clientSummary;
            });

        return compact(
            'reports',
            'staffActivity',
            'monthlyTrend',
            'statusDistribution',
            'dailyAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'clientVisitSummary',
        );
    }

    protected function authenticatedUser(): Authenticatable
    {
        return request()->user();
    }

    protected function appointmentRow(Appointment $appointment): array
    {
        return [
            $appointment->client?->full_name ?? 'Unknown client',
            $appointment->staff?->name ?? 'Unassigned staff',
            $appointment->service_type,
            optional($appointment->appointment_date)->format('M d, Y') ?? 'N/A',
            Carbon::parse($appointment->appointment_time)->format('g:i A'),
            $appointment->status,
        ];
    }

    protected function summaryRows(array $reports): array
    {
        $rows = [];

        foreach ($reports as $label => $value) {
            $rows[] = [str($label)->replace('_', ' ')->title()->toString(), (string) $value];
        }

        return $rows;
    }

    protected function exportSections(array $data): array
    {
        return [
            [
                'title' => 'Daily Appointments',
                'headers' => ['Client', 'Staff', 'Service', 'Date', 'Time', 'Status'],
                'widths' => [115, 90, 90, 78, 55, 64],
                'rows' => $data['dailyAppointments']->map(fn (Appointment $appointment) => $this->appointmentRow($appointment))->all(),
            ],
            [
                'title' => 'Completed Appointments',
                'headers' => ['Client', 'Staff', 'Service', 'Date', 'Time', 'Status'],
                'widths' => [115, 90, 90, 78, 55, 64],
                'rows' => $data['completedAppointments']->map(fn (Appointment $appointment) => $this->appointmentRow($appointment))->all(),
            ],
            [
                'title' => 'Cancelled Appointments',
                'headers' => ['Client', 'Staff', 'Service', 'Date', 'Time', 'Status'],
                'widths' => [115, 90, 90, 78, 55, 64],
                'rows' => $data['cancelledAppointments']->map(fn (Appointment $appointment) => $this->appointmentRow($appointment))->all(),
            ],
            [
                'title' => 'Staff Activity',
                'headers' => ['Staff', 'Assigned Appointments', 'Completed Appointments', 'Service Records'],
                'widths' => [196, 112, 112, 112],
                'rows' => $data['staffActivity']->map(fn ($staff) => [
                    $staff->name,
                    (string) $staff->assigned_appointments_count,
                    (string) $staff->completed_appointments_count,
                    (string) $staff->service_records_count,
                ])->all(),
            ],
            [
                'title' => 'Client Visit Summary',
                'headers' => ['Client', 'Phone', 'Total Visits', 'Completed Visits', 'Cancelled Visits', 'Latest Visit'],
                'widths' => [120, 100, 76, 76, 76, 84],
                'rows' => $data['clientVisitSummary']->map(fn ($client) => [
                    $client->full_name,
                    $client->phone,
                    (string) $client->total_visits,
                    (string) $client->completed_visits,
                    (string) $client->cancelled_visits,
                    $client->latest_visit_date ? Carbon::parse($client->latest_visit_date)->format('M d, Y') : 'N/A',
                ])->all(),
            ],
        ];
    }

    protected function buildStyledPdfDocument(array $data, array $sections): string
    {
        $pages = [];
        $stream = '';
        $pageNumber = 0;
        $y = 0.0;

        $this->startPdfPage($pages, $stream, $y, $pageNumber);

        $this->drawPdfText($stream, 'Appointment Reports Export', 40, $y, 18, 'F2', [0.11, 0.19, 0.36]);
        $y -= 20;
        $this->drawPdfText($stream, 'Generated At: ' . now()->format('M d, Y h:i A'), 40, $y, 10, 'F1', [0.36, 0.43, 0.55]);
        $y -= 26;

        $summarySection = [
            'title' => 'Summary',
            'headers' => ['Metric', 'Value'],
            'rows' => $this->summaryRows($data['reports']),
            'widths' => [360, 172],
        ];

        foreach (array_merge([$summarySection], $sections) as $section) {
            $neededHeight = 42 + (max(count($section['rows']), 1) * 18);
            $this->ensurePdfSpace($pages, $stream, $y, $pageNumber, $neededHeight);

            $y = $this->drawPdfPagedTable(
                $pages,
                $stream,
                $y,
                $pageNumber,
                $section['title'],
                $section['headers'],
                $section['rows'],
                $section['widths']
            );

            $y -= 18;
        }

        $pages[] = $stream;

        return $this->buildPdfDocument($pages);
    }

    protected function buildPdfDocument(array $pages): string
    {
        $objects = [];

        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Count 0 /Kids [] >>';

        $pageObjectNumbers = [];
        $fontObjectNumber = 3 + (count($pages) * 2);
        $boldFontObjectNumber = $fontObjectNumber + 1;

        foreach ($pages as $index => $pageContent) {
            $pageObjectNumber = 3 + ($index * 2);
            $contentObjectNumber = $pageObjectNumber + 1;
            $pageObjectNumbers[] = $pageObjectNumber;

            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 ' . $fontObjectNumber . ' 0 R /F2 ' . $boldFontObjectNumber . ' 0 R >> >> /Contents ' . $contentObjectNumber . ' 0 R >>';
            $objects[] = '<< /Length ' . strlen($pageContent) . " >>\nstream\n" . $pageContent . "\nendstream";
        }

        $objects[1] = '<< /Type /Pages /Count ' . count($pages) . ' /Kids [' . implode(' ', array_map(fn (int $number) => $number . ' 0 R', $pageObjectNumbers)) . '] >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    protected function startPdfPage(array &$pages, string &$stream, float &$y, int &$pageNumber): void
    {
        if ($stream !== '') {
            $pages[] = $stream;
        }

        $pageNumber++;
        $stream = '';
        $y = 720;

        $this->drawPdfRect($stream, 0, 744, 612, 48, [0.16, 0.26, 0.53], [0.16, 0.26, 0.53]);
        $this->drawPdfText($stream, 'Appointment System Report', 40, 763, 16, 'F2', [1, 1, 1]);
        $this->drawPdfText($stream, 'Page ' . $pageNumber, 520, 763, 10, 'F1', [0.90, 0.94, 1.0]);
    }

    protected function ensurePdfSpace(array &$pages, string &$stream, float &$y, int &$pageNumber, float $neededHeight): void
    {
        if ($y - $neededHeight >= 55) {
            return;
        }

        $this->startPdfPage($pages, $stream, $y, $pageNumber);
    }

    protected function drawPdfPagedTable(
        array &$pages,
        string &$stream,
        float $y,
        int &$pageNumber,
        string $title,
        array $headers,
        array $rows,
        array $widths
    ): float {
        $x = 40;
        $tableWidth = array_sum($widths);
        $headerHeight = 18;
        $rowHeight = 18;

        $this->drawPdfText($stream, $title, $x, $y, 14, 'F2', [0.11, 0.19, 0.36]);
        $y -= 14;

        if ($rows === []) {
            $this->drawPdfRect($stream, $x, $y - 26, $tableWidth, 22, [0.98, 0.98, 0.99], [0.87, 0.89, 0.93]);
            $this->drawPdfText($stream, 'No data available for this section.', $x + 10, $y - 18, 10, 'F1', [0.42, 0.46, 0.53]);

            return $y - 38;
        }

        $rowIndex = 0;

        while ($rowIndex < count($rows)) {
            $this->ensurePdfSpace($pages, $stream, $y, $pageNumber, 44);

            $this->drawPdfRect($stream, $x, $y - $headerHeight, $tableWidth, $headerHeight, [0.90, 0.93, 0.98], [0.78, 0.83, 0.92]);

            $cursorX = $x;
            foreach ($headers as $columnIndex => $header) {
                $this->drawPdfText($stream, $header, $cursorX + 6, $y - 12, 9, 'F2', [0.14, 0.20, 0.33]);
                $cursorX += $widths[$columnIndex];
            }

            $y -= $headerHeight;

            while ($rowIndex < count($rows)) {
                if ($y - $rowHeight < 55) {
                    $this->startPdfPage($pages, $stream, $y, $pageNumber);
                    $this->drawPdfText($stream, $title . ' (continued)', $x, $y, 14, 'F2', [0.11, 0.19, 0.36]);
                    $y -= 14;
                    break;
                }

                $fill = $rowIndex % 2 === 0 ? [1, 1, 1] : [0.98, 0.98, 0.99];
                $this->drawPdfRect($stream, $x, $y - $rowHeight, $tableWidth, $rowHeight, $fill, [0.88, 0.90, 0.94]);

                $cursorX = $x;
                foreach ($rows[$rowIndex] as $columnIndex => $cell) {
                    $cellWidth = $widths[$columnIndex] - 10;
                    $text = $this->truncatePdfText((string) $cell, $cellWidth, 9);
                    $this->drawPdfText($stream, $text, $cursorX + 6, $y - 12, 9, 'F1', [0.18, 0.20, 0.25]);
                    $cursorX += $widths[$columnIndex];
                }

                $y -= $rowHeight;
                $rowIndex++;
            }
        }

        return $y;
    }

    protected function drawPdfRect(string &$stream, float $x, float $y, float $width, float $height, array $fill, array $stroke): void
    {
        $stream .= sprintf(
            "%.3F %.3F %.3F rg %.3F %.3F %.3F RG %.2F %.2F %.2F %.2F re B\n",
            $fill[0], $fill[1], $fill[2],
            $stroke[0], $stroke[1], $stroke[2],
            $x, $y, $width, $height
        );
    }

    protected function drawPdfText(string &$stream, string $text, float $x, float $y, float $size, string $font, array $color): void
    {
        $stream .= sprintf(
            "BT /%s %.2F Tf %.3F %.3F %.3F rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n",
            $font,
            $size,
            $color[0], $color[1], $color[2],
            $x, $y,
            $this->escapePdfText($text)
        );
    }

    protected function truncatePdfText(string $text, float $width, float $fontSize): string
    {
        $maxChars = max((int) floor($width / ($fontSize * 0.48)), 4);

        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $maxChars - 3)) . '...';
    }

    protected function escapePdfText(string $value): string
    {
        $sanitized = str_replace(["\r", "\n"], ' ', $value);

        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $sanitized) ?: $sanitized,
        );
    }
}
