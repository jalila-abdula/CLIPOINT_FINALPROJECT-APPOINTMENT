<x-app-layout>
    <div class="client-page-stack">
        @if (session('status'))
            <div class="auth-status auth-status-success">{{ session('status') }}</div>
        @endif

        <section>
            <h2 class="client-section-title">Client/View</h2>
        </section>

        <section class="client-profile-header">
            <h3 class="client-profile-name">{{ $client->full_name }}</h3>

            <div class="client-profile-actions">
                <a href="{{ route('clients.edit', $client) }}" class="client-secondary-button border-0 bg-amber-100 text-amber-800 hover:bg-amber-200">Edit Profile</a>
                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Delete this client?')">
                    @csrf
                    @method('DELETE')
                    <button class="client-danger-button" type="submit">Delete Client</button>
                </form>
            </div>
        </section>

        <section class="client-detail-grid">
            <div class="client-detail-card">
                <h4 class="client-detail-title">Information</h4>

                <div class="client-info-list">
                    <div class="client-info-row">
                        <span class="client-info-label">First Name</span>
                        <div class="client-info-value">{{ $client->first_name }}</div>
                    </div>
                    <div class="client-info-row">
                        <span class="client-info-label">Last Name</span>
                        <div class="client-info-value">{{ $client->last_name }}</div>
                    </div>
                    <div class="client-info-row">
                        <span class="client-info-label">Phone #</span>
                        <div class="client-info-value">{{ $client->phone }}</div>
                    </div>
                    <div class="client-info-row">
                        <span class="client-info-label">Email</span>
                        <div class="client-info-value">{{ $client->email ?: 'No email provided' }}</div>
                    </div>
                    <div class="client-info-row">
                        <span class="client-info-label">Address</span>
                        <div class="client-info-value">{{ $client->address ?: 'No address provided' }}</div>
                    </div>
                    <div class="client-info-row">
                        <span class="client-info-label">Notes</span>
                        <div class="client-info-value">{{ $client->notes ?: 'No notes available' }}</div>
                    </div>
                </div>
            </div>

            <div class="client-detail-card">
                <h4 class="client-detail-title">Appointment History</h4>

                <div class="client-history-list">
                    @forelse ($client->appointments as $appointment)
                        <div class="client-history-item">
                            <p class="text-base font-bold text-slate-900">{{ $appointment->service_type }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-600">{{ $appointment->appointment_date->format('M d, Y') }} at {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Assigned staff: {{ optional($appointment->staff)->name ?? 'Unassigned' }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Status: {{ str($appointment->status)->replace('_', ' ')->title() }}</p>
                        </div>
                    @empty
                        <div class="client-history-empty">No appointments yet.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
