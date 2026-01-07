/* ============================================
   IMMUNIZATION INDEX JAVASCRIPT
   ============================================
   Extracted from: resources/views/midwife/immunization/index.blade.php
   Last Updated: 2025-11-09

   NOTE: Blade Route Directives
   This code requires the following Laravel routes to be made available:
   - {{ route('midwife.immunization.index') }} - Main index route
   See CONFIG section below for route handling.
   ============================================ */


/* ============================================
   CONFIG - Route Management
   ============================================
   Replace these with actual routes from your server.
   These Blade directives were originally in the template:
   - route('midwife.immunization.index')

   Implementation option 1: Pass routes via data attributes on page load
   Implementation option 2: Define a config object with all routes
   Implementation option 3: Inject routes via script tag
   */

const immunizationConfig = {
    routes: {
        indexRoute: null, // Set this via data attribute or from server
    }
};


/* ============================================
   1. MODAL MANAGEMENT - ADD IMMUNIZATION
   ============================================ */

/**
 * Opens the Add Immunization modal
 * Resets form and prepares UI for new entry
 */
function openAddModal() {
    const modal = document.getElementById('immunizationModal');
    const form = document.getElementById('immunizationForm');

    if (!modal || !form) {
        console.error('Add modal elements not found');
        return;
    }

    // Reset form
    form.reset();
    clearValidationStates(form);

    // Show modal with animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(() => {
        const firstInput = form.querySelector('select[name="child_record_id"]');
        if (firstInput) firstInput.focus();
    }, 300);
}

/**
 * Closes the Add Immunization modal
 * @param {Event} event - Click event from modal overlay
 */
function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('immunizationModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        // Reset form only if no validation errors
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('immunizationForm');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }
        }
    }, 300);
}


/* ============================================
   2. MODAL MANAGEMENT - VIEW IMMUNIZATION
   ============================================ */

/**
 * Opens the View Immunization modal
 * Displays read-only immunization details
 * @param {Object} immunization - Immunization data object
 */
