@php
    $maxTrend = max(collect($monthlyTrend)->max('count') ?? 0, 1);
    $totalStatuses = max(collect($statusDistribution)->sum('count'), 1);
    $statusPalette = [
        'Scheduled' => '#4f7df2',
        'Completed' => '#19c38f',
        'Confirmed' => '#b26fff',
        'Cancelled' => '#ff5f5f',
        'No Show' => '#f6a623',
    ];

    $segments = [];
    $offset = 0;

    foreach ($statusDistribution as $item) {
        $portion = ($item['count'] / $totalStatuses) * 100;
        $color = $statusPalette[$item['label']] ?? '#cbd5e1';
        $segments[] = "{$color} {$offset}% " . ($offset + $portion) . '%';
        $offset += $portion;
    }

    $chartBackground = 'conic-gradient(' . implode(', ', $segments ?: ['#e5e7eb 0% 100%']) . ')';
@endphp

<x-app-layout>
    <section class="reports-ui">
        <header class="dashboard-topbar">
            <h1 class="dashboard-topbar-title">Reports</h1>
        </header>

        <div class="reports-body">
            <div class="reports-intro">
                <p class="reports-intro-copy">Generates summaries for appointment and staff monitoring.</p>
            </div>

            <section class="reports-summary-grid">
                <article class="reports-summary-card reports-summary-card-violet">
                    <div>
                        <p class="reports-summary-label">Total Appointments</p>
                        <p class="reports-summary-value">{{ $reports['total'] }}</p>
                    </div>
                    <span class="reports-summary-icon reports-summary-icon-violet">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 2v3M17 2v3M4 8h16M5 5h14v15H5z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </article>

                <article class="reports-summary-card reports-summary-card-blue">
                    <div>
                        <p class="reports-summary-label">Daily Appointments</p>
                        <p class="reports-summary-value">{{ $reports['daily'] }}</p>
                    </div>
                    <span class="reports-summary-icon reports-summary-icon-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 3v3M17 3v3M5 8h14M6 5h12v14H6z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </article>

                <article class="reports-summary-card reports-summary-card-green">
                    <div>
                        <p class="reports-summary-label">Completed</p>
                        <p class="reports-summary-value">{{ $reports['completed'] }}</p>
                    </div>
                    <span class="reports-summary-icon reports-summary-icon-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="8"/>
                            <path d="m8.5 12 2.4 2.4 4.6-5.1" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </article>

                <article class="reports-summary-card reports-summary-card-red">
                    <div>
                        <p class="reports-summary-label">Cancelled</p>
                        <p class="reports-summary-value">{{ $reports['cancelled'] }}</p>
                    </div>
                    <span class="reports-summary-icon reports-summary-icon-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="8"/>
                            <path d="M9 9l6 6M15 9l-6 6" stroke-linecap="round"/>
                        </svg>
                    </span>
                </article>
            </section>

            <section class="reports-grid">
                <article class="reports-panel">
                    <h2 class="reports-panel-title">Monthly Appointment Trend</h2>

                    <div class="reports-trend-chart" aria-label="Monthly appointment trend">
                        <div class="reports-trend-axis">
                            @foreach([12, 9, 6, 3, 0] as $tick)
                                <span>{{ $tick }}</span>
                            @endforeach
                        </div>

                        <div class="reports-trend-bars">
                            @foreach($monthlyTrend as $month)
                                <div class="reports-trend-column">
                                    <div class="reports-trend-bar-shell">
                                        <div class="reports-trend-bar" style="height: {{ max(($month['count'] / $maxTrend) * 100, $month['count'] > 0 ? 10 : 0) }}%"></div>
                                    </div>
                                    <p class="reports-trend-label">{{ $month['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <article class="reports-panel">
                    <h2 class="reports-panel-title">Status Distribution</h2>

                    <div class="reports-distribution-wrap">
                        <div class="reports-donut" style="background: {{ $chartBackground }};">
                            <div class="reports-donut-hole"></div>
                        </div>

                        <div class="reports-legend">
                            @foreach($statusDistribution as $item)
                                <div class="reports-legend-item">
                                    <span class="reports-legend-swatch" style="background-color: {{ $statusPalette[$item['label']] ?? '#cbd5e1' }}"></span>
                                    <span>{{ $item['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            </section>

            <section class="reports-grid reports-grid-wide">
                <article class="reports-panel">
                    <div class="reports-section-head">
                        <div>
                            <h2 class="reports-panel-title">Daily Appointment Report</h2>
                            <p class="reports-section-copy">Shows appointments scheduled for today.</p>
                        </div>
                        <span class="reports-section-badge">{{ $dailyAppointments->count() }} today</span>
                    </div>

                    <div class="reports-list">
                        @forelse($dailyAppointments as $appointment)
                            <article class="reports-list-row">
                                <div>
                                    <p class="reports-list-title">{{ $appointment->client?->full_name ?? 'Unknown client' }}</p>
                                    <p class="reports-list-meta">{{ $appointment->service_type }} with {{ $appointment->staff?->name ?? 'Unassigned staff' }}</p>
                                </div>
                                <div class="reports-list-side">
                                    <p class="reports-list-time">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                                    <p class="reports-list-status">{{ $appointment->status }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="reports-empty">No appointments scheduled for today.</p>
                        @endforelse
                    </div>
                </article>

                <article class="reports-panel">
                    <div class="reports-section-head">
                        <div>
                            <h2 class="reports-panel-title">Completed Appointment Report</h2>
                            <p class="reports-section-copy">Recent appointments that were successfully completed.</p>
                        </div>
                        <span class="reports-section-badge">{{ $reports['completed'] }} total</span>
                    </div>

                    <div class="reports-list">
                        @forelse($completedAppointments as $appointment)
                            <article class="reports-list-row">
                                <div>
                                    <p class="reports-list-title">{{ $appointment->client?->full_name ?? 'Unknown client' }}</p>
                                    <p class="reports-list-meta">{{ $appointment->service_type }} by {{ $appointment->staff?->name ?? 'Unassigned staff' }}</p>
                                </div>
                                <div class="reports-list-side">
                                    <p class="reports-list-time">{{ optional($appointment->appointment_date)->format('M d, Y') }}</p>
                                    <p class="reports-list-status reports-list-status-completed">{{ $appointment->status }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="reports-empty">No completed appointments yet.</p>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="reports-grid reports-grid-wide">
                <article class="reports-panel">
                    <div class="reports-section-head">
                        <div>
                            <h2 class="reports-panel-title">Cancelled Appointment Report</h2>
                            <p class="reports-section-copy">Recent appointments that were cancelled.</p>
                        </div>
                        <span class="reports-section-badge">{{ $reports['cancelled'] }} total</span>
                    </div>

                    <div class="reports-list">
                        @forelse($cancelledAppointments as $appointment)
                            <article class="reports-list-row">
                                <div>
                                    <p class="reports-list-title">{{ $appointment->client?->full_name ?? 'Unknown client' }}</p>
                                    <p class="reports-list-meta">{{ $appointment->service_type }} with {{ $appointment->staff?->name ?? 'Unassigned staff' }}</p>
                                </div>
                                <div class="reports-list-side">
                                    <p class="reports-list-time">{{ optional($appointment->appointment_date)->format('M d, Y') }}</p>
                                    <p class="reports-list-status reports-list-status-cancelled">{{ $appointment->status }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="reports-empty">No cancelled appointments yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="reports-panel reports-staff-panel">
                    <div class="reports-section-head">
                        <div>
                            <h2 class="reports-panel-title">Staff Activity Report</h2>
                            <p class="reports-section-copy">Tracks assigned appointments and completed work per staff.</p>
                        </div>
                    </div>

                    <div class="reports-staff-list">
                        @forelse($staffActivity as $staff)
                            @php
                                $completionRate = $staff->assigned_appointments_count > 0
                                    ? min(($staff->completed_appointments_count / $staff->assigned_appointments_count) * 100, 100)
                                    : 0;
                                $initials = collect(explode(' ', $staff->name))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                    ->implode('');
                            @endphp

                            <article class="reports-staff-row">
                                <div class="reports-staff-profile">
                                    <div class="reports-staff-avatar">{{ $initials }}</div>
                                    <div>
                                        <p class="reports-staff-name">{{ strtoupper($staff->name) }}</p>
                                        <p class="reports-staff-meta">{{ $staff->assigned_appointments_count }} appointments assigned</p>
                                        <p class="reports-staff-meta">{{ $staff->completed_appointments_count }} completed | {{ $staff->service_records_count }} service records</p>
                                    </div>
                                </div>

                                <div class="reports-staff-progress">
                                    <div class="reports-staff-progress-track">
                                        <div class="reports-staff-progress-fill" style="width: {{ $completionRate }}%"></div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="reports-empty">No staff activity available yet.</p>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="reports-panel">
                <div class="reports-section-head">
                    <div>
                        <h2 class="reports-panel-title">Client Visit Summary</h2>
                        <p class="reports-section-copy">Highlights clients with the most appointment activity.</p>
                    </div>
                    <span class="reports-section-badge">{{ $reports['clients'] }} clients</span>
                </div>

                <div class="reports-client-table-shell">
                    <table class="reports-client-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Phone</th>
                                <th>Total Visits</th>
                                <th>Completed</th>
                                <th>Cancelled</th>
                                <th>Last Visit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clientVisitSummary as $client)
                                <tr>
                                    <td>{{ $client->full_name }}</td>
                                    <td>{{ $client->phone }}</td>
                                    <td>{{ $client->total_visits }}</td>
                                    <td>{{ $client->completed_visits }}</td>
                                    <td>{{ $client->cancelled_visits }}</td>
                                    <td>{{ $client->latest_visit_date ? \Illuminate\Support\Carbon::parse($client->latest_visit_date)->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="reports-client-empty">No client visit data available yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
</x-app-layout>
