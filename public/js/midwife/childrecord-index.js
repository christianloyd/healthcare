/* ============================================================================
   Child Record Index - JavaScript
   Extracted from: resources/views/midwife/childrecord/index.blade.php

   NOTE: Blade Routes (must be handled via config object):
   - {{ route('midwife.childrecord.create') }}
   - {{ route('midwife.childrecord.index') }}
   - {{ route('midwife.childrecord.search') }}
   ============================================================================ */

/* ============================================================================
   SECTION 1: Global State Variables
   ============================================================================ */

// Global variables to store current record for modal operations
let currentRecord = null;
let isExistingMother = false;

// Real-time search state
let searchTimeout = null;
let isSearching = false;

/* ============================================================================
   SECTION 2: Modal Management Functions
   Functions to open, close, and manage modal dialogs
   ============================================================================ */

/**
 * Close Edit Child Modal
 * Prevents closing if click is inside modal content
 * Includes fade out animation and form cleanup
 */
function closeEditChildModal(event) {
    // Prevent closing if click is inside modal content
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('edit-child-modal');
    if (!modal) return;

    // Remove show class to trigger fade out animation
    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        // Clear form if no server validation errors
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('edit-child-form');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }
        }
    }, 300);
}

/**
 * Close Add Record Modal
 * Prevents closing if click is inside modal content
 */
function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('recordModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        // Only reset if no validation errors from server
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('recordForm');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }

            // Reset modal state including mother selection
            resetModalState();
        }
    }, 300);
}

/**
 * Close View Child Record Modal
 */
function closeViewChildModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('viewChildModal');
    const content = document.getElementById('viewChildModalContent');

    if (!modal || !content) return;

    content.classList.remove('translate-y-0', 'opacity-100');
    content.classList.add('-translate-y-10', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

/**
 * Open Add Child Record Modal
 * Handles both simple and mother-selection versions
 */
function openAddModal() {
    const modal = document.getElementById('recordModal');
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');

    if (!modal) {
        console.error('Add modal element not found');
        return;
    }

    // Check if this modal has mother selection functionality
    const hasMotherSelection = motherConfirmationStep && childRecordForm;

    if (hasMotherSelection) {
        // Reset modal state for mother selection version
        resetModalState();

        // Show confirmation step, hide form
        motherConfirmationStep.classList.remove('hidden');
        childRecordForm.classList.add('hidden');
    } else {
        // Simple modal version - reset form directly
        const form = document.getElementById('recordForm');
        if (form) {
            // Set form action dynamically
            const storeUrl = form.dataset.storeUrl || form.action;
            form.action = storeUrl;
            form.reset();

            // Clear validation states
            clearValidationStates(form);
        }

        // Set modal title if available
        const modalTitle = document.getElementById('modalTitle');
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="fas fa-baby text-[#68727A] mr-2"></i>Add Child Record';
        }
    }

    // Show modal with animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input after animation
    setTimeout(() => {
        let firstInput;
        if (hasMotherSelection) {
            // Focus will be handled after mother selection
            return;
        } else {
            firstInput = document.querySelector('#recordForm input[name="first_name"]');
        }

        if (firstInput) firstInput.focus();
    }, 300);
}

/**
 * Open View Record Modal
 * Populates modal with record data and displays formatted information
 */