function openViewModal(immunization) {
    if (!immunization) {
        console.error('No immunization record provided');
        return;
    }

    try {
        // Populate patient information
        updateElementText('modalChildName', immunization.child_record?.full_name);

        // Calculate and display age
        if (immunization.child_record?.birthdate) {
            const birthDate = new Date(immunization.child_record.birthdate);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            // Format age appropriately
            if (age < 1) {
                const months = monthDiff < 0 ? 12 + monthDiff : monthDiff;
                updateElementText('modalChildAge', `${months} month${months !== 1 ? 's' : ''}`);
            } else {
                updateElementText('modalChildAge', `${age} year${age !== 1 ? 's' : ''}`);
            }
        } else {
            updateElementText('modalChildAge', 'N/A');
        }

        // Display gender
        updateElementText('modalChildGender', immunization.child_record?.gender || 'N/A');

        // Display mother's name
        updateElementText('modalMotherName', immunization.child_record?.mother_name || 'N/A');

        // Populate vaccine information
        updateElementText('modalVaccineName', immunization.vaccine_name);
        updateElementText('modalDose', immunization.dose);
        updateElementText('modalNotes', immunization.notes);

        // Update status with badge styling
        const statusElement = document.getElementById('modalStatus');
        const statusTextElement = document.getElementById('modalStatusText');
        const statusIconElement = document.getElementById('modalStatusIcon');

        if (statusElement && statusTextElement && statusIconElement) {
            const status = immunization.status;
            statusTextElement.textContent = status;

            // Remove all status classes
            statusElement.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold';

            // Apply status-specific styling
            if (status === 'Done') {
                statusElement.classList.add('bg-green-100', 'text-green-800');
                statusIconElement.className = 'fas fa-check-circle mr-1';
            } else if (status === 'Upcoming') {
                statusElement.classList.add('bg-blue-100', 'text-blue-800');
                statusIconElement.className = 'fas fa-clock mr-1';
            } else if (status === 'Missed') {
                statusElement.classList.add('bg-red-100', 'text-red-800');
                statusIconElement.className = 'fas fa-times-circle mr-1';
            } else {
                statusElement.classList.add('bg-gray-100', 'text-gray-800');
                statusIconElement.className = 'fas fa-question-circle mr-1';
            }
        }

        // Format and display schedule date
        if (immunization.schedule_date) {
            const scheduleDate = new Date(immunization.schedule_date);
            updateElementText('modalScheduleDate', scheduleDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));
        } else {
            updateElementText('modalScheduleDate', 'N/A');
        }

        // Display schedule time
        updateElementText('modalScheduleTime', immunization.schedule_time || 'N/A');

        // Show modal with animation
        const modal = document.getElementById('viewImmunizationModal');
        const content = document.getElementById('viewImmunizationModalContent');

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
 * Closes the View Immunization modal
 * @param {Event} event - Click event from modal overlay
 */
function closeViewModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('viewImmunizationModal');
    const content = document.getElementById('viewImmunizationModalContent');

    if (!modal || !content) return;

    content.classList.remove('translate-y-0', 'opacity-100');
    content.classList.add('-translate-y-10', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}


/* ============================================
   3. MODAL MANAGEMENT - EDIT IMMUNIZATION
   ============================================ */

/**
 * Opens the Edit Immunization modal
 * Populates form with existing immunization data
 * @param {Object} immunization - Immunization data object
 */
function openEditModal(immunization) {
    console.log('Opening edit modal for immunization:', immunization);

    if (!immunization) {
        console.error('No immunization record provided');
        showError('Error: No immunization data provided');
        return;
    }

    const modal = document.getElementById('editImmunizationModal');
    const form = document.getElementById('editImmunizationForm');

    if (!modal || !form) {
        console.error('Edit modal elements not found');
        showError('Error: Modal elements not found');
        return;
    }

    try {
        // Set form action - get user role from a data attribute or PHP variable
        const userRole = document.body.getAttribute('data-user-role') || 'midwife';
        const routeName = userRole === 'bhw' ? 'immunizations' : 'immunization';
        form.action = `/${userRole}/${routeName}/${immunization.id}`;
        console.log('Form action set to:', form.action);

        // Populate form fields
        populateEditForm(immunization);

        // Clear validation states
        clearValidationStates(form);

        // Show modal
        modal.classList.remove('hidden');
        requestAnimationFrame(() => modal.classList.add('show'));
        document.body.style.overflow = 'hidden';

        // Focus first input (but only if not readonly)
        setTimeout(() => {
            const firstInput = document.getElementById('editChildRecordId');
            if (firstInput && !firstInput.disabled) {
                firstInput.focus();
            }
        }, 300);

    } catch (error) {
        console.error('Error opening edit modal:', error);
        showError('Error opening edit modal. Please try again.');
    }
}

/**
 * Closes the Edit Immunization modal
 * @param {Event} event - Click event from modal overlay
 */
function closeEditModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('editImmunizationModal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        const form = document.getElementById('editImmunizationForm');
        if (form) {
            form.reset();
            clearValidationStates(form);
        }
    }, 300);
}


/* ============================================
   4. MODAL MANAGEMENT - MARK AS DONE
   ============================================ */

/**
 * Opens the Mark as Done modal
 * Confirms completion of immunization
 * @param {Object} immunizationData - Immunization data object
 */
function openMarkDoneModal(immunizationData) {
    console.log('Opening mark done modal:', immunizationData);

    // Set immunization details in modal
    const childNameElement = document.getElementById('done-child-name');
    const vaccineNameElement = document.getElementById('done-vaccine-name');
    const doseElement = document.getElementById('done-dose');

    if (childNameElement) {
        childNameElement.textContent = immunizationData.child_record?.full_name || 'N/A';
    }
    if (vaccineNameElement) {
        vaccineNameElement.textContent = immunizationData.vaccine_name || immunizationData.vaccine?.name || 'N/A';
    }
    if (doseElement) {
        doseElement.textContent = immunizationData.dose || 'N/A';
    }

    // Set form action
    const form = document.getElementById('markDoneForm');
    if (form) {
        const userRole = document.body.getAttribute('data-user-role') || 'midwife';
        const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';
        form.action = `/${userRole}/${endpoint}/${immunizationData.id}/complete`;
        console.log('Form action set to:', form.action);
    }

    // Show modal with proper flexbox centering
    const modal = document.getElementById('markDoneModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        console.log('Modal should be visible now');
    } else {
        console.error('markDoneModal not found');
    }
}

/**
 * Closes the Mark as Done modal
 */
