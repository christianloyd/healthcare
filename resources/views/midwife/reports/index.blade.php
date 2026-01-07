@extends('layout.midwife')
@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Generate detailed analytics and reports')

@section('content')
<style>
    /* @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'); */
    
    * {
        font-family: 'Inter', sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    :root {
        --primary: #D4A373;
        --secondary: #ecb99e;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --white: #ffffff;
        --success: #059669;
        --warning: #d97706;
        --error: #dc2626;
        --info: #2563eb;
    }
    
    body {
        background-color: var(--gray-50);
        color: var(--gray-800);
    }
    
    .card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card:hover {
        border-color: var(--gray-300);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .section-title i {
        color: var(--primary);
        font-size: 1rem;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 6px;
    }
    
    .form-input, .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 0.875rem;
        background-color: var(--white);
        transition: all 0.15s ease;
    }
    
    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(36, 59, 85, 0.1);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    
    .btn-primary {
        background-color: var(--primary);
        color: var(--white);
    }
    
    .btn-primary:hover {
        background-color: var(--secondary);
        transform: translateY(-1px);
    }
    
    /* Skeleton Loading Animation */
    @keyframes skeleton-loading {
        0% {
            background-position: -200px 0;
        }
        100% {
            background-position: calc(200px + 100%) 0;
        }
    }
    
    .skeleton-loading {
        position: relative;
        overflow: hidden;
    }
    
    .skeleton-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.8),
            transparent
        );
        animation: skeleton-loading 1.5s infinite;
        z-index: 1;
    }
    
    .skeleton-loading .report-metric-number,
    .skeleton-loading .report-metric-label {
        color: transparent;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.5s infinite;
        border-radius: 4px;
    }
    
    /* Button loading state */
    .btn:disabled {
        cursor: not-allowed !important;
        transform: none !important;
    }
    
    .btn-loading {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-secondary {
        background-color: var(--white);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }
    
    .btn-secondary:hover {
        background-color: var(--gray-50);
        border-color: var(--gray-400);
    }
    
    .btn-success {
        background-color: var(--success);
        color: var(--white);
    }
    
    .btn-success:hover {
        background-color: #047857;
    }
    
    .report-metric {
        text-align: center;
        padding: 20px;
    }
    
    .report-metric-number {
        font-size: 2rem !important;
        font-weight: 700 !important;
        color: var(--primary) !important;
        margin-bottom: 4px !important;
        line-height: 1.1 !important;
    }

    @media (max-width: 1024px) {
        .report-metric-number {
            font-size: 2.5rem !important;
        }
    }

    @media (max-width: 768px) {
        .report-metric-number {
            font-size: 2rem !important;
        }
    }
    
    .report-metric-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .report-table th {
        background-color: var(--gray-50);
        padding: 12px 16px;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-200);
    }
    
    .report-table td {
        padding: 12px 16px;
        font-size: 0.875rem;
        border-bottom: 1px solid var(--gray-100);
    }
    
    .report-table tr:hover {
        background-color: var(--gray-50);
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .status-normal {
        background-color: #f0fdf4;
        color: var(--success);
    }
    
    .status-warning {
        background-color: #fffbeb;
        color: var(--warning);
    }
    
    .status-critical {
        background-color: #fef2f2;
        color: var(--error);
    }
    
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
        padding: 20px 0;
    }
    
    .grid-1 { display: grid; grid-template-columns: 1fr; gap: 24px; }
    .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    
    @media (max-width: 1024px) {
        .grid-4 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 640px) {
        .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
        .chart-container { height: 300px; width: 100%; }
        .report-metric-number {
            font-size: 1rem !important;
        }
    }
    
    .spacing-section {
        margin-bottom: 32px;
    }
    
    .spacing-section:last-child {
        margin-bottom: 0;
    }
    
    /* Print styles */
    @media print {
        .report-filters, .btn, button {
            display: none !important;
        }
        
        .report-content {
            display: block !important;
        }
        
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }
        
        .spacing-section {
            page-break-inside: avoid;
        }
        
        .grid-2 {
            display: block !important;
        }
        
        .grid-2 > div {
            margin-bottom: 20px;
            width: 100% !important;
        }
        
        body {
            font-size: 12px !important;
            background: white !important;
        }
        
        /* Header styles - 16px */
        h1, h2, h3, .section-title {
            font-size: 16px !important;
            font-weight: 600 !important;
            margin-bottom: 12px !important;
        }
        
        /* Subheading styles - 14px */
        h4, h5, h6, .report-metric-label, th {
            font-size: 14px !important;
            font-weight: 500 !important;
        }
        
        /* Body text styles - 12px */
        p, td, span, div, .report-table td, .status-badge {
            font-size: 12px !important;
        }
        
        .report-metric-number {
            font-size: 20px !important;
            font-weight: 700 !important;
        }
        
        .chart-container {
            height: 250px !important;
            width: 100% !important;
        }
        
        .report-table th {
            font-size: 14px !important;
            font-weight: 600 !important;
        }
        
        .report-table td {
            font-size: 12px !important;
        }
        
        /* Add print header */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .print-footer {
            display: block !important;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            background: white;
        }
    }
    
    .print-header, .print-footer {
        display: none;
    }
    
    /* Dropdown styles */
    .dropdown-item:hover {
        background-color: var(--gray-50);
    }
    
    .report-filters {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 32px;
    }
    
    .report-content {
        display: none;
    }
    
    .report-content.active {
        display: block;
    }
    
    .loading {
        text-align: center;
        padding: 40px;
        color: var(--gray-500);
    }
    
    .loading i {
        font-size: 2rem;
        margin-bottom: 16px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>

<!-- Font Awesome loaded via Vite in layout -->
<!-- Chart.js loaded via Vite in layout -->

<!-- Success/Error Messages -->
@include('components.flowbite-alert')

<!-- Report Filters -->
<div class="report-filters">
    <div style="margin-bottom: 24px;">
        <h3 class="section-title">
            <i class="fas fa-filter"></i>
            Report Configuration
        </h3>
    </div>

    <!-- Month and Department selections repositioned -->
    <div style="display: flex; gap: 16px; align-items: end; margin-bottom: 24px;">
        <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
            <label class="form-label">Month</label>
            <select class="form-select" name="month" id="monthFilter" onchange="updateHiddenInputs()" style="padding: 12px 16px; font-size: 0.875rem;">
                @foreach($availableMonths as $value => $label)
                    <option value="{{ $value }}" {{ ($currentFilters['month'] ?? '') === (string)$value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
            <label class="form-label">Department</label>
            <select class="form-select" name="department" id="departmentFilter" onchange="updateHiddenInputs()" style="padding: 12px 16px; font-size: 0.875rem;">
                <option value="all" {{ ($currentFilters['department'] ?? 'all') === 'all' ? 'selected' : '' }}>All Services</option>
                <option value="prenatal" {{ ($currentFilters['department'] ?? '') === 'prenatal' ? 'selected' : '' }}>Prenatal Care</option>
                <option value="immunization" {{ ($currentFilters['department'] ?? '') === 'immunization' ? 'selected' : '' }}>Child Immunization</option>
            </select>
        </div>
    </div>

    <!-- Export buttons section with Update Report -->
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <!-- Update Report button positioned with other buttons -->
        <form method="GET" action="{{ route('midwife.report') }}" style="display: inline-block;">
            <input type="hidden" name="report_format" value="dynamic">
            <input type="hidden" name="report_type" value="monthly">
            <input type="hidden" name="month" value="{{ $currentFilters['month'] ?? '' }}" id="hiddenMonth">
            <input type="hidden" name="department" value="{{ $currentFilters['department'] ?? 'all' }}" id="hiddenDepartment">

            <button type="submit" class="btn btn-primary" id="updateReportBtn" style="padding: 12px 16px; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 8px;">
                <span class="btn-content">
                    <i class="fas fa-chart-bar"></i>
                    Update Report
                </span>
                <span class="btn-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                    Updating Report...
                </span>
            </button>
        </form>

        <button class="btn btn-primary" onclick="printReport()" style="padding: 12px 16px; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-print"></i>
            Print Report
        </button>
        <button class="btn btn-secondary" onclick="exportPDF()" style="padding: 12px 16px; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-file-pdf"></i>
            Export PDF
        </button>
        <div class="dropdown" style="position: relative; display: inline-block;">
            <button class="btn btn-secondary dropdown-toggle" onclick="toggleExportDropdown()" style="padding: 12px 16px; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-file-excel"></i>
                Export Excel
                <i class="fas fa-chevron-down" style="margin-left: 8px; font-size: 0.8em;"></i>
            </button>
            <div class="dropdown-menu" id="exportDropdown" style="display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid var(--gray-300); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; min-width: 200px;">
                <button class="dropdown-item" onclick="exportExcel('full')" style="display: block; width: 100%; padding: 12px 16px; text-align: left; border: none; background: none; color: var(--gray-700); cursor: pointer;">
                    <i class="fas fa-table" style="margin-right: 8px;"></i>
                    Complete Report
                </button>
                <button class="dropdown-item" onclick="exportExcel('summary')" style="display: block; width: 100%; padding: 12px 16px; text-align: left; border: none; background: none; color: var(--gray-700); cursor: pointer;">
                    <i class="fas fa-chart-bar" style="margin-right: 8px;"></i>
                    Summary Only
                </button>
                <button class="dropdown-item" onclick="exportExcel('demographics')" style="display: block; width: 100%; padding: 12px 16px; text-align: left; border: none; background: none; color: var(--gray-700); cursor: pointer;">
                    <i class="fas fa-users" style="margin-right: 8px;"></i>
                    Demographics Data
                </button>
            </div>
        </div>
        <!--<button class="btn btn-success" onclick="scheduleReport()">
            <i class="fas fa-clock"></i>
            Schedule Report
        </button>-->

        <!-- Accomplishment Report Button -->
        <a href="{{ route('midwife.report.bhw.accomplishment') }}" target="_blank" class="btn btn-success" style="padding: 12px 16px; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
            <i class="fas fa-file-alt"></i>
            Accomplishment Report
        </a>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="loading" style="display: none;">
    <i class="fas fa-spinner"></i>
    <p>Generating your report...</p>
</div>

<!-- Print Header (only visible when printing) -->
<div class="print-header">
    <h1 style="margin: 0; font-size: 24px; color: var(--primary);">Healthcare Report</h1>
    <p style="margin: 5px 0 0 0; color: var(--gray-600);">{{ $availableMonths[$currentFilters['month'] ?? ''] ?? 'All Data' }} - Generated on {{ now()->format('F j, Y') }}</p>
    <p style="margin: 0; color: var(--gray-600);">Prepared by: {{ auth()->user()->name ?? 'System' }}</p>
</div>

<!-- Dynamic Report Content -->
<div id="dynamicReportContent" class="report-content {{ ($currentFilters['report_format'] ?? 'dynamic') === 'dynamic' ? 'active' : '' }}">
    <!-- Summary Metrics -->
    <div class="spacing-section">
        <div class="card" style="padding: 24px;">
            <h3 class="section-title">
                <i class="fas fa-chart-line"></i>
                <span id="reportTitle">{{ $availableMonths[$currentFilters['month'] ?? ''] ?? 'All Data' }} Report</span>
                @if(config('app.debug'))
                <small style="color: #666; font-size: 0.8rem; font-weight: normal;">
                    (Filter: {{ $currentFilters['month'] ?? 'None' }})
                </small>
                @endif
            </h3>
            
            <div class="grid-4">
                <div class="report-metric">
                    <div class="report-metric-number" id="totalPatients">{{ number_format($totalPatients) }}</div>
                    <div class="report-metric-label">Total Patients</div>
                </div>
                <div class="report-metric">
                    <div class="report-metric-number" id="totalCheckups">{{ number_format($totalCheckups) }}</div>
                    <div class="report-metric-label">Checkups Completed</div>
                </div>
                <div class="report-metric">
                    <div class="report-metric-number" id="totalVaccinations">{{ number_format($totalVaccinations) }}</div>
                    <div class="report-metric-label">Vaccinations Given</div>
                </div>
                <div class="report-metric">
                    <div class="report-metric-number" id="totalChildren">{{ $totalChildren }}</div>
                    <div class="report-metric-label">Total Children</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="spacing-section">
        <div class="grid-2">
            <div class="card" style="padding: 24px;">
                <h3 class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Most Used Vaccines
                </h3>
                <div class="chart-container">
                    <canvas id="dailyTrendsChart"></canvas>
                </div>
            </div>

            <div class="card" style="padding: 24px;">
                <h3 class="section-title">
                    <i class="fas fa-chart-pie"></i>
                    Service Distribution
                </h3>
                <div class="chart-container">
                    <canvas id="serviceDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="spacing-section">
        <div class="grid-2">
            <!-- Child Immunization Statistics -->
            <div class="card">
                <div style="padding: 24px 24px 0 24px;">
                    <h3 class="section-title">
                        <i class="fas fa-syringe"></i>
                        Child Immunization Statistics
                    </h3>
                </div>
                
                <div style="padding: 0 24px 24px 24px;">
                    <div class="grid-2" style="gap: 16px;">
                        <div class="report-metric">
                            <div class="report-metric-number">{{ number_format($childImmunizationStats['totalImmunizedGirls'] ?? $totalImmunizedGirls ?? 0) }}</div>
                            <div class="report-metric-label">Immunized Girls</div>
                        </div>
                        <div class="report-metric">
                            <div class="report-metric-number">{{ number_format($childImmunizationStats['totalImmunizedBoys'] ?? $totalImmunizedBoys ?? 0) }}</div>
                            <div class="report-metric-label">Immunized Boys</div>
                        </div>
                        <div class="report-metric">
                            <div class="report-metric-number">{{ number_format($childImmunizationStats['upcomingImmunizations'] ?? $upcomingImmunizations ?? 0) }}</div>
                            <div class="report-metric-label">Upcoming Immunizations</div>
                        </div>
                        <div class="report-metric">
                            <div class="report-metric-number">{{ number_format($checkupStats['upcomingCheckups'] ?? $upcomingCheckups ?? 0) }}</div>
                            <div class="report-metric-label">Upcoming Checkups</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Summary -->
            <div class="card">
                <div style="padding: 24px 24px 0 24px;">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Monthly Summary
                    </h3>
                </div>
                
                <div style="padding: 0 24px 24px 24px;">
                    <div class="grid-2" style="gap: 16px;">
                        <div class="report-metric">
                            <div class="report-metric-number">{{ number_format($totalCheckups ?? 0) }}</div>
                            <div class="report-metric-label">Checkups Completed</div>
                        </div>
                        <div class="report-metric">
                            <div class="report-metric-number">{{ number_format($totalVaccinations ?? 0) }}</div>
                            <div class="report-metric-label">Vaccinations Given</div>
                        </div>
                        <div class="report-metric">
                            <div class="report-metric-number">{{ number_format($checkupStats['upcomingCheckups'] ?? $upcomingCheckups ?? 0) }}</div>
                            <div class="report-metric-label">Upcoming Checkups</div>
                        </div>
                        <div class="report-metric">
                            <div class="report-metric-number">{{ number_format($upcomingImmunizations ?? 0) }}</div>
                            <div class="report-metric-label">Upcoming Immunizations</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Demographics
    <div class="spacing-section">
        <div class="card" style="padding: 24px;">
            <h3 class="section-title">
                <i class="fas fa-users"></i>
                Patient Demographics by Age Group
            </h3>
            
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Age Group</th>
                            <th>Total Patients</th>
                            <th>New Patients</th>
                            <th>Immunized Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patientDemographics as $demographic)
                        <tr>
                            <td>{{ $demographic['age_group'] }}</td>
                            <td>{{ $demographic['total_patients'] }}</td>
                            <td>{{ $demographic['new_patients'] }}</td>
                            <td>{{ $demographic['immunized_count'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>-->

<!-- Custom Paper Report Content -->
<div id="customReportContent" class="report-content {{ ($currentFilters['report_format'] ?? '') === 'custom' ? 'active' : '' }}">
    <div class="spacing-section">
        <div class="card" style="padding: 40px; text-align: center; border: 2px dashed var(--gray-300);">
            <div style="max-width: 500px; margin: 0 auto;">
                <i class="fas fa-clipboard-list" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 24px;"></i>
                <h3 style="color: var(--gray-700); margin-bottom: 16px;">Custom Paper Report Format</h3>
                <p style="color: var(--gray-500); margin-bottom: 24px; line-height: 1.6;">
                    This section will display your custom report format based on your paper document.
                    Please describe the layout and sections you'd like me to implement.
                </p>
                
                <div style="background: var(--gray-50); padding: 20px; border-radius: 8px; text-align: left; margin-bottom: 24px;">
                    <h4 style="color: var(--gray-700); margin-bottom: 12px; font-size: 1rem;">To customize this report, I need to know:</h4>
                    <ul style="color: var(--gray-600); font-size: 0.9rem; line-height: 1.6; margin: 0; padding-left: 20px;">
                        <li>What sections should be included?</li>
                        <li>How should the data be organized?</li>
                        <li>What specific metrics do you want to show?</li>
                        <li>Any specific formatting requirements?</li>
                        <li>Headers, footers, or signature areas needed?</li>
                    </ul>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button class="btn btn-secondary" onclick="alert('Please describe your paper report format so I can implement it for you!')">
                        <i class="fas fa-comments"></i>
                        Describe Your Format
                    </button>
                    <button class="btn btn-primary" onclick="switchToFormat('dynamic')">
                        <i class="fas fa-arrow-left"></i>
                        Use Dynamic Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Footer (only visible when printing) -->
<div class="print-footer">
    <p style="margin: 0;">Healthcare Management System | Generated: {{ now()->format('F j, Y g:i A') }} | Page <span class="page-number"></span></p>
</div>

<script>
    // Chart.js configuration
    Chart.defaults.font.family = 'Inter';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6b7280';
    
    const primaryColor = '#D4A373';
    const secondaryColor = '#ecb99e';
    const successColor = '#059669';
    const warningColor = '#d97706';
    const infoColor = '#2563eb';
    
    let dailyTrendsChart, serviceDistributionChart;
    
    // Initialize charts with error handling (same pattern as dashboard)
    function initializeCharts() {
        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        console.log('Chart Data:', {!! json_encode($charts) !!});

        if (typeof Chart === 'undefined') {
            console.error('Chart.js failed to load!');
            // Show fallback message
            document.querySelectorAll('.chart-container').forEach(container => {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Chart library failed to load</div>';
            });
            return;
        }

        console.log('DOM loaded, initializing charts...');
        generateCharts();

        // Show report content
        const dynamicContent = document.getElementById('dynamicReportContent');
        if (dynamicContent) {
            dynamicContent.classList.add('active');
        }
    }

    function generateCharts() {
        // Destroy existing charts
        if (dailyTrendsChart) dailyTrendsChart.destroy();
        if (serviceDistributionChart) serviceDistributionChart.destroy();

        // Most Used Vaccines Chart
        try {
            const dailyTrendsCtx = document.getElementById('dailyTrendsChart');
            if (dailyTrendsCtx) {
                // Real vaccine data from backend
                let vaccineData = {!! json_encode($charts['vaccine_usage']) !!};

        // Handle empty vaccine data - show chart with placeholder data
        if (!vaccineData.labels || vaccineData.labels.length === 0) {
            vaccineData = {
                labels: ['No Data Available'],
                data: [0]
            };
        }

        // Generate dynamic colors based on number of vaccines
        const generateColors = (count) => {
            const baseColors = [
                '#D4A373',  // Primary warm brown
                '#ecb99e',  // Secondary peach
                '#059669',  // Success green
                '#d97706',  // Warning orange
                '#2563eb',  // Info blue
                '#dc2626',  // Error red
                '#6b7280',  // Gray
                '#8b5cf6',  // Purple
                '#06b6d4',  // Cyan
                '#84cc16',  // Lime
            ];

            const borderColors = [
                '#B8956A',  // Darker brown
                '#d4a373',  // Darker peach
                '#047857',  // Darker green
                '#b45309',  // Darker orange
                '#1d4ed8',  // Darker blue
                '#b91c1c',  // Darker red
                '#4b5563',  // Darker gray
                '#7c3aed',  // Darker purple
                '#0891b2',  // Darker cyan
                '#65a30d',  // Darker lime
            ];

            // Repeat colors if we have more vaccines than predefined colors
            const bgColors = [];
            const bdColors = [];
            for (let i = 0; i < count; i++) {
                bgColors.push(baseColors[i % baseColors.length]);
                bdColors.push(borderColors[i % borderColors.length]);
            }

            return { backgroundColor: bgColors, borderColor: bdColors };
        };

        const colors = generateColors(vaccineData.labels.length);

        // Special styling for "No Data Available" state
        if (vaccineData.labels[0] === 'No Data Available') {
            colors.backgroundColor = ['#f3f4f6']; // Light gray
            colors.borderColor = ['#d1d5db']; // Gray border
        }

                dailyTrendsChart = new Chart(dailyTrendsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: vaccineData.labels,
                datasets: [{
                    label: 'Doses Administered',
                    data: vaccineData.data,
                    backgroundColor: colors.backgroundColor,
                    borderColor: colors.borderColor,
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: vaccineData.labels[0] === 'No Data Available',
                        text: 'No vaccine data for selected period',
                        color: '#6b7280',
                        font: {
                            size: 14
                        }
                    },
                    tooltip: {
                        enabled: vaccineData.labels[0] !== 'No Data Available',
                        callbacks: {
                            label: function(context) {
                                if (context.label === 'No Data Available') {
                                    return 'No vaccine data available';
                                }
                                return context.dataset.label + ': ' + context.parsed.y + ' doses';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            font: {
                                size: function(context) {
                                    return window.innerWidth < 640 ? 10 : 12;
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Number of Doses',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: function(context) {
                                    return window.innerWidth < 640 ? 10 : 12;
                                }
                            }
                        }
                    }
                }
            }
        });
            }
        } catch (error) {
            console.error('Error creating vaccines chart:', error);
        }

        // Service Distribution Chart
        try {
            const serviceDistributionCtx = document.getElementById('serviceDistributionChart');
            if (serviceDistributionCtx) {
                const serviceData = {!! json_encode($charts['service_distribution']) !!};
                serviceDistributionChart = new Chart(serviceDistributionCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: serviceData.labels,
                datasets: [{
                    data: serviceData.data,
                    backgroundColor: [primaryColor, secondaryColor, successColor, warningColor],
                    borderWidth: 0,
                    cutout: '60%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: function(context) {
                                    return window.innerWidth < 640 ? 12 : 14;
                                }
                            }
                        }
                    }
                }
            }
                });
            }
        } catch (error) {
            console.error('Error creating service distribution chart:', error);
        }
    }
    
    function exportPDF() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
        btn.disabled = true;
        
        // Build URL with current filters
        const urlParams = new URLSearchParams(window.location.search);
        let exportUrl = '{{ route("midwife.report.export.pdf") }}';
        
        // Add current filters to export URL
        if (urlParams.toString()) {
            exportUrl += '?' + urlParams.toString();
        }
        
        // Create a temporary form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = exportUrl;
        form.target = '_blank';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        // Restore button after a delay
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('PDF export started! Check your downloads folder for the PDF file.');
        }, 1000);
    }
    
    function printReport() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening Print View...';
        btn.disabled = true;
        
        // Build URL with current filters
        const urlParams = new URLSearchParams(window.location.search);
        let printUrl = '{{ route("midwife.report.print") }}';
        
        // Add current filters to print URL
        if (urlParams.toString()) {
            printUrl += '?' + urlParams.toString() + '&autoprint=1';
        } else {
            printUrl += '?autoprint=1';
        }
        
        // Open print view in new window
        const printWindow = window.open(printUrl, '_blank', 'width=1000,height=800,scrollbars=yes,resizable=yes');
        
        // Restore button after a short delay
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 1000);
        
        // Focus the print window
        if (printWindow) {
            printWindow.focus();
        }
    }

    function toggleExportDropdown() {
        const dropdown = document.getElementById('exportDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('exportDropdown');
        const button = event.target.closest('.dropdown-toggle');
        
        if (!button && dropdown) {
            dropdown.style.display = 'none';
        }
    });

    function exportExcel(exportType = 'full') {
        // Close dropdown
        document.getElementById('exportDropdown').style.display = 'none';
        
        const btn = event.target.closest('.dropdown') ? 
                   event.target.closest('.dropdown').querySelector('.dropdown-toggle') : 
                   event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;
        
        // Create form with current filters
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("midwife.report.export.excel") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add export type
        const exportTypeInput = document.createElement('input');
        exportTypeInput.type = 'hidden';
        exportTypeInput.name = 'export_type';
        exportTypeInput.value = exportType;
        form.appendChild(exportTypeInput);
        
        // Add current filters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.forEach((value, key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        
        // Submit the form to trigger download
        form.target = '_blank';
        form.submit();
        
        // Show success message after a short delay
        setTimeout(() => {
            const exportTypeNames = {
                'full': 'Complete Report',
                'summary': 'Summary Report',
                'demographics': 'Demographics Report'
            };
            alert(`${exportTypeNames[exportType]} Excel export started! Check your downloads folder.`);
        }, 500);
        
        // Clean up and restore button
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            document.body.removeChild(form);
        }, 1000);
    }
    
    function scheduleReport() {
        // Simulate scheduling
        const reportType = document.querySelector('select[name="report_type"]').value;
        const frequency = prompt('How often would you like to receive this report?\n\n1. Weekly\n2. Monthly\n3. Quarterly\n\nEnter 1, 2, or 3:');
        
        if (frequency) {
            const frequencies = {'1': 'weekly', '2': 'monthly', '3': 'quarterly'};
            const selectedFrequency = frequencies[frequency];
            
            if (selectedFrequency) {
                alert(`Report scheduled successfully! You will receive ${reportType} reports ${selectedFrequency} via email.`);
            } else {
                alert('Invalid selection. Please try again.');
            }
        }
    }
    
    function toggleReportFormat() {
        const formatSelect = document.querySelector('select[name="report_format"]');
        const formatDescription = document.getElementById('formatDescription');
        const dynamicContent = document.getElementById('dynamicReportContent');
        const customContent = document.getElementById('customReportContent');
        
        if (formatSelect.value === 'dynamic') {
            formatDescription.textContent = ' ';
            dynamicContent.classList.add('active');
            customContent.classList.remove('active');
        } else {
            formatDescription.textContent = '📋 Traditional paper-style report format with structured layout and summary tables';
            dynamicContent.classList.remove('active');
            customContent.classList.add('active');
        }
    }
    
    function switchToFormat(format) {
        const formatSelect = document.querySelector('select[name="report_format"]');
        formatSelect.value = format;
        toggleReportFormat();
    }
    
    // Handle Update Report button loading state
    function handleUpdateReportLoading() {
        const updateBtn = document.getElementById('updateReportBtn');
        const btnContent = updateBtn.querySelector('.btn-content');
        const btnLoading = updateBtn.querySelector('.btn-loading');
        
        if (updateBtn && btnContent && btnLoading) {
            // Show loading state
            btnContent.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            updateBtn.disabled = true;
            updateBtn.style.opacity = '0.7';
            updateBtn.style.cursor = 'not-allowed';
            
            // Create skeleton effect for report sections
            const reportMetrics = document.querySelectorAll('.report-metric');
            reportMetrics.forEach(metric => {
                metric.classList.add('skeleton-loading');
            });
            
            // Add skeleton class to chart areas
            const chartContainers = document.querySelectorAll('[id*="chart"], .chart-container');
            chartContainers.forEach(chart => {
                chart.style.opacity = '0.5';
                chart.style.position = 'relative';
                
                // Create skeleton overlay
                const skeleton = document.createElement('div');
                skeleton.className = 'chart-skeleton';
                skeleton.innerHTML = `
                    <div class="skeleton-bar" style="height: 20px; margin-bottom: 10px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: skeleton-loading 1.5s infinite;"></div>
                    <div class="skeleton-bar" style="height: 15px; margin-bottom: 10px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: skeleton-loading 1.5s infinite; width: 80%;"></div>
                    <div class="skeleton-bar" style="height: 15px; margin-bottom: 10px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: skeleton-loading 1.5s infinite; width: 60%;"></div>
                `;
                skeleton.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; flex-direction: column; justify-content: center; padding: 20px; background: rgba(255,255,255,0.8); z-index: 10;';
                chart.appendChild(skeleton);
            });
        }
    }
    
    // Initialize report on page load (same pattern as dashboard)
    document.addEventListener('DOMContentLoaded', function() {
        // Add small delay to ensure Chart.js is fully loaded
        setTimeout(() => {
            const currentFormat = '{{ $currentFilters["report_format"] ?? "dynamic" }}';
            if (currentFormat === 'dynamic') {
                initializeCharts();
            }

            // Set initial format description
            toggleReportFormat();
        }, 100);
        
        // Add form submit handler for loading state
        const reportForm = document.querySelector('form');
        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                // Debug: Log form data before submission
                const formData = new FormData(reportForm);
                console.log('Form data being submitted:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, '=', value);
                }

                // Check if month filter has a value
                const monthSelect = document.getElementById('monthFilter');
                if (monthSelect) {
                    console.log('Month filter value:', monthSelect.value);
                }

                handleUpdateReportLoading();
            });
        }

        // Add debug info about current filters
        console.log('Current filters on page load:', @json($currentFilters ?? []));
    });

    // Add loading states for charts (same as dashboard)
    function showChartLoading(chartId) {
        const container = document.getElementById(chartId).parentElement;
        container.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">Loading chart...</p>
                </div>
            </div>
        `;
    }

    // Responsive chart handling (same as dashboard)
    window.addEventListener('resize', function() {
        if (typeof Chart !== 'undefined' && Chart.instances) {
            Object.values(Chart.instances).forEach(function(instance) {
                instance.resize();
            });
        }
    });

    // Function to update hidden inputs when dropdowns change
    function updateHiddenInputs() {
        const monthSelect = document.getElementById('monthFilter');
        const departmentSelect = document.getElementById('departmentFilter');
        const hiddenMonth = document.getElementById('hiddenMonth');
        const hiddenDepartment = document.getElementById('hiddenDepartment');

        if (monthSelect && hiddenMonth) {
            hiddenMonth.value = monthSelect.value;
        }
        if (departmentSelect && hiddenDepartment) {
            hiddenDepartment.value = departmentSelect.value;
        }
    }
</script>
@endsection
