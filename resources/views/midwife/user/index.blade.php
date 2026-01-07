@extends('layout.midwife')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage system users and roles')

@push('styles')
    <!-- User Management Module Styles -->
    <link href="{{ asset('css/modules/user-management.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="space-y-6">
     

    <!-- Header Actions --> 
<div class="flex justify-between items-center mb-6">
    <div>
        <!-- Statistics Cards 
        <div id="stats-container" class="flex space-x-4">
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-primary">{{ $users->total() ?? 0 }}</div>
                <div class="text-sm text-gray-600">Total Users</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-green-600">{{ $users->where('is_active', true)->count() ?? 0 }}</div>
                <div class="text-sm text-gray-600">Active Users</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-red-600">{{ $users->where('is_active', false)->count() ?? 0 }}</div>
                <div class="text-sm text-gray-600">Inactive Users</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-blue-600">{{ $users->where('role', 'Midwife')->count() ?? 0 }}</div>
                <div class="text-sm text-gray-600">Midwives</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-purple-600">{{ $users->where('role', 'BHW')->count() ?? 0 }}</div>
                <div class="text-sm text-gray-600">BHWs</div>
            </div>
        </div>-->
        
        <!-- Statistics Skeleton -->
        <div id="stats-skeleton" class="hidden flex space-x-4">
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
        </div>
    </div>
    <div class="flex space-x-3">
        <button onclick="openAddModal()"
            class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-all duration-200 flex items-center space-x-2">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            <span>Add User</span>
        </button>
    </div>
