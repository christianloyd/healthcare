<!-- Add Immunization Modal -->
<div id="immunizationModal"
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeModal(event)">

    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
         onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-syringe text-[#68727A] mr-2"></i>
                Schedule New Immunization
            </h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="immunizationForm" action="{{ route('midwife.immunization.store') }}" method="POST" class="space-y-5">
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

            <div class="modal-form-grid grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Patient Selection -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Child Information</h3>
                    </div>
                    <div class="space-y-4">
                        <!-- Child Search Implementation -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Search and Select Child *
                            </label>
                            <div class="search-container relative">
                                <input type="text"
                                       id="child-search"
                                       placeholder="Type child name, ID, or mother name to search..."
                                       class="form-input input-clean w-full pl-10 pr-10 py-2.5 rounded-lg"
                                       autocomplete="off">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <div id="search-loading" class="hidden">
                                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                    </div>
                                    <button type="button" id="clear-search" class="hidden text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Search Dropdown -->
                                <div id="search-dropdown" class="absolute top-full left-0 right-0 bg-white border border-gray-300 border-t-0 rounded-b-lg max-h-60 overflow-y-auto z-50 shadow-lg hidden">
                                    <!-- Results will be populated here -->
                                </div>
                            </div>

                            <!-- Hidden input for selected child ID -->
                            <input type="hidden" name="child_record_id" id="selected-child-id" value="{{ old('child_record_id') }}">

                            <!-- Selected Child Display -->
                            <div id="selected-child-display" class="hidden mt-3 bg-blue-50 border-2 border-blue-200 rounded-lg p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-semibold text-blue-900" id="selected-child-name"></div>
                                        <div class="text-sm text-blue-700 mt-1" id="selected-child-details"></div>
                                    </div>
                                    <button type="button" id="remove-selection" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <p class="text-sm text-gray-500 mt-2">
                                Don't see the child?
                                <a href="{{ route('midwife.childrecord.index') }}" class="text-blue-600 hover:text-blue-800 underline" target="_blank">
                                    Register a new child first
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Vaccine Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Vaccine Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Vaccine *</label>
                            <select name="vaccine_id" id="vaccine_id" required
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg">
                                <option value="">Choose a vaccine...</option>
                                @foreach($availableVaccines ?? [] as $vaccine)
                                    <option value="{{ $vaccine->id }}"
                                            data-category="{{ $vaccine->category }}"
                                            data-dose-count="{{ $vaccine->dose_count ?? 1 }}"
                                            data-vaccine-name="{{ $vaccine->name }}">
                                        {{ $vaccine->name }} ({{ $vaccine->dose_count ?? 1 }} {{ ($vaccine->dose_count ?? 1) > 1 ? 'doses' : 'dose' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dose *</label>
                            <select name="dose" id="dose" required
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg">
                                <option value="">Select dose...</option>
                            </select>
                        </div>

                        <!-- Vaccine Information Display -->
                        <div id="vaccineInfo" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h4 class="font-medium text-blue-900 mb-2">Vaccine Information</h4>
                            <div id="vaccineDetails" class="text-sm text-blue-800"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Information (Full Width) -->
            <div class="schedule-section">
                <div class="section-header border-b pb-2 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Schedule Information</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date *</label>
                        <input type="date" name="schedule_date" id="schedule_date" required
                               class="form-input input-clean w-full px-4 py-2.5 rounded-lg"
                               value="{{ old('schedule_date') }}" min="{{ date('Y-m-d') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Time *</label>
                        <input type="time" name="schedule_time" id="schedule_time" required min="05:00" max="16:59"
                               class="form-input input-clean w-full px-4 py-2.5 rounded-lg"
                               value="{{ old('schedule_time') }}">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes *</label>
                    <textarea name="notes" id="notes" rows="3" required
                              class="form-input input-clean w-full px-4 py-2.5 rounded-lg"
                              placeholder="Enter any additional notes or instructions...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="submit-btn"
                        class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors flex items-center">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Schedule Immunization
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM elements for child search
    const searchInput = document.getElementById('child-search');
    const searchDropdown = document.getElementById('search-dropdown');
    const selectedChildId = document.getElementById('selected-child-id');
    const selectedChildName = document.getElementById('selected-child-name');
    const selectedChildDetails = document.getElementById('selected-child-details');
    const selectedChildDisplay = document.getElementById('selected-child-display');
    const searchLoading = document.getElementById('search-loading');
    const clearSearchBtn = document.getElementById('clear-search');
    const removeSelectionBtn = document.getElementById('remove-selection');

    let children = [];
    let searchTimeout = null;

    // Load all children on page load
    loadChildren();

    async function loadChildren() {
        try {
            showLoading();

            const userRole = '{{ auth()->user()->role }}';
            const routeName = userRole === 'bhw' ? 'immunizations' : 'immunization';
            const url = `/${userRole}/${routeName}/children-data`;

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            children = data;
            console.log('Loaded', children.length, 'children for search');

            hideLoading();
        } catch (error) {
            console.error('Error loading children:', error);
            hideLoading();
            searchDropdown.innerHTML = '<div class="p-3 text-red-500">Error loading children. Please refresh the page.</div>';
            searchDropdown.classList.add('show');
        }
    }

    // Child search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        if (query.length < 2) {
            hideDropdown();
            hideClearButton();
            return;
        }

        showClearButton();

        searchTimeout = setTimeout(() => {
            searchChildren(query);
        }, 200);
    });

    function searchChildren(query) {
        const lowerQuery = query.toLowerCase();
        const filteredChildren = children.filter(child =>
            child.search_text.includes(lowerQuery)
        );

        displaySearchResults(filteredChildren);
    }

    function displaySearchResults(results) {
        searchDropdown.innerHTML = '';

        if (results.length === 0) {
            searchDropdown.innerHTML = '<div class="p-3 text-gray-500">No children found</div>';
        } else {
            results.slice(0, 10).forEach(child => {
                const option = document.createElement('div');
                option.className = 'p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors';
                option.innerHTML = `
                    <div class="font-medium text-gray-900">${child.name} (${child.formatted_child_id})</div>
                    <div class="text-sm text-gray-600 mt-1">
                        Mother: ${child.mother_name} • Age: ${child.age} • Gender: ${child.gender}
                    </div>
                `;

                option.addEventListener('click', () => selectChild(child));
                searchDropdown.appendChild(option);
            });
        }

        showDropdown();
    }

    function selectChild(child) {
        selectedChildId.value = child.id;
        selectedChildName.textContent = `${child.name} (${child.formatted_child_id})`;
        selectedChildDetails.textContent = `Mother: ${child.mother_name} • Age: ${child.age} • Gender: ${child.gender}`;

        searchInput.value = `${child.name} (${child.formatted_child_id})`;
        selectedChildDisplay.classList.remove('hidden');
        hideDropdown();
        showClearButton();

        // Remove any error styling
        searchInput.classList.remove('error-border');

        // Filter vaccines based on completion status for this child
        filterVaccinesForChild(child.id);

        console.log('Selected child:', child);
    }

    // Filter vaccines to hide completed ones and show only available doses
    function filterVaccinesForChild(childId) {
        const vaccineSelect = document.getElementById('vaccine_id');
        if (!vaccineSelect) return;

        const completionData = @json($vaccineCompletionData ?? []);
        const childData = completionData[childId] || {};

        // Reset vaccine select and clear dose select
        vaccineSelect.value = '';
        const doseSelect = document.getElementById('dose');
        if (doseSelect) {
            doseSelect.innerHTML = '<option value="">Select dose...</option>';
        }

        // Loop through all vaccine options
        Array.from(vaccineSelect.options).forEach(option => {
            if (!option.value) return; // Skip the "Choose a vaccine..." option

            const vaccineId = option.value;
            const vaccineCompletion = childData[vaccineId];

            if (vaccineCompletion) {
                if (vaccineCompletion.completed) {
                    // Hide completed vaccines
                    option.style.display = 'none';
                    option.disabled = true;
                    option.setAttribute('data-completed', 'true');
                } else {
                    // Show vaccines with remaining doses
                    option.style.display = '';
                    option.disabled = false;
                    option.removeAttribute('data-completed');

                    // Update option text to show remaining doses
                    const vaccineName = option.getAttribute('data-vaccine-name');
                    const doseCount = parseInt(option.getAttribute('data-dose-count')) || 1;
                    const remaining = vaccineCompletion.remaining;
                    option.textContent = `${vaccineName} (${remaining} of ${doseCount} ${doseCount > 1 ? 'doses' : 'dose'} remaining)`;
                }
            }
        });
    }

    function clearSelection() {
        selectedChildId.value = '';
        searchInput.value = '';
        selectedChildDisplay.classList.add('hidden');
        hideDropdown();
        hideClearButton();
        searchInput.focus();
    }

    function showDropdown() {
        searchDropdown.classList.remove('hidden');
    }

    function hideDropdown() {
        searchDropdown.classList.add('hidden');
    }

    function showLoading() {
        if (searchLoading) searchLoading.classList.remove('hidden');
    }

    function hideLoading() {
        if (searchLoading) searchLoading.classList.add('hidden');
    }

    function showClearButton() {
        if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
    }

    function hideClearButton() {
        if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
    }

    // Event listeners
    if (clearSearchBtn) clearSearchBtn.addEventListener('click', clearSelection);
    if (removeSelectionBtn) removeSelectionBtn.addEventListener('click', clearSelection);

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (searchInput && searchDropdown &&
            !searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            hideDropdown();
        }
    });

    // Form validation
    const form = document.getElementById('immunizationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!selectedChildId.value) {
                e.preventDefault();
                if (searchInput) {
                    searchInput.classList.add('error-border');
                    searchInput.focus();
                }
                alert('Please select a child before submitting the form.');
                return false;
            }
        });
    }

    // Clear error styling on input
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            this.classList.remove('error-border');
        });
    }

    // Restore selected child if there's an old value (after validation error)
    @if(old('child_record_id'))
        const oldChildId = '{{ old('child_record_id') }}';
        if (oldChildId && children.length > 0) {
            setTimeout(function() {
                const child = children.find(c => c.id == oldChildId);
                if (child) {
                    selectChild(child);
                }
            }, 1000);
        }
    @endif

    // Handle vaccine selection and load available doses
    const vaccineSelect = document.getElementById('vaccine_id');
    const doseSelect = document.getElementById('dose');
    const vaccineInfo = document.getElementById('vaccineInfo');
    const vaccineDetails = document.getElementById('vaccineDetails');

    if (vaccineSelect) {
        vaccineSelect.addEventListener('change', function() {
            const vaccineId = this.value;
            const childId = selectedChildId.value;

            // Clear dose dropdown
            doseSelect.innerHTML = '<option value="">Select dose...</option>';
            vaccineInfo.classList.add('hidden');

            if (!vaccineId) {
                return;
            }

            if (!childId) {
                alert('Please select a child first');
                this.value = '';
                return;
            }

            // Get selected option attributes
            const selectedOption = this.options[this.selectedIndex];
            const category = selectedOption.getAttribute('data-category') || 'N/A';
            const doseCount = parseInt(selectedOption.getAttribute('data-dose-count')) || 1;

            // Get completion data for this child and vaccine
            const completionData = @json($vaccineCompletionData ?? []);
            const childData = completionData[childId] || {};
            const vaccineCompletion = childData[vaccineId];

            // Generate doses based on what's remaining
            let doses = [];
            let nextDoseText = '';

            if (vaccineCompletion && vaccineCompletion.next_dose) {
                // Only show the next dose that needs to be administered
                doses = [vaccineCompletion.next_dose];
                nextDoseText = ` - Next: ${vaccineCompletion.next_dose}`;
            } else {
                // Show all doses if no completion data
                if (doseCount === 1) {
                    doses = ['1st Dose'];
                } else if (doseCount === 2) {
                    doses = ['1st Dose', '2nd Dose'];
                } else if (doseCount === 3) {
                    doses = ['1st Dose', '2nd Dose', '3rd Dose'];
                } else if (doseCount >= 4) {
                    for (let i = 1; i <= doseCount; i++) {
                        doses.push(i + (i === 1 ? 'st' : i === 2 ? 'nd' : i === 3 ? 'rd' : 'th') + ' Dose');
                    }
                }
            }

            // Populate dose dropdown
            doses.forEach(dose => {
                const option = document.createElement('option');
                option.value = dose;
                option.textContent = dose;
                doseSelect.appendChild(option);
            });

            // Auto-select if only one dose available
            if (doses.length === 1) {
                doseSelect.value = doses[0];
            }

            // Show vaccine info
            const vaccineName = selectedOption.getAttribute('data-vaccine-name');
            let completionInfo = '';
            if (vaccineCompletion) {
                completionInfo = `
                    <p><strong>Completed Doses:</strong> ${doseCount - vaccineCompletion.remaining} of ${doseCount}</p>
                    <p><strong>Remaining:</strong> ${vaccineCompletion.remaining} ${vaccineCompletion.remaining > 1 ? 'doses' : 'dose'}</p>
                `;
            }

            vaccineDetails.innerHTML = `
                <p><strong>Vaccine:</strong> ${vaccineName}</p>
                <p><strong>Category:</strong> ${category}</p>
                <p><strong>Total Doses:</strong> ${doseCount}</p>
                ${completionInfo}
                <p><strong>Next Dose:</strong> ${doses.join(', ')}</p>
            `;
            vaccineInfo.classList.remove('hidden');
        });
    }
});
</script>