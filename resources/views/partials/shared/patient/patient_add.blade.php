{{-- Shared Patient Add Modal - Works for both Midwife and BHW --}}
<div id="patient-modal"
    class="modal-overlay {{ $errors->any() ? '' : 'hidden' }} fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="add-modal-title"
    onclick="closePatientModal(event)">

    <div class="modal-content relative w-full max-w-md bg-white rounded-xl shadow-2xl p-6 my-8"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 id="add-modal-title" class="text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Register New Parent
            </h3>
            <button type="button"
                    onclick="closePatientModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                    aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="@roleRoute('patients.store')"
            method="POST"
            id="patient-form"
            class="space-y-5">
            @csrf

            <!-- Show server-side validation errors -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <div class="font-medium">Please correct the following errors:</div>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Personal Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="md:col-span-2 border-b pb-2 mb-2">
                    <h4 class="font-semibold text-gray-800">Personal Information</h4>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" id="add-first-name" required value="{{ old('first_name') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('first_name') error-border @enderror">
                    @error('first_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" id="add-last-name" required value="{{ old('last_name') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('last_name') error-border @enderror">
                    @error('last_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Age *</label>
                    <input type="number" name="age" id="add-age" min="15" max="50" required value="{{ old('age') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('age') error-border @enderror">
                    @error('age')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Occupation *</label>
                    <input type="text" name="occupation" id="add-occupation" required value="{{ old('occupation') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('occupation') error-border @enderror">
                    @error('occupation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Contact *</label>
                    <input type="tel" name="contact" id="add-contact" required value="{{ old('contact') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('contact') error-border @enderror">
                    @error('contact')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact *</label>
                    <input type="tel" name="emergency_contact" id="add-emergency-contact" required value="{{ old('emergency_contact') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('emergency_contact') error-border @enderror">
                    @error('emergency_contact')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registration Date (Optional)</label>
                    <input type="date" name="registration_date" id="add-registration-date" max="{{ date('Y-m-d') }}" value="{{ old('registration_date') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('registration_date') error-border @enderror">
                    <p class="text-xs text-gray-500 mt-1">Leave blank for current date</p>
                    @error('registration_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                    <select name="address" id="add-address" required
                            class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('address') error-border @enderror">
                        <option value="">-- Select Address --</option>
                        <option value="Brgy. Mecolong, Dumalinao, Zamboanga del Sur" {{ old('address') == 'Brgy. Mecolong, Dumalinao, Zamboanga del Sur' ? 'selected' : '' }}>
                            Brgy. Mecolong, Dumalinao, Zamboanga del Sur
                        </option>
                    </select>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-4 border-t">
                <button type="submit"
                        id="add-submit-btn"
                        class="btn-primary flex-1 bg-primary text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: var(--primary); color: white;" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                        <i class="fas fa-save mr-2"></i>
                    Register Parent
                </button>
                <button type="button"
                        onclick="closePatientModal()"
                        class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
