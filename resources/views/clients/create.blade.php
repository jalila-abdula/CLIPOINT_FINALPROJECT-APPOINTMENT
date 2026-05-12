<x-app-layout>
    <div class="client-page-stack">
        <section>
            <h2 class="client-section-title">Client/Add Client</h2>
        </section>

        <section class="client-page-card mx-auto max-w-5xl">
            <form method="POST" action="{{ route('clients.store') }}" class="space-y-6">
                @csrf

                @include('clients._form', ['client' => $client, 'addressParts' => $addressParts])

                <div class="client-form-actions">
                    <button class="client-primary-button" type="submit">Save Client</button>
                    <a href="{{ route('clients.index') }}" class="client-secondary-button">Cancel</a>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
