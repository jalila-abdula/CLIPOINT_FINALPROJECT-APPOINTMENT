<x-app-layout>
    @php
        $appointmentPayload = $appointments->getCollection()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'client_id' => $appointment->client_id,
                'client_name' => $appointment->client->full_name,
                'client_initial' => str($appointment->client->full_name)->substr(0, 1)->upper()->toString(),
                'service_type' => $appointment->service_type,
                'staff_id' => $appointment->staff_id,
                'staff_name' => $appointment->staff->name ?? 'Unassigned',
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'appointment_date_label' => $appointment->appointment_date->format('m/d/y'),
                'appointment_date_long' => $appointment->appointment_date->format('M j, Y'),
                'appointment_time' => \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('H:i'),
                'appointment_time_label' => \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A'),
                'status' => $appointment->status,
                'status_key' => str($appointment->status)->lower()->replace(' ', '-')->replace('_', '-')->toString(),
                'notes' => $appointment->notes,
            ];
        })->values();

        $editAppointmentId = old('appointment_id');
        $canManageAppointments = ! auth()->user()->isStaff();
    @endphp

    <div
        x-data="{
            appointments: @js($appointmentPayload),
            detailsAppointment: null,
            editAppointment: null,
            deleteAppointment: null,
            today: '',
            currentTime: '',
            detailsOpen: false,
            createOpen: {{ $errors->any() && old('appointment_modal_mode', 'create') === 'create' ? 'true' : 'false' }},
            editOpen: {{ $errors->any() && old('appointment_modal_mode') === 'edit' ? 'true' : 'false' }},
            deleteOpen: false,
            appointmentAction(id) {
                return `{{ url('/appointments') }}/${id}`;
            },
            syncCurrentMoment() {
                const now = new Date();
                const pad = (value) => String(value).padStart(2, '0');

                this.today = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
                this.currentTime = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
            },
            getAppointment(id) {
                return this.appointments.find((appointment) => String(appointment.id) === String(id)) ?? null;
            },
            minDateForEdit() {
                if (!this.editAppointment?.appointment_date) {
                    return this.today;
                }

                return this.editAppointment.appointment_date < this.today
                    ? this.editAppointment.appointment_date
                    : this.today;
            },
            minTimeFor(date, fallbackTime = null) {
                if (!date || date !== this.today) {
                    return null;
                }

                if (fallbackTime && fallbackTime < this.currentTime) {
                    return fallbackTime;
                }

                return this.currentTime;
            },
            openDetails(id) {
                this.detailsAppointment = this.getAppointment(id);
                this.detailsOpen = true;
            },
            openEdit(id) {
                this.editAppointment = this.getAppointment(id);
                this.editOpen = true;
            },
            openDelete(id) {
                this.deleteAppointment = this.getAppointment(id);
                this.deleteOpen = true;
            },
            closeAll() {
                this.detailsOpen = false;
                this.createOpen = false;
                this.editOpen = false;
                this.deleteOpen = false;
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
                    scheduled: 'appointment-status-pill appointment-status-scheduled',
                    confirmed: 'appointment-status-pill appointment-status-confirmed',
                    completed: 'appointment-status-pill appointment-status-completed',
                    cancelled: 'appointment-status-pill appointment-status-cancelled',
                    'no-show': 'appointment-status-pill appointment-status-no-show',
                }[status] || 'appointment-status-pill';
            },
            init() {
                this.syncCurrentMoment();
                window.setInterval(() => this.syncCurrentMoment(), 30000);

                if (this.editOpen) {
                    this.editAppointment = this.getAppointment(@js($editAppointmentId));
                }
            },
        }"
        class="appointment-directory"
        x-init="init()"
        @keydown.escape.window="closeAll()"
    >
        @if (session('status'))
            <div class="auth-status auth-status-success mb-4 mx-5 lg:mx-8 mt-4">{{ session('status') }}</div>
        @endif

        <header class="dashboard-topbar">
            <h1 class="dashboard-topbar-title">Appointment</h1>
        </header>

        <section class="appointment-directory-page">
            <div class="appointment-directory-head">
                <p class="appointment-directory-count">{{ $appointments->total() }} total appointment</p>

                @if ($canManageAppointments)
                    <button type="button" class="appointment-directory-add" @click="createOpen = true">
                        <span class="appointment-directory-add-icon">+</span>
                        <span>Add Appointment</span>
                    </button>
                @endif
            </div>

            <div class="appointment-directory-toolbar">
                <form method="GET" class="appointment-directory-search">
                    <svg class="appointment-directory-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" class="appointment-directory-search-input" placeholder="Search Appointment">
                    <input type="hidden" name="status" value="{{ $status }}">
                </form>

                <form method="GET" class="appointment-directory-filter">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <svg class="appointment-directory-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 6h16l-6 7v5l-4 2v-7L4 6Z"></path>
                    </svg>
                    <select name="status" class="appointment-directory-filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        @foreach ($statuses as $item)
                            <option value="{{ $item }}" @selected($status === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <section class="appointment-directory-table-shell">
                <table class="appointment-directory-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Staff</th>
                            <th>Date</th>
                            <th>Status</th>
                            @if ($canManageAppointments)
                                <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr
                                class="appointment-directory-row"
                                role="button"
                                tabindex="0"
                                @click="openDetails({{ $appointment->id }})"
                                @keydown.enter.prevent="openDetails({{ $appointment->id }})"
                                @keydown.space.prevent="openDetails({{ $appointment->id }})"
                            >
                                <td class="appointment-directory-client">
                                    <span>{{ $appointment->client->first_name }}</span>
                                    <span>{{ $appointment->client->last_name }}</span>
                                </td>
                                <td>{{ $appointment->service_type }}</td>
                                <td>{{ $appointment->staff->name ?? 'Unassigned' }}</td>
                                <td>{{ $appointment->appointment_date->format('m/d/y') }}</td>
                                <td>
                                    <span class="appointment-status-pill {{ match (strtolower(str_replace('_', '-', str_replace(' ', '-', $appointment->status)))) {
                                        'scheduled' => 'appointment-status-scheduled',
                                        'confirmed' => 'appointment-status-confirmed',
                                        'completed' => 'appointment-status-completed',
                                        'cancelled' => 'appointment-status-cancelled',
                                        'no-show' => 'appointment-status-no-show',
                                        default => '',
                                    } }}">
                                        {{ $appointment->status }}
                                    </span>
                                </td>
                                @if ($canManageAppointments)
                                    <td>
                                        <div class="appointment-directory-actions">
                                            <button type="button" class="appointment-directory-icon-button" @click.stop="openEdit({{ $appointment->id }})" aria-label="Edit appointment">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20Z"></path>
                                                    <path d="m13.5 6.5 3.5 3.5"></path>
                                                </svg>
                                            </button>
                                            <button type="button" class="appointment-directory-icon-button appointment-directory-icon-button-danger" @click.stop="openDelete({{ $appointment->id }})" aria-label="Delete appointment">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path d="M4 7h16"></path>
                                                    <path d="M9 7V4h6v3"></path>
                                                    <path d="M7 7v13h10V7"></path>
                                                    <path d="M10 11v6M14 11v6"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManageAppointments ? 6 : 5 }}" class="appointment-directory-empty">No appointments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <div class="mt-5">
                {{ $appointments->links() }}
            </div>
        </section>

        <div x-cloak x-show="detailsOpen || createOpen || editOpen || deleteOpen" class="client-modal-overlay"></div>

        <section x-cloak x-show="detailsOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal appointment-details-modal" @click.outside="detailsOpen = false">
                <div class="client-form-modal-head">
                    <h2 class="client-modal-title">Appointment Details</h2>
                    <button type="button" class="client-form-close-lite" @click="detailsOpen = false" aria-label="Close appointment details">x</button>
                </div>

                <template x-if="detailsAppointment">
                    <div class="appointment-details-shell">
                        <section class="appointment-details-hero">
                            <div class="appointment-details-hero-main">
                                <div class="appointment-details-avatar" x-text="detailsAppointment.client_initial"></div>
                                <div>
                                    <p class="appointment-details-name" x-text="detailsAppointment.client_name"></p>
                                    <p class="appointment-details-role">Client</p>
                                </div>
                            </div>

                            <div :class="statusClass(detailsAppointment.status_key)" x-text="detailsAppointment.status"></div>
                        </section>

                        <section class="appointment-details-progress">
                            <p class="appointment-details-section-label">Status Progress</p>
                            <div class="appointment-progress-steps">
                                <div class="appointment-progress-step">
                                    <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep(detailsAppointment.status_key) >= 1 }">1</div>
                                    <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep(detailsAppointment.status_key) >= 1 }">Scheduled</p>
                                </div>
                                <div class="appointment-progress-line" :class="{ 'appointment-progress-line-active': progressStep(detailsAppointment.status_key) >= 2 }"></div>
                                <div class="appointment-progress-step">
                                    <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep(detailsAppointment.status_key) >= 2 }">2</div>
                                    <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep(detailsAppointment.status_key) >= 2 }">Confirmed</p>
                                </div>
                                <div class="appointment-progress-line" :class="{ 'appointment-progress-line-active': progressStep(detailsAppointment.status_key) >= 3 }"></div>
                                <div class="appointment-progress-step">
                                    <div class="appointment-progress-circle" :class="{ 'appointment-progress-circle-active': progressStep(detailsAppointment.status_key) >= 3 }">3</div>
                                    <p class="appointment-progress-text" :class="{ 'appointment-progress-text-active': progressStep(detailsAppointment.status_key) >= 3 }">Completed</p>
                                </div>
                            </div>
                        </section>

                        <section class="appointment-details-grid">
                            <div class="appointment-details-card">
                                <p class="appointment-details-card-label">Service</p>
                                <p class="appointment-details-card-value" x-text="detailsAppointment.service_type"></p>
                            </div>

                            <div class="appointment-details-card">
                                <p class="appointment-details-card-label">Staff</p>
                                <p class="appointment-details-card-value" x-text="detailsAppointment.staff_name"></p>
                            </div>

                            <div class="appointment-details-card">
                                <p class="appointment-details-card-label">Date</p>
                                <p class="appointment-details-card-value" x-text="detailsAppointment.appointment_date_long"></p>
                            </div>

                            <div class="appointment-details-card">
                                <p class="appointment-details-card-label">Time</p>
                                <p class="appointment-details-card-value" x-text="detailsAppointment.appointment_time_label"></p>
                            </div>

                            <div class="appointment-details-card appointment-details-card-full">
                                <p class="appointment-details-card-label">Notes</p>
                                <p class="appointment-details-notes" x-text="detailsAppointment.notes || 'No notes added.'"></p>
                            </div>
                        </section>
                    </div>
                </template>
            </div>
        </section>

        @if ($canManageAppointments)
            <section x-cloak x-show="createOpen" x-transition.opacity class="client-modal-wrap">
                <div class="client-modal appointment-form-modal" @click.outside="createOpen = false">
                    <div class="client-form-modal-head">
                        <h2 class="client-modal-title">New Appointment</h2>
                        <button type="button" class="client-form-close-lite" @click="createOpen = false" aria-label="Close add appointment">x</button>
                    </div>

                    <form method="POST" action="{{ route('appointments.store') }}" class="client-modal-form">
                        @csrf
                        <input type="hidden" name="appointment_modal_mode" value="create">

                        <div class="appointment-modal-grid">
                            <div class="client-modal-field appointment-modal-field-full">
                                <label for="create_client_id">Client *</label>
                                <select id="create_client_id" name="client_id" class="appointment-modal-select" required>
                                    <option value="">Select client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('client_id')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field appointment-modal-field-full">
                                <label for="create_staff_id">Staff *</label>
                                <select id="create_staff_id" name="staff_id" class="appointment-modal-select" required>
                                    <option value="">Assign staff</option>
                                    @foreach ($staffMembers as $staffMember)
                                        <option value="{{ $staffMember->id }}" @selected(old('staff_id') == $staffMember->id)>{{ $staffMember->name }}</option>
                                    @endforeach
                                </select>
                                @error('staff_id')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field appointment-modal-field-full">
                                <label for="create_service_type">Service Type *</label>
                                <select id="create_service_type" name="service_type" class="appointment-modal-select" required>
                                    <option value="">Select service type</option>
                                    @foreach ($serviceTypes as $serviceType)
                                        <option value="{{ $serviceType }}" @selected(old('service_type') === $serviceType)>{{ $serviceType }}</option>
                                    @endforeach
                                </select>
                                @error('service_type')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field">
                                <label for="create_appointment_date">Date *</label>
                                <input id="create_appointment_date" x-ref="createAppointmentDate" type="date" name="appointment_date" value="{{ old('appointment_date') }}" class="client-modal-input" :min="today" required>
                                @error('appointment_date')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field">
                                <label for="create_appointment_time">Time *</label>
                                <input id="create_appointment_time" type="time" name="appointment_time" value="{{ old('appointment_time') }}" class="client-modal-input" :min="minTimeFor($refs.createAppointmentDate?.value)" required>
                                @error('appointment_time')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field appointment-modal-field-full">
                                <label for="create_status">Status</label>
                                <input id="create_status" value="Scheduled" class="client-modal-input bg-slate-50" readonly>
                                <p class="text-[0.7rem] font-medium text-slate-500">New appointments are automatically created as scheduled.</p>
                            </div>

                            <div class="client-modal-field appointment-modal-field-full">
                                <label for="create_notes">Notes</label>
                                <textarea id="create_notes" name="notes" class="client-modal-textarea">{{ old('notes') }}</textarea>
                                @error('notes')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="client-modal-actions">
                            <button type="button" class="client-modal-button client-modal-button-secondary" @click="createOpen = false">Cancel</button>
                            <button type="submit" class="client-modal-button client-modal-button-primary">Create Appointment</button>
                        </div>
                    </form>
                </div>
            </section>

            <section x-cloak x-show="editOpen" x-transition.opacity class="client-modal-wrap">
                <div class="client-modal appointment-form-modal" @click.outside="editOpen = false">
                    <div class="client-form-modal-head">
                        <h2 class="client-modal-title">Edit Appointment</h2>
                        <button type="button" class="client-form-close-lite" @click="editOpen = false" aria-label="Close edit appointment">x</button>
                    </div>

                    <template x-if="editAppointment">
                        <form method="POST" :action="appointmentAction(editAppointment.id)" class="client-modal-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="appointment_modal_mode" value="edit">
                            <input type="hidden" name="appointment_id" :value="editAppointment.id">

                            <div class="appointment-modal-grid">
                                <div class="client-modal-field appointment-modal-field-full">
                                    <label for="edit_client_id">Client *</label>
                                    <select id="edit_client_id" name="client_id" class="appointment-modal-select" required>
                                        <option value="">Select client</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" :selected="String(editAppointment.client_id) === '{{ $client->id }}'">{{ $client->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_id')<p class="client-modal-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="client-modal-field appointment-modal-field-full">
                                    <label for="edit_staff_id">Staff *</label>
                                    <select id="edit_staff_id" name="staff_id" class="appointment-modal-select" required>
                                        <option value="">Assign staff</option>
                                        @foreach ($staffMembers as $staffMember)
                                            <option value="{{ $staffMember->id }}" :selected="String(editAppointment.staff_id) === '{{ $staffMember->id }}'">{{ $staffMember->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('staff_id')<p class="client-modal-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="client-modal-field appointment-modal-field-full">
                                    <label for="edit_service_type">Service Type *</label>
                                    <select id="edit_service_type" name="service_type" class="appointment-modal-select" required>
                                        <option value="">Select service type</option>
                                        @foreach ($serviceTypes as $serviceType)
                                            <option value="{{ $serviceType }}" :selected="editAppointment.service_type === '{{ $serviceType }}'">{{ $serviceType }}</option>
                                        @endforeach
                                    </select>
                                    @error('service_type')<p class="client-modal-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="client-modal-field">
                                    <label for="edit_appointment_date">Date *</label>
                                    <input id="edit_appointment_date" x-ref="editAppointmentDate" type="date" name="appointment_date" :value="editAppointment.appointment_date" class="client-modal-input" :min="minDateForEdit()" required>
                                    @error('appointment_date')<p class="client-modal-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="client-modal-field">
                                    <label for="edit_appointment_time">Time *</label>
                                    <input id="edit_appointment_time" type="time" name="appointment_time" :value="editAppointment.appointment_time" class="client-modal-input" :min="minTimeFor($refs.editAppointmentDate?.value, editAppointment.appointment_time)" required>
                                    @error('appointment_time')<p class="client-modal-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="client-modal-field appointment-modal-field-full">
                                    <label for="edit_status">Status</label>
                                    <select id="edit_status" name="status" class="appointment-modal-select" required>
                                        @foreach ($statuses as $item)
                                            <option value="{{ $item }}" :selected="editAppointment.status === '{{ $item }}'">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<p class="client-modal-error">{{ $message }}</p>@enderror
                                </div>

                                <div class="client-modal-field appointment-modal-field-full">
                                    <label for="edit_notes">Notes</label>
                                    <textarea id="edit_notes" name="notes" class="client-modal-textarea" x-text="editAppointment.notes"></textarea>
                                    @error('notes')<p class="client-modal-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="client-modal-actions">
                                <button type="button" class="client-modal-button client-modal-button-secondary" @click="editOpen = false">Cancel</button>
                                <button type="submit" class="client-modal-button client-modal-button-primary">Update</button>
                            </div>
                        </form>
                    </template>
                </div>
            </section>

            <section x-cloak x-show="deleteOpen" x-transition.opacity class="client-modal-wrap">
                <div class="client-modal appointment-delete-modal" @click.outside="deleteOpen = false">
                    <template x-if="deleteAppointment">
                        <form method="POST" :action="appointmentAction(deleteAppointment.id)">
                            @csrf
                            @method('DELETE')

                            <h2 class="client-modal-title">Delete Appointment</h2>
                            <p class="client-delete-copy">Are you sure you want to delete this appointment?</p>

                            <div class="client-modal-actions">
                                <button type="button" class="client-modal-button client-modal-button-secondary" @click="deleteOpen = false">Cancel</button>
                                <button type="submit" class="client-modal-button client-modal-button-danger">Delete</button>
                            </div>
                        </form>
                    </template>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
