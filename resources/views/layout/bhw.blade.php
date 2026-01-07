<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Barangay Health Worker Dashboard') - Laravel</title>
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
        } elseif (str_contains($routeName, 'prenatal') || str_contains($routeName, 'maternal')) {
            $favicon = 'images/maternalhealth.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'patient')) {
            $favicon = 'images/medical.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'vaccine') || str_contains($routeName, 'immunization')) {
            $favicon = 'images/vaccine.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'child')) {
            $favicon = 'images/childrecord.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'user')) {
            $favicon = 'images/usermanagement.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'report')) {
            $favicon = 'images/report.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'cloudbackup')) {
            $favicon = 'images/cloudbackup.png';
            $faviconType = 'image/png';
        } elseif (str_contains($routeName, 'clinic') || str_contains($routeName, 'hospital')) {
            $favicon = 'images/clinic.png';
            $faviconType = 'image/png';
        }
    @endphp

    <link rel="icon" type="{{ $faviconType }}" href="{{ asset($favicon) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset($favicon) }}">
    <link rel="icon" type="{{ $faviconType }}" sizes="32x32" href="{{ asset($favicon) }}">
    <link rel="icon" type="{{ $faviconType }}" sizes="16x16" href="{{ asset($favicon) }}">
    <link rel="shortcut icon" href="{{ asset($favicon) }}">

    <!-- All assets bundled via Vite (Font Awesome, Flowbite, SweetAlert2, Alpine.js) -->
    
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

        /* Sidebar scrollbars blend with sidebar background */
        .sidebar-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #B8956A #D4A373;
        }

        .sidebar-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: #D4A373;
        }

        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background: #B8956A;
            border-radius: 4px;
        }

        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a1784f;
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

        /* All sidebar text elements */
        .sidebar-nav h1,
        .sidebar-nav p,
        .sidebar-nav span,
        .sidebar-nav .text-gray-300,
        .sidebar-nav .text-gray-400 {
            color: white !important; /* White text on green navbar */
        }

        /* Hover states */
        .sidebar-nav button:hover,
        .sidebar-nav .text-gray-300:hover {
            color: #f0f0f0 !important; /* Light gray on hover */
        }

        /* Warm Brown Sidebar and Cream Color Scheme */
        .bg-primary {
            background-color: #D4A373 !important; /* Warm brown for sidebar */
        }

        .bg-secondary {
            background-color: #ecb99e !important; /* Peach for buttons and accents */
        }

        .bg-neutral {
            background-color: #FFFFFF !important; /* White for main content background */
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

        /* ====================================
           Global Modal Background Fix
           ==================================== */
        .modal-overlay {
            transition: opacity 0.3s ease-out;
            background-color: rgba(17, 24, 39, 0) !important; /* Override any background */
        }

        .modal-overlay.hidden {
            opacity: 0;
            pointer-events: none;
            background-color: rgba(17, 24, 39, 0) !important;
        }

        .modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
            background-color: rgba(17, 24, 39, 0.5) !important; /* Semi-transparent dark overlay */
        }

        .modal-content {
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            transform: translateY(-20px) scale(0.95);
            opacity: 0;
        }

        .modal-overlay.show .modal-content {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        /* Notification Badge Styles */
        .notification-badge-count {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }

        /* Notification bell animation on new notification */
        @keyframes ring {
            0% { transform: rotate(0); }
            10% { transform: rotate(14deg); }
            20% { transform: rotate(-8deg); }
            30% { transform: rotate(14deg); }
            40% { transform: rotate(-4deg); }
            50% { transform: rotate(10deg); }
            60% { transform: rotate(0); }
            100% { transform: rotate(0); }
        }

        .ring-bell {
            animation: ring 0.8s ease-in-out;
        }

        /* SweetAlert2 Z-Index Fix - Ensure it appears above all modals */
        .swal2-container {
            z-index: 99999 !important;
        }

        .swal2-overlay {
            z-index: 99999 !important;
        }

        /* SweetAlert2 Global Button Styling */
        .swal2-confirm {
            background-color: #D4A373 !important;
            border: none !important;
            box-shadow: none !important;
        }

        .swal2-confirm:hover {
            background-color: #D4A373 !important;
            background-image: none !important;
        }

        .swal2-confirm:focus {
            background-color: #D4A373 !important;
            box-shadow: 0 0 0 3px rgba(212, 163, 115, 0.3) !important;
        }

        .swal2-confirm:active {
            background-color: #D4A373 !important;
        }

    </style>
    
    @stack('styles')

    <!-- SweetAlert2 CSS -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"> -->

    <!-- SweetAlert2 JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

    <!-- BHW SweetAlert Handler -->
    <script src="{{ asset('js/bhw/sweetalert-handler.js') }}"></script>

    @stack('scripts')


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
        <div class="sidebar-nav fixed inset-y-0 left-0 z-50 w-64 bg-primary text-gray-800 flex flex-col lg:static lg:inset-0"
             :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'transition-transform duration-300 ease-in-out': sidebarInitialized}"
             x-show="sidebarOpen"
             x-init="setTimeout(() => sidebarInitialized = true, 100); window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024; })"
              >
            <!-- TODO: Replace with DaisyUI navbar brand -->
            <!-- Original: div with border-b border-primary -->
            <div class="p-4 sm:p-6 border-b-2 border-gray-400" style="border-bottom: 2px solid #8B7355;">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col items-center text-center">
                        <img src="{{ asset('images/logo_final.jpg') }}"
                             alt="Healthcare Logo"
                             class="w-16 h-16 sm:w-20 sm:h-20 mb-2 object-cover rounded-full">
                        <div>
                            <h1 class="text-lg sm:text-xl font-bold">Baragay Mecolong Health Center</h1>
                            <p class="text-xs sm:text-sm text-gray-300 mt-1">Barangay Health Worker Portal</p>
                        </div>
                    </div>
                    <!-- Close button for mobile -->
                    <button @click="sidebarInitialized = true; sidebarOpen = false"
                            class="lg:hidden p-2 rounded-md text-gray-300 hover:text-white hover-cream transition-colors">
                        <i class="fas fa-times w-5 h-5"></i>
                    </button>
                </div>
            </div>
            
            <!-- TODO: Replace with DaisyUI menu component -->
            <!-- Original: nav with flex-1 p-4 -->
            <nav class="flex-1 p-3 sm:p-4 overflow-y-auto sidebar-scrollbar">
                <ul class="space-y-1 sm:space-y-2">
                    <li>
                        <a href="{{ route('dashboard') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('dashboard') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="dashboard"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-tachometer-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Patient Registration -->
                    <li>
                        <a href="{{ route('bhw.patients.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('bhw.patients.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="patients"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-user-plus w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Parent Registration
                        </a>
                    </li>

                    <!-- Prenatal Records -->
                    <li>
                        <a href="{{ route('bhw.prenatalrecord.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('bhw.prenatalrecord.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="prenatal"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-file-medical w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Prenatal Records
                        </a>
                    </li>

                    <!-- Child Records -->
                    <li>
                        <a href="{{ route('bhw.childrecord.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('bhw.childrecord.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="child-records"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-child w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Child Registration
                        </a>
                    </li>

                    <!-- SMS Logs -->
                    <li>
                        <a href="{{ route('bhw.sms-logs.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('bhw.sms-logs.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="sms-logs"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-sms w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            SMS Logs
                        </a>
                    </li>

                    <!-- Reports -->
                    <li>
                        <a href="{{ route('bhw.report') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('bhw.report*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="reports"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-chart-bar w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Reports
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- User Profile & Logout Section -->
            <div class="p-3 sm:p-4 border-t border-primary" style="border-color: #B8956A; background-color: #B8956A;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xs sm:text-sm font-semibold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'BHW', 0, 2)) }}</span>
                        </div>
                        <div class="ml-2 sm:ml-3 min-w-0">
                            <p class="text-xs sm:text-sm font-medium truncate text-white">{{ auth()->user()->name ?? 'BHW User' }}</p>
                            <p class="text-xs text-gray-200 truncate">{{ auth()->user()->role ?? 'Barangay Health Worker' }}</p>
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
        <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
            <!-- Header -->
            <header class="shadow-sm border-b p-3 sm:p-4" style="background-color: #FEFAE0;">
                <div class="flex justify-between items-center">
                    <div class="flex items-center min-w-0">
                        <!-- Mobile menu button -->
                        <button @click="sidebarInitialized = true; sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary mr-2 sm:mr-3">
                            <i class="fas fa-bars w-5 h-5"></i>
                        </button>

                        <div class="min-w-0">
                            <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 truncate" id="page-title">@yield('page-title', 'Dashboard Overview')</h2>
                            <p class="text-gray-600 text-xs sm:text-sm truncate" id="page-subtitle">@yield('page-subtitle', 'Monitor patient care and health records')</p>
                        </div>
                    </div>

                    <!-- Notifications Icon -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open; loadRecentNotifications()" class="p-2 text-gray-400 hover:text-gray-600 relative">
                            <i class="fas fa-bell w-6 h-6 sm:w-8 sm:h-8"></i>
                            <span id="notification-badge" class="notification-badge-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center hidden">0</span>
                        </button>

                        <!-- Notifications Dropdown -->
                        <div x-show="open" @click.outside="open = false"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                             style="display: none;">
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                                    <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View All
                                    </a>
                                </div>
                            </div>
                            <div id="recent-notifications" class="max-h-64 overflow-y-auto">
                                <div class="p-4 text-center text-gray-500">
                                    Loading notifications...
                                </div>
                            </div>
                            <div class="p-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                                <button onclick="markAllAsRead()" class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Mark All as Read
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-3 sm:p-4 lg:p-6 overflow-y-scroll custom-scrollbar bg-gray-50">
                <div class="content-wrapper">
                    <!-- Breadcrumb -->
                    @if(!request()->routeIs('dashboard'))
                    <nav class="mb-3 sm:mb-4">
                        <ol class="flex items-center space-x-1 sm:space-x-2 text-xs sm:text-sm text-gray-500">
                            <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700 truncate">Dashboard</a></li>
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

    <!-- Navigation Loading Scripts -->
    <script>
        // Navigation Loading Function
        function showNavigationLoading(event, linkElement) {
            // Don't show loading if navigating to the same page
            const currentPath = window.location.pathname;
            const linkPath = new URL(linkElement.href).pathname;

            if (currentPath === linkPath) {
                return; // Allow normal navigation to same page
            }

            // Show loading state immediately
            try {
                // Try to call page-specific skeleton functions if they exist
                if (typeof showSkeletonLoaders === 'function') {
                    showSkeletonLoaders();
                }

                // Generic loading indicator for all pages
                showGenericPageLoading();

                // Add loading indicator to navigation link
                const icon = linkElement.querySelector('i');

                if (icon) {
                    icon.className = 'fas fa-spinner fa-spin w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3';
                }

                // Add loading state to the link
                linkElement.style.opacity = '0.7';
                linkElement.style.pointerEvents = 'none';

                // Reset after a delay in case navigation is slow
                setTimeout(() => {
                    if (icon) {
                        // Restore original icon based on data-section
                        const section = linkElement.getAttribute('data-section');
                        const iconMap = {
                            'dashboard': 'fa-tachometer-alt',
                            'patients': 'fa-user-plus',
                            'prenatal': 'fa-file-medical',
                            'child-records': 'fa-child',
                            'sms-logs': 'fa-sms',
                            'reports': 'fa-chart-bar'
                        };

                        icon.className = `fas ${iconMap[section] || 'fa-circle'} w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3`;
                    }
                    linkElement.style.opacity = '';
                    linkElement.style.pointerEvents = '';
                }, 3000);

            } catch (error) {
                console.log('Loading indicator error:', error);
            }

            // Allow normal navigation to proceed
            return true;
        }

        // Generic page loading function that works on all pages
        function showGenericPageLoading() {
            // Target only the main content area, not the entire page
            const mainContent = document.querySelector('main .content-wrapper');
            if (mainContent) {
                // Create or show loading skeleton in main content area only
                let contentLoading = document.getElementById('main-content-loading');
                if (!contentLoading) {
                    contentLoading = document.createElement('div');
                    contentLoading.id = 'main-content-loading';
                    contentLoading.className = 'bg-white rounded-lg shadow-sm border border-gray-200 p-8';
                    contentLoading.innerHTML = `
                        <div class="animate-pulse space-y-6">
                            <!-- Header skeleton -->
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="h-8 bg-gray-200 rounded w-48 mb-2"></div>
                                    <div class="h-4 bg-gray-200 rounded w-64"></div>
                                </div>
                                <div class="flex space-x-3">
                                    <div class="h-10 bg-gray-200 rounded w-24"></div>
                                    <div class="h-10 bg-gray-200 rounded w-24"></div>
                                </div>
                            </div>

                            <!-- Search/Filter skeleton -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex gap-4">
                                    <div class="flex-1 h-10 bg-gray-200 rounded"></div>
                                    <div class="h-10 bg-gray-200 rounded w-32"></div>
                                    <div class="h-10 bg-gray-200 rounded w-32"></div>
                                    <div class="h-10 bg-gray-200 rounded w-20"></div>
                                </div>
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

        // Load page cleanup
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loading skeleton when page is fully loaded
            const contentLoading = document.getElementById('main-content-loading');
            if (contentLoading) {
                contentLoading.remove();
            }

            // Restore main content display
            const mainContent = document.querySelector('main .content-wrapper');
            if (mainContent) {
                mainContent.style.display = '';
                mainContent.style.opacity = '';
            }
        });
    </script>

    <script>
        // Real-time notification system for BHW
        let lastNotificationCheck = new Date().toISOString();

        function checkForNewNotifications() {
            fetch(`/notifications/new?last_check=${lastNotificationCheck}`)
                .then(response => response.json())
                .then(data => {
                    if (data.notifications && data.notifications.length > 0) {
                        // Update the last check timestamp
                        lastNotificationCheck = data.timestamp;

                        // Update notification count badge
                        loadNotificationCount();

                        // Animate the bell icon
                        const bellIcon = document.querySelector('.fa-bell');
                        if (bellIcon) {
                            bellIcon.classList.add('ring-bell');
                            setTimeout(() => bellIcon.classList.remove('ring-bell'), 800);
                        }

                        // Play notification sound if available
                        try {
                            const audio = new Audio('data:audio/wav;base64,UklGRnQDAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YVAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
                            audio.volume = 0.3;
                            audio.play().catch(() => {}); // Ignore errors if audio can't play
                        } catch (e) {
                            // Ignore audio errors
                        }
                    } else {
                        // Update timestamp even if no new notifications
                        lastNotificationCheck = data.timestamp;
                    }
                })
                .catch(error => console.error('Error checking for new notifications:', error));
        }

        function showNotificationToast(notification) {
            const notificationData = notification.data || {};
            const type = notificationData.type || 'info';
            const title = notificationData.title || 'New Notification';
            const message = notificationData.message || '';
            const user = notificationData.notified_by || 'System';

            // Map notification types to toast types
            const toastType = type === 'success' ? 'success' :
                              type === 'error' ? 'error' :
                              type === 'warning' ? 'warning' : 'info';

            // Show toast notification
            if (window.flowbiteToast) {
                window.flowbiteToast[toastType](`${title}: ${message}`, {
                    duration: 8000,
                    position: 'top-right'
                });
            }
        }

        // Load notification count and update badge
        function loadNotificationCount() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                })
                .catch(error => console.error('Error loading notification count:', error));
        }

        // Load recent notifications for dropdown
        function loadRecentNotifications() {
            fetch('/notifications/recent')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recent-notifications');
                    if (container && data.notifications) {
                        if (data.notifications.length === 0) {
                            container.innerHTML = '<div class="p-4 text-center text-gray-500">No new notifications</div>';
                        } else {
                            container.innerHTML = data.notifications.map(notification => {
                                const type = notification.data.type || 'info';
                                const iconClass = type === 'success' ? 'fa-check-circle text-green-500' :
                                                 type === 'error' ? 'fa-exclamation-circle text-red-500' :
                                                 type === 'warning' ? 'fa-exclamation-triangle text-yellow-500' :
                                                 'fa-info-circle text-blue-500';

                                return `
                                    <div class="p-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer" onclick="window.location.href='${notification.data.action_url || '/notifications'}'">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas ${iconClass} mr-2"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">
                                                    ${notification.data.title || 'Notification'}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    ${notification.data.message || ''}
                                                </p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    ${formatDate(notification.created_at)}
                                                </p>
                                            </div>
                                            ${!notification.read_at ? '<div class="w-2 h-2 bg-blue-500 rounded-full"></div>' : ''}
                                        </div>
                                    </div>
                                `;
                            }).join('');
                        }
                    }
                })
                .catch(error => console.error('Error loading recent notifications:', error));
        }

        // Mark all notifications as read
        function markAllAsRead() {
            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotificationCount();
                    loadRecentNotifications();

                    // Show success toast
                    if (window.flowbiteToast) {
                        window.flowbiteToast.success('All notifications marked as read');
                    }
                }
            })
            .catch(error => console.error('Error marking notifications as read:', error));
        }

        // Format date helper function
        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        // Start real-time notification checking when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Load initial notification count
            loadNotificationCount();

            // Check for new notifications every 10 seconds
            setInterval(checkForNewNotifications, 10000);

            // Update notification count every 30 seconds
            setInterval(loadNotificationCount, 30000);
        });
    </script>

    @stack('scripts')

    {{-- Include SweetAlert Flash Messages --}}
    @include('components.sweetalert-flash')

    {{-- Include Global Confirmation Modal --}}
    @include('components.confirmation-modal')

    {{-- Include Toast Notification System --}}
    @include('components.toast-notification')

    {{-- Include Modal Form Reset System --}}
    @include('components.modal-form-reset')
</body>
</html>