function openViewRecordModal(record) {
    if (!record) {
        console.error('No child record provided');
        return;
    }

    try {
        // Store current record
        currentRecord = record;

        // Populate modal fields - safely handle null/undefined values
        const fieldMappings = [
            { id: 'modalChildName', value: record.child_name },
            { id: 'modalChildGender', value: record.gender },
            { id: 'modalMotherName', value: record.mother_name },
            { id: 'modalFatherName', value: record.father_name },
            { id: 'modalBirthPlace', value: record.birthplace }
        ];

        fieldMappings.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.textContent = field.value || 'N/A';
            }
        });

        // Format birth date and calculate age
        if (record.birthdate) {
            const birthDate = new Date(record.birthdate);
            const birthdateElement = document.getElementById('modalBirthDate');
            if (birthdateElement) {
                birthdateElement.textContent = birthDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            // Calculate age
            const today = new Date();
            const ageInMonths = (today.getFullYear() - birthDate.getFullYear()) * 12 + (today.getMonth() - birthDate.getMonth());
            const years = Math.floor(ageInMonths / 12);
            const months = ageInMonths % 12;

            let ageString = '';
            if (years > 0) {
                ageString = `${years} year${years > 1 ? 's' : ''}`;
                if (months > 0) {
                    ageString += ` ${months} month${months > 1 ? 's' : ''}`;
                }
            } else {
                ageString = `${months} month${months > 1 ? 's' : ''}`;
            }

            const ageElement = document.getElementById('modalChildAge');
            if (ageElement) {
                ageElement.textContent = ageString;
            }
        } else {
            const birthdateElement = document.getElementById('modalBirthDate');
            const ageElement = document.getElementById('modalChildAge');
            if (birthdateElement) birthdateElement.textContent = 'N/A';
            if (ageElement) ageElement.textContent = 'N/A';
        }

        // Birth details
        const birthWeightElement = document.getElementById('modalBirthWeight');
        if (birthWeightElement) {
            birthWeightElement.textContent = record.birth_weight ? `${record.birth_weight} kg` : 'N/A';
        }

        const birthHeightElement = document.getElementById('modalBirthHeight');
        if (birthHeightElement) {
            birthHeightElement.textContent = record.birth_height ? `${record.birth_height} cm` : 'N/A';
        }

        // Contact information - Format phone number for display
        let displayPhone = record.phone_number || 'N/A';
        if (displayPhone !== 'N/A' && displayPhone.length === 10 && displayPhone.startsWith('9')) {
            displayPhone = `+63${displayPhone}`;
        }
        const phoneElement = document.getElementById('modalPhoneNumber');
        if (phoneElement) {
            phoneElement.textContent = displayPhone;
        }

        const addressElement = document.getElementById('modalAddress');
        if (addressElement) {
            addressElement.textContent = record.address || 'N/A';
        }

        // Created date
        if (record.created_at) {
            const createdDate = new Date(record.created_at);
            const createdDateElement = document.getElementById('modalCreatedDate');
            if (createdDateElement) {
                createdDateElement.textContent = createdDate.toLocaleDateString();
            }
        } else {
            const createdDateElement = document.getElementById('modalCreatedDate');
            if (createdDateElement) {
                createdDateElement.textContent = 'N/A';
            }
        }

        // Show modal with animation
        const modal = document.getElementById('viewChildModal');
        const content = document.getElementById('viewChildModalContent');

        if (!modal || !content) {
            console.error('View modal elements not found');
            return;
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Trigger animation
        requestAnimationFrame(() => {
            content.classList.remove('-translate-y-10', 'opacity-0');
            content.classList.add('translate-y-0', 'opacity-100');
        });

    } catch (error) {
        console.error('Error opening view modal:', error);
    }
}

/**
 * Open Edit Record Modal
 * Populates form with existing record data
 */