function closeMarkDoneModal() {
    const modal = document.getElementById('markDoneModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}


/* ============================================
   5. MODAL MANAGEMENT - MARK AS MISSED
   ============================================ */

/**
 * Opens the Mark as Missed modal
 * Marks immunization as missed with optional rescheduling
 * @param {Object} immunization - Immunization data object
 */
function openMarkMissedModal(immunization) {
    if (!immunization) {
        console.error('No immunization data provided');
        return;
    }

    // Populate immunization details
    document.getElementById('missed-immunization-id').value = immunization.id;
    document.getElementById('missed-child-name').textContent = immunization.child_record?.full_name || 'Unknown';
    document.getElementById('missed-vaccine-name').textContent = immunization.vaccine?.name || immunization.vaccine_name || 'Unknown';
    document.getElementById('missed-dose').textContent = immunization.dose || 'N/A';
    document.getElementById('missed-schedule-date').textContent = new Date(immunization.schedule_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Set form action
    const userRole = document.body.getAttribute('data-user-role') || 'midwife';
    const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';
    document.getElementById('markMissedForm').action = `/${userRole}/${endpoint}/${immunization.id}/mark-missed`;

    // Reset form
    document.getElementById('markMissedForm').reset();
    document.getElementById('missed-immunization-id').value = immunization.id;
    document.getElementById('missed-confirm-checkbox').checked = false;
    document.getElementById('missed-reschedule-checkbox').checked = false;
    document.getElementById('reschedule-fields').classList.add('hidden');

    // Show modal
    const modal = document.getElementById('markMissedModal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(() => {
        document.getElementById('missed-reason').focus();
    }, 300);
}

/**
 * Closes the Mark as Missed modal
 * @param {Event} event - Click event from modal overlay
 */
function closeMarkMissedModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('markMissedModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        document.getElementById('markMissedForm').reset();
    }, 300);
}


/* ============================================
   6. MODAL MANAGEMENT - RESCHEDULE IMMUNIZATION
   ============================================ */

/**
 * Opens the Reschedule modal
 * Allows rescheduling of missed immunizations
 * @param {Object} immunization - Immunization data object
 */
function openImmunizationRescheduleModal(immunization) {
    console.log('Opening reschedule modal with data:', immunization);

    if (!immunization) {
        console.error('No immunization data provided');
        return;
    }

    // Populate immunization details
    const childNameEl = document.getElementById('reschedule-child-name');
    const vaccineNameEl = document.getElementById('reschedule-vaccine-name');
    const doseEl = document.getElementById('reschedule-dose');
    const originalDateEl = document.getElementById('reschedule-original-date');

    if (childNameEl) childNameEl.textContent = immunization.child_record?.full_name || 'Unknown';

    let vaccineName = 'Unknown';
    if (immunization.vaccine && immunization.vaccine.name) {
        vaccineName = immunization.vaccine.name;
    } else if (immunization.vaccine_name) {
        vaccineName = immunization.vaccine_name;
    }
    if (vaccineNameEl) vaccineNameEl.textContent = vaccineName;

    if (doseEl) doseEl.textContent = immunization.dose || 'N/A';

    if (originalDateEl) {
        const scheduleDate = new Date(immunization.schedule_date);
        originalDateEl.textContent = scheduleDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Reset form
    const form = document.getElementById('rescheduleForm');
    if (form) form.reset();

    // Set current reschedule immunization for form submission
    window.currentRescheduleImmunization = immunization;

    // Show modal
    const modal = document.getElementById('rescheduleModal');
    if (modal) {
        modal.classList.remove('hidden');
        requestAnimationFrame(() => modal.classList.add('show'));
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            const dateInput = document.getElementById('reschedule-date');
            if (dateInput) dateInput.focus();
        }, 300);
    }
}

/**
 * Closes the Reschedule modal
 * @param {Event} event - Click event from modal overlay
 */
function closeImmunizationRescheduleModal(event) {
    if (event && event.target !== event.currentTarget && arguments.length > 0) return;

    const modal = document.getElementById('rescheduleModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        window.currentRescheduleImmunization = null;

        const form = document.getElementById('rescheduleForm');
        if (form) form.reset();
    }, 300);
}


/* ============================================
   7. UTILITY FUNCTIONS - DOM & TEXT
   ============================================ */

/**
 * Updates text content of an element safely
 * @param {string} elementId - ID of element to update
 * @param {string} value - New text content
 */
function updateElementText(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value || 'N/A';
    }
}

/**
 * Populates the edit form with immunization data
 * Handles vaccine ID lookup and date/time formatting
 * @param {Object} immunization - Immunization data object
 */
