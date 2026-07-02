@extends('layout.' . auth()->user()->role)

@section('title', 'Patient Profile - ' . ($patient->name ?? ($patient->first_name . ' ' . $patient->last_name)))

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route(auth()->user()->role . '.patients.index') }}"
                           class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Patients
                        </a>
                        <div class="h-6 border-l border-gray-300"></div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Patient Profile
                        </h1>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route(auth()->user()->role . '.patients.print', $patient->id) }}" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-print mr-2"></i>
                            Print Profile
                        </a>
                        <a href="{{ route(auth()->user()->role . '.patients.edit', $patient->id) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Patient
                        </a>
                        {{-- TDaP Vaccine Card button (rendered by partial, status badge computed there) --}}
                        @include('partials.shared.patient.vaccine_card_modal', ['patient' => $patient])
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Patient Overview -->
            <div class="lg:col-span-1">
                <!-- Patient Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-user-circle text-blue-600 mr-2"></i>
                            Patient Information
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user text-blue-600 text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ $patient->name ?? ($patient->first_name . ' ' . $patient->last_name) }}
                            </h3>
                            <p class="text-gray-600">{{ $patient->formatted_patient_id }}</p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Age</span>
                                <span class="font-medium">{{ $patient->age }} years</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Occupation</span>
                                <span class="font-medium">{{ $patient->occupation }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Contact</span>
                                <span class="font-medium">{{ $patient->contact }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Emergency Contact</span>
                                <span class="font-medium">{{ $patient->emergency_contact }}</span>
                            </div>
                            <div class="flex justify-between items-start py-2">
                                <span class="text-gray-600">Address</span>
                                <span class="font-medium text-right">{{ $patient->address }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Health Status Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-heartbeat text-red-500 mr-2"></i>
                            Health Status
                        </h2>
                    </div>
                    <div class="p-6">
                        @if($patient->activePrenatalRecord)
                            <div class="text-center mb-4">
                                @php
                                    $status = $patient->activePrenatalRecord->status;
                                    $statusColor = match($status) {
                                        'normal' => 'green',
                                        'monitor' => 'yellow',
                                        'high-risk' => 'red',
                                        'due' => 'blue',
                                        default => 'gray'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                    {{ ucfirst(str_replace('-', ' ', $status)) }}
                                </span>
                            </div>

                            @if($patient->latestCheckup)
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Last Checkup</span>
                                        <span class="font-medium">{{ $patient->latestCheckup->checkup_date ? date('M j, Y', strtotime($patient->latestCheckup->checkup_date)) : 'N/A' }}</span>
                                    </div>
                                    @if($patient->latestCheckup->next_visit_date)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Next Visit</span>
                                            <span class="font-medium">{{ date('M j, Y', strtotime($patient->latestCheckup->next_visit_date)) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="text-center text-gray-500">
                                <i class="fas fa-info-circle mb-2"></i>
                                <p>No active prenatal record</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                            Quick Statistics
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $patient->prenatalRecords->count() }}</div>
                                <div class="text-sm text-gray-600">Prenatal Records</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $patient->prenatalCheckups->where('status', 'done')->count() }}</div>
                                <div class="text-sm text-gray-600">Completed Checkups</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $patient->childRecords->count() }}</div>
                                <div class="text-sm text-gray-600">Children</div>
                            </div>
                            <div class="text-center">
                                @php
                                    $missedCheckups = $patient->prenatalCheckups->where('status', 'missed')->count();
                                @endphp
                                <div class="text-2xl font-bold {{ $missedCheckups > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $missedCheckups }}</div>
                                <div class="text-sm text-gray-600">Missed Checkups</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Detailed Records -->
            <div class="lg:col-span-2">
                <!-- Tabs Navigation -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                            <button onclick="showTab('prenatal')"
                                    class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600"
                                    id="prenatal-tab">
                                <i class="fas fa-baby mr-2"></i>
                                Prenatal Care
                            </button>
                            <button onclick="showTab('checkups')"
                                    class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300"
                                    id="checkups-tab">
                                <i class="fas fa-stethoscope mr-2"></i>
                                Checkup History
                            </button>
                            <button onclick="showTab('children')"
                                    class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300"
                                    id="children-tab">
                                <i class="fas fa-child mr-2"></i>
                                Children ({{ $patient->childRecords->count() }})
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Content -->
                <div id="tab-content">
                    <!-- Prenatal Records Tab -->
                    <div id="prenatal-content" class="tab-content">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Prenatal Records</h3>
                            </div>
                            <div class="p-6">
                                @if($patient->prenatalRecords->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($patient->prenatalRecords as $record)
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <div class="flex justify-between items-start mb-3">
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900">
                                                            Record #{{ $loop->iteration }}
                                                            @if($record->status)
                                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                    @if($record->status === 'normal') bg-green-100 text-green-800
                                                                    @elseif($record->status === 'monitor') bg-yellow-100 text-yellow-800
                                                                    @elseif($record->status === 'high-risk') bg-red-100 text-red-800
                                                                    @elseif($record->status === 'due') bg-blue-100 text-blue-800
                                                                    @else bg-gray-100 text-gray-800 @endif">
                                                                    {{ ucfirst(str_replace('-', ' ', $record->status)) }}
                                                                </span>
                                                            @endif
                                                        </h4>
                                                        <p class="text-sm text-gray-600">Created: {{ $record->created_at->format('M j, Y') }}</p>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-gray-600">LMP:</span>
                                                        <span class="font-medium">{{ $record->last_menstrual_period ? date('M j, Y', strtotime($record->last_menstrual_period)) : 'N/A' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600">Due Date:</span>
                                                        <span class="font-medium">{{ $record->expected_due_date ? date('M j, Y', strtotime($record->expected_due_date)) : 'N/A' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600">Gravida:</span>
                                                        <span class="font-medium">{{ $record->gravida ?? 'N/A' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600">Para:</span>
                                                        <span class="font-medium">{{ $record->para ?? 'N/A' }}</span>
                                                    </div>
                                                </div>

                                                @if($record->medical_history)
                                                    <div class="mt-3">
                                                        <span class="text-gray-600 text-sm">Medical History:</span>
                                                        <p class="text-gray-900 text-sm mt-1">{{ $record->medical_history }}</p>
                                                    </div>
                                                @endif

                                                @if($record->notes)
                                                    <div class="mt-3">
                                                        <span class="text-gray-600 text-sm">Notes:</span>
                                                        <p class="text-gray-900 text-sm mt-1">{{ $record->notes }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                                        <p class="text-lg">No prenatal records found</p>
                                        <p class="text-sm">Prenatal records will appear here when created</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Checkups Tab -->
                    <div id="checkups-content" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Prenatal Checkup Journey</h3>
                            </div>
                            <div class="p-6">
                                @if($patient->prenatalCheckups->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($patient->prenatalCheckups as $checkup)
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <div class="flex justify-between items-start mb-3">
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900">
                                                            Checkup - {{ $checkup->checkup_date ? date('M j, Y', strtotime($checkup->checkup_date)) : 'Date not set' }}
                                                            @if($checkup->status)
                                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                    @if($checkup->status === 'done' || $checkup->status === 'completed') bg-green-100 text-green-800
                                                                    @elseif($checkup->status === 'upcoming') bg-blue-100 text-blue-800
                                                                    @elseif($checkup->status === 'missed') bg-red-100 text-red-800
                                                                    @else bg-gray-100 text-gray-800 @endif">
                                                                    <i class="fas {{ $checkup->status === 'done' || $checkup->status === 'completed' ? 'fa-check-circle' : ($checkup->status === 'missed' ? 'fa-times-circle' : 'fa-clock') }} mr-1"></i>
                                                                    {{ ucfirst($checkup->status) }}
                                                                </span>
                                                            @endif
                                                        </h4>
                                                        @if($checkup->checkup_time)
                                                            <p class="text-sm text-gray-600">Time: {{ date('g:i A', strtotime($checkup->checkup_time)) }}</p>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-4 text-sm">
                                                    @if($checkup->blood_pressure)
                                                        <div>
                                                            <span class="text-gray-600">Blood Pressure:</span>
                                                            <span class="font-medium">{{ $checkup->blood_pressure }}</span>
                                                        </div>
                                                    @endif
                                                    @if($checkup->weight)
                                                        <div>
                                                            <span class="text-gray-600">Weight:</span>
                                                            <span class="font-medium">{{ $checkup->weight }} kg</span>
                                                        </div>
                                                    @endif
                                                    @if($checkup->gestational_weeks)
                                                        <div>
                                                            <span class="text-gray-600">Gestational Age:</span>
                                                            <span class="font-medium">{{ $checkup->gestational_weeks }} weeks</span>
                                                        </div>
                                                    @endif
                                                    @if($checkup->next_visit_date)
                                                        <div>
                                                            <span class="text-gray-600">Next Visit:</span>
                                                            <span class="font-medium">{{ date('M j, Y', strtotime($checkup->next_visit_date)) }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($checkup->status === 'missed')
                                                    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                                        <div class="flex items-start">
                                                            <i class="fas fa-exclamation-triangle text-red-600 mt-0.5 mr-2"></i>
                                                            <div class="text-sm">
                                                                <span class="text-red-800 font-medium">Missed Appointment</span>
                                                                @if($checkup->missed_date)
                                                                    <p class="text-red-700">Marked as missed on {{ date('M j, Y g:i A', strtotime($checkup->missed_date)) }}</p>
                                                                @endif
                                                                @if($checkup->missed_reason)
                                                                    <p class="text-red-700 mt-1">Reason: {{ $checkup->missed_reason }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($checkup->notes)
                                                    <div class="mt-3">
                                                        <span class="text-gray-600 text-sm">Notes:</span>
                                                        <p class="text-gray-900 text-sm mt-1 whitespace-pre-line">{{ $checkup->notes }}</p>
                                                    </div>
                                                @endif

                                                @if($checkup->findings)
                                                    <div class="mt-3">
                                                        <span class="text-gray-600 text-sm">Findings:</span>
                                                        <p class="text-gray-900 text-sm mt-1">{{ $checkup->findings }}</p>
                                                    </div>
                                                @endif

                                                @if($checkup->recommendations)
                                                    <div class="mt-3">
                                                        <span class="text-gray-600 text-sm">Recommendations:</span>
                                                        <p class="text-gray-900 text-sm mt-1">{{ $checkup->recommendations }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-stethoscope text-4xl mb-4"></i>
                                        <p class="text-lg">No checkups recorded</p>
                                        <p class="text-sm">Prenatal checkups will appear here when conducted</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Children Tab -->
                    <div id="children-content" class="tab-content hidden">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Children & Immunization Records</h3>
                            </div>
                            <div class="p-6">
                                @if($patient->childRecords->count() > 0)
                                    <div class="space-y-6">
                                        @foreach($patient->childRecords as $child)
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <div class="flex justify-between items-start mb-4">
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900">{{ $child->full_name }}</h4>
                                                        <p class="text-sm text-gray-600">
                                                            {{ ucfirst($child->gender) }} • Born: {{ $child->birthdate ? date('M j, Y', strtotime($child->birthdate)) : 'Date not set' }}
                                                            @if($child->birthdate)
                                                                • Age: {{ \Carbon\Carbon::parse($child->birthdate)->age }} years old
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                @if($child->immunizations->count() > 0)
                                                    <div class="mt-4">
                                                        <h5 class="font-medium text-gray-900 mb-3">Immunization History</h5>
                                                        <div class="space-y-2">
                                                            @foreach($child->immunizations as $immunization)
                                                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded">
                                                                    <div>
                                                                        <span class="font-medium">{{ $immunization->vaccine->name ?? 'Unknown Vaccine' }}</span>
                                                                        @if($immunization->dose)
                                                                            <span class="text-gray-600 text-sm">(Dose {{ $immunization->dose }})</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <div class="text-sm font-medium">{{ $immunization->schedule_date ? date('M j, Y', strtotime($immunization->schedule_date)) : 'Date not set' }}</div>
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                            @if($immunization->status === 'completed') bg-green-100 text-green-800
                                                                            @elseif($immunization->status === 'scheduled') bg-blue-100 text-blue-800
                                                                            @elseif($immunization->status === 'missed') bg-red-100 text-red-800
                                                                            @else bg-gray-100 text-gray-800 @endif">
                                                                            {{ ucfirst($immunization->status) }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="mt-4 text-center py-4 text-gray-500">
                                                        <i class="fas fa-syringe text-2xl mb-2"></i>
                                                        <p class="text-sm">No immunizations recorded for this child</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-child text-4xl mb-4"></i>
                                        <p class="text-lg">No children recorded</p>
                                        <p class="text-sm">Child records will appear here when added to the system</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/' . auth()->user()->role . '/' . auth()->user()->role . '.js') }}"></script>
<script src="{{ asset('js/' . auth()->user()->role . '/patients-profile.js') }}"></script>
@endpush
@endsection
