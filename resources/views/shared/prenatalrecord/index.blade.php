@extends('layout.' . auth()->user()->role)
@section('title', 'Prenatal Records')
@section('page-title', 'Prenatal Records')
@section('page-subtitle', 'Manage and monitor prenatal records')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/' . auth()->user()->role . '/' . auth()->user()->role . '.css') }}">
<link rel="stylesheet" href="{{ asset('css/' . auth()->user()->role . '/prenatalrecord-index.css') }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
         <div> </div>
        <div class="flex space-x-3">
            <!-- Changed from modal button to direct link to create page -->
            <a href="{{ route(auth()->user()->role . '.prenatalrecord.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add Prenatal Record
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route(auth()->user()->role . '.prenatalrecord.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name" class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <select name="status" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary form-input">
                        <option value="">All Status</option>
                        <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="monitor" {{ request('status') == 'monitor' ? 'selected' : '' }}>Monitor</option>
                        <option value="high-risk" {{ request('status') == 'high-risk' ? 'selected' : '' }}>High Risk</option>
                        <option value="due" {{ request('status') == 'due' ? 'selected' : '' }}>Appointment Due</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200 btn-primary">
                        Search
                    </button>
                    <a href="{{ route(auth()->user()->role . '.prenatalrecord.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Prenatal Records Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        @include('components.table-skeleton', ['id' => 'prenatal-skeleton'])
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <!--<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record ID</th>-->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gestational Age</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trimester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Visit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($prenatalRecords as $record)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <!--<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                            {{ $record->formatted_prenatal_id ?? 'PR-001' }}
                        </td>-->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $record->patient->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $record->gestational_age ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($record->trimester)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                    {{ $record->trimester }}{{ $record->trimester == 1 ? 'st' : ($record->trimester == 2 ? 'nd' : 'rd') }} Trimester
                                </span>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($record->expected_due_date)
                                {{ $record->expected_due_date->format('M d, Y') }}
                                @if($record->is_overdue)
                                    <!--<span class="text-red-600 text-xs block">Overdue</span>-->
                                @elseif($record->days_until_due <= 14 && $record->days_until_due >= 0)
                                    <!--<span class="text-orange-600 text-xs block">{{ $record->days_until_due }} days left</span>-->
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-{{ $record->status }}">
                                {{ $record->status_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($record->latestCheckup && $record->latestCheckup->checkup_date)
                                {{ $record->latestCheckup->checkup_date->format('M d, Y') }}
                            @else
                                <span class="text-gray-500">No checkups</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-4">
                                <a href="{{ route(auth()->user()->role . '.prenatalrecord.show', $record->id) }}"
                                class="btn-action btn-view inline-flex items-center justify-center">
                                <i class="fas fa-eye mr-1"></i>
                            <span class="hidden sm:inline">View Details</span>
                                </a>
                                <button onclick='openEditPrenatalModal(@json($record))'
                                class="btn-action btn-edit inline-flex items-center justify-center">
                                <i class="fas fa-edit mr-1"></i>
                            <span class="hidden sm:inline">Edit</span>
                                </button>
                                @if($record->status !== 'completed')
                                <button onclick="openCompletePregnancyModal({{ $record->id }}, '{{ $record->patient->name }}')"
                                class="btn-action btn-complete inline-flex items-center justify-center"
                                title="Mark pregnancy as completed">
                                <i class="fas fa-check-circle mr-1"></i>
                            <span class="hidden sm:inline">Complete</span>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-2">No prenatal records found</p>
                                <p class="text-gray-600 mb-4">Get started by creating your first prenatal record</p>
                                <a href="{{ route(auth()->user()->role . '.prenatalrecord.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors btn-primary">
                                    Create First Record
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @include('components.pagination', ['paginator' => $prenatalRecords])
    </div>
</div>

<!-- Edit Prenatal Record Modal -->
@include('partials.shared.prenatalrecord.prenataledit')

<!-- Complete Pregnancy Confirmation Modal -->
<div id="completePregnancyModal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="modal-content relative w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="text-center">
            <!-- Warning Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                <i class="fas fa-exclamation-triangle text-3xl text-yellow-600"></i>
            </div>

            <!-- Modal Title -->
            <h3 class="text-xl font-bold text-gray-900 mb-2">Complete Pregnancy Record?</h3>

            <!-- Patient Name -->
            <p class="text-gray-600 mb-4">
                You are about to mark the pregnancy record for:<br>
                <span class="font-semibold text-gray-900" id="completePatientName"></span>
            </p>

            <!-- Warning Message -->
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4 text-left">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-semibold">
                            ⚠️ Warning: This action cannot be reversed!
                        </p>
                        <p class="text-sm text-red-600 mt-1">
                            Once completed, you will NOT be able to change the status back or edit this record.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Confirmation Question -->
            <p class="text-gray-700 font-medium mb-6">
                Are you sure you want to proceed?
            </p>

            <!-- Buttons -->
            <form id="completePregnancyForm" method="POST" action="">
                @csrf
                <div class="flex space-x-3">
                    <button type="button" onclick="closeCompletePregnancyModal()"
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors font-medium">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" id="complete-submit-btn"
                            class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                        <i class="fas fa-check-circle mr-2"></i>Yes, Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@include('components.refresh-data-script', ['refreshButtonId' => 'prenatal-refresh-btn', 'skeletonId' => 'prenatal-skeleton', 'tableSelector' => '.overflow-x-auto table'])

@push('scripts')
    {{-- Unified Prenatal Records Module - Works for both BHW and Midwife --}}
    @vite('resources/js/shared/pages/prenatalrecords.js')
@endpush
