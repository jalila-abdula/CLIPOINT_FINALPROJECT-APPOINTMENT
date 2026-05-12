<x-app-layout>
    @php
        $availableAppointmentPayload = $availableAppointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'client_name' => $appointment->client?->full_name ?? 'Unknown client',
                'client_initial' => str($appointment->client?->full_name ?? 'U')->substr(0, 1)->upper()->toString(),
                'service_type' => $appointment->service_type,
                'staff_name' => $appointment->staff?->name ?? 'Unassigned',
                'appointment_date_label' => $appointment->appointment_date->format('M j, Y'),
                'appointment_time_label' => \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A'),
                'status' => $appointment->status,
                'status_key' => str($appointment->status)->lower()->replace(' ', '-')->replace('_', '-')->toString(),
            ];
        })->values();

        $defaultAppointmentId = old('appointment_id', $availableAppointments->first()?->id);
    @endphp

    <section
        x-data="{
            availableAppointments: @js($availableAppointmentPayload),
            selectedAppointmentId: @js($defaultAppointmentId ? (string) $defaultAppointmentId : ''),
            deleteRecordId: null,
            createOpen: {{ $errors->any() ? 'true' : 'false' }},
            deleteOpen: false,
            get selectedAppointment() {
                return this.availableAppointments.find((appointment) => String(appointment.id) === String(this.selectedAppointmentId)) ?? null;
            },
            openCreate() {
                if (!this.availableAppointments.length) {
                    return;
                }

                if (!this.selectedAppointmentId) {
                    this.selectedAppointmentId = String(this.availableAppointments[0].id);
                }

                this.createOpen = true;
            },
            openDelete(id) {
                this.deleteRecordId = id;
                this.deleteOpen = true;
            },
            closeCreate() {
                this.createOpen = false;
            },
            closeDelete() {
                this.deleteOpen = false;
                this.deleteRecordId = null;
            },
            deleteAction(id) {
                return `{{ url('/service-records') }}/${id}`;
            },
            progressStep(status) {
                return {
                    scheduled: 1,
                    confirmed: 2,
                    completed: 3,
                }[status] ?? 0;
            },
            statusClass(status) {
                return {
                    scheduled: 'service-record-status-pill service-record-status-scheduled',
                    confirmed: 'service-record-status-pill service-record-status-confirmed',
                    completed: 'service-record-status-pill service-record-status-completed',
                    cancelled: 'service-record-status-pill service-record-status-cancelled',
                    'no-show': 'service-record-status-pill service-record-status-no-show',
                }[status] || 'service-record-status-pill';
            },
        }"
        class="service-record-ui"
        @keydown.escape.window="closeCreate(); closeDelete()"
    >
        @if (session('status'))
            <div class="auth-status auth-status-success mx-5 mt-4 lg:mx-8">{{ session('status') }}</div>
        @endif

        <header class="dashboard-topbar">
            <h1 class="dashboard-topbar-title">Service Records</h1>
        </header>

        <div class="service-record-directory">
            <div class="service-record-directory-head">
                <div>
                    <p class="service-record-directory-count">{{ $serviceRecords->total() }} total service records</p>
                </div>
            </div>

            <div class="service-record-directory-stats">
                <article class="service-record-mini-stat service-record-mini-stat-green">
                    <p class="service-record-mini-label">Completed Appointments</p>
                    <div class="service-record-mini-foot">
                        <p class="service-record-mini-value">{{ $recordStats['completed_appointments'] }}</p>
                        <span class="service-record-mini-icon service-record-mini-icon-green" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="8"></circle>
                                <path d="m8.5 12.5 2.4 2.4 4.6-5.4"></path>
                            </svg>
                        </span>
                    </div>
                </article>
                <article class="service-record-mini-stat service-record-mini-stat-violet">
                    <p class="service-record-mini-label">With Record</p>
                    <div class="service-record-mini-foot">
                        <p class="service-record-mini-value">{{ $recordStats['with_records'] }}</p>
                        <span class="service-record-mini-icon service-record-mini-icon-violet" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M8 4h7l4 4v12H8z"></path>
                                <path d="M15 4v4h4"></path>
                                <path d="M10 12h6M10 16h6"></path>
                            </svg>
                        </span>
                    </div>
                </article>
                <article class="service-record-mini-stat service-record-mini-stat-amber">
                    <p class="service-record-mini-label">Pending Record</p>
                    <div class="service-record-mini-foot">
                        <p class="service-record-mini-value">{{ $recordStats['pending_records'] }}</p>
                        <span class="service-record-mini-icon service-record-mini-icon-amber" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M7 5h10"></path>
                                <path d="M9 3v4M15 3v4"></path>
                                <rect x="6" y="7" width="12" height="14" rx="2"></rect>
                                <circle cx="12" cy="14" r="2.5"></circle>
                            </svg>
                        </span>
                    </div>
                </article>
            </div>

            <div class="service-record-directory-toolbar">
                <form method="GET" class="service-record-directory-search">
                    <svg class="service-record-directory-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" class="service-record-directory-search-input" placeholder="Search record">
                    <input type="hidden" name="status" value="{{ $status }}">
                </form>

                <form method="GET" class="service-record-directory-filter">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <svg class="service-record-directory-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 6h16l-6 7v5l-4 2v-7L4 6Z"></path>
                    </svg>
                    <select name="status" class="service-record-directory-filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        @foreach ($statuses as $item)
                            <option value="{{ $item }}" @selected($status === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </form>

                <button
                    type="button"
                    class="service-record-directory-add"
                    @click="openCreate()"
                    @disabled($availableAppointments->isEmpty())
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 8v8M8 12h8"></path>
                    </svg>
                    <span>Add Record</span>
                </button>
            </div>

            <section class="service-record-directory-table-shell">
                <table class="service-record-directory-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Appointment</th>
                            <th>Service Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceRecords as $record)
                            @php
                                $statusKey = str($record->appointment?->status ?? 'Completed')->lower()->replace(' ', '-')->replace('_', '-')->toString();
                            @endphp
                            <tr>
                                <td class="service-record-directory-client">
                                    <span>{{ $record->client?->first_name ?? 'Unknown' }}</span>
                                    <span>{{ $record->client?->last_name ?? 'Client' }}</span>
                                </td>
                                <td>
                                    <span class="service-record-directory-appointment">{{ $record->appointment?->service_type ?? 'Not available' }}</span>
                                </td>
                                <td>{{ $record->service_date->format('m/d/y') }}</td>
                                <td>
                                    <span class="service-record-status-pill {{ match ($statusKey) {
                                        'scheduled' => 'service-record-status-scheduled',
                                        'confirmed' => 'service-record-status-confirmed',
                                        'completed' => 'service-record-status-completed',
                                        'cancelled' => 'service-record-status-cancelled',
                                        'no-show' => 'service-record-status-no-show',
                                        default => '',
                                    } }}">
                                        {{ $record->appointment?->status ?? 'Completed' }}
                                    </span>
                                </td>
                                <td class="service-record-directory-remarks">
                                    {{ \Illuminate\Support\Str::limit($record->remarks ?: $record->description, 48) }}
                                </td>
                                <td>
                                    <div class="service-record-directory-actions">
                                        <a href="{{ route('service-records.edit', $record) }}" class="service-record-directory-icon-button" aria-label="Edit service record">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20Z"></path>
                                                <path d="m13.5 6.5 3.5 3.5"></path>
                                            </svg>
                                        </a>

                                        <button type="button" class="service-record-directory-icon-button service-record-directory-icon-button-danger" @click="openDelete({{ $record->id }})" aria-label="Delete service record">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path d="M4 7h16"></path>
                                                <path d="M9 7V4h6v3"></path>
                                                <path d="M7 7v13h10V7"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="service-record-directory-empty">No service records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <div class="service-record-pagination">
                {{ $serviceRecords->links() }}
            </div>
        </div>

        <div x-cloak x-show="createOpen || deleteOpen" class="client-modal-overlay"></div>

        <section x-cloak x-show="createOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal service-record-modal" @click.outside="closeCreate()">
                <div class="client-form-modal-head">
                    <div>
                        <p class="service-record-modal-kicker">Service Record</p>
                        <h2 class="client-modal-title">Add Record</h2>
                    </div>
                    <button type="button" class="client-form-close-lite" @click="closeCreate()" aria-label="Close add record">x</button>
                </div>

                @if ($availableAppointments->isEmpty())
                    <div class="service-record-empty-box">
                        All completed appointments already have service records.
                    </div>
                @else
                    <form method="POST" action="{{ route('service-records.store') }}" class="service-record-modal-form">
                        @csrf

                        <div class="service-record-field">
                            <label for="appointment_id">Linked Appointment *</label>
                            <select id="appointment_id" name="appointment_id" class="service-record-modal-select" x-model="selectedAppointmentId" required>
                                @foreach($availableAppointments as $appointment)
                                    <option value="{{ $appointment->id }}">
                                        {{ $appointment->client?->full_name ?? 'Unknown client' }} - {{ $appointment->service_type }} ({{ $appointment->appointment_date->format('Y-m-d') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('appointment_id')
                                <p class="service-record-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <template x-if="selectedAppointment">
                            <section class="service-record-modal-linked-card">
                                <p class="service-record-modal-linked-title">Linked Appointment Details</p>

                                <div class="service-record-modal-linked-grid">
                                    <div class="service-record-modal-linked-item">
                                        <div class="service-record-modal-linked-avatar" x-text="selectedAppointment.client_initial"></div>
                                        <div>
                                            <p class="service-record-modal-linked-name" x-text="selectedAppointment.client_name"></p>
                                            <p class="service-record-modal-linked-meta" x-text="selectedAppointment.appointment_date_label"></p>
                                        </div>
                                    </div>

                                    <div class="service-record-modal-linked-item service-record-modal-linked-item-end">
                                        <p class="service-record-modal-linked-meta">Service</p>
                                        <p class="service-record-modal-linked-name" x-text="selectedAppointment.service_type"></p>
                                    </div>

                                    <div class="service-record-modal-linked-item">
                                        <p class="service-record-modal-linked-meta">Staff</p>
                                        <p class="service-record-modal-linked-name" x-text="selectedAppointment.staff_name"></p>
                                    </div>

                                    <div class="service-record-modal-linked-item service-record-modal-linked-item-end">
                                        <p class="service-record-modal-linked-meta">Time</p>
                                        <p class="service-record-modal-linked-name" x-text="selectedAppointment.appointment_time_label"></p>
                                    </div>
                                </div>

                                <div class="service-record-modal-status-row">
                                    <div :class="statusClass(selectedAppointment.status_key)" x-text="selectedAppointment.status"></div>
                                </div>

                                <div class="appointment-progress-steps service-record-modal-progress">
                                    <div class="appointment-progress-step">
                                        <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep(selectedAppointment.status_key) >= 1 }">
                                            <template x-if="progressStep(selectedAppointment.status_key) >= 1">
                                                <svg class="appointment-progress-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                                    <path d="m7 12.5 3.2 3.2L17 9"></path>
                                                </svg>
                                            </template>
                                            <template x-if="progressStep(selectedAppointment.status_key) < 1">
                                                <span>1</span>
                                            </template>
                                        </div>
                                        <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep(selectedAppointment.status_key) >= 1 }">Scheduled</p>
                                    </div>
                                    <div class="appointment-progress-line" :class="{ 'appointment-progress-line-active': progressStep(selectedAppointment.status_key) >= 2 }"></div>
                                    <div class="appointment-progress-step">
                                        <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep(selectedAppointment.status_key) >= 2 }">
                                            <template x-if="progressStep(selectedAppointment.status_key) >= 2">
                                                <svg class="appointment-progress-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                                    <path d="m7 12.5 3.2 3.2L17 9"></path>
                                                </svg>
                                            </template>
                                            <template x-if="progressStep(selectedAppointment.status_key) < 2">
                                                <span>2</span>
                                            </template>
                                        </div>
                                        <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep(selectedAppointment.status_key) >= 2 }">Confirmed</p>
                                    </div>
                                    <div class="appointment-progress-line" :class="{ 'appointment-progress-line-active': progressStep(selectedAppointment.status_key) >= 3 }"></div>
                                    <div class="appointment-progress-step">
                                        <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep(selectedAppointment.status_key) >= 3 }">
                                            <template x-if="progressStep(selectedAppointment.status_key) >= 3">
                                                <svg class="appointment-progress-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                                    <path d="m7 12.5 3.2 3.2L17 9"></path>
                                                </svg>
                                            </template>
                                            <template x-if="progressStep(selectedAppointment.status_key) < 3">
                                                <span>3</span>
                                            </template>
                                        </div>
                                        <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep(selectedAppointment.status_key) >= 3 }">Completed</p>
                                    </div>
                                </div>
                            </section>
                        </template>

                        <div class="service-record-field">
                            <label for="service_date">Service Date *</label>
                            <input id="service_date" type="date" name="service_date" class="service-record-modal-input" value="{{ old('service_date', now()->format('Y-m-d')) }}" required>
                            @error('service_date')
                                <p class="service-record-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="service-record-field">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" class="service-record-modal-textarea" placeholder="Describe the service outcome..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <p class="service-record-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="service-record-field">
                            <label for="remarks">Remarks</label>
                            <textarea id="remarks" name="remarks" class="service-record-modal-textarea" placeholder="Any additional notes or outcome...">{{ old('remarks') }}</textarea>
                            @error('remarks')
                                <p class="service-record-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="service-record-modal-actions">
                            <button type="button" class="service-record-modal-button service-record-modal-button-secondary" @click="closeCreate()">Cancel</button>
                            <button type="submit" class="service-record-modal-button service-record-modal-button-primary">Save Record</button>
                        </div>
                    </form>
                @endif
            </div>
        </section>

        <section x-cloak x-show="deleteOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal service-record-delete-modal" @click.outside="closeDelete()">
                <template x-if="deleteRecordId">
                    <form method="POST" :action="deleteAction(deleteRecordId)">
                        @csrf
                        @method('DELETE')

                        <h2 class="client-modal-title">Delete Service Record</h2>
                        <p class="service-record-delete-copy">Are you sure you want to delete this record?</p>

                        <div class="service-record-modal-actions">
                            <button type="button" class="service-record-modal-button service-record-modal-button-secondary" @click="closeDelete()">Cancel</button>
                            <button type="submit" class="service-record-modal-button service-record-modal-button-danger">Delete</button>
                        </div>
                    </form>
                </template>
            </div>
        </section>
    </section>
</x-app-layout>