function openEditRecordModal(record) {
    if (!record) {
        console.error('No child record provided');
        return;
    }

    const modal = document.getElementById('edit-child-modal');
    if (!modal) {
        console.error('Edit modal element not found');
        return;
    }

    const form = document.getElementById('edit-child-form');
    if (!form) {
        console.error('Edit form not found');
        return;
    }

    // CRITICAL FIX: Update form action with correct ID
    const updateUrl = form.dataset.updateUrl;
    if (updateUrl && record.id) {
        // Replace :id placeholder with actual record ID
        form.action = updateUrl.replace(':id', record.id);
        console.log('Form action set to:', form.action); // Debug log
    } else {
        console.error('Unable to set form action. UpdateUrl:', updateUrl, 'Record ID:', record.id);
        return;
    }

    // Store current record
    currentRecord = record;

    // Format the date to "yyyy-MM-dd" for date inputs
    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    };

    // Populate form fields
    const fieldMappings = [
        { id: 'edit-record-id', value: record.id },
        { id: 'edit-first-name', value: record.first_name },
        { id: 'edit-middle-name', value: record.middle_name },
        { id: 'edit-last-name', value: record.last_name },
        { id: 'edit-birthdate', value: formatDate(record.birthdate) },
        { id: 'edit-birth-height', value: record.birth_height },
        { id: 'edit-birth-weight', value: record.birth_weight },
        { id: 'edit-birthplace', value: record.birthplace },
        { id: 'edit-mother-name', value: record.mother_name },
        { id: 'edit-father-name', value: record.father_name },
        { id: 'edit-address', value: record.address }
    ];

    fieldMappings.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.value = field.value || '';
            element.classList.remove('error-border', 'success-border');
        } else {
            console.warn('Element not found:', field.id);
        }
    });

    // Set gender radio button
    const maleRadio = document.getElementById('edit-gender-male');
    const femaleRadio = document.getElementById('edit-gender-female');
    if (maleRadio && femaleRadio) {
        maleRadio.checked = record.gender === 'Male';
        femaleRadio.checked = record.gender === 'Female';
    }

    // Format phone number for editing (remove +63 prefix if present)
    let phoneValue = record.phone_number || '';
    if (phoneValue.startsWith('+63')) {
        phoneValue = phoneValue.substring(3);
    } else if (phoneValue.startsWith('63')) {
        phoneValue = phoneValue.substring(2);
    } else if (phoneValue.startsWith('0')) {
        phoneValue = phoneValue.substring(1);
    }
    const phoneInput = document.getElementById('edit-phone-number');
    if (phoneInput) {
        phoneInput.value = phoneValue;
    }

    // Clear validation states
    clearValidationStates(form);

    // Show modal with proper animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(() => {
        const nameInput = document.getElementById('edit-first-name');
        if (nameInput) nameInput.focus();
    }, 100);
}

/* ============================================================================
   SECTION 3: Mother Selection and Management
   Functions for handling existing and new mother selection
   ============================================================================ */

/**
 * Reset Modal State
 * Clears form and resets all mother selection sections
 */
function resetModalState() {
    const form = document.getElementById('recordForm');
    if (form) {
        form.reset();
        clearValidationStates(form);
    }

    // Reset mother selection sections if they exist
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherDetails = document.getElementById('motherDetails');

    if (existingMotherSection) existingMotherSection.classList.add('hidden');
    if (newMotherSection) newMotherSection.classList.add('hidden');
    if (motherDetails) motherDetails.classList.add('hidden');

    // Clear mother exists flag
    const motherExistsInput = document.getElementById('motherExists');
    if (motherExistsInput) {
        motherExistsInput.value = '';
    }

    isExistingMother = false;
}

/**
 * Show Mother Form
 * Shows either existing mother selection or new mother form
 */
