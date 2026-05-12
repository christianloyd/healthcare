@extends('layout.midwife')
@section('title', 'Prenatal Checkups')
@section('page-title', 'Prenatal Checkups')
@section('page-subtitle', 'Manage and monitor prenatal checkup appointments')

@push('styles')
    <link href="{{ asset('css/midwife/prenatalcheckup-index.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="space-y-6">
    @if($errors->any())
    <div class="alert alert-error">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
            @foreach($errors->all() as $error)
                <p class="mb-1">{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <div></div>
        <div class="flex space-x-3">
            <button onclick="openCheckupModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-all duration-200 flex items-center btn-primary" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                <i class="fas fa-plus mr-2"></i>
                Add Checkup
            </button>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route('midwife.prenatalcheckup.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name"
                               class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input" style="border-color: #e5e7eb;">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <select name="status" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary form-input" style="border-color: #e5e7eb; focus:border-color: var(--primary);">
                        <option value="">All Status</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                        <option value="missed" {{ request('status') == 'missed' ? 'selected' : '' }}>Missed</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-all duration-200 btn-primary" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                    <a href="{{ route('midwife.prenatalcheckup.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Prenatal Checkups Table -->
    <div class="bg-white rounded-lg shadow-sm border table-container">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <!--<th>Patient ID</th>-->
                        <th>Patient Name</th>
                        <th>Checkup Date</th>
                        <th>Checkup Time</th>
                        <th>Status</th>
                        <!--<th>Next Visit</th>-->
                        <th>Follow-Up Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($checkups as $checkup)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <!--<td class="font-medium text-blue-600">
                        {{ $checkup->prenatalRecord->patient->formatted_patient_id ?? 'N/A' }}
                    </td>-->
                    <td>
                        <div class="flex items-center space-x-3">
                             
                                
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $checkup->patient->name ?? ($checkup->prenatalRecord->patient->name ?? 'N/A') }}</p>
                                    </div>
                        </div>
                    </td>
                    <td class="text-gray-900">
                        <span class="font-medium">{{ $checkup->checkup_date ? $checkup->checkup_date->format('M d, Y') : 'N/A' }}</span>
                    </td>
                    <td class="text-gray-900">
                        <span class="text-sm">{{ $checkup->checkup_time ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-{{ $checkup->status ?? 'upcoming' }}">
                            <i class="fas {{ $checkup->status === 'done' ? 'fa-check' : ($checkup->status === 'missed' ? 'fa-times-circle' : 'fa-clock') }} mr-1"></i>
                            {{ ucfirst($checkup->status ?? 'Upcoming') }}
                        </span>
                    </td>
                    <td class="text-gray-600">
                        @if($checkup->next_visit_date)
                            {{ \Carbon\Carbon::parse($checkup->next_visit_date)->format('M d, Y') }}
                        @else
                            <span class="text-gray-500">Not scheduled</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex space-x-2">
                            <button onclick="openViewCheckupModal({{ $checkup->id }})"
                                    class="btn-action btn-view inline-flex items-center justify-center" title="View Checkup Details">
                                <i class="fas fa-eye"></i>
                            </button>

                            @if($checkup->status === 'upcoming')
                                <!-- Mark as missed button - always available for upcoming checkups -->
                                <button onclick="openMarkCheckupMissedModal({{ $checkup->id }}, '{{ $checkup->patient->name ?? ($checkup->prenatalRecord->patient->name ?? 'N/A') }}', '{{ $checkup->checkup_date ? $checkup->checkup_date->format('M d, Y') : 'N/A' }}', '{{ $checkup->checkup_time ?? 'N/A' }}')"
                                        class="btn-action btn-missed inline-flex items-center justify-center" title="Mark as Missed">
                                    <i class="fas fa-times"></i>
                                </button>
                                <!-- Always show edit for scheduled -->
                                <button onclick="openScheduleEditModal({{ $checkup->id }})"
                                        class="btn-action btn-edit inline-flex items-center justify-center" title="Edit Schedule">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @elseif($checkup->status === 'missed')
                                <!-- For missed checkups - show reschedule button only if not already rescheduled -->
                                @if(!$checkup->rescheduled)
                                    <button onclick="openRescheduleModal({{ $checkup->id }})"
                                            class="btn-action btn-reschedule inline-flex items-center justify-center" title="Reschedule">
                                        <i class="fas fa-calendar-plus"></i>
                                    </button>
                                @else
                                    <!-- Show indicator that it has been rescheduled -->
                                    <span class="text-xs text-gray-500 italic" title="This checkup has been rescheduled">
                                        <i class="fas fa-check-circle text-green-500"></i> Rescheduled
                                    </span>
                                @endif
                            @elseif($checkup->status === 'done')
                                <!-- For done/completed checkups - NO EDIT BUTTON, view only -->
                                <!-- Edit button hidden as requested -->
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">No prenatal checkups found</p>
                            <p class="text-gray-600 mb-4">Get started by creating your first prenatal checkup</p>
                            <button onclick="openCheckupModal()" class="btn-primary" style="background-color: var(--primary); color: white; padding: 8px 16px; border-radius: 8px; border: none; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                                <i class="fas fa-plus mr-2"></i>
                                Create First Checkup
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @include('components.pagination', ['paginator' => $checkups])
    </div>
</div>

<!-- Prenatal Checkup Routes Configuration -->
<script>
    window.prenatalRoutes = {
        searchPatients: '{{ route("midwife.prenatalcheckup.patients.search") }}'
    };
</script>

<!-- Include Prenatal Checkup Module JavaScript -->
<script src="{{ asset('js/midwife/prenatalcheckup-index.js') }}"></script>

<!-- Include Edit, Schedule Edit, View, Reschedule and Mark Missed Partials -->
@include('partials.midwife.prenatalcheckup.prenatalcheckupedit')
@include('partials.midwife.prenatalcheckup.schedule_edit')
@include('partials.midwife.prenatalcheckup.prenatalcheckupview')
@include('partials.midwife.prenatalcheckup.reschedule_modal')
@include('partials.midwife.prenatalcheckup.mark-missed-modal')
@include('partials.midwife.prenatalcheckup.add-prenatal-check')

@endsection
