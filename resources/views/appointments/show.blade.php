<x-app-layout>
    <x-slot name="header">
        <div><p class="text-xs uppercase tracking-[0.35em] text-amber-300">Appointments</p><h2 class="mt-2 text-3xl text-stone-50">{{ $appointment->client->full_name }}</h2></div>
        @if (!auth()->user()->isStaff())<a href="{{ route('appointments.edit', $appointment) }}" class="btn-secondary">Edit</a>@endif
    </x-slot>
    <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <div class="panel p-6 space-y-3 text-sm text-stone-300">
            <p><span class="text-stone-500">Service:</span> {{ $appointment->service_type }}</p>
            <p><span class="text-stone-500">Staff:</span> {{ $appointment->staff->name }}</p>
            <p><span class="text-stone-500">Created by:</span> {{ $appointment->creator->name }}</p>
            <p><span class="text-stone-500">Status:</span> {{ $appointment->status }}</p>
            <p><span class="text-stone-500">Date:</span> {{ $appointment->appointment_date->format('M d, Y') }}</p>
            <p><span class="text-stone-500">Time:</span> {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</p>
            <p><span class="text-stone-500">Notes:</span> {{ $appointment->notes ?: 'No notes' }}</p>
        </div>
        <div class="space-y-6">
            <div class="panel p-6">
                <h3 class="section-title">Update status</h3>
                <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="mt-4 flex flex-col gap-4 md:flex-row">
                    @csrf
                    <select name="status" class="field mt-0">@foreach($statuses as $item)<option value="{{ $item }}" @selected($appointment->status === $item)>{{ $item }}</option>@endforeach</select>
                    <button class="btn-primary">Save status</button>
                </form>
            </div>
            @if (!auth()->user()->isStaff())
                <div class="panel p-6">
                    <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" onsubmit="return confirm('Delete this appointment?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn-secondary" type="submit">Delete appointment</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
