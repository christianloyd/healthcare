<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Midwife Dashboard')</title> 
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

    <!-- Midwife SweetAlert Handler -->
    <script src="{{ asset('js/midwife/sweetalert-handler.js') }}"></script>

    <style>
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
            box-shadow: none !important;
        }

        .swal2-confirm:active {
            background-color: #D4A373 !important;
        }

        /* Notification Badge and Bell Ring Animation */
        .notification-badge-count {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }

        .ring-bell {
            animation: ring 0.8s ease-in-out;
        }

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
        /* Apply Inter font system-wide with better fallbacks */
        * {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* CRITICAL FIX: Prevent layout shift during font loading */
        @font-face {
            font-family: 'Inter-fallback';
            size-adjust: 108.5%;
            ascent-override: 90%;
            src: local('Arial');
        }

        body {
            font-family: 'Inter', 'Inter-fallback', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* CRITICAL FIX: Ensure consistent text metrics */
        * {
            text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        
        /* CRITICAL FIX: Fix layout shaking by ensuring consistent scrollbar behavior */
        html {
            overflow-y: scroll; /* Always show vertical scrollbar space */
            scroll-behavior: smooth;
        }
        
        body {
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        
        /* Custom scrollbar styling - matches layout background colors */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #f9fafb #f9fafb;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f9fafb;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #f3f4f6;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #e5e7eb;
        }
        
        /* CRITICAL FIX: Ensure layout stability */
        .main-container {
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* CRITICAL FIX: Prevent content jumping during navigation */
        .content-wrapper {
            min-height: calc(100vh - 120px);
        }
        
        /* CRITICAL FIX: Prevent layout shifts during transitions */
        * {
            box-sizing: border-box;
        }
        
        /* CRITICAL FIX: Remove all problematic animations and transforms */
        .transition-all {
            transition: none !important;
        }
        
        /* CRITICAL FIX: Prevent transform origin issues */
        [class*="transform"] {
            transform: none !important;
        }
        
        /* CRITICAL FIX: Ensure consistent width calculations */
        .w-64 {
            width: 16rem !important;
        }
        
        /* CRITICAL FIX: Fix navigation menu button movements - REMOVE ALL TRANSFORMS */
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
        
        /* CRITICAL FIX: Prevent any button movement or jumping */
        nav a, nav button, .sidebar-nav a, .sidebar-nav button {
            transform: none !important;
            will-change: auto !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
        }
        
        /* CRITICAL FIX: Ensure sidebar stays completely static */
        .sidebar-nav {
            transform: none !important;
            transition: none !important;
        }
        
        /* CRITICAL FIX: Stop ALL transforms and animations on navigation */
        nav *, nav i, .fas, .fa, .sidebar-nav *, .sidebar-nav i {
            transform: none !important;
            animation: none !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
        }
        
        /* CRITICAL FIX: Prevent flash of unstyled content */
        main {
            opacity: 1;
            visibility: visible;
        }

        /* CRITICAL FIX: Ensure sidebar appears instantly on desktop without ANY animation */
        @media (min-width: 1024px) {
            .sidebar-nav {
                position: static !important;
                transform: none !important;
                opacity: 1 !important;
                visibility: visible !important;
                display: flex !important;
                transition: none !important;
            }
        }

        /* CRITICAL FIX: Remove ALL transitions from sidebar */
        .sidebar-nav, .sidebar-nav * {
            transition: none !important;
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
        

        /* CRITICAL FIX: Navigation Group Styles - Remove problematic animations */
        .nav-group-toggle {
            cursor: pointer;
            user-select: none;
        }

        .nav-submenu {
            border-left: 2px solid #e5e7eb;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .nav-submenu .nav-link {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            transition: background-color 0.2s ease, color 0.2s ease !important;
        }

        .nav-submenu .nav-link.bg-primary {
            background-color: #D4A373 !important; /* Use explicit color instead of CSS variable */
           
        }

        /* CRITICAL FIX: Remove chevron rotation animation */
        .nav-group-toggle i {
            transition: none !important;
            transform: none !important;
        }

        /* CRITICAL FIX: Remove modal overlay animations that cause layout shifts */
        .modal-overlay {
            transition: none !important;
            background-color: rgba(17, 24, 39, 0) !important;
        }

        .modal-overlay.hidden {
            opacity: 0;
            pointer-events: none;
            background-color: rgba(17, 24, 39, 0) !important;
        }

        .modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
            background-color: rgba(17, 24, 39, 0.5) !important;
        }

        .modal-content {
            transition: none !important;
            transform: none !important;
            opacity: 1;
        }

        /* CRITICAL FIX: Laptop-specific optimizations - Remove transforms */
        @media screen and (min-width: 1024px) {
            .sidebar-nav {
                position: static !important;
                transform: none !important;
                display: flex !important;
                transition: none !important;
            }

            .lg\\:hidden {
                display: none !important;
            }
        }

        /* CRITICAL FIX: Prevent Alpine.js from causing layout shifts */
        [x-cloak] { display: none !important; }

        /* BHW Theme Colors for Midwife */
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

        /* All navigation text must be BLACK for consistency - but allow active states to override */
        .sidebar-nav a:not(.bg-primary),
        .sidebar-nav li a:not(.bg-primary),
        .sidebar-nav .nav-link:not(.bg-primary),
        .nav-group-toggle:not(.bg-primary):not(.bg-blue-100),
        .nav-submenu .nav-link:not(.bg-primary) {
            color: #000000 !important; /* Pure black text */
            opacity: 1 !important;
            visibility: visible !important;
            font-weight: 500 !important;
        }

        /* Active navigation links - use consistent active styling */
        .sidebar-nav .nav-link.bg-primary,
        .nav-submenu .nav-link.bg-primary {
            background-color: #e2e8f0 !important; /* Match hover color for consistency */
            color: #000000 !important; /* Black text for readability */
            font-weight: 600 !important;
        }

        /* Navigation group buttons - active state matches hover */
        .nav-group-toggle.bg-blue-100 {
            background-color: #e2e8f0 !important; /* Match hover color */
            color: #000000 !important;
            font-weight: 600 !important;
        }

        /* Header text - black */
        .sidebar-nav h1 {
            color: #000000 !important;
            font-weight: 700 !important;
        }

        .sidebar-nav p {
            color: #000000 !important;
            opacity: 0.8;
        }

        /* Icons should also be black - but allow active states to override */
        .sidebar-nav i:not(.bg-primary i),
        .nav-link:not(.bg-primary) i,
        .nav-group-toggle:not(.bg-primary):not(.bg-blue-100) i {
            color: #000000 !important;
        }

        
        
        /* CRITICAL FIX: Force stable layout during page transitions */
        body.loading * {
            pointer-events: none;
        }

        /* CRITICAL FIX: Remove all transitions during navigation */
        body.navigating * {
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-cloak>
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Overlay - SIMPLIFIED -->
        <div x-show="sidebarOpen" 
             class="fixed inset-0 z-40 lg:hidden bg-gray-600 bg-opacity-75"
             @click="sidebarOpen = false"
             style="display: none;"
              ></div>

        <!-- Left Sidebar Navigation - SIMPLIFIED -->
        <div class="sidebar-nav fixed inset-y-0 left-0 z-50 w-64 bg-secondary text-gray-800 flex flex-col lg:static lg:inset-0"
             :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
             x-show="sidebarOpen"
             style="display: flex;"
              >
            
            <div class="p-4 sm:p-6 border-b-2 border-gray-400" style="border-bottom: 2px solid #8B7355;">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col items-center text-center">
                        <img src="{{ asset('images/logo_final.jpg') }}"
                             alt="Healthcare Logo"
                             class="w-16 h-16 sm:w-20 sm:h-20 mb-2 object-cover rounded-full">
                        <div>
                            <h1 class="text-lg sm:text-xl font-bold">Barangay Mecolong Health Center</h1>
                            <p class="text-xs sm:text-sm text-gray-300 mt-1">Midwife Portal</p>
                        </div>
                    </div>
                    <!-- Close button for mobile -->
                    <button @click="sidebarOpen = false"
                            class="lg:hidden p-2 rounded-md text-gray-300 hover:text-white hover-cream">
                        <i class="fas fa-times w-5 h-5"></i>
                    </button>
                </div>
            </div>
            
            <nav class="flex-1 p-3 sm:p-4 overflow-y-auto sidebar-scrollbar">
                <ul class="space-y-1 sm:space-y-2">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('dashboard') ? 'nav-active bg-primary' : 'hover-cream' }}">
                            <i class="fas fa-tachometer-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Patient Management -->
                    <li>
                        <a href="{{ route('midwife.patients.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.patients.*') ? 'nav-active bg-primary' : 'hover-cream' }}">
                            <i class="fas fa-user-plus w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Parent Registration
                        </a>
                    </li>

                    <!-- Prenatal Care Group -->
                    <li>
                        <div class="nav-group">
                            <button class="nav-group-toggle w-full flex items-center justify-between p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.prenatalrecord.*', 'midwife.prenatalcheckup.*') ? 'nav-active bg-blue-100 font-medium' : 'hover-cream' }}"
                                    onclick="toggleNavGroup('prenatal-group')">
                                <div class="flex items-center">
                                    <i class="fas fa-baby w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                                    <span>Prenatal Care</span>
                                </div>
                                <i class="fas fa-chevron-down w-3 h-3" id="prenatal-group-icon"></i>
                            </button>
                            <ul class="nav-submenu ml-6 sm:ml-8 mt-1 space-y-1 {{ request()->routeIs('midwife.prenatalrecord.*', 'midwife.prenatalcheckup.*') ? '' : 'hidden' }}"
                                id="prenatal-group-menu">
                                <li>
                                    <a href="{{ route('midwife.prenatalrecord.index') }}"
                                       class="nav-link flex items-center p-2 rounded-lg text-sm {{ request()->routeIs('midwife.prenatalrecord.*') ? 'nav-active bg-primary' : 'hover-cream' }}">
                                        <i class="fas fa-file-medical w-4 h-4 mr-2"></i>
                                        Prenatal Records
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('midwife.prenatalcheckup.index') }}"
                                       class="nav-link flex items-center p-2 rounded-lg text-sm {{ request()->routeIs('midwife.prenatalcheckup.*') ? 'nav-active bg-primary' : 'hover-cream' }}">
                                        <i class="fas fa-stethoscope w-4 h-4 mr-2"></i>
                                        Prenatal Check-up
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Child Health Group -->
                    <li>
                        <div class="nav-group">
                            <button class="nav-group-toggle w-full flex items-center justify-between p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.childrecord.*', 'midwife.immunization.*', 'midwife.vaccines.*') ? 'nav-active bg-blue-100 font-medium' : 'hover-cream' }}"
                                    onclick="toggleNavGroup('child-group')">
                                <div class="flex items-center">
                                    <i class="fas fa-child w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                                    <span>Immunization Tracking</span>
                                </div>
                                <i class="fas fa-chevron-down w-3 h-3" id="child-group-icon"></i>
                            </button>
                            <ul class="nav-submenu ml-6 sm:ml-8 mt-1 space-y-1 {{ request()->routeIs('midwife.childrecord.*', 'midwife.immunization.*', 'midwife.vaccines.*') ? '' : 'hidden' }}"
                                id="child-group-menu">
                                <li>
                                    <a href="{{ route('midwife.childrecord.index') }}"
                                       class="nav-link flex items-center p-2 rounded-lg text-sm {{ request()->routeIs('midwife.childrecord.*') ? 'nav-active bg-primary' : 'hover-cream' }}">
                                        <i class="fas fa-clipboard-list w-4 h-4 mr-2"></i>
                                        Child Registration
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('midwife.immunization.index') }}"
                                       class="nav-link flex items-center p-2 rounded-lg text-sm {{ request()->routeIs('midwife.immunization.*') ? 'nav-active bg-primary' : 'hover-cream' }}">
                                        <i class="fas fa-syringe w-4 h-4 mr-2"></i>
                                        Immunization Schedule
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('midwife.vaccines.index') }}"
                                       class="nav-link flex items-center p-2 rounded-lg text-sm {{ request()->routeIs('midwife.vaccines.*') ? 'nav-active bg-primary' : 'hover-cream' }}">
                                        <i class="fas fa-vial w-4 h-4 mr-2"></i>
                                        Vaccine
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <!-- Users -->
                    <li>
                        <a href="{{ route('midwife.user.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('admin.users.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="users"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-users w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            User Management
                        </a>
                    </li>

                    <!-- Cloud Backup-->
                    <li>
                        <a href="{{ route('midwife.cloudbackup.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('admin.cloudbackup.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="cloud-backup"
                           onclick="showNavigationLoading(event, this)">
                           <i class="fas fa-cloud-upload-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Cloud Backup
                        </a>
                    </li>

                    <!-- SMS Logs -->
                    <li>
                        <a href="{{ route('midwife.sms-logs.index') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.sms-logs.*') ? 'nav-active bg-primary' : 'hover-cream' }}"
                           data-section="sms-logs"
                           onclick="showNavigationLoading(event, this)">
                            <i class="fas fa-sms w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            SMS Logs
                        </a>
                    </li>

                    <!-- Reports -->
                    <li>
                        <a href="{{ route('midwife.report') }}"
                           class="nav-link flex items-center p-2 sm:p-3 rounded-lg text-sm sm:text-base {{ request()->routeIs('midwife.report*') ? 'nav-active bg-primary' : 'hover-cream' }}">
                            <i class="fas fa-chart-bar w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            Reports
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="p-3 sm:p-4 border-t border-primary" style="border-color: #B8956A; background-color: #B8956A;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xs sm:text-sm font-semibold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'MW', 0, 2)) }}</span>
                        </div>
                        <div class="ml-2 sm:ml-3 min-w-0">
                            <p class="text-xs sm:text-sm font-medium truncate text-white">{{ auth()->user()->name ?? 'Midwife' }}</p>
                            <p class="text-xs text-gray-200 truncate">{{ auth()->user()->role ?? 'Midwife' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-white hover:text-gray-200 hover-cream rounded-lg flex-shrink-0" title="Logout">
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
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none mr-2 sm:mr-3">
                            <i class="fas fa-bars w-5 h-5"></i>
                        </button>

                        <div class="min-w-0">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-tachometer-alt text-blue-600 text-xl"></i>
                                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent truncate">@yield('page-title', 'Dashboard Overview')</h2>
                            </div>
                            <p class="text-gray-600 text-xs sm:text-sm truncate ml-8">@yield('page-subtitle', 'Monitor patient care and health records')</p>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                        <div class="hidden md:flex items-center space-x-2"> 
                        </div>
                        
                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 relative">
                                <i class="fas fa-bell w-6 h-6 sm:w-8 sm:h-8"></i>
                                <span id="notification-badge" class="notification-badge-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center hidden">0</span>
                            </button>
                            
                            <!-- Notifications Dropdown -->
                            <div x-show="open" @click.outside="open = false" 
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
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
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-3 sm:p-4 lg:p-6 overflow-y-scroll custom-scrollbar">
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
    <!-- Notification System -->
    <script>
        // Enhanced notification system with toast integration
        let lastNotificationCount = 0;
        let lastCheckedTime = new Date();

        function loadNotificationCount() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        badge.textContent = data.count;
                        if (data.count > 0) {
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }

                    // Check for new notifications and show toast
                    if (data.count > lastNotificationCount && lastNotificationCount !== 0) {
                        checkForNewNotifications();
                    }
                    lastNotificationCount = data.count;
                })
                .catch(error => console.error('Error loading notification count:', error));
        }

        function checkForNewNotifications() {
            fetch('/notifications/recent')
                .then(response => response.json())
                .then(data => {
                    if (data.notifications && data.notifications.length > 0) {
                        // Find the newest notification
                        const newestNotification = data.notifications[0];
                        const notificationTime = new Date(newestNotification.created_at);

                        // Only show toast if notification is newer than last check
                        if (notificationTime > lastCheckedTime) {
                            showNotificationToast(newestNotification);
                        }
                    }
                    lastCheckedTime = new Date();
                })
                .catch(error => console.error('Error checking new notifications:', error));
        }

        function showNotificationToast(notification) {
            if (window.flowbiteToast) {
                const notificationData = notification.data || {};
                const type = notificationData.type || 'info';
                const title = notificationData.title || 'New Notification';
                const message = notificationData.message || '';
                const user = notificationData.notified_by || 'System';
                const notifiedByRole = notificationData.notified_by_role || '';
                const notificationCategory = notificationData.notification_category || 'normal';
                const priority = notificationData.toast_priority || 'normal';

                // Map notification types to toast types
                const toastType = type === 'error' ? 'error' :
                                type === 'success' ? 'success' :
                                type === 'warning' ? 'warning' : 'info';

                // Determine duration based on priority and category
                let duration = 8000; // Default duration
                if (notificationCategory === 'bhw_to_midwife_priority') {
                    duration = 12000; // Show longer for BHW notifications to midwives
                } else if (priority === 'urgent') {
                    duration = 10000; // Show longer for urgent notifications
                }

                // Enhanced toast for BHW-to-Midwife notifications
                if (notificationCategory.includes('bhw_to_midwife')) {
                    window.flowbiteToast.show({
                        type: toastType,
                        title: title,
                        message: message,
                        user: user + ' (BHW)',
                        time: 'just now',
                        duration: duration,
                        priority: 'urgent'
                    });

                    // Also show a system sound notification (if supported)
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification('🏥 BHW Data Entry Alert', {
                            body: `${user} has ${message}`,
                            icon: '/favicon.ico',
                            tag: 'bhw-notification'
                        });
                    }
                } else {
                    // Regular toast notification
                    window.flowbiteToast.show({
                        type: toastType,
                        title: title,
                        message: message,
                        user: user + (notifiedByRole ? ` (${notifiedByRole.toUpperCase()})` : ''),
                        time: 'just now',
                        duration: duration
                    });
                }
            }
        }

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
                                const icons = {
                                    'info': 'fa-info-circle text-blue-500',
                                    'success': 'fa-check-circle text-green-500',
                                    'warning': 'fa-exclamation-triangle text-yellow-500',
                                    'error': 'fa-times-circle text-red-500'
                                };
                                
                                return `
                                    <div class="p-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer" onclick="window.location.href='${notification.data.action_url || '/notifications'}'">
                                        <div class="flex items-start space-x-3">
                                            <i class="fas ${icons[type] || icons.info} mt-1"></i>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-gray-900 truncate">
                                                    ${notification.data.title || 'Notification'}
                                                </div>
                                                <div class="text-sm text-gray-600 truncate">
                                                    ${notification.data.message || ''}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    ${formatDate(notification.created_at)}
                                                </div>
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

        function markAllAsRead() {
            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotificationCount();
                    loadRecentNotifications();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Real-time notification checking
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

                        // Notification visual feedback only (sound disabled to avoid CSP issues)
                        const bellIcon = document.querySelector('.fa-bell');
                        if (bellIcon) {
                            bellIcon.classList.add('ring-bell');
                            setTimeout(() => bellIcon.classList.remove('ring-bell'), 800);
                        }
                    } else {
                        // Update timestamp even if no new notifications
                        lastNotificationCheck = data.timestamp;
                    }
                })
                .catch(error => console.error('Error checking for new notifications:', error));
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            return `${days}d ago`;
        }
        

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
                const originalText = linkElement.textContent.trim();

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
                            'prenatal-checkups': 'fa-stethoscope',
                            'child-records': 'fa-child',
                            'immunizations': 'fa-syringe',
                            'vaccines': 'fa-vial',
                            'user-management': 'fa-users-cog',
                            'cloud-backup': 'fa-cloud-upload-alt',
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

        // Navigation Group Toggle Functionality
        function toggleNavGroup(groupId) {
            const menu = document.getElementById(groupId + '-menu');
            const icon = document.getElementById(groupId + '-icon');

            if (menu && icon) {
                if (menu.classList.contains('hidden')) {
                    // Show menu
                    menu.classList.remove('hidden');
                    icon.style.transform = 'rotate(180deg)';

                    // Store state in localStorage
                    localStorage.setItem('nav-group-' + groupId, 'open');
                } else {
                    // Hide menu
                    menu.classList.add('hidden');
                    icon.style.transform = 'rotate(0deg)';

                    // Store state in localStorage
                    localStorage.setItem('nav-group-' + groupId, 'closed');
                }
            }
        }

        // Initialize navigation groups state on page load
        function initializeNavGroups() {
            const groups = ['prenatal-group', 'child-group'];

            groups.forEach(groupId => {
                const menu = document.getElementById(groupId + '-menu');
                const icon = document.getElementById(groupId + '-icon');
                const savedState = localStorage.getItem('nav-group-' + groupId);

                if (menu && icon) {
                    // Check if any submenu item is currently active
                    const hasActiveChild = menu.querySelector('.nav-link.bg-primary');

                    if (hasActiveChild) {
                        // Only open if there's an active child menu item
                        menu.classList.remove('hidden');
                        icon.style.transform = 'rotate(180deg)';
                        localStorage.setItem('nav-group-' + groupId, 'open');
                    } else {
                        // Keep menus closed by default
                        menu.classList.add('hidden');
                        icon.style.transform = 'rotate(0deg)';
                        // Don't override saved state unless there's an active child
                        if (savedState === 'open' && !hasActiveChild) {
                            localStorage.setItem('nav-group-' + groupId, 'closed');
                        }
                    }
                }
            });
        }

        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Clear any existing localStorage for navigation groups on dashboard load
            if (window.location.pathname.includes('dashboard')) {
                localStorage.removeItem('nav-group-prenatal-group');
                localStorage.removeItem('nav-group-child-group');
            }

            // Initialize navigation groups
            initializeNavGroups();
            loadNotificationCount();

            // Load recent notifications when dropdown opens
            const notificationButton = document.querySelector('[x-data*="open"]');
            if (notificationButton) {
                notificationButton.addEventListener('click', loadRecentNotifications);
            }

            // Initialize last notification count on page load
            setTimeout(() => {
                fetch('/notifications/unread-count')
                    .then(response => response.json())
                    .then(data => {
                        lastNotificationCount = data.count;
                        lastCheckedTime = new Date();
                    });
            }, 1000);

            // Request notification permissions for BHW alerts
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(function (permission) {
                    if (permission === 'granted') {
                        console.log('Browser notifications enabled for BHW alerts');
                    }
                });
            }

            // Check for new notifications more frequently (every 5 seconds for real-time)
            setInterval(checkForNewNotifications, 5000);
            setInterval(loadNotificationCount, 30000);

            // Show initial toast for existing unread notifications (optional)
            setTimeout(() => {
                const badge = document.getElementById('notification-badge');
                if (badge && !badge.classList.contains('hidden')) {
                    const count = parseInt(badge.textContent);
                    if (count > 0 && window.flowbiteToast) {
                        window.flowbiteToast.info(`You have ${count} unread notification${count > 1 ? 's' : ''}`, {
                            title: 'Unread Notifications',
                            user: 'System',
                            time: 'now',
                            duration: 6000
                        });
                    }
                }
            }, 2000);

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

        // Navigation Group Toggle Function
        function toggleNavGroup(groupId) {
            const menu = document.getElementById(groupId + '-menu');
            const icon = document.getElementById(groupId + '-icon');

            if (menu && icon) {
                menu.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            }
        }

    </script>
    
    <!-- Custom Scripts -->
    @stack('scripts')
    
    {{-- Include Global Confirmation Modal --}}
    @include('components.confirmation-modal')

    {{-- Include Toast Notification System --}}
    @include('components.toast-notification')

    {{-- Include Modal Form Reset System --}}
    @include('components.modal-form-reset')

    {{-- Include SweetAlert Flash Messages --}}
    @include('components.sweetalert-flash')
</body>
</html>