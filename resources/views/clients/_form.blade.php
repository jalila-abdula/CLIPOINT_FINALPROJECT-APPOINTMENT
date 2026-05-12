<div class="client-form-grid">
    <div class="client-field-group">
        <label for="first_name" class="client-field-label">First Name</label>
        <input id="first_name" name="first_name" value="{{ old('first_name', $client->first_name) }}" class="client-field-input" required>
        @error('first_name')<p class="text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="client-field-group">
        <label for="last_name" class="client-field-label">Last Name</label>
        <input id="last_name" name="last_name" value="{{ old('last_name', $client->last_name) }}" class="client-field-input" required>
        @error('last_name')<p class="text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="client-field-group">
        <label for="email" class="client-field-label">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $client->email) }}" class="client-field-input">
        @error('email')<p class="text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="client-field-group">
        <label for="phone" class="client-field-label">Phone Number</label>
        <input id="phone" name="phone" value="{{ old('phone', $client->phone) }}" class="client-field-input" required>
        @error('phone')<p class="text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="client-field-group md:col-span-2">
        <label class="client-field-label">Address</label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label for="address_house_street" class="text-sm text-stone-600 block mb-1">House Number & Street</label>
                <input
                    id="address_house_street"
                    name="address_house_street"
                    value="{{ old('address_house_street', $addressParts['house_street'] ?? '') }}"
                    class="client-field-input"
                    placeholder="e.g., 123 Main Street"
                    required>
            </div>

            <div>
                <label for="address_barangay" class="text-sm text-stone-600 block mb-1">Barangay/Subdivision</label>
                <input
                    id="address_barangay"
                    name="address_barangay"
                    value="{{ old('address_barangay', $addressParts['barangay'] ?? '') }}"
                    class="client-field-input"
                    placeholder="e.g., Barangay San Jose"
                    required>
            </div>

            <div>
                <label for="address_city" class="text-sm text-stone-600 block mb-1">City/Municipality</label>
                <input
                    id="address_city"
                    name="address_city"
                    value="{{ old('address_city', $addressParts['city'] ?? '') }}"
                    class="client-field-input"
                    placeholder="e.g., Manila"
                    required>
            </div>

            <div>
                <label for="address_postal_province" class="text-sm text-stone-600 block mb-1">Postal Code & Province</label>
                <input
                    id="address_postal_province"
                    name="address_postal_province"
                    value="{{ old('address_postal_province', $addressParts['postal_province'] ?? '') }}"
                    class="client-field-input"
                    placeholder="e.g., 1000 NCR"
                    required>
            </div>
        </div>

        <input type="hidden" id="address" name="address">

        @error('address')<p class="text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>

    <script>
        function updateAddressField() {
            const houseStreet = document.getElementById('address_house_street').value;
            const barangay = document.getElementById('address_barangay').value;
            const city = document.getElementById('address_city').value;
            const postalProvince = document.getElementById('address_postal_province').value;

            const addressField = document.getElementById('address');
            if (houseStreet || barangay || city || postalProvince) {
                addressField.value = `${houseStreet}, ${barangay}, ${city}, ${postalProvince}`;
            }
        }


        ['address_house_street', 'address_barangay', 'address_city', 'address_postal_province'].forEach(id => {
            document.getElementById(id).addEventListener('input', updateAddressField);
        });

        document.addEventListener('DOMContentLoaded', updateAddressField);
    </script>

    <div class="client-field-group md:col-span-2">
        <label for="notes" class="client-field-label">Notes</label>
        <textarea id="notes" name="notes" class="client-field-textarea">{{ old('notes', $client->notes) }}</textarea>
        @error('notes')<p class="text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
