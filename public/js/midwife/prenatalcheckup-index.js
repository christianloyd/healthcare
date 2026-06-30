/* ========================================
   Prenatal Checkup Index Module JavaScript
   ======================================== */

// Modal functions
function openCheckupModal() {
    const modal = document.getElementById('checkupModal');
    if (!modal) {
        console.error('Checkup modal not found');
        return;
    }

    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });
    document.body.style.overflow = 'hidden';

    // Initialize patient search when modal opens
    setTimeout(() => {
        initializePatientSearch();
    }, 100);
}

// Simple patient search functionality
function initializePatientSearch() {
    const searchInput = document.getElementById('patient-search');
    const searchDropdown = document.getElementById('search-dropdown');
    const selectedPatientId = document.getElementById('selected-patient-id');
    const selectedPatientDisplay = document.getElementById('selected-patient-display');
    const selectedPatientName = document.getElementById('selected-patient-name');
    const selectedPatientDetails = document.getElementById('selected-patient-details');
    const searchLoading = document.getElementById('search-loading');

    if (!searchInput || !selectedPatientId || !searchDropdown) {
        console.warn('Search elements not found, retrying...');
        setTimeout(initializePatientSearch, 100);
        return;
    }

    console.log('Initializing patient search...');

    let searchTimeout;

    // Search input handler
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            searchDropdown.classList.remove('show');
            if (searchLoading) searchLoading.classList.add('hidden');
            return;
        }

        if (searchLoading) searchLoading.classList.remove('hidden');

        searchTimeout = setTimeout(() => {
            fetch(`${window.prenatalRoutes.searchPatients}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    const results = data.data || data;
                    displayResults(results);
                    if (searchLoading) searchLoading.classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error fetching patients:', error);
                    if (searchLoading) searchLoading.classList.add('hidden');
                });
        }, 300);
    });

    function displayResults(results) {
        searchDropdown.innerHTML = '';

        if (results.length === 0) {
            searchDropdown.innerHTML = '<div class="search-option" style="color: #374151;">No patients found</div>';
        } else {
            results.forEach(patient => {
                const option = document.createElement('div');
                option.className = 'search-option';
                option.innerHTML = `
                    <div class="patient-info">
                        <div class="patient-name">${patient.name || (patient.first_name + ' ' + patient.last_name)}</div>
                        <div class="patient-details">${patient.formatted_patient_id || 'P-' + String(patient.id).padStart(3, '0')} • Age: ${patient.age || 'N/A'}</div>
                    </div>
                `;
                option.addEventListener('click', () => selectPatient(patient));
                searchDropdown.appendChild(option);
            });
        }

        searchDropdown.classList.add('show');
    }

    function selectPatient(patient) {
        selectedPatientId.value = patient.id;
        selectedPatientName.textContent = patient.name || (patient.first_name + ' ' + patient.last_name);
        selectedPatientDetails.textContent = `${patient.formatted_patient_id || 'P-' + String(patient.id).padStart(3, '0')} • Age: ${patient.age || 'N/A'}`;

        searchInput.value = patient.name || (patient.first_name + ' ' + patient.last_name);
        selectedPatientDisplay.classList.remove('hidden');
        searchDropdown.classList.remove('show');

        console.log('Patient selected:', patient.name || (patient.first_name + ' ' + patient.last_name));
    }

    // Clear button
    const clearBtn = document.getElementById('clear-search');
    const removeBtn = document.getElementById('remove-selection');

    if (clearBtn) {
        clearBtn.addEventListener('click', clearSelection);
    }
    if (removeBtn) {
        removeBtn.addEventListener('click', clearSelection);
    }

    function clearSelection() {
        selectedPatientId.value = '';
        searchInput.value = '';
        selectedPatientDisplay.classList.add('hidden');
        searchDropdown.classList.remove('show');
    }

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.classList.remove('show');
        }
    });
}

function closeCheckupModal(e) {
    // Don't close if click is inside modal content
    if (e && e.target !== e.currentTarget) return;

    const modal = document.getElementById('checkupModal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// View checkup details for patient
function viewCheckupDetails(patientId) {
    console.log('View checkup details for patient:', patientId);
    // This will be implemented to show existing checkup details
    showError('View checkup functionality - Patient ID: ' + patientId);
}

// Edit scheduled checkup
function editScheduledCheckup(patientId) {
    console.log('Edit scheduled checkup for patient:', patientId);
    showError('Edit scheduled checkup functionality - Patient ID: ' + patientId);
}

// Handle "None" swelling checkbox
function toggleNoneSwelling(noneCheckbox) {
    const swellingCheckboxes = document.querySelectorAll('input[name="swelling[]"]:not([value="none"])');

    if (noneCheckbox.checked) {
        swellingCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }
}

// Handle other swelling checkboxes
document.querySelectorAll('input[name="swelling[]"]:not([value="none"])').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            document.querySelector('input[name="swelling[]"][value="none"]').checked = false;
        }
    });
});

// Search patients
function searchPatients() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.patient-row');

    rows.forEach(row => {
        const patientName = row.querySelector('.patient-name').textContent.toLowerCase();
        const patientId = row.querySelector('.patient-id').textContent.toLowerCase();

        if (patientName.includes(searchTerm) || patientId.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Filter patients by status
function filterPatients() {
    const filterValue = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.patient-row');

    rows.forEach(row => {
        if (!filterValue || row.getAttribute('data-status') === filterValue) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.id === 'checkupModal') {
        closeCheckupModal();
    }
});

// Form submission with loading state (NO AJAX - just visual feedback)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#checkupModal form');
    if (form) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            const originalText = submitButton.innerHTML;

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            });
        }
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Show modal if there are validation errors
    const hasErrors = document.querySelectorAll('.error-message').length > 0;
    if (hasErrors) {
        openCheckupModal();
    }
});