function showMotherForm(exists) {
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const contactDetailsSection = document.getElementById('contactDetailsSection');
    const motherAddressField = document.getElementById('motherAddressField');
    const motherExistsInput = document.getElementById('motherExists');

    if (!motherConfirmationStep || !childRecordForm) {
        console.error('Mother selection elements not found');
        return;
    }

    // Store the choice
    isExistingMother = exists;
    if (motherExistsInput) {
        motherExistsInput.value = exists ? 'yes' : 'no';
    }

    // Hide confirmation step, show form
    motherConfirmationStep.classList.add('hidden');
    childRecordForm.classList.remove('hidden');

    // Handle Contact Details section visibility
    if (contactDetailsSection) {
        contactDetailsSection.style.display = 'block';
    }

    // Handle Mother Address field visibility (in Birth Details section)
    if (motherAddressField) {
        if (exists) {
            // Hide mother address field for existing mother
            motherAddressField.classList.add('hidden');
        } else {
            // Show mother address field for new mother
            motherAddressField.classList.remove('hidden');
        }
    }

    // Show appropriate section
    if (exists) {
        if (existingMotherSection) existingMotherSection.classList.remove('hidden');
        if (newMotherSection) newMotherSection.classList.add('hidden');
        updateRequiredFields(true);

        // Clear new mother fields
        clearNewMotherFields();
    } else {
        if (existingMotherSection) existingMotherSection.classList.add('hidden');
        if (newMotherSection) newMotherSection.classList.remove('hidden');
        updateRequiredFields(false);

        // Clear existing mother selection
        clearExistingMotherSelection();
    }

    // Focus first input
    setTimeout(() => {
        const firstInput = document.querySelector('#recordForm input[name="child_name"]');
        if (firstInput) firstInput.focus();
    }, 100);
}

/**
 * Change Mother Type
 * Shows confirmation step again to change mother selection
 */
function changeMotherType() {
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');

    if (!motherConfirmationStep || !childRecordForm) return;

    // Show confirmation step again
    childRecordForm.classList.add('hidden');
    motherConfirmationStep.classList.remove('hidden');

    // Reset sections
    resetMotherSections();
}

/**
 * Go Back to Confirmation
 * Returns to mother selection confirmation step
 */
function goBackToConfirmation() {
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');

    if (!motherConfirmationStep || !childRecordForm) return;

    childRecordForm.classList.add('hidden');
    motherConfirmationStep.classList.remove('hidden');

    // Reset sections
    resetMotherSections();
}

/**
 * Reset Mother Sections
 * Clears all mother-related sections
 */
function resetMotherSections() {
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherDetails = document.getElementById('motherDetails');

    if (existingMotherSection) existingMotherSection.classList.add('hidden');
    if (newMotherSection) newMotherSection.classList.add('hidden');
    if (motherDetails) motherDetails.classList.add('hidden');

    clearExistingMotherSelection();
    clearNewMotherFields();
}

/**
 * Setup Mother Selection
 * Initializes mother selection dropdown with change event handler
 */
function setupMotherSelection() {
    const motherSelect = document.getElementById('mother_id');
    if (!motherSelect) return;

    motherSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const motherDetails = document.getElementById('motherDetails');

        if (!motherDetails) return;

        if (this.value && selectedOption.dataset.name) {
            // Show mother details
            const motherName = document.getElementById('motherName');
            const motherAge = document.getElementById('motherAge');
            const motherContact = document.getElementById('motherContact');
            const motherAddress = document.getElementById('motherAddress');

            if (motherName) motherName.textContent = selectedOption.dataset.name || '-';
            if (motherAge) motherAge.textContent = selectedOption.dataset.age || '-';
            if (motherContact) motherContact.textContent = selectedOption.dataset.contact || '-';
            if (motherAddress) motherAddress.textContent = selectedOption.dataset.address || '-';

            motherDetails.classList.remove('hidden');

            // Auto-fill contact details from mother info
            const phoneInput = document.getElementById('phone_number');
            const addressInput = document.getElementById('address');

            if (phoneInput && selectedOption.dataset.contact) {
                let contact = selectedOption.dataset.contact;
                // Format for phone input (convert +63 to 09 format)
                if (contact.startsWith('+639')) {
                    contact = '0' + contact.substring(3);
                } else if (contact.startsWith('639')) {
                    contact = '0' + contact.substring(2);
                } else if (contact.startsWith('+63')) {
                    contact = '0' + contact.substring(3);
                } else if (contact.startsWith('63')) {
                    contact = '0' + contact.substring(2);
                }
                // Keep 09 format as is, don't remove leading zero
                phoneInput.value = contact;
                phoneInput.readOnly = true;
                phoneInput.classList.add('bg-gray-100');
            }

            if (addressInput && selectedOption.dataset.address) {
                addressInput.value = selectedOption.dataset.address;
                addressInput.readOnly = true;
                addressInput.classList.add('bg-gray-100');
            }

        } else {
            motherDetails.classList.add('hidden');
            // Clear and enable contact inputs
            const phoneInput = document.getElementById('phone_number');
            const addressInput = document.getElementById('address');

            if (phoneInput) {
                phoneInput.value = '';
                phoneInput.readOnly = false;
                phoneInput.classList.remove('bg-gray-100');
            }

            if (addressInput) {
                addressInput.value = '';
                addressInput.readOnly = false;
                addressInput.classList.remove('bg-gray-100');
            }
        }
    });
}

