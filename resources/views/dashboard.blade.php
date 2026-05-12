<x-app-layout>
    @php
        $todaySchedule = $upcomingAppointments
            ->filter(fn ($appointment) => $appointment->appointment_date->isToday())
            ->sortBy('appointment_time')
            ->values();

        $recentAppointments = $upcomingAppointments;
        $upcomingCount = $upcomingAppointments->count();
        $displayRole = match (auth()->user()->role) {
            \App\Models\User::ROLE_ADMIN => 'Admin',
            \App\Models\User::ROLE_RECEPTIONIST => 'Receptionist',
            \App\Models\User::ROLE_STAFF => 'Staff',
            default => 'User',
        };

        $statusStyles = [
            'confirmed' => 'dashboard-status-pill dashboard-status-confirmed',
            'scheduled' => 'dashboard-status-pill dashboard-status-scheduled',
            'completed' => 'dashboard-status-pill dashboard-status-completed',
            'cancelled' => 'dashboard-status-pill dashboard-status-cancelled',
            'no show' => 'dashboard-status-pill dashboard-status-no-show',
            'no_show' => 'dashboard-status-pill dashboard-status-no-show',
        ];
    @endphp

    <div class="dashboard-ui">
        @if (session('status'))
            <div class="auth-status auth-status-success">{{ session('status') }}</div>
        @endif

        <header class="dashboard-topbar">
            <h1 class="dashboard-topbar-title">Dashboard</h1>
        </header>

        <section class="dashboard-welcome">
            <h2 class="dashboard-welcome-title">Welcome back, {{ $displayRole }}!</h2>
            <p class="dashboard-welcome-copy">Here's your appointment overview.</p>
        </section>

        <section class="dashboard-stats-grid">
            <article class="dashboard-stat-box dashboard-stat-box-clients">
                <div>
                    <p class="dashboard-stat-box-label">Total Clients</p>
                    <p class="dashboard-stat-box-value">{{ $stats['clients'] }}</p>
                </div>
                <span class="dashboard-stat-box-icon dashboard-stat-box-icon-violet">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" />
                        <path d="M4 21a8 8 0 0 1 16 0" />
                    </svg>
                </span>
            </article>

            <article class="dashboard-stat-box dashboard-stat-box-appointments">
                <div>
                    <p class="dashboard-stat-box-label">Today's Appointment</p>
                    <p class="dashboard-stat-box-value">{{ $stats['appointments_today'] }}</p>
                </div>
                <span class="dashboard-stat-box-icon dashboard-stat-box-icon-blue">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M7 2v3M17 2v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/>
                    </svg>
                </span>
            </article>

            <article class="dashboard-stat-box dashboard-stat-box-completed">
                <div>
                    <p class="dashboard-stat-box-label">Completed</p>
                    <p class="dashboard-stat-box-value">{{ $stats['completed_appointments'] }}</p>
                </div>
                <span class="dashboard-stat-box-icon dashboard-stat-box-icon-green">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </span>
            </article>

            <article class="dashboard-stat-box dashboard-stat-box-upcoming">
                <div>
                    <p class="dashboard-stat-box-label">Upcoming</p>
                    <p class="dashboard-stat-box-value">{{ $upcomingCount }}</p>
                </div>
                <span class="dashboard-stat-box-icon dashboard-stat-box-icon-orange">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                </span>
            </article>
        </section>

        <section class="dashboard-main-grid">
            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3 class="dashboard-panel-title">Recent Appointments</h3>
                    <a href="{{ route('appointments.index') }}" class="dashboard-panel-link">View All</a>
                </div>

                <div class="dashboard-appointment-list">
                    @forelse ($recentAppointments as $appointment)
                        <div class="dashboard-appointment-row">
                            <div class="dashboard-appointment-main">
                                <p class="dashboard-appointment-name">{{ $appointment->client->full_name }}</p>
                                <p class="dashboard-appointment-service">{{ $appointment->service_type }}</p>
                            </div>

                            <div class="dashboard-appointment-datetime">
                                <p class="dashboard-appointment-date">{{ $appointment->appointment_date->format('F d') }}</p>
                                <p class="dashboard-appointment-time">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('H:i') }}</p>
                            </div>

                            <div class="dashboard-appointment-status">
                                <span class="{{ $statusStyles[strtolower($appointment->status)] ?? 'dashboard-status-pill' }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">No recent appointments yet.</div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel dashboard-schedule-panel">
                <div class="dashboard-panel-head">
                    <h3 class="dashboard-panel-title">Today's Schedule</h3>
                </div>

                <div class="dashboard-schedule-list">
                    @forelse ($todaySchedule as $appointment)
                        <div class="dashboard-schedule-card">
                            <div class="dashboard-schedule-head">
                                <p class="dashboard-schedule-time">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('H:i') }}</p>
                                <span class="{{ $statusStyles[strtolower($appointment->status)] ?? 'dashboard-status-pill' }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                            </div>

                            <p class="dashboard-schedule-name">{{ $appointment->client->full_name }}</p>
                            <p class="dashboard-schedule-meta">{{ $appointment->service_type }}{{ $appointment->staff ? ' - ' . $appointment->staff->name : '' }}</p>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">No schedule for today.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
