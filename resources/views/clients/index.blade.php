<x-app-layout>
    @php
        $clientPayload = $clients->getCollection()->map(function ($client) {
            return [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'full_name' => $client->full_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'notes' => $client->notes,
                'created_at_label' => $client->created_at->format('M Y'),
                'created_on_table' => $client->created_at->format('m/d/Y'),
                'initials' => strtoupper(substr($client->first_name, 0, 1).substr($client->last_name, 0, 1)),
                'appointments' => $client->appointments->map(function ($appointment) {
                    return [
                        'service_type' => $appointment->service_type,
                        'date' => $appointment->appointment_date->format('M d, Y'),
                        'time' => \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('H:i'),
                        'staff' => $appointment->staff->name ?? 'Unassigned',
                        'status' => str($appointment->status)->replace('_', ' ')->title()->toString(),
                        'status_key' => str($appointment->status)->lower()->replace(' ', '-')->replace('_', '-')->toString(),
                    ];
                })->values()->all(),
            ];
        })->values();

        $editClientId = old('client_id');
    @endphp

    <div
        x-data="{
            clients: @js($clientPayload),
            profileClient: null,
            editClient: null,
            deleteClient: null,
            createOpen: {{ $errors->any() && old('client_modal_mode', 'create') === 'create' ? 'true' : 'false' }},
            editOpen: {{ $errors->any() && old('client_modal_mode') === 'edit' ? 'true' : 'false' }},
            profileOpen: false,
            deleteOpen: false,
            parseAddressPart(address, partIndex) {
                if (!address) return '';
                const parts = address.split(',');
                return (parts[partIndex] || '').trim();
            },
            clientAction(id) {
                return `{{ url('/clients') }}/${id}`;
            },
            getClient(id) {
                return this.clients.find((client) => String(client.id) === String(id)) ?? null;
            },
            openProfile(id) {
                this.profileClient = this.getClient(id);
                this.profileOpen = true;
            },
            openEdit(id) {
                this.editClient = this.getClient(id);
                this.editOpen = true;
            },
            openDelete(id) {
                this.deleteClient = this.getClient(id);
                this.deleteOpen = true;
            },
            closeAll() {
                this.profileOpen = false;
                this.createOpen = false;
                this.editOpen = false;
                this.deleteOpen = false;
            },
            statusClass(status) {
                return {
                    scheduled: 'client-modal-status client-modal-status-scheduled',
                    confirmed: 'client-modal-status client-modal-status-confirmed',
                    completed: 'client-modal-status client-modal-status-completed',
                    cancelled: 'client-modal-status client-modal-status-cancelled',
                    'no-show': 'client-modal-status client-modal-status-no-show',
                }[status] || 'client-modal-status';
            },
            init() {
                if (this.editOpen) {
                    this.editClient = this.clients.find((client) => String(client.id) === String(@js($editClientId))) ?? null;
                }
            },
        }"
        class="client-directory"
        x-init="init()"
        @keydown.escape.window="closeAll()"
    >
        @if (session('status'))
            <div class="auth-status auth-status-success mb-4 mx-5 lg:mx-8 mt-4">{{ session('status') }}</div>
        @endif

        <header class="dashboard-topbar">
            <h1 class="dashboard-topbar-title">Clients</h1>
        </header>

        <section class="client-directory-page">
            <div class="client-directory-head">
                <p class="client-directory-count">{{ $clients->total() }} total clients</p>

                <button type="button" class="client-directory-add" @click="createOpen = true">
                    <span class="client-directory-add-icon">+</span>
                    <span>Add Client</span>
                </button>
            </div>

            <div class="client-directory-toolbar">
                <form method="GET" class="client-directory-search">
                    <svg class="client-directory-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" class="client-directory-search-input" placeholder="Search Clients">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                </form>

                <form method="GET" class="client-directory-filter">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <svg class="client-directory-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 6h16l-6 7v5l-4 2v-7L4 6Z"></path>
                    </svg>
                    <select name="filter" class="client-directory-filter-select" onchange="this.form.submit()">
                        <option value="latest" @selected($filter === 'latest')>Filter</option>
                        <option value="oldest" @selected($filter === 'oldest')>Oldest</option>
                    </select>
                </form>
            </div>

            <section class="client-directory-table-shell">
                <table class="client-directory-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Added</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr>
                                <td>
                                    <button type="button" class="client-directory-name-button" @click="openProfile({{ $client->id }})">
                                        <span>{{ $client->first_name }}</span>
                                        <span>{{ $client->last_name }}</span>
                                    </button>
                                </td>
                                <td class="client-directory-email">{{ $client->email ?: 'No email provided' }}</td>
                                <td>{{ $client->phone }}</td>
                                <td class="client-directory-date">{{ $client->created_at->format('m/d/Y') }}</td>
                                <td>
                                    <div class="client-directory-actions">
                                        <button type="button" class="client-directory-icon-button" @click="openEdit({{ $client->id }})" aria-label="Edit client">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20Z"></path>
                                                <path d="m13.5 6.5 3.5 3.5"></path>
                                            </svg>
                                        </button>

                                        <button type="button" class="client-directory-icon-button client-directory-icon-button-danger" @click="openDelete({{ $client->id }})" aria-label="Delete client">
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
                                <td colspan="5" class="client-directory-empty">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <div class="mt-5">
                {{ $clients->links() }}
            </div>
        </section>

        <div x-cloak x-show="profileOpen || createOpen || editOpen || deleteOpen" class="client-modal-overlay"></div>

        <section x-cloak x-show="profileOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal client-profile-modal" @click.outside="profileOpen = false">
                <button type="button" class="client-modal-close" @click="profileOpen = false" aria-label="Close profile">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 6l12 12M18 6 6 18"></path>
                    </svg>
                </button>

                <h2 class="client-modal-title">Client Profile</h2>

                <template x-if="profileClient">
                    <div>
                        <div class="client-profile-summary">
                            <div class="client-profile-avatar" x-text="profileClient.initials"></div>

                            <div class="client-profile-main">
                                <h3 class="client-profile-name" x-text="profileClient.full_name"></h3>
                                <p class="client-profile-since">Client since <span x-text="profileClient.created_at_label"></span></p>

                                <div class="client-profile-grid">
                                    <p class="client-profile-meta">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 5h4l2 5-2.5 1.5a14.2 14.2 0 0 0 5 5L16 14l5 2v4a2 2 0 0 1-2 2A17 17 0 0 1 2 5a2 2 0 0 1 2-2Z"></path></svg>
                                        <span x-text="profileClient.phone"></span>
                                    </p>
                                    <p class="client-profile-meta">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16v12H4z"></path><path d="m4 7 8 6 8-6"></path></svg>
                                        <span x-text="profileClient.email || 'No email provided'"></span>
                                    </p>
                                    <p class="client-profile-meta">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
                                        <span x-text="profileClient.address || 'No address provided'"></span>
                                    </p>
                                    <p class="client-profile-meta client-profile-meta-wide">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7h8M8 12h8M8 17h5"></path><path d="M5 4h14v16H5z"></path></svg>
                                        <span x-text="profileClient.notes || 'No notes available'"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="client-profile-history-head">
                            <h3 class="client-profile-history-title">Appointment History</h3>
                            <span class="client-profile-history-count" x-text="`${profileClient.appointments.length} total`"></span>
                        </div>

                        <div class="client-profile-history-list">
                            <template x-if="profileClient.appointments.length">
                                <div class="space-y-3">
                                    <template x-for="appointment in profileClient.appointments" :key="`${appointment.service_type}-${appointment.date}-${appointment.time}`">
                                        <article class="client-history-card">
                                            <div class="client-history-icon">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M7 2v3M17 2v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"></path>
                                                </svg>
                                            </div>

                                            <div class="client-history-main">
                                                <h4 class="client-history-name" x-text="appointment.service_type"></h4>
                                                <p class="client-history-meta">
                                                    <span x-text="appointment.date"></span>
                                                    <span x-text="appointment.time"></span>
                                                    <span x-text="appointment.staff"></span>
                                                </p>
                                            </div>

                                            <span :class="statusClass(appointment.status_key)" x-text="appointment.status"></span>
                                        </article>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!profileClient.appointments.length">
                                <div class="client-history-empty">No appointments yet.</div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <section x-cloak x-show="createOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal client-form-modal" @click.outside="createOpen = false">
                <div class="client-form-modal-head">
                    <h2 class="client-modal-title">Add New Client</h2>
                    <button type="button" class="client-form-close-lite" @click="createOpen = false" aria-label="Close add client">×</button>
                </div>

                <form method="POST" action="{{ route('clients.store') }}" class="client-modal-form">
                    @csrf
                    <input type="hidden" name="client_modal_mode" value="create">

                    <div class="client-modal-form-grid">
                        <div class="client-modal-field">
                            <label for="create_first_name">First Name *</label>
                            <input id="create_first_name" name="first_name" value="{{ old('first_name') }}" class="client-modal-input" required>
                            @error('first_name')<p class="client-modal-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="client-modal-field">
                            <label for="create_last_name">Last Name *</label>
                            <input id="create_last_name" name="last_name" value="{{ old('last_name') }}" class="client-modal-input" required>
                            @error('last_name')<p class="client-modal-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="client-modal-field">
                            <label for="create_email">Email</label>
                            <input id="create_email" type="email" name="email" value="{{ old('email') }}" class="client-modal-input">
                            @error('email')<p class="client-modal-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="client-modal-field">
                            <label for="create_phone">Phone *</label>
                            <input id="create_phone" name="phone" value="{{ old('phone') }}" class="client-modal-input" required>
                            @error('phone')<p class="client-modal-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="client-modal-field client-modal-field-full">
                            <label>Address</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div>
                                    <label for="create_address_house_street" class="text-xs text-stone-600 block mb-1">House Number & Street</label>
                                    <input
                                        id="create_address_house_street"
                                        name="address_house_street"
                                        value="{{ old('address_house_street') }}"
                                        class="client-modal-input"
                                        placeholder="e.g., 123 Main Street"
                                        required>
                                </div>

                                <div>
                                    <label for="create_address_barangay" class="text-xs text-stone-600 block mb-1">Barangay/Subdivision</label>
                                    <input
                                        id="create_address_barangay"
                                        name="address_barangay"
                                        value="{{ old('address_barangay') }}"
                                        class="client-modal-input"
                                        placeholder="e.g., Barangay San Jose"
                                        required>
                                </div>

                                <div>
                                    <label for="create_address_city" class="text-xs text-stone-600 block mb-1">City/Municipality</label>
                                    <input
                                        id="create_address_city"
                                        name="address_city"
                                        value="{{ old('address_city') }}"
                                        class="client-modal-input"
                                        placeholder="e.g., Manila"
                                        required>
                                </div>

                                <div>
                                    <label for="create_address_postal_province" class="text-xs text-stone-600 block mb-1">Postal Code & Province</label>
                                    <input
                                        id="create_address_postal_province"
                                        name="address_postal_province"
                                        value="{{ old('address_postal_province') }}"
                                        class="client-modal-input"
                                        placeholder="e.g., 1000 NCR"
                                        required>
                                </div>
                            </div>
                            <input type="hidden" id="create_address" name="address">
                            @error('address')<p class="client-modal-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="client-modal-field client-modal-field-full">
                            <label for="create_notes">Notes</label>
                            <textarea id="create_notes" name="notes" class="client-modal-textarea">{{ old('notes') }}</textarea>
                            @error('notes')<p class="client-modal-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="client-modal-actions">
                        <button type="button" class="client-modal-button client-modal-button-secondary" @click="createOpen = false">Cancel</button>
                        <button type="submit" class="client-modal-button client-modal-button-primary">Add Client</button>
                    </div>
                </form>

                <script>
                    function updateCreateAddressField() {
                        const houseStreet = document.getElementById('create_address_house_street')?.value || '';
                        const barangay = document.getElementById('create_address_barangay')?.value || '';
                        const city = document.getElementById('create_address_city')?.value || '';
                        const postalProvince = document.getElementById('create_address_postal_province')?.value || '';

                        const addressField = document.getElementById('create_address');
                        if (addressField) {
                            addressField.value = `${houseStreet}, ${barangay}, ${city}, ${postalProvince}`;
                        }
                    }

                    // Update address field when any input changes
                    ['create_address_house_street', 'create_address_barangay', 'create_address_city', 'create_address_postal_province'].forEach(id => {
                        const element = document.getElementById(id);
                        if (element) {
                            element.addEventListener('input', updateCreateAddressField);
                        }
                    });

                    // Update on page load
                    document.addEventListener('DOMContentLoaded', updateCreateAddressField);
                </script>
            </div>
        </section>

        <section x-cloak x-show="editOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal client-form-modal" @click.outside="editOpen = false">
                <div class="client-form-modal-head">
                    <h2 class="client-modal-title">Edit Client</h2>
                    <button type="button" class="client-form-close-lite" @click="editOpen = false" aria-label="Close edit client">×</button>
                </div>

                <template x-if="editClient">
                    <form method="POST" :action="clientAction(editClient.id)" class="client-modal-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="client_modal_mode" value="edit">
                        <input type="hidden" name="client_id" :value="editClient.id">

                        <div class="client-modal-form-grid">
                            <div class="client-modal-field">
                                <label for="edit_first_name">First Name *</label>
                                <input id="edit_first_name" name="first_name" :value="editClient.first_name" class="client-modal-input" required>
                                @error('first_name')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field">
                                <label for="edit_last_name">Last Name *</label>
                                <input id="edit_last_name" name="last_name" :value="editClient.last_name" class="client-modal-input" required>
                                @error('last_name')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field">
                                <label for="edit_email">Email</label>
                                <input id="edit_email" type="email" name="email" :value="editClient.email" class="client-modal-input">
                                @error('email')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field">
                                <label for="edit_phone">Phone *</label>
                                <input id="edit_phone" name="phone" :value="editClient.phone" class="client-modal-input" required>
                                @error('phone')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field client-modal-field-full">
                                <label>Address</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label for="edit_address_house_street" class="text-xs text-stone-600 block mb-1">House Number & Street</label>
                                        <input
                                            id="edit_address_house_street"
                                            name="address_house_street"
                                            :value="parseAddressPart(editClient.address, 0)"
                                            class="client-modal-input"
                                            placeholder="e.g., 123 Main Street"
                                            required>
                                    </div>

                                    <div>
                                        <label for="edit_address_barangay" class="text-xs text-stone-600 block mb-1">Barangay/Subdivision</label>
                                        <input
                                            id="edit_address_barangay"
                                            name="address_barangay"
                                            :value="parseAddressPart(editClient.address, 1)"
                                            class="client-modal-input"
                                            placeholder="e.g., Barangay San Jose"
                                            required>
                                    </div>

                                    <div>
                                        <label for="edit_address_city" class="text-xs text-stone-600 block mb-1">City/Municipality</label>
                                        <input
                                            id="edit_address_city"
                                            name="address_city"
                                            :value="parseAddressPart(editClient.address, 2)"
                                            class="client-modal-input"
                                            placeholder="e.g., Manila"
                                            required>
                                    </div>

                                    <div>
                                        <label for="edit_address_postal_province" class="text-xs text-stone-600 block mb-1">Postal Code & Province</label>
                                        <input
                                            id="edit_address_postal_province"
                                            name="address_postal_province"
                                            :value="parseAddressPart(editClient.address, 3)"
                                            class="client-modal-input"
                                            placeholder="e.g., 1000 NCR"
                                            required>
                                    </div>
                                </div>
                                <input type="hidden" id="edit_address" name="address" :value="editClient.address">
                                @error('address')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="client-modal-field client-modal-field-full">
                                <label for="edit_notes">Notes</label>
                                <textarea id="edit_notes" name="notes" class="client-modal-textarea" x-text="editClient.notes"></textarea>
                                @error('notes')<p class="client-modal-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="client-modal-actions">
                            <button type="button" class="client-modal-button client-modal-button-secondary" @click="editOpen = false">Cancel</button>
                            <button type="submit" class="client-modal-button client-modal-button-primary">Update Client</button>
                        </div>
                    </form>

                    <script>
                        function updateEditAddressField() {
                            const houseStreet = document.getElementById('edit_address_house_street')?.value || '';
                            const barangay = document.getElementById('edit_address_barangay')?.value || '';
                            const city = document.getElementById('edit_address_city')?.value || '';
                            const postalProvince = document.getElementById('edit_address_postal_province')?.value || '';

                            const addressField = document.getElementById('edit_address');
                            if (addressField) {
                                addressField.value = `${houseStreet}, ${barangay}, ${city}, ${postalProvince}`;
                            }
                        }

                        // Update address field when any input changes
                        ['edit_address_house_street', 'edit_address_barangay', 'edit_address_city', 'edit_address_postal_province'].forEach(id => {
                            const element = document.getElementById(id);
                            if (element) {
                                element.addEventListener('input', updateEditAddressField);
                            }
                        });

                        // Update on page load and after Alpine updates
                        document.addEventListener('DOMContentLoaded', updateEditAddressField);
                        setInterval(updateEditAddressField, 100); // Check every 100ms for updates from Alpine
                    </script>
                </template>
            </div>
        </section>

        <section x-cloak x-show="deleteOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal client-delete-modal" @click.outside="deleteOpen = false">
                <template x-if="deleteClient">
                    <form method="POST" :action="clientAction(deleteClient.id)">
                        @csrf
                        @method('DELETE')

                        <h2 class="client-modal-title">Delete Client</h2>
                        <p class="client-delete-copy">Are you sure you want to delete this client? This action cannot be undone.</p>

                        <div class="client-modal-actions">
                            <button type="button" class="client-modal-button client-modal-button-secondary" @click="deleteOpen = false">Cancel</button>
                            <button type="submit" class="client-modal-button client-modal-button-danger">Delete</button>
                        </div>
                    </form>
                </template>
            </div>
        </section>
    </div>
</x-app-layout>
