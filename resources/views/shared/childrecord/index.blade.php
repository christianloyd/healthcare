@extends('layout.' . auth()->user()->role)
@section('title', 'Child Records')
@section('page-title', 'Child Records')
@section('page-subtitle', 'Manage and monitor child health records')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script type="module" src="https://unpkg.com/cally"></script>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/' . auth()->user()->role . '/' . auth()->user()->role . '.css') }}">
<link rel="stylesheet" href="{{ asset('css/' . auth()->user()->role . '/childrecord-index.css') }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
        <div>

        </div>
        <div class="flex space-x-3">
            <a href="{{ route(auth()->user()->role . '.childrecord.create') }}"
                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-all duration-200 flex items-center btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add Record
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-4 sm:p-6">
            <form method="GET" action="{{ route(auth()->user()->role . '.childrecord.index') }}" class="search-form flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by child name"
                               class="input-clean w-full pl-10 pr-4 py-2.5 rounded-lg">
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 search-controls">
                    <select name="gender" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                        <option value="">All Genders</option>
                        <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                    <button type="submit" class="btn-minimal px-4 py-2.5 bg-secondary text-white rounded-lg hover:bg-hover-color transition-colors">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="{{ route(auth()->user()->role . '.childrecord.index') }}" class="btn-minimal px-4 py-2.5 text-gray-600 border border-gray-300 rounded-lg text-center">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Include Table Skeleton -->
    @include('components.table-skeleton', [
        'id' => 'child-table-skeleton',
        'rows' => 5,
        'columns' => 6,
        'showStats' => false
    ])

    <!-- Records Table -->
    <div id="child-main-content" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Loading indicator -->
        <div id="search-loading" class="hidden">
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-secondary"></div>
                <span class="ml-3 text-gray-600">Searching...</span>
            </div>
        </div>

        <!-- Table content -->
        <div id="table-content">
            @include('shared.childrecord.table', ['childRecords' => $childRecords])
        </div>
    </div>
</div>



<!-- Edit Modal -->
    @include('partials.shared.childrecord.childedit')
@endsection

@push('scripts')
{{-- Configuration for child record management --}}
<script>
    window.CHILDRECORD_CONFIG = {
        searchRoute: '{{ route(auth()->user()->role . ".childrecord.search") }}'
    };
</script>

<script src="{{ asset('js/' . auth()->user()->role . '/' . auth()->user()->role . '.js') }}"></script>
<script src="{{ asset('js/' . auth()->user()->role . '/childrecord-index.js') }}"></script>
@endpush