function populateEditForm(immunization) {
    console.log('Populating edit form with:', immunization);

    let vaccineId = immunization.vaccine_id;
    if (!vaccineId && immunization.vaccine_name) {
        const vaccineSelect = document.getElementById('editVaccineId');
        if (vaccineSelect) {
            for (let option of vaccineSelect.options) {
                if (option.textContent.includes(immunization.vaccine_name)) {
                    vaccineId = option.value;
                    console.log(`Found vaccine ID ${vaccineId} for vaccine name "${immunization.vaccine_name}"`);
                    break;
                }
            }
        }
    }

    const fieldMappings = [
        { id: 'editImmunizationId', value: immunization.id },
        { id: 'editChildRecordId', value: immunization.child_record_id },
        { id: 'editVaccineId', value: vaccineId },
        { id: 'editDose', value: immunization.dose },
        { id: 'editScheduleDate', value: formatDateForInput(immunization.schedule_date) },
        { id: 'editScheduleTime', value: formatTimeForInput(immunization.schedule_time) },
        { id: 'editStatus', value: immunization.status },
        { id: 'editNotes', value: immunization.notes }
    ];

    fieldMappings.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.value = field.value || '';
            element.classList.remove('error-border', 'success-border');
            console.log(`Set ${field.id} to: ${field.value}`);
        } else {
            console.warn(`Element not found: ${field.id}`);
        }
    });

    setTimeout(() => {
        if (typeof updateEditVaccineInfo === 'function') {
            updateEditVaccineInfo();
        }
    }, 50);

    setTimeout(() => {
        if (typeof toggleFieldsBasedOnStatus === 'function') {
            toggleFieldsBasedOnStatus();
        }
    }, 100);
}


/* ============================================
   8. UTILITY FUNCTIONS - FORMAT & VALIDATE
   ============================================ */

/**
 * Formats date string for HTML date input
 * Converts to YYYY-MM-DD format
 * @param {string} dateString - Date string to format
 * @returns {string} - Formatted date (YYYY-MM-DD)
 */
