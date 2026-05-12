<x-app-layout>
    @php
        $statusStyles = [
            'confirmed' => 'profile-status-pill profile-status-confirmed',
            'scheduled' => 'profile-status-pill profile-status-scheduled',
            'completed' => 'profile-status-pill profile-status-completed',
            'cancelled' => 'profile-status-pill profile-status-cancelled',
            'no show' => 'profile-status-pill profile-status-no-show',
            'no_show' => 'profile-status-pill profile-status-no-show',
        ];
    @endphp

    <div class="profile-page">
        <header class="profile-page-head">
            <h1 class="profile-page-title">PROFILE</h1>
        </header>

        <div class="profile-stage">
            <section class="profile-layout-grid">
                <div class="profile-sidebar-column">
                    <article class="profile-summary-card">
                        <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <p class="profile-summary-name">{{ strtoupper($user->name) }}</p>
                        <p class="profile-summary-email">{{ $user->email }}</p>
                        <div class="profile-summary-meta">
                            <span class="profile-role-badge">{{ $roleLabel }}</span>
                            <span class="profile-member-since">Member since {{ $user->created_at?->format('M Y') }}</span>
                        </div>
                    </article>

                    <div class="profile-sidebar-stats">
                        <article class="profile-mini-stat-card">
                            <p class="profile-mini-stat-value">{{ $profileStats['completed'] }}</p>
                            <p class="profile-mini-stat-label">Completed</p>
                        </article>
                        <article class="profile-mini-stat-card">
                            <p class="profile-mini-stat-value">{{ $profileStats['upcoming'] }}</p>
                            <p class="profile-mini-stat-label">Upcoming</p>
                        </article>
                    </div>
                </div>

                <div class="profile-main-column">
                    <div class="profile-panel">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <article class="profile-panel">
                        <div class="profile-section-head">
                            <div class="profile-section-title-wrap">
                                <svg class="profile-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M7 2v3M17 2v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/>
                                </svg>
                                <h2 class="profile-section-title">My Appointments</h2>
                            </div>
                        </div>

                        <div class="profile-appointments-list">
                            @forelse ($recentAppointments as $appointment)
                                <div class="profile-appointment-card">
                                    <div class="profile-appointment-leading">
                                        <span class="profile-appointment-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path d="M7 2v3M17 2v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/>
                                            </svg>
                                        </span>

                                        <div class="profile-appointment-main">
                                            <p class="profile-appointment-name">{{ $appointment->client?->full_name ?? 'Walk-in Client' }}</p>
                                            <p class="profile-appointment-meta">
                                                {{ $appointment->service_type }}
                                                <span>&bull;</span>
                                                {{ $appointment->appointment_date->format('M j, Y') }}
                                                <span>&bull;</span>
                                                {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <span class="{{ $statusStyles[strtolower($appointment->status)] ?? 'profile-status-pill' }}">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                    </span>
                                </div>
                            @empty
                                <div class="profile-empty-state">
                                    No appointments available.
                                </div>
                            @endforelse
                        </div>
                    </article>
                </div>
            </section>

            <section class="profile-bottom-stack">
                <div class="profile-panel">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="profile-panel profile-panel-danger">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
