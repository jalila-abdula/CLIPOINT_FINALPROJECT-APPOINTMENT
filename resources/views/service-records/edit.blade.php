<x-app-layout>
    @php
        $appointment = $serviceRecord->appointment;
        $appointmentStatus = $appointment?->status ?? \App\Models\Appointment::STATUS_COMPLETED;
        $statusKey = str($appointmentStatus)->lower()->replace(' ', '-')->replace('_', '-')->toString();
        $clientName = $serviceRecord->client?->full_name ?? 'Unknown client';
        $clientInitial = str($clientName)->substr(0, 1)->upper()->toString();
    @endphp

    <section
        x-data="{
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
    >
        <div class="service-record-edit-shell">
            <div class="service-record-edit-backdrop">
                <div class="service-record-edit-bg">
                    <header class="dashboard-topbar">
                        <h1 class="dashboard-topbar-title">Service Records</h1>
                    </header>

                    <div class="service-record-directory service-record-edit-bg-content">
                        <div class="service-record-directory-toolbar">
                            <div class="service-record-directory-search">
                                <svg class="service-record-directory-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7"></circle>
                                    <path d="m20 20-3.5-3.5"></path>
                                </svg>
                                <div class="service-record-edit-bg-input">Search record</div>
                            </div>
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
                                    @for ($i = 0; $i < 4; $i++)
                                        <tr>
                                            <td class="service-record-edit-bg-cell">
                                                <span class="service-record-edit-bg-line service-record-edit-bg-line-wide"></span>
                                                <span class="service-record-edit-bg-line service-record-edit-bg-line-small"></span>
                                            </td>
                                            <td><span class="service-record-edit-bg-line service-record-edit-bg-line-mid"></span></td>
                                            <td><span class="service-record-edit-bg-line service-record-edit-bg-line-small"></span></td>
                                            <td><span class="service-record-edit-bg-pill"></span></td>
                                            <td><span class="service-record-edit-bg-line service-record-edit-bg-line-mid"></span></td>
                                            <td><span class="service-record-edit-bg-actions"></span></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </section>
                    </div>
                </div>
            </div>

            <section class="client-modal-wrap service-record-edit-wrap">
                <div class="client-modal service-record-modal" @click.outside="window.location='{{ route('service-records.index') }}'">
                    <div class="client-form-modal-head">
                        <div>
                            <p class="service-record-modal-kicker">Service Record</p>
                            <h2 class="client-modal-title">Edit Service Record</h2>
                        </div>

                        <a href="{{ route('service-records.index') }}" class="client-form-close-lite" aria-label="Close edit service record">x</a>
                    </div>

                    <form method="POST" action="{{ route('service-records.update', $serviceRecord) }}" class="service-record-modal-form">
                        @csrf
                        @method('PUT')

                        <div class="service-record-field">
                            <label for="appointment_id_display">Linked Appointment *</label>
                            <select id="appointment_id_display" class="service-record-modal-select service-record-modal-select-disabled" disabled>
                                <option selected>
                                    {{ $clientName }} - {{ $appointment?->service_type ?? 'Not available' }} ({{ $appointment?->appointment_date?->format('Y-m-d') ?? 'No date' }})
                                </option>
                            </select>
                        </div>

                        <section class="service-record-modal-linked-card">
                            <p class="service-record-modal-linked-title">Linked Appointment Details</p>

                            <div class="service-record-modal-linked-grid">
                                <div class="service-record-modal-linked-item">
                                    <div class="service-record-modal-linked-avatar">{{ $clientInitial }}</div>
                                    <div>
                                        <p class="service-record-modal-linked-name">{{ $clientName }}</p>
                                        <p class="service-record-modal-linked-meta">{{ $appointment?->appointment_date?->format('M j, Y') ?? 'No date' }}</p>
                                    </div>
                                </div>

                                <div class="service-record-modal-linked-item service-record-modal-linked-item-end">
                                    <p class="service-record-modal-linked-meta">Service</p>
                                    <p class="service-record-modal-linked-name">{{ $appointment?->service_type ?? 'Not available' }}</p>
                                </div>

                                <div class="service-record-modal-linked-item">
                                    <p class="service-record-modal-linked-meta">Staff</p>
                                    <p class="service-record-modal-linked-name">{{ $serviceRecord->staff?->name ?? 'Unassigned' }}</p>
                                </div>

                                <div class="service-record-modal-linked-item service-record-modal-linked-item-end">
                                    <p class="service-record-modal-linked-meta">Time</p>
                                    <p class="service-record-modal-linked-name">{{ $appointment?->appointment_time ? \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') : 'No time' }}</p>
                                </div>
                            </div>

                            <div class="service-record-modal-status-row">
                                <div :class="statusClass('{{ $statusKey }}')">{{ $appointmentStatus }}</div>
                            </div>

                            <div class="appointment-progress-steps service-record-modal-progress">
                                <div class="appointment-progress-step">
                                    <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep('{{ $statusKey }}') >= 1 }">
                                        <template x-if="progressStep('{{ $statusKey }}') >= 1">
                                            <svg class="appointment-progress-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                                <path d="m7 12.5 3.2 3.2L17 9"></path>
                                            </svg>
                                        </template>
                                        <template x-if="progressStep('{{ $statusKey }}') < 1">
                                            <span>1</span>
                                        </template>
                                    </div>
                                    <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep('{{ $statusKey }}') >= 1 }">Scheduled</p>
                                </div>
                                <div class="appointment-progress-line" :class="{ 'appointment-progress-line-active': progressStep('{{ $statusKey }}') >= 2 }"></div>
                                <div class="appointment-progress-step">
                                    <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep('{{ $statusKey }}') >= 2 }">
                                        <template x-if="progressStep('{{ $statusKey }}') >= 2">
                                            <svg class="appointment-progress-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                                <path d="m7 12.5 3.2 3.2L17 9"></path>
                                            </svg>
                                        </template>
                                        <template x-if="progressStep('{{ $statusKey }}') < 2">
                                            <span>2</span>
                                        </template>
                                    </div>
                                    <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep('{{ $statusKey }}') >= 2 }">Confirmed</p>
                                </div>
                                <div class="appointment-progress-line" :class="{ 'appointment-progress-line-active': progressStep('{{ $statusKey }}') >= 3 }"></div>
                                <div class="appointment-progress-step">
                                    <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep('{{ $statusKey }}') >= 3 }">
                                        <template x-if="progressStep('{{ $statusKey }}') >= 3">
                                            <svg class="appointment-progress-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                                <path d="m7 12.5 3.2 3.2L17 9"></path>
                                            </svg>
                                        </template>
                                        <template x-if="progressStep('{{ $statusKey }}') < 3">
                                            <span>3</span>
                                        </template>
                                    </div>
                                    <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep('{{ $statusKey }}') >= 3 }">Completed</p>
                                </div>
                            </div>
                        </section>

                        <div class="service-record-field">
                            <label for="service_date">Service Date *</label>
                            <input id="service_date" type="date" name="service_date" class="service-record-modal-input" value="{{ old('service_date', $serviceRecord->service_date->format('Y-m-d')) }}" required>
                            @error('service_date')
                                <p class="service-record-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="service-record-field">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" class="service-record-modal-textarea" required>{{ old('description', $serviceRecord->description) }}</textarea>
                            @error('description')
                                <p class="service-record-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="service-record-field">
                            <label for="remarks">Remarks</label>
                            <textarea id="remarks" name="remarks" class="service-record-modal-textarea" placeholder="Any additional notes or outcome...">{{ old('remarks', $serviceRecord->remarks) }}</textarea>
                            @error('remarks')
                                <p class="service-record-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="service-record-modal-actions">
                            <a href="{{ route('service-records.index') }}" class="service-record-modal-button service-record-modal-button-secondary">Cancel</a>
                            <button type="submit" class="service-record-modal-button service-record-modal-button-primary">Update Record</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </section>
</x-app-layout>
