<x-app-layout>
    @php
        $userPayload = $users->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ];
        })->values();

        $editUserId = old('user_id');
    @endphp

    <section
        x-data="{
            users: @js($userPayload),
            createOpen: {{ $errors->any() && old('user_modal_mode', 'create') === 'create' ? 'true' : 'false' }},
            editOpen: {{ $errors->any() && old('user_modal_mode') === 'edit' ? 'true' : 'false' }},
            deleteOpen: false,
            editUser: null,
            deleteUser: null,
            userAction(id) {
                return `{{ url('/users') }}/${id}`;
            },
            getUser(id) {
                return this.users.find((user) => String(user.id) === String(id)) ?? null;
            },
            openCreate() {
                this.createOpen = true;
            },
            openEdit(id) {
                this.editUser = this.getUser(id);
                this.editOpen = true;
            },
            openDelete(id) {
                this.deleteUser = this.getUser(id);
                this.deleteOpen = true;
            },
            closeAll() {
                this.createOpen = false;
                this.editOpen = false;
                this.deleteOpen = false;
            },
            init() {
                if (this.editOpen) {
                    this.editUser = this.getUser(@js($editUserId));
                }
            },
        }"
        class="users-directory"
        x-init="init()"
        @keydown.escape.window="closeAll()"
    >
        @if (session('status'))
            <div class="auth-status auth-status-success mx-5 mt-4 lg:mx-8">{{ session('status') }}</div>
        @endif

        <header class="dashboard-topbar">
            <h1 class="dashboard-topbar-title">Users</h1>
        </header>

        <div class="users-directory-page">
            <div class="users-directory-head">
                <div>
                    <p class="users-directory-copy">Manage team members and send invitations</p>
                </div>
            </div>

            <div class="users-directory-toolbar">
                <form method="GET" class="users-directory-search">
                    <svg class="users-directory-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" class="users-directory-search-input" placeholder="Search users">
                    <input type="hidden" name="role" value="{{ $role }}">
                </form>

                <form method="GET" class="users-directory-filter">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <svg class="users-directory-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 6h16l-6 7v5l-4 2v-7L4 6Z"></path>
                    </svg>
                    <select name="role" class="users-directory-filter-select" onchange="this.form.submit()">
                        <option value="">Filter</option>
                        @foreach ($roles as $item)
                            <option value="{{ $item }}" @selected($role === $item)>{{ ucfirst($item) }}</option>
                        @endforeach
                    </select>
                </form>

                <button type="button" class="users-directory-add" @click="openCreate()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 8v8M8 12h8"></path>
                    </svg>
                    <span>Add New User</span>
                </button>
            </div>

            <section class="users-directory-table-shell">
                <table class="users-directory-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>
                                    <div class="users-directory-actions">
                                        <button type="button" class="users-directory-icon-button" @click="openEdit({{ $user->id }})" aria-label="Edit user">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path d="m4 20 4.5-1 9-9-3.5-3.5-9 9L4 20Z"></path>
                                                <path d="m13.5 6.5 3.5 3.5"></path>
                                            </svg>
                                        </button>

                                        @if (auth()->id() !== $user->id)
                                            <button type="button" class="users-directory-icon-button users-directory-icon-button-danger" @click="openDelete({{ $user->id }})" aria-label="Delete user">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path d="M4 7h16"></path>
                                                    <path d="M9 7V4h6v3"></path>
                                                    <path d="M7 7v13h10V7"></path>
                                                    <path d="M10 11v6M14 11v6"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="users-directory-empty">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <div class="users-directory-pagination">
                {{ $users->links() }}
            </div>
        </div>

        <div x-cloak x-show="createOpen || editOpen || deleteOpen" class="client-modal-overlay"></div>

        <section x-cloak x-show="createOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal users-modal" @click.outside="createOpen = false">
                <div class="client-form-modal-head">
                    <div class="users-modal-title-wrap">
                        <svg class="users-modal-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M19 8v6M16 11h6"></path>
                        </svg>
                        <h2 class="client-modal-title">Register New User</h2>
                    </div>
                    <button type="button" class="client-form-close-lite" @click="createOpen = false" aria-label="Close register user">x</button>
                </div>

                <form method="POST" action="{{ route('register') }}" class="users-modal-form">
                    @csrf
                    <input type="hidden" name="user_modal_mode" value="create">

                    <div class="users-modal-field">
                        <label for="create_name">Full Name *</label>
                        <div class="users-modal-input-wrap">
                            <svg class="users-modal-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M20 21a8 8 0 0 0-16 0"></path>
                                <circle cx="12" cy="8" r="4"></circle>
                            </svg>
                            <input id="create_name" name="name" value="{{ old('name') }}" class="users-modal-input users-modal-input-with-icon" placeholder="Ineryss" required>
                        </div>
                        @error('name')<p class="service-record-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="users-modal-field">
                        <label for="create_email">Email Address *</label>
                        <div class="users-modal-input-wrap">
                            <svg class="users-modal-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                <path d="m4 7 8 6 8-6"></path>
                            </svg>
                            <input id="create_email" type="email" name="email" value="{{ old('email') }}" class="users-modal-input users-modal-input-with-icon" placeholder="user@example.com" required>
                        </div>
                        @error('email')<p class="service-record-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="users-modal-field">
                        <label for="create_role">Role *</label>
                        <select id="create_role" name="role" class="users-modal-select" required>
                            @foreach ($registerableRoles as $item)
                                <option value="{{ $item }}" @selected(old('role', $registerableRoles[1] ?? $registerableRoles[0] ?? '') === $item)>{{ ucfirst($item) }}</option>
                            @endforeach
                        </select>
                        @error('role')<p class="service-record-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="users-modal-field">
                        <label for="create_password">Password *</label>
                        <input id="create_password" type="password" name="password" class="users-modal-input" required autocomplete="new-password">
                        @error('password')<p class="service-record-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="users-modal-field">
                        <label for="create_password_confirmation">Confirm Password *</label>
                        <input id="create_password_confirmation" type="password" name="password_confirmation" class="users-modal-input" required autocomplete="new-password">
                        @error('password_confirmation')<p class="service-record-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="users-modal-note">
                        The user will be created with this password and can sign in right away.
                    </div>

                    <div class="users-modal-actions">
                        <button type="button" class="service-record-modal-button service-record-modal-button-secondary" @click="createOpen = false">Cancel</button>
                        <button type="submit" class="service-record-modal-button service-record-modal-button-primary">Register User</button>
                    </div>
                </form>
            </div>
        </section>

        <section x-cloak x-show="editOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal users-modal" @click.outside="editOpen = false">
                <div class="client-form-modal-head">
                    <h2 class="client-modal-title">Edit User</h2>
                    <button type="button" class="client-form-close-lite" @click="editOpen = false" aria-label="Close edit user">x</button>
                </div>

                <template x-if="editUser">
                    <form method="POST" :action="userAction(editUser.id)" class="users-modal-form">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="user_modal_mode" value="edit">
                        <input type="hidden" name="user_id" :value="editUser.id">

                        <div class="users-modal-field">
                            <label for="edit_name">Full Name *</label>
                            <input id="edit_name" name="name" :value="editUser.name" class="users-modal-input" required>
                            @error('name')<p class="service-record-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="users-modal-field">
                            <label for="edit_email">Email Address *</label>
                            <input id="edit_email" type="email" name="email" :value="editUser.email" class="users-modal-input" required>
                            @error('email')<p class="service-record-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="users-modal-field">
                            <label for="edit_role">Role *</label>
                            <select id="edit_role" name="role" class="users-modal-select" required>
                                @foreach ($roles as $item)
                                    <option value="{{ $item }}" :selected="editUser.role === '{{ $item }}'">{{ ucfirst($item) }}</option>
                                @endforeach
                            </select>
                            @error('role')<p class="service-record-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="users-modal-actions">
                            <button type="button" class="service-record-modal-button service-record-modal-button-secondary" @click="editOpen = false">Cancel</button>
                            <button type="submit" class="service-record-modal-button service-record-modal-button-primary">Update User</button>
                        </div>
                    </form>
                </template>
            </div>
        </section>

        <section x-cloak x-show="deleteOpen" x-transition.opacity class="client-modal-wrap">
            <div class="client-modal users-delete-modal" @click.outside="deleteOpen = false">
                <template x-if="deleteUser">
                    <form method="POST" :action="userAction(deleteUser.id)">
                        @csrf
                        @method('DELETE')

                        <h2 class="client-modal-title">Delete User</h2>
                        <p class="service-record-delete-copy">Are you sure you want to delete this user account?</p>

                        <div class="users-modal-actions">
                            <button type="button" class="service-record-modal-button service-record-modal-button-secondary" @click="deleteOpen = false">Cancel</button>
                            <button type="submit" class="service-record-modal-button service-record-modal-button-danger">Delete</button>
                        </div>
                    </form>
                </template>
            </div>
        </section>
    </section>
</x-app-layout>