/**
 * Update Required Fields
 * Sets required attributes on mother fields based on selection type
 */
function updateRequiredFields(isExisting) {
    const motherIdSelect = document.getElementById('mother_id');
    const motherNameInput = document.getElementById('mother_name');
    const motherAgeInput = document.getElementById('mother_age');
    const motherContactInput = document.getElementById('mother_contact');
    const motherAddressInput = document.getElementById('mother_address');

    if (isExisting) {
        // Existing mother - require selection
        if (motherIdSelect) motherIdSelect.setAttribute('required', 'required');
        if (motherNameInput) motherNameInput.removeAttribute('required');
        if (motherAgeInput) motherAgeInput.removeAttribute('required');
        if (motherContactInput) motherContactInput.removeAttribute('required');
        if (motherAddressInput) motherAddressInput.removeAttribute('required');
    } else {
        // New mother - require manual inputs
        if (motherIdSelect) motherIdSelect.removeAttribute('required');
        if (motherNameInput) motherNameInput.setAttribute('required', 'required');
        if (motherAgeInput) motherAgeInput.setAttribute('required', 'required');
        if (motherContactInput) motherContactInput.setAttribute('required', 'required');
        if (motherAddressInput) motherAddressInput.setAttribute('required', 'required');
    }
}

/**
 * Clear Existing Mother Selection
 * Resets mother dropdown and triggers change event
 */
function clearExistingMotherSelection() {
    const motherSelect = document.getElementById('mother_id');
    if (motherSelect) {
        motherSelect.value = '';
        motherSelect.dispatchEvent(new Event('change'));
    }
}

/**
 * Clear New Mother Fields
 * Clears all input fields for new mother entry
 */
function clearNewMotherFields() {
    const fields = ['mother_name', 'mother_age', 'mother_contact', 'mother_address'];
    fields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.value = '';
            field.classList.remove('error-border', 'success-border');
        }
    });
}

/* ============================================================================
   SECTION 4: Form Validation Functions
   Field validation and error handling
   ============================================================================ */

/**
 * Format Phone Number
 * Validates and formats phone numbers to standard formats
 * Supports: +639xxxxxxxxx, 09xxxxxxxxx, 9xxxxxxxxx
 */