</div>

    <!-- Search and Filters -->
    <!-- Add this to your search and filters section (around line 186) -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-4 sm:p-6">
        <form method="GET" action="{{ route('midwife.user.index') }}" class="search-form flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, username..." 
                           class="input-clean w-full pl-10 pr-4 py-2.5 rounded-lg">
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 search-controls">
                <select name="role" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                    <option value="">All Roles</option>
                    <option value="Midwife" {{ request('role') == 'Midwife' ? 'selected' : '' }}>Midwife</option>
                    <option value="BHW" {{ request('role') == 'BHW' ? 'selected' : '' }}>BHW</option>
                </select>
                <select name="gender" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                    <option value="">All Genders</option>
                    <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
                <!-- NEW STATUS FILTER -->
                <select name="status" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" onclick="showSkeletonLoaders()" class="btn-minimal px-4 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all duration-200">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('midwife.user.index') }}" class="btn-minimal px-4 py-2.5 text-gray-600 border border-gray-300 rounded-lg text-center">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table Skeleton -->
        <div id="table-skeleton" class="hidden">
            <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-4 animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-20"></div>
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                        <div class="h-4 bg-gray-200 rounded w-12"></div>
                        <div class="h-4 bg-gray-200 rounded w-14"></div>
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                        <div class="h-4 bg-gray-200 rounded w-20"></div>
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @for($i = 0; $i < 5; $i++)
                <div class="px-4 py-3 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="h-4 bg-gray-200 rounded w-32"></div>
                            <div class="h-4 bg-gray-200 rounded w-24"></div>
                            <div class="h-6 bg-gray-200 rounded-full w-20"></div>
                            <div class="h-6 bg-gray-200 rounded-full w-16"></div>
                            <div class="h-4 bg-gray-200 rounded w-20"></div>
                            <div class="h-4 bg-gray-200 rounded w-28"></div>
                        </div>
                        <div class="flex space-x-2">
                            <div class="h-8 bg-gray-200 rounded w-12"></div>
                            <div class="h-8 bg-gray-200 rounded w-12"></div>
                            <div class="h-8 bg-gray-200 rounded w-20"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Actual Table Content -->
        <div id="table-content">
        @if($users->count() > 0)
            <div class="table-wrapper">
            <table class="w-full table-container">
    <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                    Full Name <i class="fas fa-sort ml-1 text-gray-400"></i>
                </a>
            </th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Username</th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Role</th>
            <!-- NEW STATUS COLUMN -->
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_active', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                    Status <i class="fas fa-sort ml-1 text-gray-400"></i>
                </a>
            </th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Gender</th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Contact</th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @foreach($users as $user)
        <tr class="table-row-hover">
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <div class="font-medium text-gray-900">{{ $user->name ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500 sm:hidden">{{ $user->username ?? 'N/A' }}</div>
            </td>
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <div class="text-sm sm:text-base text-gray-700">{{ $user->username ?? 'N/A' }}</div>
                <div class="text-xs text-gray-500 sm:hidden">{{ $user->contact_number ?? 'N/A' }}</div>
            </td>
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ ($user->role ?? '') === 'Midwife' ? 'role-badge-midwife' : 'role-badge-bhw' }}">
                    <span class="hidden sm:inline">{{ ucfirst($user->role ?? 'N/A') }}</span>
                    <span class="sm:hidden">{{ substr(ucfirst($user->role ?? 'N'), 0, 1) }}</span>
                </span>
            </td>
            <!-- NEW STATUS COLUMN -->
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    <i class="fas {{ $user->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                    <span class="hidden sm:inline">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="sm:hidden">{{ $user->is_active ? 'A' : 'I' }}</span>
                </span>
            </td>
            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ ucfirst($user->gender ?? '') === 'Male' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                    {{ ucfirst($user->gender ?? 'N/A') }}
                </span>
            </td>
            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                {{ $user->contact_number ?? 'N/A' }}
            </td>
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <div class="action-buttons flex flex-col sm:flex-row sm:justify-center space-y-2 sm:space-y-0 sm:space-x-2">
                    <a href="#" onclick='openViewUserModal(@json($user->toArray()))' class="btn-action btn-view inline-flex items-center justify-center">
                        <i class="fas fa-eye mr-1"></i><span class="hidden sm:inline"> </span>
                    </a>
                    <a href="#" onclick='openEditUserModal(@json($user->toArray()))' class="btn-action btn-edit inline-flex items-center justify-center">
                        <i class="fas fa-edit mr-1"></i><span class="hidden sm:inline"> </span>
                    </a>
                    <!-- UPDATED ACTION BUTTONS -->
                    @if($user->is_active)
                        <button onclick="confirmDeactivate('{{ $user->name }}', function() { deactivateUser({{ $user->id }}) })" class="btn-action btn-deactivate inline-flex items-center justify-center bg-orange-100 text-orange-700 border-orange-200 hover:bg-orange-500 hover:text-white hover:border-orange-500">
                            <i class="fas fa-user-slash mr-1"></i><span class="hidden sm:inline"> </span>
                        </button>
                    @else
                        <button onclick="confirmActivate('{{ $user->name }}', function() { activateUser({{ $user->id }}) })" class="btn-action btn-activate inline-flex items-center justify-center bg-green-100 text-green-700 border-green-200 hover:bg-green-500 hover:text-white hover:border-green-500">
                            <i class="fas fa-user-check mr-1"></i><span class="hidden sm:inline"> </span>
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
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 overflow-x-auto">
                {{ $users->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                    @if(request()->hasAny(['search', 'role', 'gender']))
                        No users match your search criteria. Try adjusting your filters.
                    @else
                        Get started by adding your first user.
                    @endif
                </p>
                <button onclick="openAddModal()" class="btn-minimal btn-primary-clean px-6 py-3 rounded-lg font-medium inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add User
                </button>
            </div>
        @endif
        </div> <!-- Close table-content div -->
    </div>
</div>

<!-- Add/Edit User Modal -->
@include('partials.user.addform')

<!-- View User Modal -->
@include('partials.user.userview')

{{-- Note: Confirmation modal is already included in layout.midwife --}}

@endsection

@push('scripts')
    <!-- Laravel Routes Configuration for JavaScript -->
    <script>
        window.userManagementRoutes = {
            store: '{{ route("midwife.user.store") }}',
            update: '{{ route("midwife.user.update", ":id") }}',
            deactivate: '{{ route("midwife.user.deactivate", ":id") }}',
            activate: '{{ route("midwife.user.activate", ":id") }}'
        };
    </script>

    <!-- User Management Module JavaScript -->
    <script src="{{ asset('js/midwife/user-index.js') }}"></script>

    <!-- Modular User Management JavaScript - ES6 Modules -->
    <script type="module" src="{{ asset('js/modules/usermanagement/index.js') }}"></script>

    <!-- Fallback to monolithic version for older browsers -->
    <script nomodule src="{{ asset('js/modules/user-management.js') }}"></script>
@endpush