function formatDateForInput(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

/**
 * Formats time string for HTML time input
 * Converts to HH:MM format
 * @param {string} timeString - Time string to format
 * @returns {string} - Formatted time (HH:MM)
 */
function formatTimeForInput(timeString) {
    if (!timeString) return '';
    if (timeString.includes(':')) {
        return timeString.substring(0, 5);
    }
    return timeString;
}

/**
 * Clears validation states from form elements
 * Removes error-border and success-border classes
 * @param {HTMLFormElement} form - Form element to clear
 */
function clearValidationStates(form) {
    if (!form) return;

    form.querySelectorAll('.error-border, .success-border').forEach(input => {
        input.classList.remove('error-border', 'success-border');
    });
}

/**
 * Validates a form field
 * Checks required fields, date constraints, and time format
 * @param {HTMLInputElement} field - Form field to validate
 * @returns {boolean} - True if field is valid
 */
function validateField(field) {
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    let isValid = true;

    field.classList.remove('error-border', 'success-border');

    if (isRequired && !value) {
        isValid = false;
    } else if (value) {
        switch (field.name) {
            case 'schedule_date':
                const scheduleDate = new Date(value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (scheduleDate < today) {
                    isValid = false;
                }
                break;
            case 'schedule_time':
                const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
                if (!timeRegex.test(value)) {
                    isValid = false;
                }
                break;
        }
    }

    if (!isValid) {
        field.classList.add('error-border');
    } else if (value) {
        field.classList.add('success-border');
    }

    return isValid;
}


/* ============================================
   9. FORM HANDLING - VALIDATION & SUBMISSION
   ============================================ */

/**
 * Sets up form validation and submission handling with AJAX + SweetAlert
 * @param {HTMLFormElement} form - Form element to setup
 * @param {HTMLButtonElement} submitBtn - Submit button element
 * @param {string} loadingText - Text to show during submission
 */
function setupFormHandling(form, submitBtn, loadingText) {
    if (!form || !submitBtn) return;

    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function () {
            validateField(this);
        });
        input.addEventListener('input', function () {
            this.classList.remove('error-border');
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        const originalText = submitBtn.innerHTML;
        const formData = new FormData(this);
        const formAction = this.action;

        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${loadingText}
        `;

        // Send AJAX request
        fetch(formAction, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(result => {
                // Restore button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (result.status === 200 && result.body.success) {
                    // Close modal ONLY on success
                    closeModal();

                    // Show success SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.body.message || 'Immunization schedule created successfully!',
                        confirmButtonColor: '#68727A',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload page to show new data
                        window.location.reload();
                    });
                } else {
                    // DON'T close modal - keep it open for corrections
                    // Format validation errors
                    let errorMessage = '';
                    if (result.body.errors) {
                        // Laravel validation errors format
                        errorMessage = '<div class="text-left"><strong>Please correct the following errors:</strong><ul class="mt-2 ml-4 list-disc">';
                        Object.keys(result.body.errors).forEach(key => {
                            result.body.errors[key].forEach(error => {
                                errorMessage += `<li class="mb-1">${error}</li>`;
                            });
                        });
                        errorMessage += '</ul></div>';
                    } else {
                        errorMessage = result.body.message || 'Please correct the errors and try again.';
                    }

                    // Show validation error with SweetAlert (modal stays open in background)
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMessage,
                        confirmButtonColor: '#68727A',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Restore button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                // Show error SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    confirmButtonColor: '#68727A',
                    confirmButtonText: 'OK'
                });
            });
    });
}


/* ============================================
   10. FORM UTILITIES - DATE CONSTRAINTS
   ============================================ */

/**
 * Sets date constraints on date inputs
 * Sets minimum date to today and maximum to 2 years in future
 */
function setDateConstraints() {
    const scheduleDateInputs = ['schedule_date', 'editScheduleDate'];

    scheduleDateInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('min', today);

            const maxDate = new Date();
            maxDate.setFullYear(maxDate.getFullYear() + 2);
            input.setAttribute('max', maxDate.toISOString().split('T')[0]);
        }
    });
}


/* ============================================
   11. SEARCH & FILTER UTILITIES
   ============================================ */

/**
 * Clears search input and submits form
 * Used for the search clear button
 */
function clearSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
        searchInput.form.submit();
    }
}


/* ============================================
   12. ERROR HANDLING
   ============================================ */

/**
 * Shows error message to user
 * @param {string} message - Error message to display
 */
function showError(message) {
    console.error(message);
    // You can replace this with a toast notification library
    // or show an alert modal
    alert(message);
}


/* ============================================
   13. DOCUMENT INITIALIZATION
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
    // Setup form handling for Add form
    const addForm = document.getElementById('immunizationForm');
    const addSubmitBtn = document.getElementById('submit-btn');
    if (addForm && addSubmitBtn) {
        setupFormHandling(addForm, addSubmitBtn, 'Scheduling...');
    }

    // Setup form handling for Edit form
    const editForm = document.getElementById('editImmunizationForm');
    const editSubmitBtn = editForm?.querySelector('button[type="submit"]');
    if (editForm && editSubmitBtn) {
        setupFormHandling(editForm, editSubmitBtn, 'Updating...');
    }

    // Set date constraints
    setDateConstraints();

    // Close modals on Escape key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
            closeEditModal();
            closeViewModal();
            closeImmunizationRescheduleModal();
            closeMarkDoneModal();
            closeMarkMissedModal();
        }
    });

    // Note: Reschedule form submission is handled in reschedule_modal.blade.php

    // Setup mark missed reschedule checkbox
    const missedRescheduleCheckbox = document.getElementById('missed-reschedule-checkbox');
    if (missedRescheduleCheckbox) {
        missedRescheduleCheckbox.addEventListener('change', function () {
            const rescheduleFields = document.getElementById('reschedule-fields');
            const dateInput = document.getElementById('missed-reschedule-date');
            const timeInput = document.getElementById('missed-reschedule-time');

            if (this.checked) {
                rescheduleFields.classList.remove('hidden');
                dateInput.required = true;
                timeInput.required = true;
            } else {
                rescheduleFields.classList.add('hidden');
                dateInput.required = false;
                timeInput.required = false;
                dateInput.value = '';
                timeInput.value = '';
            }
        });
    }

    // Setup mark missed form submission
    const markMissedForm = document.getElementById('markMissedForm');
    if (markMissedForm) {
        markMissedForm.addEventListener('submit', function (e) {
            if (!document.getElementById('missed-confirm-checkbox').checked) {
                e.preventDefault();
                showError('Please confirm by checking the checkbox');
                return false;
            }

            const submitBtn = document.getElementById('missed-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            }
        });
    }

    // Setup mark done modal click outside
    const doneModal = document.getElementById('markDoneModal');
    if (doneModal) {
        doneModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeMarkDoneModal();
            }
        });
    }
});
