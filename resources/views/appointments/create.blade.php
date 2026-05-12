<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-amber-300">Appointments</p>
            <h2 class="mt-2 text-3xl text-stone-50">Book appointment</h2>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-400/30 bg-rose-400/10 px-5 py-4 text-sm text-rose-200">
                <p class="font-semibold">Please fix the appointment form.</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($clients->isEmpty() || $staffMembers->isEmpty())
            <div class="panel p-6">
                <h3 class="section-title">Setup needed before booking</h3>
                <div class="mt-4 space-y-3 text-sm text-stone-300">
                    @if ($clients->isEmpty())
                        <p>No clients exist yet. Add a client first before creating an appointment.</p>
                    @endif
                    @if ($staffMembers->isEmpty())
                        <p>No staff accounts are available to assign. An admin needs to register at least one staff user.</p>
                    @endif
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($clients->isEmpty())
                        <a href="{{ route('clients.create') }}" class="btn-primary">Add client</a>
                    @endif
                    <a href="{{ route('appointments.index') }}" class="btn-secondary">Back to appointments</a>
                </div>
            </div>
        @else
            <div class="panel p-6">
                <form method="POST" action="{{ route('appointments.store') }}" class="grid gap-5 md:grid-cols-2">
                    @csrf

                    <div>
                        <label class="text-sm text-stone-200">Client</label>
                        <select name="client_id" class="field" required>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm text-stone-200">Staff</label>
                        <select name="staff_id" class="field" required>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" @selected(old('staff_id') == $staff->id)>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm text-stone-200">Service type</label>
                        <select name="service_type" class="field" required>
                            <option value="">Select service type</option>
                            @foreach($serviceTypes as $serviceType)
                                <option value="{{ $serviceType }}" @selected(old('service_type') === $serviceType)>{{ $serviceType }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm text-stone-200">Status</label>
                        <select name="status" class="field">
                            @foreach($statuses as $item)
                                <option value="{{ $item }}" @selected(old('status', $appointment->status) === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm text-stone-200">Date</label>
                        <input type="date" name="appointment_date" class="field" value="{{ old('appointment_date') }}" required>
                    </div>

                    <div>
                        <label class="text-sm text-stone-200">Time</label>
                        <input type="time" name="appointment_time" class="field" value="{{ old('appointment_time') }}" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm text-stone-200">Notes</label>
                        <textarea name="notes" class="field">{{ old('notes') }}</textarea>
                    </div>

                    <div class="md:col-span-2 flex gap-3">
                        <button class="btn-primary">Save appointment</button>
                        <a href="{{ route('appointments.index') }}" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
