<x-app-layout>
    <div class="client-page-stack">
        <section>
            <h2 class="client-section-title">Client/Edit</h2>
        </section>

        <section class="client-page-card mx-auto max-w-5xl">
            <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('clients._form', ['client' => $client, 'addressParts' => $addressParts])

                <div class="client-form-actions">
                    <button class="client-primary-button" type="submit">Update Client</button>
                    <a href="{{ route('clients.show', $client) }}" class="client-secondary-button">Cancel</a>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
