{{-- Shared Patients Index View - Works for both Midwife and BHW --}}
@extends('layout.' . auth()->user()->role)

@section('title', 'Parent Management')
@section('page-title', 'Parent Management')
@section('page-subtitle', 'Manage parent basic information')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/' . auth()->user()->role . '/' . auth()->user()->role . '.css') }}">
<link rel="stylesheet" href="@roleCss('patients-index.css')">
@endpush

@section('content')
<div class="space-y-6">
    {{-- Success/Error Messages --}}
    @include('components.flowbite-alert')

    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
         <div> </div>
        <div class="flex space-x-3">
            <button onclick="openPatientModal()" class="bg-secondary text-white px-4 py-2 rounded-lg hover:bg-hover-color transition-all duration-200 flex items-center btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Register New Parent
            </button>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="@roleRoute('patients.index')">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-secondary text-white px-4 py-2 rounded-lg hover:bg-hover-color transition-all duration-200 btn-primary">
                        Search
                    </button>
                    <a href="@roleRoute('patients.index')" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Include Table Skeleton -->
    @include('components.table-skeleton', [
        'id' => auth()->user()->role . '-patient-table-skeleton',
        'rows' => 5,
        'columns' => 7,
        'showStats' => false
    ])

    <!-- Patients Table -->
    <div id="{{ auth()->user()->role }}-patient-main-content" class="bg-white rounded-lg shadow-sm border">
        <div class="overflow-x-auto">
            <table class="patients-table w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prenatal Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($patients as $patient)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $patient->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient->age }} years</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $patient->contact ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Str::limit($patient->address ?? 'N/A', 30) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="text-lg font-semibold">{{ $patient->total_prenatal_records }}</span>
                            @if($patient->has_active_prenatal_record)
                                <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Active</span>
                            @else
                                <span class="ml-2 text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($patient->is_high_risk_patient)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    High Risk Age
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Normal
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="@roleRoute('patients.profile', $patient->id)" class="btn-action btn-view inline-flex items-center justify-center">
                                    <i class="fas fa-eye mr-1"></i>
                                    <span class="hidden sm:inline">View</span>
                                </a>
                                <button data-patient='@json($patient)' onclick='openEditPatientModal(JSON.parse(this.dataset.patient))' class="btn-action btn-edit inline-flex items-center justify-center">
                                    <i class="fas fa-edit mr-1"></i>
                                    <span class="hidden sm:inline">Edit</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-2">No parents found</p>
                                <p class="text-gray-600 mb-4">Get started by registering your first parent</p>
                                <button onclick="openPatientModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors btn-primary">
                                    Register First Parent
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @include('components.pagination', ['paginator' => $patients])
    </div>
</div>

<!-- Modals - Using Shared Partials -->
@include('partials.shared.patient.patient_add')
@include('partials.shared.patient.patient_view')
@include('partials.shared.patient.patient_edit')

@endsection

@push('scripts')
{{-- Load old JavaScript implementation from public folder --}}
<script src="{{ asset('js/' . auth()->user()->role . '/patients-index.js') }}"></script>
@endpush
