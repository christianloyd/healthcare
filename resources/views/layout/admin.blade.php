<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Dynamic Favicon -->
    @php
        $favicon = 'images/healthcare.png'; // default
        $faviconType = 'image/png';

        // Get current route to determine appropriate favicon
        $routeName = Route::currentRouteName();

        if (str_contains($routeName, 'dashboard')) {
            $favicon = 'images/dash.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'user')) {
            $favicon = 'images/usermanagement.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'patient')) {
            $favicon = 'images/medical.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'record')) {
            $favicon = 'images/medical.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'cloudbackup')) {
            $favicon = 'images/cloudbackup.png';
            $faviconType = 'image/png';
        }
    @endphp

    <link rel="icon" type="{{ $faviconType }}" href="{{ asset($favicon) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset($favicon) }}">
    <link rel="icon" type="{{ $faviconType }}" sizes="32x32" href="{{ asset($favicon) }}">
    <link rel="icon" type="{{ $faviconType }}" sizes="16x16" href="{{ asset($favicon) }}">
    <link rel="shortcut icon" href="{{ asset($favicon) }}">

    <!-- All assets bundled via Vite (Font Awesome, Flowbite, Alpine.js) -->

    <style>
        /* Import Inter font for system-wide use - optimized weights only */
        /* @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'); */

        /* Apply Inter font system-wide */
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Fix layout shaking by ensuring consistent scrollbar behavior */
        html {
            overflow-y: scroll; /* Always show vertical scrollbar space */
            scroll-behavior: smooth;
        }

        body {
            overflow-x: hidden; /* Prevent horizontal scroll */
        }

        /* Custom scrollbar styling */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f3f4f6;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f3f4f6;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Ensure layout stability */
        .main-container {
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Prevent content jumping during navigation */
        .content-wrapper {
            min-height: calc(100vh - 120px); /* Adjust based on header height */
        }

        /* Prevent layout shifts during transitions */
        * {
            box-sizing: border-box;
        }

        /* Smooth transitions for all elements */
        .transition-all {
            transition-duration: 150ms !important;
        }

        /* Prevent transform origin issues */
        [class*="transform"] {
            transform-origin: center;
        }

        /* Ensure consistent width calculations */
        .w-64 {
            width: 16rem !important;
        }

        /* Fix navigation menu button movements */
        .nav-link {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            text-decoration: none !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
            transform: none !important;
        }

        .nav-link:hover,
        .nav-link:focus,
        .nav-link:active {
            transform: none !important;
            outline: none !important;
        }

        /* Prevent any button movement or jumping */
        nav a, nav button {
            transform: none !important;
            will-change: auto !important;
        }

        /* Ensure sidebar stays fixed during navigation */
        .sidebar-nav {
            position: fixed !important;
            transform: none !important;
        }

        /* Stop all transforms and animations on navigation */
        nav *, nav i, .fas, .fa {
            transform: none !important;
            animation: none !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
        }

        /* Prevent flash of unstyled content */
        main {
            opacity: 1;
            visibility: visible;
        }

        /* Ensure sidebar appears instantly on desktop without animation */
        @media (min-width: 1024px) {
            .sidebar-nav {
                position: static !important;
                transform: translateX(0) !important;
                opacity: 1 !important;
                visibility: visible !important;
                display: flex !important;
            }
        }

        /* Prevent initial animation flash */
        .sidebar-nav:not(.transition-transform) {
            transition: none !important;
        }

        /* All navigation text must be BLACK for consistency - but allow active states to override */
        .sidebar-nav a:not(.bg-primary),
        .sidebar-nav li a:not(.bg-primary),
        .sidebar-nav .nav-link:not(.bg-primary),
        nav a:not(.bg-primary),
        nav ul li a:not(.bg-primary) {
            color: #000000 !important; /* Pure black text */
            opacity: 1 !important;
            visibility: visible !important;
            font-weight: 500 !important;
        }

        /* Icons should also be black - but allow active states to override */
        .sidebar-nav i:not(.bg-primary i),
        .nav-link:not(.bg-primary) i,
        nav i:not(.bg-primary i) {
            color: #000000 !important;
        }

        /* Active icons stay white */
        .sidebar-nav .nav-link.bg-primary i,
        nav .nav-link.bg-primary i {
            color: white !important;
        }

        /* BHW Theme Colors for Admin */
        .bg-primary {
            background-color: #D4A373 !important; /* Warm brown for sidebar */
        }

        .bg-secondary {
            background-color: #D4A373 !important; /* Warm brown for sidebar */
        }

        .bg-header {
            background-color: #FEFAE0 !important; /* Near-white ivory for header */
        }

        /* Custom hover states */
        .hover-cream:hover {
            background-color: #e2e8f0 !important; /* Soft cream beige for hover */
            color: #000000 !important;
            font-weight: 600 !important;
        }

        /* Active state that matches hover - this stays persistent when clicked */
        .nav-active {
            background-color: #e2e8f0 !important; /* Same as hover color */
            color: #000000 !important;
            font-weight: 600 !important;
        }

        /* Override the bg-primary for active nav links to use consistent styling */
        .nav-link.bg-primary {
            background-color: #e2e8f0 !important; /* Match hover color for consistency */
            color: #000000 !important; /* Black text for readability */
            font-weight: 600 !important;
        }

        /* Admin text colors for better visibility on warm brown */
        .sidebar-nav a,
        .sidebar-nav li a,
        nav a,
        nav ul li a {
            color: #1f2937 !important; /* Dark gray text on warm brown sidebar */
            opacity: 1 !important;
            visibility: visible !important;
            font-weight: 600 !important;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: window.innerWidth >= 1024, sidebarInitialized: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 lg:hidden bg-gray-600 bg-opacity-75"
             @click="sidebarOpen = false"
              ></div>

        <!-- Left Sidebar Navigation -->
        <div class="sidebar-nav fixed inset-y-0 left-0 z-50 w-64 bg-secondary text-gray-800 flex flex-col lg:static lg:inset-0"
             :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'transition-transform duration-300 ease-in-out': sidebarInitialized}"
             x-show="sidebarOpen"
             x-init="setTimeout(() => sidebarInitialized = true, 100); window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024; })"
              >

            <div class="p-4 sm:p-6 border-b-2 border-gray-400" style="border-bottom: 2px solid #1e3a5f;">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col items-center text-center">
                        <img src="{{ asset('images/logo_final.jpg') }}"
                             alt="Healthcare Logo"
                             class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-2 border-white shadow-lg mb-2 sm:mb-3">
                        <h1 class="text-sm sm:text-lg font-bold text-white truncate">MATERNAL & CHILD CARE</h1>
                        <p class="text-xs sm:text-sm text-gray-300 mt-1">Healthcare Management System</p>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500 text-white">
                                <i class="fas fa-user-shield mr-1"></i>
                                Administrator
                            </span>
                        </div>
                    </div>

                    <!-- Mobile Close Button -->
                    <button @click="sidebarInitialized = true; sidebarOpen = false"
                            class="lg:hidden p-2 rounded-md text-gray-300 hover:text-white hover-cream transition-colors">
                        <i class="fas fa-times w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <nav class="flex-1 p-3 sm:p-4 overflow-y-auto">
                <ul class="space-y-1 sm:space-y-2">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('admin.dashboard') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="dashboard"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-tachometer-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Users -->
                    <li>
                        <a href="{{ route('admin.user.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('admin.users.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="users"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-users w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            User Management
                        </a>
                    </li>

                    <!-- Patients -->
                    <li>
                        <a href="{{ route('admin.patients.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('admin.patients.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="patients"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-user-plus w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Patient Registry
                        </a>
                    </li>

                    <!-- Records -->
                    <li>
                        <a href="{{ route('admin.records.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('admin.records.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="records"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-clipboard-list w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Medical Records
                        </a>
                    </li>

                    <!-- Cloud Backup -->
                    {{-- <li>
                        <a href="{{ route('admin.cloudbackup.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('admin.cloudbackup.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="cloud-backup"
                           onclick="showNavigationLoading(event, this)">
                           <i class="fas fa-cloud-upload-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Cloud Backup
                        </a>
                    </li> --}}
                </ul>
            </nav>

            <!-- User Profile Section -->
            <div class="p-3 sm:p-4 border-t border-primary" style="border-color: #B8956A; background-color: #B8956A;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xs sm:text-sm font-semibold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'ADMIN', 0, 2)) }}</span>
                        </div>
                        <div class="ml-2 sm:ml-3 min-w-0">
                            <p class="text-xs sm:text-sm font-medium truncate text-white">{{ auth()->user()->name ?? 'Administrator' }}</p>
                            <p class="text-xs text-gray-200 truncate">System Administrator</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-white hover:text-gray-200 hover-cream rounded-lg transition-colors flex-shrink-0" title="Logout">
                            <i class="fas fa-sign-out-alt w-4 h-4 sm:w-5 sm:h-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header - NOW VISIBLE ON ALL SCREEN SIZES -->
            <header class="shadow-sm border-b border-gray-200 p-3 sm:p-4" style="background-color: #FEFAE0;">
                <div class="flex justify-between items-center">
                    <div class="flex items-center min-w-0">
                        <!-- Mobile menu button -->
                        <button type="button"
                                class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500 mr-2 sm:mr-3"
                                @click="sidebarOpen = !sidebarOpen">
                            <i class="fas fa-bars w-5 h-5"></i>
                        </button>

                        <div class="min-w-0">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-user-shield text-red-600 text-xl"></i>
                                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent truncate" id="page-title">@yield('page-title', 'Admin Panel')</h2>
                            </div>
                            @if(trim($__env->yieldContent('page-subtitle')))
                                <p class="text-gray-600 text-xs sm:text-sm truncate ml-8" id="page-subtitle">@yield('page-subtitle')</p>
                            @endif
                        </div>
                    </div>

                    
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 custom-scrollbar bg-gray-50 main-container content-wrapper">
                <div class="max-w-7xl mx-auto">
                    <!-- Breadcrumb -->
                    @if(!request()->routeIs('admin.dashboard'))
                    <nav class="mb-3 sm:mb-4">
                        <ol class="flex items-center space-x-1 sm:space-x-2 text-xs sm:text-sm text-gray-500">
                            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700 truncate">Dashboard</a></li>
                            <li><span>/</span></li>
                            <li class="text-gray-700 font-medium truncate">@yield('page-title', 'Current Page')</li>
                        </ol>
                    </nav>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

                   <!-- Alpine.js and Flowbite loaded via Vite -->

    <script>
        // Navigation loading functionality
        function showNavigationLoading(event, linkElement) {
            // Don't show loading for certain sections that should be instant
            const section = linkElement.getAttribute('data-section');
            const instantSections = ['dashboard'];

            if (instantSections.includes(section)) {
                return true; // Allow normal navigation
            }

            // Show generic loading state for page
            showGenericPageLoading();

            // Add loading indicator to navigation link
            const icon = linkElement.querySelector('i');
            const originalText = linkElement.textContent.trim();

            if (icon) {
                icon.className = 'fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3';
            }

            // Allow the navigation to proceed
            return true;
        }

        function showGenericPageLoading() {
            const mainContent = document.querySelector('main .max-w-7xl');

            if (mainContent) {
                let contentLoading = document.getElementById('content-loading');

                if (!contentLoading) {
                    contentLoading = document.createElement('div');
                    contentLoading.id = 'content-loading';
                    contentLoading.className = 'animate-pulse space-y-6 p-6';
                    contentLoading.innerHTML = `
                        <div class="space-y-3">
                            <!-- Header skeleton -->
                            <div class="flex items-center justify-between">
                                <div class="flex space-x-4">
                                    <div class="h-8 bg-gray-200 rounded w-32"></div>
                                    <div class="h-8 bg-gray-200 rounded w-24"></div>
                                </div>
                                <div class="h-10 bg-gray-200 rounded w-20"></div>
                            </div>

                            <!-- Table skeleton -->
                            <div class="space-y-3">
                                <div class="h-12 bg-gray-200 rounded"></div>
                                <div class="h-12 bg-gray-200 rounded"></div>
                                <div class="h-12 bg-gray-200 rounded"></div>
                                <div class="h-12 bg-gray-200 rounded"></div>
                                <div class="h-12 bg-gray-200 rounded"></div>
                            </div>
                        </div>
                    `;

                    // Hide original content and show skeleton
                    mainContent.style.display = 'none';
                    mainContent.parentNode.insertBefore(contentLoading, mainContent);
                } else {
                    contentLoading.classList.remove('hidden');
                    mainContent.style.display = 'none';
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>