@extends('layout.midwife') 
@section('title', 'Immunization Schedule')
@section('page-title', 'Immunization Schedule')
@section('page-subtitle', 'Manage and track child immunization records')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<link rel="stylesheet" href="{{ asset('css/midwife/midwife.css') }}">
<link rel="stylesheet" href="{{ asset('css/midwife/immunization-index.css') }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Stats -->
    <div class="flex justify-between items-center mb-6">
         <div> </div>
        <div class="flex space-x-3">
            <button onclick="openAddModal()"
                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-all duration-200 flex items-center btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add Schedule
            </button>

            <!-- Test Toast Button (Remove in production) -->
            

            <!-- Test Notification Integration Button (Remove in production) -->
            

            <!-- Test BHW-to-Midwife Notifications (Remove in production) -->
             
        </div>
    </div>

    
    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route('midwife.immunization.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-8 gap-4">
                <!-- Search Input - takes 4 columns -->
                <div class="md:col-span-4">
                    <div class="relative">
                        <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                               placeholder="Search by child name or vaccine"
                               class="w-full pl-10 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary form-input">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                        <!-- Clear button (x) inside input -->
                        @if(request('search'))
                        <button type="button" onclick="clearSearch()" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
                <!-- Status Filter, Vaccine Filter, and Search Button grouped closer - takes 4 columns total -->
                <div class="md:col-span-4 flex gap-2">
                    <!-- Status Filter -->
                    <div class="flex-1">
                        <select name="status" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-secondary form-input">
                            <option value="">All Status</option>
                            <option value="Upcoming" {{ request('status') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="Done" {{ request('status') == 'Done' ? 'selected' : '' }}>Done</option>
                            <option value="Missed" {{ request('status') == 'Missed' ? 'selected' : '' }}>Missed</option>
                        </select>
                    </div>
                    <!-- Vaccine Filter -->
                    <div class="flex-1">
                        <select name="vaccine" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-secondary form-input">
                            <option value="">All Vaccines</option>
                            @foreach($availableVaccines ?? [] as $vaccine)
                                <option value="{{ $vaccine->id }}" {{ request('vaccine') == $vaccine->id ? 'selected' : '' }}>
                                    {{ $vaccine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Search Button -->
                    <div class="flex items-end">
                        <button type="submit" class="bg-secondary text-white px-4 py-2 rounded-lg hover:bg-hover-color transition-all duration-200 btn-primary whitespace-nowrap">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($immunizations->count() > 0)
            <div class="table-wrapper">
                <table class="w-full table-container">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <!--<th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Immunization ID</th>-->
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'child_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Child Name <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'vaccine_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Vaccine <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'schedule_date', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Schedule Date <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'schedule_time', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Schedule Time <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Dose</th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($immunizations as $immunization)
                        <tr class="table-row-hover">
                            <!--<td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-blue-600">{{ $immunization->formatted_immunization_id ?? 'IM-001' }}</div>
                            </td>-->
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $immunization->childRecord->full_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $immunization->vaccine_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500 sm:hidden">{{ $immunization->dose ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 whitespace-nowrap">
                                <div class="text-sm sm:text-base">{{ $immunization->schedule_date ? $immunization->schedule_date->format('M j, Y') : 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 whitespace-nowrap">
                                <div class="text-sm sm:text-base">{{ $immunization->schedule_time ? \Carbon\Carbon::parse($immunization->schedule_time)->format('h:i A') : 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                                {{ $immunization->dose ?? 'N/A' }}
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $immunization->status === 'Upcoming' ? 'status-upcoming' : '' }}
                                    {{ $immunization->status === 'Done' ? 'status-done' : '' }}
                                    {{ $immunization->status === 'Missed' ? 'status-missed' : '' }}">
                                    <i class="fas {{ $immunization->status === 'Done' ? 'fa-check' : ($immunization->status === 'Upcoming' ? 'fa-clock' : 'fa-times') }} mr-1"></i>
                                    {{ $immunization->status }}
                                </span>
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap text-center align-middle">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                @php
                                    $immunizationData = [
                                        'id' => $immunization->id,
                                        'child_record' => [
                                            'full_name' => $immunization->childRecord->full_name ?? 'Unknown',
                                            'birthdate' => $immunization->childRecord->birthdate ?? null,
                                            'gender' => $immunization->childRecord->gender ?? null,
                                            'mother_name' => $immunization->childRecord->mother->name ?? 'N/A'
                                        ],
                                        'vaccine' => ['name' => $immunization->vaccine->name ?? $immunization->vaccine_name],
                                        'vaccine_name' => $immunization->vaccine_name,
                                        'dose' => $immunization->dose,
                                        'schedule_date' => $immunization->schedule_date,
                                        'schedule_time' => $immunization->schedule_time,
                                        'status' => $immunization->status,
                                        'notes' => $immunization->notes,
                                        'batch_number' => $immunization->batch_number,
                                        'administered_by' => $immunization->administered_by,
                                        'child_record_id' => $immunization->child_record_id,
                                        'vaccine_id' => $immunization->vaccine_id
                                    ];
                                @endphp

                                <!-- View Button -->
                                <button onclick='openViewModal(@json($immunizationData))'
                                        class="btn-action btn-view inline-flex items-center justify-center"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($immunization->status === 'Upcoming')
                                    @if(!$immunization->hasBeenRescheduled())
                                        <!-- Mark as Complete Button -->
                                        <button onclick='openMarkDoneModal(@json($immunizationData))'
                                                class="btn-action btn-complete inline-flex items-center justify-center"
                                                title="Mark as Complete">
                                            <i class="fas fa-check-circle"></i>
                                        </button>

                                        <!-- Mark as Missed Button -->
                                        <button onclick='openMarkMissedModal(@json($immunizationData))'
                                                class="btn-action btn-missed inline-flex items-center justify-center"
                                                title="Mark as Missed">
                                            <i class="fas fa-times"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        <button onclick="openEditModal({{ json_encode($immunization->toArray()) }})"
                                                class="btn-action btn-edit inline-flex items-center justify-center"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <!-- Show link to new appointment if rescheduled -->
                                        @if($immunization->rescheduledToImmunization)
                                            <a href="{{ route('midwife.immunization.index') }}"
                                               class="btn-action btn-view inline-flex items-center justify-center"
                                               title="View New Appointment">
                                                <i class="fas fa-arrow-right mr-1"></i>
                                                View New
                                            </a>
                                        @endif
                                    @endif
                                @elseif($immunization->status === 'Missed')
                                    @if(!$immunization->hasBeenRescheduled())
                                        <!-- Reschedule Button for Missed Immunizations -->
                                        <button onclick='openImmunizationRescheduleModal(@json($immunizationData))'
                                                class="btn-action btn-reschedule inline-flex items-center justify-center"
                                                title="Reschedule">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    @else
                                        <!-- Show indicator that it has been rescheduled (matching prenatal checkup style) -->
                                        <span class="text-xs text-gray-500 italic" title="This immunization has been rescheduled">
                                            <i class="fas fa-check-circle text-green-500"></i> Rescheduled
                                        </span>
                                    @endif
                                @else
                                    <!-- For completed immunizations - only show edit -->
                                    <button onclick="openEditModal({{ json_encode($immunization->toArray()) }})"
                                            class="btn-action btn-edit inline-flex items-center justify-center"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @php
                $paginator = $immunizations;
                $currentPage = $paginator->currentPage();
                $lastPage = max(1, $paginator->lastPage());
            @endphp

            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-sm text-gray-600">
                <div>
                    Showing
                    <span class="font-medium">{{ $paginator->firstItem() ?? ($paginator->count() ? 1 : 0) }}</span>
                    to
                    <span class="font-medium">{{ $paginator->lastItem() ?? $paginator->count() }}</span>
                    of
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    results
                </div>

                <nav class="inline-flex items-center gap-1" role="navigation" aria-label="Pagination">
                    @php $prevDisabled = $paginator->onFirstPage(); @endphp
                    <a
                        href="{{ $prevDisabled ? '#' : $paginator->previousPageUrl() }}"
                        class="pagination-btn {{ $prevDisabled ? 'disabled' : '' }}"
                        aria-disabled="{{ $prevDisabled ? 'true' : 'false' }}"
                        aria-label="Previous page"
                    >
                        <i class="fas fa-chevron-left"></i>
                    </a>

                    @for ($page = 1; $page <= $lastPage; $page++)
                        <a
                            href="{{ $paginator->url($page) }}"
                            class="pagination-btn {{ $page === $currentPage ? 'active' : '' }}"
                            aria-current="{{ $page === $currentPage ? 'page' : 'false' }}"
                        >
                            {{ $page }}
                        </a>
                    @endfor

                    @php $nextDisabled = !$paginator->hasMorePages(); @endphp
                    <a
                        href="{{ $nextDisabled ? '#' : $paginator->nextPageUrl() }}"
                        class="pagination-btn {{ $nextDisabled ? 'disabled' : '' }}"
                        aria-disabled="{{ $nextDisabled ? 'true' : 'false' }}"
                        aria-label="Next page"
                    >
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </nav>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-syringe text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No immunization schedules found</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                    @if(request()->hasAny(['search', 'status', 'vaccine', 'date_from']))
                        No records match your search criteria. Try adjusting your filters.
                    @else
                        Get started by scheduling the first immunization.
                    @endif
                </p>
                <button onclick="openAddModal()" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition-all duration-200 inline-flex items-center btn-primary">
                    <i class="fas fa-plus mr-2"></i>Schedule Immunization
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Add Immunization Modal -->
 @include ('partials.midwife.immunization.immuadd')

<!-- View Immunization Modal -->
 @include ('partials.midwife.immunization.immuview')

<!-- Edit Immunization Modal -->
 @include ('partials.midwife.immunization.immuedit')

<!-- Confirm Missed Modal -->
 @include ('partials.midwife.immunization.mark-missed-modal')

<!-- Reschedule Modal -->
 @include ('partials.midwife.immunization.reschedule_modal')

<!-- Mark as Done Modal -->
 @include ('partials.midwife.immunization.mark-done-modal')

@endsection

@push('scripts')
<script src="{{ asset('js/midwife/midwife.js') }}"></script>
<script src="{{ asset('js/midwife/immunization-index.js') }}"></script>
@endpush