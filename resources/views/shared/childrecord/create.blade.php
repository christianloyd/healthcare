@extends('layout.' . auth()->user()->role)
@section('title', 'Add Child Record')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --primary: #D4A373;
        --secondary: #ecb99e;
        --text-dark: #3d2a1b;
    }

    * {
        font-family: 'Inter', sans-serif;
    }

    .section-header {
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .form-input {
        transition: all 0.2s ease;
    }

    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(36, 59, 85, 0.15);
        border-color: var(--primary);
        outline: none;
    }

    .btn-primary-clean {
        background-color: var(--secondary);
        color: var(--text-dark);
    }

    .btn-primary-clean:hover {
        background-color: var(--primary);
        color: white;
    }

    .error-border {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 2rem;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .step.active .step-number {
        background-color: var(--primary);
        color: white;
    }

    .step-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .step.active .step-label {
        color: var(--primary);
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="hover:text-primary">
            <i class="fas fa-home"></i> Home
        </a>
        <span>/</span>
        <a href="{{ route(auth()->user()->role . '.childrecord.index') }}" class="hover:text-primary">
            Child Records
        </a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Add New</span>
    </nav>

    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-baby text-primary mr-3"></i>
                    Add New Child Record
                </h1>
                <p class="text-gray-600 mt-1">Register a new child in the health monitoring system</p>
            </div>
            <a href="{{ route(auth()->user()->role . '.childrecord.index') }}"
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @include('components.flowbite-alert')

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            <div class="font-medium flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Please correct the following errors:
            </div>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Step Indicator
    <div id="stepIndicator" class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="step-indicator">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <div class="step-label">Mother Info</div>
            </div>
            <div class="mx-4 h-0.5 w-16 bg-gray-300"></div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-label">Child Details</div>
            </div>
        </div>
    </div>-->

    <!-- Mother Confirmation Step -->
    <div id="motherConfirmationStep" class="bg-white rounded-xl shadow-sm p-8 border border-gray-200">
        <div class="text-center max-w-2xl mx-auto">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-question text-blue-600 text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Mother Information</h2>
            <p class="text-gray-600 mb-8">Is the child's mother already registered in our system?</p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <button onclick="showMotherForm(true)" type="button"
                        class="bg-primary text-white px-8 py-4 rounded-lg font-medium hover:bg-primary-dark transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-check mr-2"></i>Yes, Select Existing Mother
                </button>
                <button onclick="showMotherForm(false)" type="button"
                        class="bg-gray-700 text-white px-8 py-4 rounded-lg font-medium hover:bg-gray-800 transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-2"></i>No, Register New Mother
                </button>
            </div>
        </div>
    </div>

    <!-- Main Form (Initially Hidden) -->
    <div id="childRecordFormContainer" class="hidden bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <form id="recordForm"
              action="{{ route(auth()->user()->role . '.childrecord.store') }}"
              method="POST"
              class="space-y-6">
            @csrf
            <input type="hidden" id="motherExists" name="mother_exists" value="">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- LEFT COLUMN -->
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <div class="section-header">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Basic Information
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                    <input type="text" name="first_name" required
                                           class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                           placeholder="Enter first name"
                                           value="{{ old('first_name') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                                    <input type="text" name="middle_name"
                                           class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                           placeholder="Optional"
                                           value="{{ old('middle_name') }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                    <input type="text" name="last_name" required
                                           class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                           placeholder="Enter last name"
                                           value="{{ old('last_name') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                    <div class="flex space-x-6 mt-2">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="gender" value="Male" required
                                                   class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">
                                            <span class="ml-2 text-gray-700">Male</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="gender" value="Female" required
                                                   class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">
                                            <span class="ml-2 text-gray-700">Female</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Birth Date *</label>
                                <input type="date" name="birthdate" required
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       value="{{ old('birthdate') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Birth Details -->
                    <div>
                        <div class="section-header">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-baby-carriage text-primary mr-2"></i>
                                Birth Details
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Birth Height (cm)</label>
                                <input type="number" name="birth_height" step="0.1" min="0" max="999.99"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       placeholder="e.g., 50.5"
                                       value="{{ old('birth_height') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Birth Weight (kg)</label>
                                <input type="number" name="birth_weight" step="0.01" min="0" max="99.999"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       placeholder="e.g., 3.25"
                                       value="{{ old('birth_weight') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Birth Place</label>
                                <input type="text" name="birthplace"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       placeholder="Hospital or location"
                                       value="{{ old('birthplace') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="space-y-6">
                    <!-- Mother Information -->
                    <div>
                        <div class="section-header flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-female text-primary mr-2"></i>
                                Mother Information
                            </h3>
                            <button type="button" onclick="changeMotherType()" class="text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit mr-1"></i>Change Selection
                            </button>
                        </div>

                        <!-- Existing Mother Selection -->
                        <div id="existingMotherSection" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Mother *</label>
                                <select name="mother_id" id="mother_id"
                                        class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300">
                                    <option value="">-- Select Mother --</option>
                                    @foreach($mothers as $mother)
                                        <option value="{{ $mother->id }}"
                                                data-name="{{ $mother->name }}"
                                                data-age="{{ $mother->age ?? '' }}"
                                                data-contact="{{ $mother->contact ?? '' }}"
                                                data-address="{{ $mother->address ?? '' }}">
                                            {{ $mother->name }} (ID: {{ $mother->formatted_patient_id ?: 'PT-' . str_pad($mother->id, 3, '0', STR_PAD_LEFT) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Mother Details Display -->
                            <div id="motherDetails" class="hidden bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                    Mother Details
                                </h4>
                                <div class="text-sm text-gray-700 space-y-2">
                                    <p><strong>Name:</strong> <span id="motherName">-</span></p>
                                    <p><strong>Age:</strong> <span id="motherAge">-</span></p>
                                    <p><strong>Contact:</strong> <span id="motherContact">-</span></p>
                                    <p><strong>Address:</strong> <span id="motherAddress">-</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- New Mother Input -->
                        <div id="newMotherSection" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Full Name *</label>
                                <input type="text" name="mother_name"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       placeholder="Enter mother's full name"
                                       value="{{ old('mother_name') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Age *</label>
                                <input type="number" name="mother_age" min="15" max="50"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       placeholder="Enter mother's age"
                                       value="{{ old('mother_age') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Contact Number *</label>
                                <input type="tel" name="mother_contact"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       placeholder="+639123456789 or 09123456789"
                                       pattern="(\+63|0)[0-9]{10}"
                                       maxlength="13"
                                       value="{{ old('mother_contact') }}">
                                <div class="text-xs text-gray-500 mt-1">Format: +639123456789 or 09123456789</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Address *</label>
                                <textarea name="mother_address" rows="3"
                                          class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 resize-none"
                                          placeholder="Enter mother's complete address">{{ old('mother_address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Father Information -->
                    <div>
                        <div class="section-header">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-male text-primary mr-2"></i>
                                Father Information
                            </h3>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Father's Name</label>
                            <input type="text" name="father_name"
                                   class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                   placeholder="Enter father's full name (optional)"
                                   value="{{ old('father_name') }}">
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div>
                        <div class="section-header">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-phone text-primary mr-2"></i>
                                Contact Details
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number</label>
                                <input type="tel" name="phone_number"
                                    class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                    placeholder="+639123456789 or 09123456789"
                                    maxlength="13"
                                    value="{{ old('phone_number') }}">
                                <div class="text-xs text-gray-500 mt-1">Optional - For direct contact</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea name="address" rows="3"
                                          class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 resize-none"
                                          placeholder="Enter complete address">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route(auth()->user()->role . '.childrecord.index') }}"
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" id="submit-btn"
                        class="bg-primary text-white px-6 py-3 rounded-lg font-medium transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i>Save Child Record
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showMotherForm(hasExisting) {
    document.getElementById('motherConfirmationStep').classList.add('hidden');
    document.getElementById('childRecordFormContainer').classList.remove('hidden');
    document.getElementById('motherExists').value = hasExisting ? 'yes' : 'no';

    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    if (step1 && step2) {
        step1.classList.remove('active');
        step2.classList.add('active');
    }

    if (hasExisting) {
        document.getElementById('existingMotherSection').classList.remove('hidden');
        document.getElementById('newMotherSection').classList.add('hidden');

        const motherIdField = document.getElementById('mother_id');
        if (motherIdField) motherIdField.required = true;

        const motherNameField = document.querySelector('[name="mother_name"]');
        const motherAgeField = document.querySelector('[name="mother_age"]');
        const motherContactField = document.querySelector('[name="mother_contact"]');
        const motherAddressField = document.querySelector('[name="mother_address"]');

        if (motherNameField) motherNameField.required = false;
        if (motherAgeField) motherAgeField.required = false;
        if (motherContactField) motherContactField.required = false;
        if (motherAddressField) motherAddressField.required = false;
    } else {
        document.getElementById('newMotherSection').classList.remove('hidden');
        document.getElementById('existingMotherSection').classList.add('hidden');

        const motherIdField = document.getElementById('mother_id');
        if (motherIdField) motherIdField.required = false;

        const motherNameField = document.querySelector('[name="mother_name"]');
        const motherAgeField = document.querySelector('[name="mother_age"]');
        const motherContactField = document.querySelector('[name="mother_contact"]');
        const motherAddressField = document.querySelector('[name="mother_address"]');

        if (motherNameField) motherNameField.required = true;
        if (motherAgeField) motherAgeField.required = true;
        if (motherContactField) motherContactField.required = true;
        if (motherAddressField) motherAddressField.required = true;
    }
}

function changeMotherType() {
    if (!confirm('Are you sure you want to change the mother selection? This will clear the current data.')) {
        return;
    }

    document.getElementById('childRecordFormContainer').classList.add('hidden');
    document.getElementById('motherConfirmationStep').classList.remove('hidden');

    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    if (step1 && step2) {
        step1.classList.add('active');
        step2.classList.remove('active');
    }

    const recordForm = document.getElementById('recordForm');
    recordForm?.reset();

    const motherDetails = document.getElementById('motherDetails');
    motherDetails?.classList.add('hidden');
}

const motherSelect = document.getElementById('mother_id');
motherSelect?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        document.getElementById('motherName').textContent = option.dataset.name || '-';
        document.getElementById('motherAge').textContent = option.dataset.age || '-';
        document.getElementById('motherContact').textContent = option.dataset.contact || '-';
        document.getElementById('motherAddress').textContent = option.dataset.address || '-';
        document.getElementById('motherDetails').classList.remove('hidden');
    } else {
        document.getElementById('motherDetails').classList.add('hidden');
    }
});

const recordForm = document.getElementById('recordForm');
const submitBtn = document.getElementById('submit-btn');

if (recordForm && submitBtn) {
    recordForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const originalBtnText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Child record created successfully!',
                        confirmButtonColor: '#D4A373',
                        confirmButtonText: 'Great!'
                    }).then(() => {
                        window.location.href = '{{ route(auth()->user()->role . ".childrecord.index") }}';
                    });
                } else {
                    let errorMessage = data.message || 'An error occurred while creating the child record.';

                    if (data.errors && Object.keys(data.errors).length > 0) {
                        const errorList = Object.values(data.errors).flat();
                        errorMessage += '\n\n' + errorList.join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#D4A373'
                    });
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An unexpected error occurred. Please try again.',
                    confirmButtonColor: '#D4A373'
                });
            });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const birthdateInput = document.querySelector('input[name="birthdate"]');
    if (birthdateInput) {
        const today = new Date().toISOString().split('T')[0];
        birthdateInput.setAttribute('max', today);
    }
});
</script>
@endpush
@endsection