function formatPhoneNumber(input) {
    // Skip formatting if field is readonly (pre-filled from mother data)
    if (input.readOnly) {
        return true;
    }

    // Get the original value without changing it first
    let originalValue = input.value;

    // Remove all non-digits and special characters
    let digitsOnly = originalValue.replace(/\D/g, '');

    // Handle different input formats and validate
    let isValid = false;
    let formattedValue = originalValue;

    if (digitsOnly.startsWith('63') && digitsOnly.length === 12) {
        // 639xxxxxxxxx format - convert to +639xxxxxxxxx
        formattedValue = '+' + digitsOnly;
        isValid = /^\+639\d{9}$/.test(formattedValue);
    } else if (digitsOnly.startsWith('09') && digitsOnly.length === 11) {
        // 09xxxxxxxxx format - keep as is
        formattedValue = digitsOnly;
        isValid = /^09\d{9}$/.test(formattedValue);
    } else if (digitsOnly.startsWith('9') && digitsOnly.length === 10) {
        // 9xxxxxxxxx format - convert to 09xxxxxxxxx
        formattedValue = '0' + digitsOnly;
        isValid = /^09\d{9}$/.test(formattedValue);
    } else if (originalValue.startsWith('+63') && /^\+639\d{9}$/.test(originalValue)) {
        // Already in +639xxxxxxxxx format
        formattedValue = originalValue;
        isValid = true;
    } else if (digitsOnly.length === 0) {
        // Empty field
        formattedValue = '';
        isValid = false;
    } else {
        // Invalid format - keep original value but mark as invalid
        formattedValue = originalValue;
        isValid = false;
    }

    // Only update the input value if it changed
    if (formattedValue !== originalValue) {
        input.value = formattedValue;
    }

    // Apply validation styling
    input.classList.toggle('error-border', !isValid && input.value.length > 0);
    input.classList.toggle('success-border', isValid);

    return isValid;
}

/**
 * Validate Field
 * Validates individual form fields based on their type and requirements
 */
function validateField() {
    const field = this;
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    let isValid = true;

    // Clear previous validation styles
    field.classList.remove('error-border', 'success-border');

    if (isRequired && !value) {
        isValid = false;
    } else if (value) {
        // Field-specific validation
        switch (field.name) {
            case 'child_name':
            case 'mother_name':
                if (value.length < 2) {
                    isValid = false;
                }
                break;
            case 'phone_number':
            case 'mother_contact':
                isValid = formatPhoneNumber(field);
                break;
            case 'birthdate':
                const birthDate = new Date(value);
                const today = new Date();
                if (birthDate > today) {
                    isValid = false;
                }
                break;
            case 'birth_height':
                const height = parseFloat(value);
                if (value && (isNaN(height) || height < 0 || height > 999.99)) {
                    isValid = false;
                }
                break;
            case 'birth_weight':
                const weight = parseFloat(value);
                if (value && (isNaN(weight) || weight < 0 || weight > 99.999)) {
                    isValid = false;
                }
                break;
            case 'mother_age':
                const age = parseInt(value);
                if (value && (isNaN(age) || age < 15 || age > 50)) {
                    isValid = false;
                }
                break;
        }
    }

    // Apply validation styling
    if (!isValid) {
        field.classList.add('error-border');
    } else if (value) {
        field.classList.add('success-border');
    }

    return isValid;
}

/**
 * Clear Validation States
 * Removes all validation styling from form elements
 */
function clearValidationStates(form) {
    if (!form) return;

    form.querySelectorAll('.error-border, .success-border').forEach(input => {
        input.classList.remove('error-border', 'success-border');
    });
}

/**
 * Set Date Constraints
 * Sets min/max dates for date inputs and validates on change
 */
function setDateConstraints() {
    const birthdateInputs = ['birthdate', 'edit-birthdate'];

    birthdateInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            // Get today's date in YYYY-MM-DD format
            const today = new Date();
            const todayString = today.toISOString().split('T')[0];

            // Set maximum date to today (prevents future dates)
            input.setAttribute('max', todayString);

            // Set reasonable minimum date (100 years ago for maximum flexibility)
            const minDate = new Date();
            minDate.setFullYear(minDate.getFullYear() - 100);
            const minDateString = minDate.toISOString().split('T')[0];
            input.setAttribute('min', minDateString);

            // Add event listener to validate on change
            input.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const currentDate = new Date();

                if (selectedDate > currentDate) {
                    this.setCustomValidity('Birth date cannot be in the future');
                    this.classList.add('error-border');
                    this.classList.remove('success-border');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('error-border');
                    if (this.value) {
                        this.classList.add('success-border');
                    }
                }
            });

            // Also validate on input (for manual typing)
            input.addEventListener('input', function() {
                if (this.value) {
                    const selectedDate = new Date(this.value);
                    const currentDate = new Date();

                    if (selectedDate > currentDate) {
                        this.setCustomValidity('Birth date cannot be in the future');
                        this.classList.add('error-border');
                        this.classList.remove('success-border');
                    } else {
                        this.setCustomValidity('');
                        this.classList.remove('error-border');
                        this.classList.add('success-border');
                    }
                }
            });
        }
    });
}

