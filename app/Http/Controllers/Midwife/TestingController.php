<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Console\Commands\SystemStressTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestingController extends Controller
{
    private const LOAD_TEST_MARKER = 'SYSTEM_LOAD_TEST';

    /**
     * Display the System Testing Dashboard.
     */
    public function index()
    {
        $metrics = $this->getCurrentMetrics();
        $loadTestMetrics = $this->getLoadTestMetrics();

        return view('midwife.testing.index', compact('metrics', 'loadTestMetrics'));
    }

    /**
     * Run the stress test data generation.
     */
    public function startTest(Request $request)
    {
        $request->validate([
            'patient_count' => 'required|integer|min:50|max:20000',
            'days'          => 'required|integer|min:30|max:1095',
            'start_year'    => 'required|integer|min:2020|max:' . Carbon::now()->year,
        ]);

        $patientCount = (int) $request->patient_count;
        $days         = (int) $request->days;
        $startYear    = (int) $request->start_year;

        try {
            $startTime = microtime(true);

            // Pre-benchmarks
            $command       = new SystemStressTest();
            $preBenchmarks = $command->runBenchmarks();

            // Generate the data
            $stats = $this->generateLoadData($patientCount, $days, $startYear);

            // Post-benchmarks
            $postBenchmarks = $command->runBenchmarks();

            $elapsed = round(microtime(true) - $startTime, 2);

            return response()->json([
                'success'        => true,
                'elapsed_s'      => $elapsed,
                'stats'          => $stats,
                'pre_benchmarks' => $preBenchmarks,
                'post_benchmarks' => $postBenchmarks,
                'message'        => "Successfully generated {$stats['patients']} patients and " . number_format(
                    $stats['prenatal'] + $stats['checkups'] + $stats['children'] + $stats['immunizations']
                ) . " cascading records in {$elapsed}s.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run benchmarks only (no data insertion).
     */
    public function runBenchmark()
    {
        try {
            $command    = new SystemStressTest();
            $benchmarks = $command->runBenchmarks();
            $metrics    = $this->getCurrentMetrics();

            return response()->json([
                'success'    => true,
                'benchmarks' => $benchmarks,
                'metrics'    => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Benchmark failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Purge all stress-test data.
     */
    public function cleanup()
    {
        try {
            $deletedCounts = $this->purgeLoadTestData();

            return response()->json([
                'success' => true,
                'deleted' => $deletedCounts,
                'message' => "Cleaned up {$deletedCounts['patients']} load-test patients and all cascading records.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current database row counts.
     */
    private function getCurrentMetrics(): array
    {
        return [
            'total_patients'       => DB::table('patients')->whereNull('deleted_at')->count(),
            'total_prenatal'       => DB::table('prenatal_records')->whereNull('deleted_at')->count(),
            'total_checkups'       => DB::table('prenatal_checkups')->count(),
            'total_children'       => DB::table('child_records')->count(),
            'total_immunizations'  => DB::table('immunizations')->count(),
            'load_test_patients'   => DB::table('patients')->where('occupation', self::LOAD_TEST_MARKER)->count(),
        ];
    }

    /**
     * Get breakdown of load-test vs real data.
     */
    private function getLoadTestMetrics(): array
    {
        $ltPatientIds = DB::table('patients')
            ->where('occupation', self::LOAD_TEST_MARKER)
            ->pluck('id');

        $ltPrenatalIds = DB::table('prenatal_records')
            ->whereIn('patient_id', $ltPatientIds)
            ->pluck('id');

        $ltChildIds = DB::table('child_records')
            ->whereIn('mother_id', $ltPatientIds)
            ->pluck('id');

        return [
            'load_test_patients'      => $ltPatientIds->count(),
            'load_test_prenatal'      => $ltPrenatalIds->count(),
            'load_test_checkups'      => DB::table('prenatal_checkups')
                ->whereIn('prenatal_record_id', $ltPrenatalIds)->count(),
            'load_test_children'      => $ltChildIds->count(),
            'load_test_immunizations' => DB::table('immunizations')
                ->whereIn('child_record_id', $ltChildIds)->count(),
        ];
    }

    /**
     * Optimized bulk data generator (same logic as Artisan command for web use).
     */
    private function generateLoadData(int $targetPatients, int $days, int $startYear): array
    {
        $stats = ['patients' => 0, 'prenatal' => 0, 'checkups' => 0, 'children' => 0, 'immunizations' => 0];

        $firstNames = ['Maria','Juana','Elena','Rose','Liza','Ana','Belen','Cora','Dina','Gina',
                       'Sandra','Lourdes','Maricel','Rowena','Nenita','Erlinda','Cristina','Marites'];
        $lastNames  = ['Santos','Reyes','Cruz','Garcia','Mendoza','Pascual','Dela Cruz','Villanueva',
                       'Torres','Bautista','Aquino','Ramos','Flores','Gonzales','Navarro'];
        $vaccineNames = ['BCG','IPV','MCV','OPV','PCV','Pentavalent','Hepatitis B','Vitamin A'];

        $startDate = Carbon::create($startYear, 1, 1);
        // If the start date + days would cross into the future, adjust the start date backwards from now
        // to ensure we cover the full duration and generate all target patients.
        if ($startDate->copy()->addDays($days)->gt(Carbon::now())) {
            $startDate = Carbon::now()->subDays($days);
        }
        $patientsPerDay = $targetPatients / $days;

        // Build day-by-day distribution
        $dayPlan   = [];
        $generated = 0;
        for ($d = 0; $d < $days && $generated < $targetPatients; $d++) {
            $currentDate = $startDate->copy()->addDays($d);
            if ($currentDate->gt(now())) break;
            $base  = (int) floor($patientsPerDay);
            $extra = (mt_rand(0, 100) < (($patientsPerDay - $base) * 100)) ? 1 : 0;
            $today = min($base + $extra, $targetPatients - $generated);
            $dayPlan[] = ['date' => $currentDate->format('Y-m-d'), 'count' => $today];
            $generated += $today;
        }

        $patientRows     = [];
        $prenatalRows    = [];
        $checkupRows     = [];
        $childRows       = [];
        $immunizRows     = [];

        $lastId          = DB::table('patients')->max('id') ?? 0;
        $newPatientId    = $lastId + 1;
        $prenatalId      = (DB::table('prenatal_records')->max('id') ?? 0) + 1;
        $childId         = (DB::table('child_records')->max('id') ?? 0) + 1;
        $vaccineIds      = DB::table('vaccines')->pluck('id')->toArray();

        $flush = function () use (&$patientRows, &$prenatalRows, &$checkupRows, &$childRows, &$immunizRows) {
            if (!empty($patientRows))   { DB::table('patients')->insert($patientRows);           $patientRows  = []; }
            if (!empty($prenatalRows))  { DB::table('prenatal_records')->insert($prenatalRows);   $prenatalRows = []; }
            if (!empty($checkupRows))   { DB::table('prenatal_checkups')->insert($checkupRows);   $checkupRows  = []; }
            if (!empty($childRows))     { DB::table('child_records')->insert($childRows);         $childRows    = []; }
            if (!empty($immunizRows))   { DB::table('immunizations')->insert($immunizRows);       $immunizRows  = []; }
        };

        foreach ($dayPlan as $dayData) {
            $date  = $dayData['date'];
            $count = $dayData['count'];

            for ($i = 0; $i < $count; $i++) {
                $fName = $firstNames[array_rand($firstNames)];
                $lName = $lastNames[array_rand($lastNames)];
                $age   = mt_rand(18, 42);

                $patientRows[] = [
                    'formatted_patient_id' => 'LT-' . str_pad($newPatientId, 6, '0', STR_PAD_LEFT),
                    'name'                 => "{$fName} {$lName}",
                    'first_name'           => $fName,
                    'last_name'            => $lName,
                    'age'                  => $age,
                    'date_of_birth'        => Carbon::parse($date)->subYears($age)->format('Y-m-d'),
                    'contact'              => '09' . mt_rand(100000000, 999999999),
                    'emergency_contact'    => '09' . mt_rand(100000000, 999999999),
                    'address'             => 'Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
                    'occupation'          => self::LOAD_TEST_MARKER,
                    'created_at'          => $date . ' 08:00:00',
                    'updated_at'          => $date . ' 08:00:00',
                ];

                $regDate = Carbon::parse($date);

                // Prenatal (80%)
                if (mt_rand(1, 10) <= 8) {
                    $lmp   = $regDate->copy()->subWeeks(mt_rand(4, 20));
                    $edd   = $lmp->copy()->addDays(280);
                    $totD  = $lmp->diffInDays($regDate);
                    $wks   = intval($totD / 7);
                    $dys   = $totD % 7;
                    $trim  = $wks <= 12 ? 1 : ($wks <= 26 ? 2 : 3);

                    $prenatalRows[] = [
                        'patient_id'            => $newPatientId,
                        'last_menstrual_period' => $lmp->format('Y-m-d'),
                        'expected_due_date'     => $edd->format('Y-m-d'),
                        'gestational_age'       => "{$wks} weeks {$dys} days",
                        'trimester'             => $trim,
                        'gravida'               => mt_rand(1, 4),
                        'para'                  => mt_rand(0, 3),
                        'status'                => 'normal',
                        'created_at'            => $date . ' 08:00:00',
                        'updated_at'            => $date . ' 08:00:00',
                    ];

                    // Checkups
                    $cCount = mt_rand(2, 4);
                    for ($j = 0; $j < $cCount; $j++) {
                        $cd = $regDate->copy()->addWeeks($j * 4);
                        if ($cd->gt(now())) break;
                        $checkupRows[] = [
                            'prenatal_record_id' => $prenatalId,
                            'patient_id'         => $newPatientId,
                            'checkup_date'       => $cd->format('Y-m-d'),
                            'checkup_time'       => '09:00:00',
                            'weight'             => mt_rand(50, 78),
                            'bp_high'            => mt_rand(110, 130),
                            'bp_low'             => mt_rand(70, 90),
                            'belly_size'         => 10 + ($j * 3),
                            'baby_heartbeat'     => mt_rand(135, 155),
                            'status'             => 'completed',
                            'notes'              => 'Load test checkup record',
                            'conducted_by'       => 1,
                            'created_at'         => $cd->format('Y-m-d H:i:s'),
                            'updated_at'         => $cd->format('Y-m-d H:i:s'),
                        ];
                        $stats['checkups']++;
                    }

                    // Child (60%)
                    if (mt_rand(1, 10) <= 6 && $regDate->lt(now()->subMonths(7))) {
                        $bDate = $lmp->copy()->addWeeks(mt_rand(38, 41));
                        if ($bDate->lt(now())) {
                            $gender = mt_rand(0, 1) ? 'Male' : 'Female';
                            $childRows[] = [
                                'mother_id'    => $newPatientId,
                                'first_name'   => ($gender === 'Male' ? 'Juan' : 'Maria') . ' Jr',
                                'last_name'    => $lastNames[array_rand($lastNames)],
                                'birthdate'    => $bDate->format('Y-m-d'),
                                'gender'       => $gender,
                                'birth_weight' => mt_rand(2500, 4200) / 1000,
                                'birth_height' => mt_rand(48, 54),
                                'created_at'   => $bDate->format('Y-m-d H:i:s'),
                                'updated_at'   => $bDate->format('Y-m-d H:i:s'),
                            ];

                            if (!empty($vaccineIds)) {
                                $vaxCount    = mt_rand(3, 5);
                                $selectedVax = array_slice($vaccineIds, 0, $vaxCount);
                                foreach ($selectedVax as $vaxId) {
                                    $sd = $bDate->copy()->addWeeks(mt_rand(0, 20));
                                    if ($sd->gt(now())) continue;
                                    $immunizRows[] = [
                                        'child_record_id' => $childId,
                                        'vaccine_id'      => $vaxId,
                                        'vaccine_name'    => $vaccineNames[array_rand($vaccineNames)],
                                        'dose'            => '1st Dose',
                                        'schedule_date'   => $sd->format('Y-m-d'),
                                        'schedule_time'   => '10:00:00',
                                        'status'          => 'Done',
                                        'notes'           => 'Load test immunization',
                                        'created_at'      => $sd->format('Y-m-d H:i:s'),
                                        'updated_at'      => $sd->format('Y-m-d H:i:s'),
                                    ];
                                    $stats['immunizations']++;
                                }
                            }
                            $childId++;
                            $stats['children']++;
                        }
                    }

                    $prenatalId++;
                    $stats['prenatal']++;
                }

                $newPatientId++;
                $stats['patients']++;

                // Flush on chunk boundary
                if ($stats['patients'] % 500 === 0) {
                    $flush();
                }
            }
        }

        $flush(); // Final flush

        return $stats;
    }

    /**
     * Purge load-test data safely.
     */
    private function purgeLoadTestData(): array
    {
        $counts = ['patients' => 0, 'prenatal' => 0, 'checkups' => 0, 'children' => 0, 'immunizations' => 0];

        $ltPatientIds = DB::table('patients')
            ->where('occupation', self::LOAD_TEST_MARKER)
            ->pluck('id')->toArray();

        if (empty($ltPatientIds)) return $counts;

        $counts['patients'] = count($ltPatientIds);

        $ltPrenatalIds = DB::table('prenatal_records')
            ->whereIn('patient_id', $ltPatientIds)
            ->pluck('id')->toArray();

        $ltChildIds = DB::table('child_records')
            ->whereIn('mother_id', $ltPatientIds)
            ->pluck('id')->toArray();

        if (!empty($ltChildIds)) {
            $counts['immunizations'] = DB::table('immunizations')
                ->whereIn('child_record_id', $ltChildIds)->count();
            DB::table('immunizations')->whereIn('child_record_id', $ltChildIds)->delete();

            $counts['children'] = DB::table('child_records')
                ->whereIn('mother_id', $ltPatientIds)->count();
            DB::table('child_records')->whereIn('mother_id', $ltPatientIds)->delete();
        }

        if (!empty($ltPrenatalIds)) {
            $counts['checkups'] = DB::table('prenatal_checkups')
                ->whereIn('prenatal_record_id', $ltPrenatalIds)->count();
            DB::table('prenatal_checkups')->whereIn('prenatal_record_id', $ltPrenatalIds)->delete();

            $counts['prenatal'] = count($ltPrenatalIds);
            DB::table('prenatal_records')->whereIn('patient_id', $ltPatientIds)->delete();
        }

        DB::table('patients')->whereIn('id', $ltPatientIds)->delete();

        return $counts;
    }
}
