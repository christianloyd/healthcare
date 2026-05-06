<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\ChildRecord;
use App\Models\Immunization;
use App\Models\ChildImmunization;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function bhwIndex(Request $request)
    {
        $stats = $this->getBhwStatistics($request);
        return view('bhw.report', $stats);
    }

    public function midwifeIndex(Request $request)
    {
        $stats = $this->getMidwifeStatistics($request);
        return view('midwife.reports.index', $stats);
    }

    private function getBhwStatistics($request)
    {
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;

        // Get filter parameters
        $reportFormat = $request->get('report_format', 'dynamic');
        $reportType = $request->get('report_type', 'monthly');
        $month = $request->get('month'); // No default - null means show all data
        $department = $request->get('department', 'all');

        // Parse the month filter (null means show all data)
        $filterDate = null;
        if ($month && !empty($month)) {
            try {
                $filterDate = Carbon::createFromFormat('Y-m', $month);
            } catch (\Exception $e) {
                // If month format is invalid, log error and use null (show all data)
                \Log::warning('Invalid month format in BHW report filter: ' . $month);
                $filterDate = null;
            }
        }
        
        if ($filterDate) {
            $totalPatients = Patient::whereMonth('created_at', $filterDate->month)
                                    ->whereYear('created_at', $filterDate->year)
                                    ->count();
            $totalPrenatalRecords = PrenatalRecord::whereMonth('created_at', $filterDate->month)
                                                  ->whereYear('created_at', $filterDate->year)
                                                  ->count();
            $totalChildRecords = ChildRecord::whereMonth('created_at', $filterDate->month)
                                            ->whereYear('created_at', $filterDate->year)
                                            ->count();
            $totalChildImmunizations = Immunization::whereMonth('schedule_date', $filterDate->month)
                                                  ->whereYear('schedule_date', $filterDate->year)
                                                  ->where('status', 'Done')
                                                  ->count();
            $thisMonthCheckups = $totalPrenatalRecords;
        } else {
            $totalPatients = Patient::count();
            $totalPrenatalRecords = PrenatalRecord::count();
            $totalChildRecords = ChildRecord::count();
            $totalChildImmunizations = Immunization::where('status', 'Done')->count();
            $thisMonthCheckups = $totalPrenatalRecords;
        }

        $stats = [
            'totalPatients' => $totalPatients,
            'totalPrenatalRecords' => $totalPrenatalRecords,
            'totalChildRecords' => $totalChildRecords,
            'thisMonthCheckups' => $thisMonthCheckups,
            'totalChildImmunizations' => $totalChildImmunizations,
            'totalImmunizedGirls' => ChildRecord::where('gender', 'Female')
                                               ->whereHas('immunizations', function($query) use ($filterDate) {
                                                   $query->where('status', 'Done');
                                                   if ($filterDate) {
                                                       $query->whereMonth('schedule_date', $filterDate->month)
                                                             ->whereYear('schedule_date', $filterDate->year);
                                                   }
                                               })->distinct()->count(),
            'totalImmunizedBoys' => ChildRecord::where('gender', 'Male')
                                              ->whereHas('immunizations', function($query) use ($filterDate) {
                                                  $query->where('status', 'Done');
                                                  if ($filterDate) {
                                                      $query->whereMonth('schedule_date', $filterDate->month)
                                                            ->whereYear('schedule_date', $filterDate->year);
                                                  }
                                              })->distinct()->count(),
            'currentMonth' => $month,
            'availableMonths' => $this->getAvailableMonths(),
        ];
        
        // Add filter options
        $stats['currentFilters'] = [
            'report_format' => $reportFormat,
            'report_type' => $reportType,
            'month' => $month,
            'department' => $department,
        ];
        
        // BHW doesn't need charts, so provide empty charts data
        $stats['charts'] = ['weekly_trends' => ['labels' => [], 'checkups' => [], 'vaccinations' => []], 'service_distribution' => ['labels' => [], 'data' => []]];
        
        // Only load detailed data for dynamic reports to improve performance
        if ($reportFormat === 'dynamic') {
            $stats['communityActivities'] = $this->getBhwCommunityActivities($filterDate);
            $stats['homeVisits'] = $this->getBhwHomeVisits($filterDate);
            $stats['healthEducation'] = $this->getBhwHealthEducation($filterDate);
        } else {
            // For custom reports, we'll load different data structure
            $stats['customReportData'] = $this->getBhwCustomReportData($filterDate);
        }
        
        return $stats;
    }

    private function getMidwifeStatistics($request)
    {
        $currentDate = Carbon::now();

        // Get filter parameters
        $reportFormat = $request->get('report_format', 'dynamic');
        $reportType = $request->get('report_type', 'monthly');
        $month = $request->get('month'); // No default - null means show all data
        $department = $request->get('department', 'all');

        // Parse the month filter (null means show all data)
        $filterDate = null;
        if ($month && !empty($month) && $month !== '') {
            try {
                $filterDate = Carbon::createFromFormat('Y-m', $month);
                \Log::info('Midwife report filter - Month: ' . $month . ', FilterDate: ' . ($filterDate ? $filterDate->toDateString() : 'null'));
            } catch (\Exception $e) {
                // If month format is invalid, log error and use null (show all data)
                \Log::warning('Invalid month format in Midwife report filter: ' . $month . ', Error: ' . $e->getMessage());
                $filterDate = null;
            }
        } else {
            \Log::info('Midwife report filter - No month filter applied (showing all data)');
        }
        
        // Base statistics
        $totalPatients = Patient::count();

        // Build checkups query
        $checkupsQuery = PrenatalRecord::query();
        if ($filterDate) {
            $checkupsQuery->whereMonth('created_at', $filterDate->month)
                         ->whereYear('created_at', $filterDate->year);
            \Log::info('Midwife report - Filtering checkups for month: ' . $filterDate->month . ', year: ' . $filterDate->year);
        }
        $totalCheckups = $checkupsQuery->count();

        // Build vaccinations query
        $vaccinationsQuery = Immunization::where('status', 'Done');
        if ($filterDate) {
            $vaccinationsQuery->whereMonth('schedule_date', $filterDate->month)
                             ->whereYear('schedule_date', $filterDate->year);
            \Log::info('Midwife report - Filtering vaccinations for month: ' . $filterDate->month . ', year: ' . $filterDate->year);
        }
        $totalVaccinations = $vaccinationsQuery->count();

        $stats = [
            'totalPatients' => $totalPatients,
            'totalCheckups' => $totalCheckups,
            'totalVaccinations' => $totalVaccinations,
            'totalChildren' => ChildRecord::count(),
            'totalImmunizedGirls' => ChildRecord::where('gender', 'Female')
                                               ->whereHas('immunizations', function($query) use ($filterDate) {
                                                   $query->where('status', 'Done');
                                                   if ($filterDate) {
                                                       $query->whereMonth('schedule_date', $filterDate->month)
                                                             ->whereYear('schedule_date', $filterDate->year);
                                                   }
                                               })->distinct()->count(),
            'totalImmunizedBoys' => ChildRecord::where('gender', 'Male')
                                              ->whereHas('immunizations', function($query) use ($filterDate) {
                                                  $query->where('status', 'Done');
                                                  if ($filterDate) {
                                                      $query->whereMonth('schedule_date', $filterDate->month)
                                                            ->whereYear('schedule_date', $filterDate->year);
                                                  }
                                              })->distinct()->count(),
            'upcomingImmunizations' => $filterDate 
                ? Immunization::where('status', 'Upcoming')
                              ->whereMonth('schedule_date', $filterDate->month)
                              ->whereYear('schedule_date', $filterDate->year)
                              ->count()
                : Immunization::where('status', 'Upcoming')->count(),
            'upcomingCheckups' => $filterDate 
                ? PrenatalRecord::where('status', 'normal')
                                ->whereNotNull('next_appointment')
                                ->whereMonth('next_appointment', $filterDate->month)
                                ->whereYear('next_appointment', $filterDate->year)
                                ->count()
                : PrenatalRecord::where('status', 'normal')
                                ->whereNotNull('next_appointment')
                                ->count(),
        ];

        // Store original immunization data before department filtering
        $originalImmunizationData = [
            'totalVaccinations' => $stats['totalVaccinations'],
            'totalImmunizedGirls' => $stats['totalImmunizedGirls'],
            'totalImmunizedBoys' => $stats['totalImmunizedBoys'],
            'upcomingImmunizations' => $stats['upcomingImmunizations']
        ];
        
        // Store original checkup data before department filtering  
        $originalCheckupData = [
            'totalCheckups' => $stats['totalCheckups'],
            'upcomingCheckups' => $stats['upcomingCheckups']
        ];
        
        // Get department-specific data if filtered
        if ($department !== 'all') {
            if ($department === 'prenatal') {
                // Filter to only prenatal-related data
                $stats['totalVaccinations'] = 0;
                $stats['totalImmunizedGirls'] = 0;
                $stats['totalImmunizedBoys'] = 0;
                $stats['upcomingImmunizations'] = 0;
            } elseif ($department === 'immunization') {
                // Filter to only immunization-related data
                $stats['totalCheckups'] = 0;
                $stats['upcomingCheckups'] = 0;
            }
        }
        
        // Always provide immunization statistics for the Child Immunization Statistics section
        $stats['childImmunizationStats'] = $originalImmunizationData;
        
        // Always provide checkup statistics for the Monthly Summary section
        $stats['checkupStats'] = $originalCheckupData;

        // Add charts data
        $stats['charts'] = $this->getChartsData($filterDate, $department);
        
        // Add filter options
        $stats['currentFilters'] = [
            'report_format' => $reportFormat,
            'report_type' => $reportType,
            'month' => $month, // Can be null for "All Data"
            'department' => $department,
        ];
        
        $stats['availableMonths'] = $this->getAvailableMonths();
        
        // Always load charts data for midwives
        if (!isset($stats['charts'])) {
            $stats['charts'] = $this->getChartsData($filterDate, $department);
        }
        
        // Only load detailed data for dynamic reports to improve performance
        if ($reportFormat === 'dynamic') {
            $stats['patientDemographics'] = $this->getPatientDemographics($filterDate);
        } else {
            // For custom reports, we'll load different data structure
            $stats['customReportData'] = $this->getCustomReportData($filterDate);
        }

        return $stats;
    }

    private function getAvailableMonths()
    {
        $months = [
            '' => 'All Data' // Default option to show all data
        ];
        
        $startDate = Carbon::now()->subMonths(35);
        
        for ($i = 0; $i < 36; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }
        
        return array_reverse($months, true); // Show newest months first
    }

    private function getDepartmentMultiplier($department)
    {
        // Return 1.0 for actual departments since we want full data
        // This function could be used for future scaling if needed
        $multipliers = [
            'prenatal' => 1.0,
            'immunization' => 1.0,
        ];

        return $multipliers[$department] ?? 1.0;
    }

    private function getChartsData($filterDate, $department)
    {
        // Weekly trends for the month
        $weeklyData = $this->getWeeklyTrends($filterDate);

        // Service distribution
        $serviceDistribution = $this->getServiceDistribution($filterDate, $department);

        // Vaccine usage data
        $vaccineUsageData = $this->getVaccineUsageData($filterDate);

        return [
            'weekly_trends' => $weeklyData,
            'service_distribution' => $serviceDistribution,
            'vaccine_usage' => $vaccineUsageData,
        ];
    }

    private function getWeeklyTrends($filterDate)
    {
        // If no filterDate, use current month for trends
        if (!$filterDate) {
            $filterDate = Carbon::now();
        }
        
        $startOfMonth = $filterDate->copy()->startOfMonth();
        $endOfMonth = $filterDate->copy()->endOfMonth();
        
        $weeks = [];
        $checkupsData = [];
        $vaccinationsData = [];
        
        // Get data for each week of the month
        $currentWeek = $startOfMonth->copy();
        $weekNumber = 1;
        
        while ($currentWeek->lte($endOfMonth)) {
            $weekEnd = $currentWeek->copy()->addDays(6)->min($endOfMonth);
            
            $checkups = PrenatalRecord::whereBetween('created_at', [$currentWeek, $weekEnd])->count();
            $vaccinations = Immunization::whereBetween('schedule_date', [$currentWeek, $weekEnd])
                                           ->where('status', 'Done')
                                           ->count();
            
            $weeks[] = "Week $weekNumber";
            $checkupsData[] = $checkups;
            $vaccinationsData[] = $vaccinations;
            
            $currentWeek->addWeek();
            $weekNumber++;
            
            if ($weekNumber > 4) break; // Limit to 4 weeks
        }
        
        return [
            'labels' => $weeks,
            'checkups' => $checkupsData,
            'vaccinations' => $vaccinationsData,
        ];
    }

    private function getServiceDistribution($filterDate, $department)
    {
        $prenatalCount = $filterDate 
            ? PrenatalRecord::whereMonth('created_at', $filterDate->month)
                            ->whereYear('created_at', $filterDate->year)
                            ->count()
            : PrenatalRecord::count();
        
        $vaccinationCount = $filterDate 
            ? Immunization::whereMonth('schedule_date', $filterDate->month)
                         ->whereYear('schedule_date', $filterDate->year)
                         ->where('status', 'Done')
                         ->count()
            : Immunization::where('status', 'Done')->count();
        
        // Apply department filter
        if ($department !== 'all') {
            if ($department === 'prenatal') {
                return [
                    'labels' => ['Prenatal Care'],
                    'data' => [$prenatalCount],
                ];
            } elseif ($department === 'immunization') {
                return [
                    'labels' => ['Immunizations'],
                    'data' => [$vaccinationCount],
                ];
            }
        }
        
        return [
            'labels' => ['Prenatal Care', 'Immunizations'],
            'data' => [$prenatalCount, $vaccinationCount],
        ];
    }

    private function getVaccineUsageData($filterDate)
    {
        try {
            // Use same pattern as dashboard - Immunization table with vaccine_name
            $query = Immunization::select('vaccine_name', DB::raw('COUNT(*) as count'))
                                 ->whereNotNull('vaccine_name')
                                 ->where('vaccine_name', '!=', '');

            // Apply date filter if specified
            if ($filterDate) {
                $query->whereMonth('schedule_date', $filterDate->month)
                      ->whereYear('schedule_date', $filterDate->year)
                      ->where('status', 'Done'); // Only count completed immunizations
            } else {
                $query->where('status', 'Done'); // Only count completed immunizations
            }

            $vaccineData = $query->groupBy('vaccine_name')
                                ->orderBy('count', 'desc')
                                ->limit(10) // Top 10 most used vaccines
                                ->get();

            // Always return the expected structure, even if empty
            return [
                'labels' => $vaccineData->pluck('vaccine_name')->toArray(),
                'data' => $vaccineData->pluck('count')->toArray(),
            ];

        } catch (\Exception $e) {
            // Log error and return safe fallback
            \Log::error('Error fetching vaccine usage data: ' . $e->getMessage());

            return [
                'labels' => [],
                'data' => [],
            ];
        }
    }



    private function getPatientDemographics($filterDate)
    {
        // Get patients by age groups (for prenatal patients - adults)
        $patientAgeGroups = [
            ['min' => 18, 'max' => 25, 'label' => '18-25 years'],
            ['min' => 26, 'max' => 30, 'label' => '26-30 years'],
            ['min' => 31, 'max' => 35, 'label' => '31-35 years'],
            ['min' => 36, 'max' => 100, 'label' => '36+ years'],
        ];

        // Get child records by age groups (for immunization - children)
        $childAgeGroups = [
            ['min' => 0, 'max' => 1, 'label' => '0-1 years (Infants)'],
            ['min' => 1, 'max' => 3, 'label' => '1-3 years (Toddlers)'],
            ['min' => 3, 'max' => 6, 'label' => '3-6 years (Preschool)'],
            ['min' => 6, 'max' => 12, 'label' => '6-12 years (School Age)'],
        ];

        $demographics = [];

        // OPTIMIZED: Get all patient demographics in a single query
        // NOTE: selectRaw is safe here - no user input, static aggregation for performance
        $patientStats = Patient::selectRaw('
            COUNT(*) as total_all,
            COUNT(CASE WHEN age BETWEEN 18 AND 24 THEN 1 END) as age_18_24_total,
            COUNT(CASE WHEN age BETWEEN 25 AND 34 THEN 1 END) as age_25_34_total,
            COUNT(CASE WHEN age BETWEEN 35 AND 44 THEN 1 END) as age_35_44_total,
            COUNT(CASE WHEN age >= 45 THEN 1 END) as age_45_plus_total
        ')->first();

        // NOTE: selectRaw is safe here - no user input, static aggregation for performance
        $patientNewStats = $filterDate
            ? Patient::whereMonth('created_at', $filterDate->month)
                     ->whereYear('created_at', $filterDate->year)
                     ->selectRaw('
                        COUNT(CASE WHEN age BETWEEN 18 AND 24 THEN 1 END) as age_18_24_new,
                        COUNT(CASE WHEN age BETWEEN 25 AND 34 THEN 1 END) as age_25_34_new,
                        COUNT(CASE WHEN age BETWEEN 35 AND 44 THEN 1 END) as age_35_44_new,
                        COUNT(CASE WHEN age >= 45 THEN 1 END) as age_45_plus_new
                     ')->first()
            : $patientStats;

        // Add patient demographics (prenatal care)
        foreach ($patientAgeGroups as $group) {
            $ageKey = str_replace([' ', '-'], '_', strtolower($group['label']));
            $totalKey = $ageKey . '_total';
            $newKey = $ageKey . '_new';

            $demographics[] = [
                'age_group' => $group['label'],
                'total_patients' => $patientStats->$totalKey ?? 0,
                'new_patients' => $patientNewStats->$newKey ?? $patientStats->$totalKey ?? 0,
                'immunized_count' => 0, // Not applicable for adult patients
            ];
        }

        // OPTIMIZED: Get all child demographics in a single query
        foreach ($childAgeGroups as $group) {
            $maxBirthdate = now()->subYears($group['min'])->format('Y-m-d');
            $minBirthdate = now()->subYears($group['max'])->format('Y-m-d');

            $totalChildren = ChildRecord::whereBetween('birthdate', [$minBirthdate, $maxBirthdate])->count();

            $newChildren = $filterDate
                ? ChildRecord::whereBetween('birthdate', [$minBirthdate, $maxBirthdate])
                             ->whereMonth('created_at', $filterDate->month)
                             ->whereYear('created_at', $filterDate->year)
                             ->count()
                : $totalChildren;

            $immunizedCount = ChildRecord::whereBetween('birthdate', [$minBirthdate, $maxBirthdate])
                                        ->whereHas('immunizations', function($query) use ($filterDate) {
                                            $query->where('status', 'Done');
                                            if ($filterDate) {
                                                $query->whereMonth('schedule_date', $filterDate->month)
                                                      ->whereYear('schedule_date', $filterDate->year);
                                            }
                                        })->distinct()->count();

            $demographics[] = [
                'age_group' => $group['label'],
                'total_patients' => $totalChildren,
                'new_patients' => $newChildren,
                'immunized_count' => $immunizedCount,
            ];
        }

        return $demographics;
    }
    
    private function getCustomReportData($filterDate)
    {
        // This method will be customized based on the user's paper report format
        // For now, return a placeholder structure that can be easily modified
        
        return [
            'report_header' => [
                'title' => 'Healthcare Activity Report',
                'period' => $filterDate ? $filterDate->format('F Y') : 'All Data',
                'generated_date' => now()->format('F j, Y'),
                'prepared_by' => auth()->user()->name ?? 'System Administrator'
            ],
            
            'summary_statistics' => [
                'total_patients_served' => Patient::count(),
                'prenatal_consultations' => $filterDate 
                    ? PrenatalRecord::whereMonth('created_at', $filterDate->month)
                                    ->whereYear('created_at', $filterDate->year)
                                    ->count()
                    : PrenatalRecord::count(),
                'immunizations_administered' => $filterDate 
                    ? Immunization::whereMonth('schedule_date', $filterDate->month)
                                 ->whereYear('schedule_date', $filterDate->year)
                                 ->where('status', 'Done')
                                 ->count()
                    : Immunization::where('status', 'Done')->count(),
                'child_records_updated' => $filterDate 
                    ? ChildRecord::whereMonth('updated_at', $filterDate->month)
                               ->whereYear('updated_at', $filterDate->year)
                               ->count()
                    : ChildRecord::count()
            ],
            
            // Placeholder sections - will be customized based on user's paper format
            'monthly_activities' => [
                'prenatal_care' => [
                    'new_registrations' => $filterDate 
                        ? Patient::whereMonth('created_at', $filterDate->month)
                                ->whereYear('created_at', $filterDate->year)
                                ->count()
                        : Patient::count(),
                    'follow_up_visits' => $filterDate 
                        ? PrenatalRecord::whereMonth('created_at', $filterDate->month)
                                        ->whereYear('created_at', $filterDate->year)
                                        ->count()
                        : PrenatalRecord::count(),
                    'high_risk_cases' => 2 // Simulated - would be calculated based on actual risk assessment
                ],
                'child_health' => [
                    'immunizations_due' => $filterDate 
                        ? Immunization::whereMonth('schedule_date', $filterDate->month)
                                    ->whereYear('schedule_date', $filterDate->year)
                                    ->where('status', 'Upcoming')
                                    ->count()
                        : Immunization::where('status', 'Upcoming')->count(),
                    'immunizations_completed' => $filterDate 
                        ? Immunization::whereMonth('schedule_date', $filterDate->month)
                                    ->whereYear('schedule_date', $filterDate->year)
                                    ->where('status', 'Done')
                                    ->count()
                        : Immunization::where('status', 'Done')->count(),
                    'nutritional_assessments' => ChildRecord::count() // Placeholder
                ]
            ],
            
            'community_outreach' => [
                'health_education_sessions' => 0, // Placeholder
                'home_visits_conducted' => 0, // Placeholder  
                'referrals_made' => 0 // Placeholder
            ],
            
            'challenges_and_recommendations' => [
                'challenges' => [
                    'Low attendance in some areas',
                    'Vaccine supply delays',
                    'Transportation difficulties'
                ],
                'recommendations' => [
                    'Increase community awareness campaigns',
                    'Improve supply chain management',
                    'Establish additional service points'
                ]
            ]
        ];
    }

    public function generateReport(Request $request)
    {
        $reportType = $request->get('report_type');
        $userRole = auth()->user()->role;
        
        if ($userRole === 'bhw') {
            return $this->bhwIndex($request);
        } else {
            return $this->midwifeIndex($request);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $userRole = auth()->user()->role;
            
            // Get the same data used for the report view
            if ($userRole === 'bhw') {
                $data = $this->getBhwStatistics($request);
            } else {
                $data = $this->getMidwifeStatistics($request);
            }
            
            // Add additional context for PDF
            $data['export_date'] = now()->format('F j, Y g:i A');
            $data['exported_by'] = auth()->user()->name ?? 'System';
            $data['report_title'] = ($userRole === 'bhw' ? 'Community Health Report' : 'Healthcare Report') . ' - ' . ($data['availableMonths'][$data['currentFilters']['month'] ?? ''] ?? 'Current Month');
            $data['userRole'] = $userRole;
            
            // Generate PDF using DOMPDF
            $filename = ($userRole === 'bhw' ? 'bhw' : 'healthcare') . '-report-' . now()->format('Y-m-d-His') . '.pdf';
            
            // Create PDF from the template
            $pdf = Pdf::loadView('reports.pdf-template', $data)
                     ->setPaper('A4', 'portrait')
                     ->setOptions([
                         'dpi' => 150, 
                         'defaultFont' => 'DejaVu Sans',
                         'isHtml5ParserEnabled' => true,
                         'isPhpEnabled' => false,
                         'defaultPaperSize' => 'A4',
                         'defaultPaperOrientation' => 'portrait'
                     ]);
            
            // Return PDF download
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $userRole = auth()->user()->role;
            $exportType = $request->get('export_type', 'full');
            
            // Get the same data used for the report view
            if ($userRole === 'bhw') {
                $data = $this->getBhwStatistics($request);
            } else {
                $data = $this->getMidwifeStatistics($request);
            }
            
            // Generate CSV content
            $csvContent = $this->generateCsvContent($data, $userRole);
            
            // Create filename
            $filename = 'healthcare-report-' . now()->format('Y-m-d-His') . '.csv';
            
            // Return downloadable CSV file
            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, must-revalidate');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function prepareExcelData($data, $exportType, $userRole)
    {
        $excelData = [];
        
        // Summary statistics
        $excelData['summary'] = [
            ['Metric', 'Value'],
            ['Total Patients', $data['totalPatients'] ?? 0],
        ];
        
        if ($userRole === 'midwife') {
            $excelData['summary'][] = ['Total Checkups', $data['totalCheckups'] ?? 0];
            $excelData['summary'][] = ['Total Vaccinations', $data['totalVaccinations'] ?? 0];
            $excelData['summary'][] = ['Satisfaction Rate', ($data['satisfactionRate'] ?? 0) . '%'];
            
            // Top performing midwives
            if (isset($data['topMidwives'])) {
                $excelData['midwives'] = [['Midwife', 'Patient Count', 'Satisfaction', 'Status']];
                foreach ($data['topMidwives'] as $midwife) {
                    $excelData['midwives'][] = [
                        $midwife['name'],
                        $midwife['patient_count'],
                        $midwife['satisfaction'] . '%',
                        $midwife['status']
                    ];
                }
            }
            
            // Child immunization statistics
            $excelData['immunizations'] = [['Category', 'Count']];
            $excelData['immunizations'][] = ['Immunized Girls', $data['totalImmunizedGirls'] ?? 0];
            $excelData['immunizations'][] = ['Immunized Boys', $data['totalImmunizedBoys'] ?? 0];
            $excelData['immunizations'][] = ['Upcoming Immunizations', $data['upcomingImmunizations'] ?? 0];
            $excelData['immunizations'][] = ['Upcoming Checkups', $data['upcomingCheckups'] ?? 0];
            
            // Patient demographics
            if (isset($data['patientDemographics'])) {
                $excelData['demographics'] = [['Age Group', 'Total Patients', 'New Patients', 'Immunized Count']];
                foreach ($data['patientDemographics'] as $demo) {
                    $excelData['demographics'][] = [
                        $demo['age_group'],
                        $demo['total_patients'],
                        $demo['new_patients'],
                        $demo['immunized_count']
                    ];
                }
            }
        } else {
            // BHW specific data
            $excelData['summary'][] = ['Prenatal Records', $data['totalPrenatalRecords'] ?? 0];
            $excelData['summary'][] = ['Child Records', $data['totalChildRecords'] ?? 0];
            $excelData['summary'][] = ['This Month Checkups', $data['thisMonthCheckups'] ?? 0];
        }
        
        return $excelData;
    }

    public function printView(Request $request)
    {
        $userRole = auth()->user()->role;

        // Get the same data used for the report view
        if ($userRole === 'bhw') {
            $data = $this->getBhwStatistics($request);
        } else {
            $data = $this->getMidwifeStatistics($request);
        }

        // Add print-specific data
        $data['print_date'] = now()->format('F j, Y g:i A');
        $data['print_user'] = auth()->user()->name ?? 'System';
        $data['report_title'] = $data['availableMonths'][$data['currentFilters']['month'] ?? ''] ?? 'Current Month';

        // Return appropriate view based on user role
        if ($userRole === 'bhw') {
            return view('bhw.reports.print', $data);
        } else {
            return view('midwife.reports.print', $data);
        }
    }

    public function bhwAccomplishmentPrint(Request $request)
    {
        // Get filter parameters
        $month = $request->get('month', now()->format('F'));
        $year = $request->get('year', now()->format('Y'));
        $barangay = $request->get('barangay', 'Mecalong II');
        $municipality = $request->get('municipality', 'Dumalinao');

        // Parse month/year for filtering
        $filterDate = null;
        try {
            $monthNum = date('m', strtotime($month . ' 1'));
            $filterDate = Carbon::createFromFormat('Y-m', $year . '-' . $monthNum);
        } catch (\Exception $e) {
            $filterDate = Carbon::now();
        }

        // Prepare data structure for the report
        $data = [
            'prenatal' => [
                'quarterly_target' => '',
                'monthly_target' => '',
                'advocated' => PrenatalRecord::whereMonth('created_at', $filterDate->month)
                                            ->whereYear('created_at', $filterDate->year)
                                            ->count(),
                'advocated_percent' => '',
                'total_tracked' => Patient::whereHas('prenatalRecords', function($query) use ($filterDate) {
                    $query->whereMonth('created_at', $filterDate->month)
                          ->whereYear('created_at', $filterDate->year);
                })->count(),
                'teen_tracked' => Patient::whereBetween('age', [10, 19])
                                        ->whereHas('prenatalRecords', function($query) use ($filterDate) {
                                            $query->whereMonth('created_at', $filterDate->month)
                                                  ->whereYear('created_at', $filterDate->year);
                                        })->count(),
                'teen_10_14' => Patient::whereBetween('age', [10, 14])
                                      ->whereHas('prenatalRecords', function($query) use ($filterDate) {
                                          $query->whereMonth('created_at', $filterDate->month)
                                                ->whereYear('created_at', $filterDate->year);
                                      })->count(),
                'teen_15_19' => Patient::whereBetween('age', [15, 19])
                                      ->whereHas('prenatalRecords', function($query) use ($filterDate) {
                                          $query->whereMonth('created_at', $filterDate->month)
                                                ->whereYear('created_at', $filterDate->year);
                                      })->count(),
                'birth_emergency_plan' => 0, // Placeholder - needs database field
                'high_risk' => 0, // Placeholder - needs risk assessment logic
                'facility_delivery' => PrenatalRecord::whereMonth('created_at', $filterDate->month)
                                                    ->whereYear('created_at', $filterDate->year)
                                                    ->count(),
                'delivered_facility' => 0, // Placeholder - needs delivery tracking
            ],
            'postpartum' => [
                'home_visits' => 0, // Placeholder - needs home visit tracking
            ],
            'family_planning' => [
                'referred' => 0, // Placeholder - needs family planning tracking
                'dropouts' => 0, // Placeholder
            ],
            'immunization' => [
                'followed_up' => Immunization::whereMonth('schedule_date', $filterDate->month)
                                            ->whereYear('schedule_date', $filterDate->year)
                                            ->where('status', 'Done')
                                            ->count(),
                'defaulters' => 0, // Placeholder - needs defaulter tracking
            ],
            'nutrition' => [
                'opt' => [
                    'coverage' => '',
                    'normal' => '',
                    'underweight' => '',
                    'severely_underweight' => '',
                    'stunted' => '',
                    'severely_stunted' => '',
                    'wasted' => '',
                    'severely_wasted' => '',
                    'overweight' => '',
                ],
                'monthly_0_23' => [
                    'normal' => ChildRecord::whereBetween('birthdate', [now()->subMonths(23), now()])
                                          ->count(),
                    'underweight' => 0,
                    'severely_underweight' => 0,
                    'stunted' => 0,
                    'severely_stunted' => 0,
                    'wasted' => 0,
                    'severely_wasted' => 0,
                    'overweight' => 0,
                ],
                'monthly_24_59' => [
                    'underweight' => 0,
                    'severely_underweight' => 0,
                    'stunted' => 0,
                    'severely_stunted' => 0,
                    'wasted' => 0,
                    'severely_wasted' => 0,
                ],
                'quarterly_24_59' => [
                    'normal' => '',
                    'overweight' => '',
                ],
                'breastfeed' => [
                    'seen' => 0,
                    'exclusive' => 0,
                ],
                'complementary' => [
                    'started' => 0,
                    'completed' => 0,
                ],
                'vitamin_a' => [
                    '6_11_months' => 0,
                    '12_59_months' => 0,
                ],
            ],
        ];

        // Determine which view to use based on user role
        $userRole = auth()->user()->role ?? 'bhw';
        $viewName = $userRole === 'midwife'
            ? 'midwife.reports.bhw-accomplishment-print'
            : 'bhw.reports.accomplishment-print';

        return view($viewName, [
            'month' => $month,
            'year' => $year,
            'barangay' => $barangay,
            'municipality' => $municipality,
            'data' => $data,
            'prepared_by_name' => $request->get('prepared_by', auth()->user()->name ?? 'BHW'),
            'noted_by_name' => $request->get('noted_by', 'JANETH B. SULTAN, RM'),
            'approved_by_name' => $request->get('approved_by', 'PATRICK KEAN L. TOLEDO, MD'),
        ]);
    }


    private function generateCsvContent($data, $userRole)
    {
        $csv = "Healthcare Report\n";
        $csv .= "Generated: " . now()->format('F j, Y g:i A') . "\n\n";
        
        // Summary Statistics
        $csv .= "Summary Statistics\n";
        $csv .= "Metric,Value\n";
        $csv .= "Total Patients," . ($data['totalPatients'] ?? 0) . "\n";
        
        if ($userRole === 'midwife') {
            $csv .= "Total Checkups," . ($data['totalCheckups'] ?? 0) . "\n";
            $csv .= "Total Vaccinations," . ($data['totalVaccinations'] ?? 0) . "\n";
            $csv .= "Total Children," . ($data['totalChildren'] ?? 0) . "\n";
            
            // Child Immunization Statistics
            $csv .= "\nChild Immunization Statistics\n";
            $csv .= "Category,Count\n";
            $csv .= "Immunized Girls," . ($data['totalImmunizedGirls'] ?? 0) . "\n";
            $csv .= "Immunized Boys," . ($data['totalImmunizedBoys'] ?? 0) . "\n";
            $csv .= "Upcoming Immunizations," . ($data['upcomingImmunizations'] ?? 0) . "\n";
            $csv .= "Upcoming Checkups," . ($data['upcomingCheckups'] ?? 0) . "\n";
            
            // Patient Demographics
            if (isset($data['patientDemographics']) && !empty($data['patientDemographics'])) {
                $csv .= "\nPatient Demographics by Age Group\n";
                $csv .= "Age Group,Total Patients,New Patients,Immunized Count\n";
                foreach ($data['patientDemographics'] as $demo) {
                    $csv .= '"' . $demo['age_group'] . '",' . $demo['total_patients'] . ',' . $demo['new_patients'] . ',' . $demo['immunized_count'] . "\n";
                }
            }
        } else {
            // BHW specific data
            $csv .= "Prenatal Records," . ($data['totalPrenatalRecords'] ?? 0) . "\n";
            $csv .= "Child Records," . ($data['totalChildRecords'] ?? 0) . "\n";
            $csv .= "This Month Checkups," . ($data['thisMonthCheckups'] ?? 0) . "\n";
        }
        
        return $csv;
    }
    
    private function getBhwCommunityActivities($filterDate)
    {
        // BHW-specific community activities data
        return [
            'home_visits' => [
                'total' => intval(Patient::count() * 0.3), // Simulate 30% home visit coverage
                'completed' => intval(Patient::count() * 0.25),
                'pending' => intval(Patient::count() * 0.05)
            ],
            'health_education' => [
                'sessions_conducted' => intval(PrenatalRecord::count() * 0.8),
                'participants' => intval(Patient::count() * 0.6),
                'topics_covered' => 12
            ],
            'referrals' => [
                'total_referrals' => $filterDate 
                    ? intval(PrenatalRecord::whereMonth('created_at', $filterDate->month)->whereYear('created_at', $filterDate->year)->count() * 0.1)
                    : intval(PrenatalRecord::count() * 0.1),
                'successful_referrals' => $filterDate 
                    ? intval(PrenatalRecord::whereMonth('created_at', $filterDate->month)->whereYear('created_at', $filterDate->year)->count() * 0.08)
                    : intval(PrenatalRecord::count() * 0.08),
                'pending_follow_up' => $filterDate 
                    ? intval(PrenatalRecord::whereMonth('created_at', $filterDate->month)->whereYear('created_at', $filterDate->year)->count() * 0.02)
                    : intval(PrenatalRecord::count() * 0.02)
            ]
        ];
    }
    
    private function getBhwHomeVisits($filterDate)
    {
        // Simulate home visit data based on patient records
        return [
            'scheduled' => intval(Patient::count() * 0.4),
            'completed' => intval(Patient::count() * 0.3),
            'missed' => intval(Patient::count() * 0.1),
            'follow_up_needed' => intval(Patient::count() * 0.15)
        ];
    }
    
    private function getBhwHealthEducation($filterDate)
    {
        // Health education activities specific to BHW role
        return [
            'prenatal_education' => PrenatalRecord::count(),
            'child_nutrition' => ChildRecord::count(),
            'family_planning' => intval(Patient::count() * 0.4),
            'immunization_awareness' => intval(ChildRecord::count() * 0.9)
        ];
    }
    
    private function getBhwCustomReportData($filterDate)
    {
        // Custom report data structure for BHW paper format
        return [
            'report_header' => [
                'title' => 'Barangay Health Worker Activity Report',
                'period' => $filterDate ? $filterDate->format('F Y') : 'All Data',
                'generated_date' => now()->format('F j, Y'),
                'prepared_by' => auth()->user()->name ?? 'BHW Representative'
            ],
            
            'community_coverage' => [
                'total_households_assigned' => intval(Patient::count() * 1.2), // Assuming 1.2 patients per household
                'households_visited' => intval(Patient::count() * 0.3),
                'coverage_percentage' => Patient::count() > 0 ? round((intval(Patient::count() * 0.3) / intval(Patient::count() * 1.2)) * 100, 1) : 0
            ],
            
            'health_services_delivered' => [
                'prenatal_monitoring' => [
                    'new_registrations' => $filterDate 
                        ? PrenatalRecord::whereMonth('created_at', $filterDate->month)
                                        ->whereYear('created_at', $filterDate->year)
                                        ->count()
                        : PrenatalRecord::count(),
                    'regular_checkups' => $filterDate 
                        ? PrenatalRecord::whereMonth('created_at', $filterDate->month)
                                        ->whereYear('created_at', $filterDate->year)
                                        ->count()
                        : PrenatalRecord::count(),
                    'high_risk_referrals' => intval(PrenatalRecord::count() * 0.1)
                ],
                'child_health' => [
                    'immunizations_facilitated' => $filterDate
                        ? Immunization::whereMonth('schedule_date', $filterDate->month)
                                      ->whereYear('schedule_date', $filterDate->year)
                                      ->where('status', 'Done')
                                      ->count()
                        : Immunization::where('status', 'Done')->count(),
                    'children_immunized' => $filterDate
                        ? ChildRecord::whereHas('immunizations', function($query) use ($filterDate) {
                            $query->where('status', 'Done')
                                  ->whereMonth('schedule_date', $filterDate->month)
                                  ->whereYear('schedule_date', $filterDate->year);
                        })->distinct()->count()
                        : ChildRecord::whereHas('immunizations', function($query) {
                            $query->where('status', 'Done');
                        })->distinct()->count(),
                    'growth_monitoring' => ChildRecord::count(),
                    'nutrition_counseling' => intval(ChildRecord::count() * 0.8)
                ]
            ],
            
            'community_engagement' => [
                'health_education_sessions' => intval(PrenatalRecord::count() * 0.5),
                'participants_reached' => intval(Patient::count() * 0.6),
                'topics_covered' => [
                    'Maternal Health', 'Child Nutrition', 'Family Planning', 
                    'Immunization', 'Disease Prevention', 'Hygiene and Sanitation'
                ]
            ],
            
            'challenges_encountered' => [
                'transportation_difficulties',
                'low_community_participation_in_some_areas',
                'language_barriers_with_some_families',
                'limited_supplies_for_health_education_materials'
            ],
            
            'recommendations' => [
                'increase_transportation_allowance',
                'conduct_more_community_awareness_campaigns',
                'provide_multilingual_health_materials',
                'establish_regular_supply_replenishment_schedule'
            ]
        ];
    }
}