/* ============================================================================
   SECTION 5: Form Handling Setup
   Initialize form validation and event listeners
   ============================================================================ */

/**
 * Setup Form Handling
 * Adds blur and input event listeners for real-time validation
 */
function setupFormHandling() {
    // Add validation to all forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', function() {
                if (this.classList.contains('error-border')) {
                    validateField.call(this);
                }
            });
        });
    });
}

/**
 * Check and Reopen Modal on Errors
 * Automatically reopens modals if validation errors are present
 */
function checkAndReopenModalOnErrors() {
    // Check if there are validation errors in the add form (NOT success messages)
    const addFormErrors = document.querySelector('#recordModal .bg-red-100, #recordModal .text-red-700');
    const hasSuccessMessage = document.querySelector('.healthcare-alert-success');

    // Only reopen modal if there are errors AND no success message
    if (addFormErrors && !hasSuccessMessage) {
        // Reopen the add modal
        const modal = document.getElementById('recordModal');
        const childRecordForm = document.getElementById('childRecordForm');
        const motherConfirmationStep = document.getElementById('motherConfirmationStep');

        if (modal && childRecordForm) {
            modal.classList.remove('hidden');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            // Skip confirmation step and show form directly
            if (motherConfirmationStep) motherConfirmationStep.classList.add('hidden');
            childRecordForm.classList.remove('hidden');

            // Determine if it was existing mother or new mother based on form data
            const motherExistsValue = document.getElementById('motherExists')?.value;
            if (motherExistsValue === 'yes') {
                showMotherForm(true);
            } else if (motherExistsValue === 'no') {
                showMotherForm(false);
            } else {
                // Check old form values to determine which form was shown
                const motherNameValue = document.querySelector('input[name="mother_name"]')?.value;
                const motherIdValue = document.querySelector('select[name="mother_id"]')?.value;

                if (motherIdValue) {
                    showMotherForm(true);
                } else if (motherNameValue) {
                    showMotherForm(false);
                } else {
                    // Default to showing confirmation step if unclear
                    motherConfirmationStep.classList.remove('hidden');
                    childRecordForm.classList.add('hidden');
                }
            }
        }
    }

    // Check if there are validation errors in the edit form
    const editFormErrors = document.querySelector('#edit-child-modal .bg-red-100, #edit-child-modal .text-red-700');
    if (editFormErrors && !hasSuccessMessage) {
        // Reopen the edit modal
        const editModal = document.getElementById('edit-child-modal');
        if (editModal) {
            editModal.classList.remove('hidden');
            editModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    // If there's a success message, ensure all modals are closed
    if (hasSuccessMessage) {
        const allModals = document.querySelectorAll('.modal-overlay');
        allModals.forEach(modal => {
            modal.classList.remove('show');
            modal.classList.add('hidden');
        });
        document.body.style.overflow = '';
    }
}

/* ============================================================================
   SECTION 6: Real-Time Search Functionality
   ============================================================================ */

/**
 * Setup Real-Time Search
 * Initializes search input with debouncing and AJAX requests
 *
 * NOTE: Update this with the actual route URL:
 * - Replace {{ route('midwife.childrecord.search') }} with your configured route
 */
function setupRealTimeSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    const genderSelect = document.querySelector('select[name="gender"]');
    const searchForm = document.querySelector('.search-form');

    if (!searchInput || !searchForm) return;

    // Debounced search function
    function performSearch() {
        if (isSearching) return;

        isSearching = true;
        const formData = new FormData(searchForm);

        // Show loading indicator
        const loadingEl = document.getElementById('search-loading');
        const tableContent = document.getElementById('table-content');
        const paginationContent = document.getElementById('pagination-content');

        if (loadingEl && tableContent) {
            loadingEl.classList.remove('hidden');
            tableContent.style.opacity = '0.5';
        }

        // Get search parameters
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            if (value.trim()) {
                params.append(key, value);
            }
        }

        // Make AJAX request
        // Dynamic search route resolution from window config
        const searchUrl = (window.CHILDRECORD_CONFIG && window.CHILDRECORD_CONFIG.searchRoute) 
            || document.body.dataset.searchRoute 
            || (window.location.pathname.includes('/bhw') ? '/bhw/childrecord-search' : '/midwife/childrecord-search');

        fetch(`${searchUrl}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update table content
                if (tableContent) {
                    tableContent.innerHTML = data.html;
                    tableContent.style.opacity = '1';
                }

                // Update pagination
                if (paginationContent) {
                    paginationContent.innerHTML = data.pagination || '';
                }
            } else {
                console.error('Search failed:', data.error);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        })
        .finally(() => {
            // Hide loading indicator
            if (loadingEl) {
                loadingEl.classList.add('hidden');
            }
            if (tableContent) {
                tableContent.style.opacity = '1';
            }
            isSearching = false;
        });
    }

    // Debounced input handler
    function handleSearchInput() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500); // 500ms delay
    }

    // Search on input (real-time)
    searchInput.addEventListener('input', handleSearchInput);

    // Search on Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            performSearch();
        }
    });

    // Search when gender filter changes
    if (genderSelect) {
        genderSelect.addEventListener('change', function() {
            clearTimeout(searchTimeout);
            performSearch();
        });
    }

    // Prevent normal form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearTimeout(searchTimeout);
        performSearch();
    });
}

/* ============================================================================
   SECTION 7: Form Submission with AJAX
   Handle add child record form submission
   ============================================================================ */

/**
 * Setup Add Child Form AJAX
 * Handles form submission with AJAX and shows SweetAlert notifications
 */
function setupAddChildFormAjax() {
    const form = document.getElementById('recordForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent traditional form submission

        const submitBtn = document.getElementById('submit-btn');
        const originalBtnText = submitBtn.innerHTML;

        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

        // Prepare form data
        const formData = new FormData(form);

        // Send AJAX request
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
                // Close modal first
                closeModal();

                // Show success SweetAlert after modal closes
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Child record created successfully!',
                        confirmButtonColor: '#D4A373',
                        confirmButtonText: 'Great!'
                    }).then(() => {
                        // Reload page to show new record
                        window.location.reload();
                    });
                }, 400);
            } else {
                // Show error SweetAlert
                let errorMessage = data.message || 'An error occurred while creating the child record.';

                // If there are validation errors, show them
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

/* ============================================================================
   SECTION 8: Initialization
   Run when DOM is loaded
   ============================================================================ */

/**
 * Initialize all functionality when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Make closeEditChildModal globally available
    window.closeEditChildModal = closeEditChildModal;

    // Setup form handling
    setupFormHandling();

    // Set date constraints
    setDateConstraints();

    // Setup mother selection if available
    setupMotherSelection();

    // Setup real-time search
    setupRealTimeSearch();

    // Setup AJAX form submission for add child modal
    setupAddChildFormAjax();

    // Check for validation errors and reopen modal if needed
    checkAndReopenModalOnErrors();

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
            closeEditChildModal();
            closeViewChildModal();
        }
    });

    // Auto-hide alerts after 2 seconds
    const alerts = document.querySelectorAll('.bg-green-100[role="alert"], .bg-red-100[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 2000);
    });
});
