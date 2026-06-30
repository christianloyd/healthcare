<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemStressTest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:stress-test
                            {--patients=500 : Number of patients to generate}
                            {--days=1095 : Number of days to spread the data (1095 = 3 years)}
                            {--start-year=2023 : Starting year for data generation}
                            {--cleanup : Remove all stress-test data instead of generating}
                            {--benchmark-only : Only run query benchmarks, do not insert data}';

    /**
     * The console command description.
     */
    protected $description = 'Run load and stress testing for the Healthcare Management System. Generates realistic multi-year Barangay data and benchmarks query performance.';

    private const LOAD_TEST_MARKER = 'SYSTEM_LOAD_TEST';
    private const CHUNK_SIZE = 500;

    private array $firstNames = [
        'Maria', 'Juana', 'Elena', 'Rose', 'Liza', 'Ana', 'Belen', 'Cora', 'Dina', 'Gina',
        'Sandra', 'Lourdes', 'Maricel', 'Rowena', 'Nenita', 'Erlinda', 'Cristina', 'Marites',
        'Rosario', 'Teresita', 'Evelyn', 'Josephine', 'Florinda', 'Natividad', 'Pacita',
    ];

    private array $lastNames = [
        'Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Pascual', 'Dela Cruz', 'Villanueva',
        'Torres', 'Bautista', 'Aquino', 'Ramos', 'Flores', 'Gonzales', 'Navarro',
        'Castillo', 'Morales', 'Soriano', 'Padilla', 'Rivera',
    ];

    private array $vaccineNames = [
        'BCG', 'IPV', 'MCV', 'OPV', 'PCV', 'Pentavalent', 'Hepatitis B', 'Vitamin A'
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('cleanup')) {
            return $this->runCleanup();
        }

        $this->displayBanner();

        $patientCount  = (int) $this->option('patients');
        $days          = (int) $this->option('days');
        $startYear     = (int) $this->option('start-year');
        $benchmarkOnly = $this->option('benchmark-only');

        // --- Pre-Generation Benchmark ---
        $this->info('');
        $this->info('📊 <fg=cyan>Phase 1: Pre-Load Query Benchmarks</>');
        $this->line('Running baseline queries before data insertion...');
        $preBenchmarks = $this->runBenchmarks();
        $this->displayBenchmarkTable($preBenchmarks, 'Baseline');

        if ($benchmarkOnly) {
            $this->info('✅ Benchmark-only mode. Skipping data generation.');
            return self::SUCCESS;
        }

        // --- Data Generation ---
        $this->info('');
        $this->info("🚀 <fg=cyan>Phase 2: Load Data Generation</>");
        $this->info("   Target: <fg=yellow>{$patientCount} patients</> over <fg=yellow>{$days} days</> (~" . ceil($patientCount / $days * 365) . " per year)");
        $this->line('');

        $startTime = microtime(true);
        $stats = $this->generateLoadData($patientCount, $days, $startYear);
        $elapsed = round(microtime(true) - $startTime, 2);

        $this->info('');
        $this->info("✅ <fg=green>Data generation complete in {$elapsed}s!</>");
        $this->displayGenerationStats($stats);

        // --- Post-Generation Benchmark ---
        $this->info('');
        $this->info('📊 <fg=cyan>Phase 3: Post-Load Query Benchmarks</>');
        $this->line('Measuring performance impact under load...');
        $postBenchmarks = $this->runBenchmarks();
        $this->displayBenchmarkComparison($preBenchmarks, $postBenchmarks);

        $this->info('');
        $this->info('<fg=green>✅ Stress test complete!</>');
        $this->line('To remove load-test data, run: <fg=yellow>php artisan system:stress-test --cleanup</>');

        return self::SUCCESS;
    }

    /**
     * Generate all load-test data using optimized bulk inserts.
     */
    private function generateLoadData(int $targetPatients, int $days, int $startYear): array
    {
        $stats = [
            'patients'       => 0,
            'prenatal'       => 0,
            'checkups'       => 0,
            'children'       => 0,
            'immunizations'  => 0,
        ];

        $startDate = Carbon::create($startYear, 1, 1);
        // If the start date + days would cross into the future, adjust the start date backwards from now
        // to ensure we cover the full duration and generate all target patients.
        if ($startDate->copy()->addDays($days)->gt(Carbon::now())) {
            $startDate = Carbon::now()->subDays($days);
        }

        $patientsPerDay = $targetPatients / $days;
        $patientBatch  = [];
        $patientMap    = []; // day_index => count of patients that day

        // Pre-compute patient distribution across days
        $totalGenerated = 0;
        for ($d = 0; $d < $days && $totalGenerated < $targetPatients; $d++) {
            $currentDate = $startDate->copy()->addDays($d);
            if ($currentDate->gt(now())) break;

            // How many patients today? Use poisson-like distribution for realism
            $baseDaily = (int) floor($patientsPerDay);
            $extra     = (mt_rand(0, 100) < (($patientsPerDay - $baseDaily) * 100)) ? 1 : 0;
            $todayCount = max(0, $baseDaily + $extra);

            if ($totalGenerated + $todayCount > $targetPatients) {
                $todayCount = $targetPatients - $totalGenerated;
            }

            $patientMap[$d] = ['date' => $currentDate->format('Y-m-d'), 'count' => $todayCount];
            $totalGenerated += $todayCount;
        }

        $this->info("  → Distributing {$totalGenerated} patients across " . count($patientMap) . ' days...');
        $progressBar = $this->output->createProgressBar(count($patientMap));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%');
        $progressBar->start();

        $patientRows    = [];
        $lastInsertedId = DB::table('patients')->max('id') ?? 0;
        $newPatientId   = $lastInsertedId + 1;

        // Prepare patient data in chunks
        foreach ($patientMap as $dayData) {
            $date = $dayData['date'];
            $count = $dayData['count'];

            for ($i = 0; $i < $count; $i++) {
                $firstName = $this->firstNames[array_rand($this->firstNames)];
                $lastName  = $this->lastNames[array_rand($this->lastNames)];
                $age       = mt_rand(18, 42);

                $patientRows[] = [
                    'formatted_patient_id' => 'LT-' . str_pad($newPatientId, 6, '0', STR_PAD_LEFT),
                    'name'                 => "{$firstName} {$lastName}",
                    'first_name'           => $firstName,
                    'last_name'            => $lastName,
                    'age'                  => $age,
                    'date_of_birth'        => Carbon::parse($date)->subYears($age)->format('Y-m-d'),
                    'contact'              => '09' . mt_rand(100000000, 999999999),
                    'emergency_contact'    => '09' . mt_rand(100000000, 999999999),
                    'address'             => 'Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
                    'occupation'          => self::LOAD_TEST_MARKER,
                    'created_at'          => $date . ' 08:00:00',
                    'updated_at'          => $date . ' 08:00:00',
                ];
                $newPatientId++;
            }

            // Bulk insert when chunk is full
            if (count($patientRows) >= self::CHUNK_SIZE) {
                DB::table('patients')->insert($patientRows);
                $stats['patients'] += count($patientRows);
                $patientRows = [];
            }

            $progressBar->advance();
        }

        // Insert remaining patients
        if (!empty($patientRows)) {
            DB::table('patients')->insert($patientRows);
            $stats['patients'] += count($patientRows);
        }

        $progressBar->finish();
        $this->line('');

        // --- Generate cascading records ---
        $this->info('  → Generating prenatal records, checkups, children & immunizations...');

        // Fetch only load-test patient IDs
        $loadTestPatients = DB::table('patients')
            ->where('occupation', self::LOAD_TEST_MARKER)
            ->select('id', 'created_at')
            ->orderBy('id')
            ->get();

        // Get vaccine IDs from DB (fall back to names if not seeded)
        $vaccineIds = DB::table('vaccines')->pluck('id')->toArray();
        if (empty($vaccineIds)) {
            $vaccineIds = [];
        }

        $prenatalRows    = [];
        $checkupRows     = [];
        $childRows       = [];
        $immunizRows     = [];

        $prenatalIdStart = (DB::table('prenatal_records')->max('id') ?? 0) + 1;
        $childIdStart    = (DB::table('child_records')->max('id') ?? 0) + 1;

        $prenatalBar = $this->output->createProgressBar($loadTestPatients->count());
        $prenatalBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
        $prenatalBar->start();

        foreach ($loadTestPatients as $patient) {
            $regDate = Carbon::parse($patient->created_at);

            // 80% chance of prenatal record
            if (mt_rand(1, 10) <= 8) {
                $lmp    = $regDate->copy()->subWeeks(mt_rand(4, 20));
                $edd    = $lmp->copy()->addDays(280);
                $totalD = $lmp->diffInDays($regDate);
                $weeks  = intval($totalD / 7);
                $ddays  = $totalD % 7;
                $trim   = $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3);

                $prenatalRows[] = [
                    'patient_id'            => $patient->id,
                    'last_menstrual_period' => $lmp->format('Y-m-d'),
                    'expected_due_date'     => $edd->format('Y-m-d'),
                    'gestational_age'       => "{$weeks} weeks {$ddays} days",
                    'trimester'             => $trim,
                    'gravida'               => mt_rand(1, 4),
                    'para'                  => mt_rand(0, 3),
                    'status'                => 'normal',
                    'created_at'            => $regDate->format('Y-m-d H:i:s'),
                    'updated_at'            => $regDate->format('Y-m-d H:i:s'),
                ];

                // 2-4 checkups per record
                $checkupCount = mt_rand(2, 4);
                for ($j = 0; $j < $checkupCount; $j++) {
                    $checkupDate = $regDate->copy()->addWeeks($j * 4);
                    if ($checkupDate->gt(now())) break;

                    $checkupRows[] = [
                        'prenatal_record_id' => $prenatalIdStart,
                        'patient_id'         => $patient->id,
                        'checkup_date'       => $checkupDate->format('Y-m-d'),
                        'checkup_time'       => '09:00:00',
                        'weight'             => mt_rand(50, 78),
                        'bp_high'            => mt_rand(110, 130),
                        'bp_low'             => mt_rand(70, 90),
                        'belly_size'         => 10 + ($j * 3),
                        'baby_heartbeat'     => mt_rand(135, 155),
                        'status'             => 'completed',
                        'notes'              => 'Load test checkup record',
                        'conducted_by'       => 1,
                        'created_at'         => $checkupDate->format('Y-m-d H:i:s'),
                        'updated_at'         => $checkupDate->format('Y-m-d H:i:s'),
                    ];
                    $stats['checkups']++;
                }

                // 60% chance of child record (completed pregnancy)
                if (mt_rand(1, 10) <= 6 && $regDate->lt(now()->subMonths(7))) {
                    $birthDate = $lmp->copy()->addWeeks(mt_rand(38, 41));
                    if ($birthDate->lt(now())) {
                        $childGender = mt_rand(0, 1) ? 'Male' : 'Female';
                        $childRows[] = [
                            'mother_id'    => $patient->id,
                            'first_name'   => ($childGender === 'Male' ? 'Juan' : 'Maria') . ' Jr',
                            'last_name'    => $this->lastNames[array_rand($this->lastNames)],
                            'birthdate'    => $birthDate->format('Y-m-d'),
                            'gender'       => $childGender,
                            'birth_weight' => mt_rand(2500, 4200) / 1000,
                            'birth_height' => mt_rand(48, 54),
                            'created_at'   => $birthDate->format('Y-m-d H:i:s'),
                            'updated_at'   => $birthDate->format('Y-m-d H:i:s'),
                        ];

                        // 3-5 immunizations per child
                        if (!empty($vaccineIds)) {
                            $immunizCount = mt_rand(3, 5);
                            $selectedVax  = array_slice($vaccineIds, 0, $immunizCount);

                            foreach ($selectedVax as $vaccineId) {
                                $schedDate = $birthDate->copy()->addWeeks(mt_rand(0, 20));
                                if ($schedDate->gt(now())) continue;

                                $immunizRows[] = [
                                    'child_record_id' => $childIdStart,
                                    'vaccine_id'      => $vaccineId,
                                    'vaccine_name'    => $this->vaccineNames[array_rand($this->vaccineNames)],
                                    'dose'            => '1st Dose',
                                    'schedule_date'   => $schedDate->format('Y-m-d'),
                                    'schedule_time'   => '10:00:00',
                                    'status'          => 'Done',
                                    'notes'           => 'Load test immunization',
                                    'created_at'      => $schedDate->format('Y-m-d H:i:s'),
                                    'updated_at'      => $schedDate->format('Y-m-d H:i:s'),
                                ];
                                $stats['immunizations']++;
                            }
                        }

                        $childIdStart++;
                        $stats['children']++;
                    }
                }

                $prenatalIdStart++;
                $stats['prenatal']++;
            }

            // Flush in chunks
            if (count($prenatalRows) >= self::CHUNK_SIZE) {
                DB::table('prenatal_records')->insert($prenatalRows);
                $prenatalRows = [];
            }
            if (count($checkupRows) >= self::CHUNK_SIZE) {
                DB::table('prenatal_checkups')->insert($checkupRows);
                $checkupRows = [];
            }
            if (count($childRows) >= self::CHUNK_SIZE) {
                DB::table('child_records')->insert($childRows);
                $childRows = [];
            }
            if (count($immunizRows) >= self::CHUNK_SIZE) {
                DB::table('immunizations')->insert($immunizRows);
                $immunizRows = [];
            }

            $prenatalBar->advance();
        }

        // Final flush
        if (!empty($prenatalRows))   DB::table('prenatal_records')->insert($prenatalRows);
        if (!empty($checkupRows))    DB::table('prenatal_checkups')->insert($checkupRows);
        if (!empty($childRows))      DB::table('child_records')->insert($childRows);
        if (!empty($immunizRows))    DB::table('immunizations')->insert($immunizRows);

        $prenatalBar->finish();
        $this->line('');

        return $stats;
    }

    /**
     * Run performance benchmark queries and measure latency.
     */
    public function runBenchmarks(): array
    {
        $benchmarks = [];

        // 1. Patient count query
        $benchmarks[] = $this->measureQuery(
            'Patient count (total)',
            fn () => DB::table('patients')->count()
        );

        // 2. Patient search (LIKE query - heavy)
        $benchmarks[] = $this->measureQuery(
            'Patient search (LIKE filter)',
            fn () => DB::table('patients')
                ->where('first_name', 'LIKE', 'Maria%')
                ->orWhere('last_name', 'LIKE', 'Santos%')
                ->count()
        );

        // 3. Prenatal records join
        $benchmarks[] = $this->measureQuery(
            'Prenatal records with patient join',
            fn () => DB::table('prenatal_records')
                ->join('patients', 'patients.id', '=', 'prenatal_records.patient_id')
                ->where('prenatal_records.status', 'normal')
                ->count()
        );

        // 4. Immunization aggregation (monthly)
        $benchmarks[] = $this->measureQuery(
            'Immunization monthly aggregation',
            fn () => DB::table('immunizations')
                ->selectRaw('MONTH(schedule_date) as month, COUNT(*) as total')
                ->where('status', 'Done')
                ->groupBy(DB::raw('MONTH(schedule_date)'))
                ->get()
        );

        // 5. Dashboard-level complex query
        $benchmarks[] = $this->measureQuery(
            'Dashboard multi-table stats',
            fn () => [
                'patients'      => DB::table('patients')->whereNull('deleted_at')->count(),
                'prenatal'      => DB::table('prenatal_records')->whereNull('deleted_at')->count(),
                'immunizations' => DB::table('immunizations')->where('status', 'Done')->count(),
                'children'      => DB::table('child_records')->count(),
            ]
        );

        // 6. Report generation query (heavy join + aggregation)
        $benchmarks[] = $this->measureQuery(
            'Report: patients by age group',
            fn () => DB::table('patients')
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN age BETWEEN 18 AND 25 THEN 1 ELSE 0 END) as age_18_25,
                    SUM(CASE WHEN age BETWEEN 26 AND 35 THEN 1 ELSE 0 END) as age_26_35,
                    SUM(CASE WHEN age > 35 THEN 1 ELSE 0 END) as age_36_plus
                ')
                ->whereNull('deleted_at')
                ->first()
        );

        return $benchmarks;
    }

    /**
     * Measure a single query's execution time.
     */
    private function measureQuery(string $name, callable $query): array
    {
        $start = microtime(true);
        $result = $query();
        $elapsed = round((microtime(true) - $start) * 1000, 2); // in ms

        return [
            'name'    => $name,
            'time_ms' => $elapsed,
            'status'  => $elapsed < 100 ? 'fast' : ($elapsed < 500 ? 'acceptable' : 'slow'),
        ];
    }

    /**
     * Display a benchmark results table.
     */
    private function displayBenchmarkTable(array $benchmarks, string $phase): void
    {
        $this->table(
            ['Query', "{$phase} Time (ms)", 'Rating'],
            array_map(fn ($b) => [
                $b['name'],
                $b['time_ms'] . 'ms',
                match ($b['status']) {
                    'fast'       => '<fg=green>✅ Fast</>',
                    'acceptable' => '<fg=yellow>⚡ Acceptable</>',
                    default      => '<fg=red>🔴 Slow</>',
                },
            ], $benchmarks)
        );
    }

    /**
     * Display a before/after comparison of benchmarks.
     */
    private function displayBenchmarkComparison(array $pre, array $post): void
    {
        $rows = [];
        foreach ($pre as $i => $preBench) {
            $postBench = $post[$i] ?? null;
            $diff = $postBench ? $postBench['time_ms'] - $preBench['time_ms'] : 0;
            $diffLabel = $diff > 0
                ? "<fg=red>+{$diff}ms</>"
                : "<fg=green>{$diff}ms</>";

            $rows[] = [
                $preBench['name'],
                $preBench['time_ms'] . 'ms',
                $postBench ? $postBench['time_ms'] . 'ms' : 'N/A',
                $diffLabel,
                $postBench ? match ($postBench['status']) {
                    'fast'       => '<fg=green>✅ Fast</>',
                    'acceptable' => '<fg=yellow>⚡ OK</>',
                    default      => '<fg=red>🔴 Slow</>',
                } : '',
            ];
        }

        $this->table(
            ['Query', 'Before (ms)', 'After (ms)', 'Delta', 'Rating'],
            $rows
        );
    }

    /**
     * Display generation statistics.
     */
    private function displayGenerationStats(array $stats): void
    {
        $this->table(
            ['Data Type', 'Records Generated'],
            [
                ['Patients',       number_format($stats['patients'])],
                ['Prenatal Records', number_format($stats['prenatal'])],
                ['Prenatal Checkups', number_format($stats['checkups'])],
                ['Child Records',  number_format($stats['children'])],
                ['Immunizations',  number_format($stats['immunizations'])],
                ['<fg=yellow>TOTAL ROWS</>', '<fg=yellow>' . number_format(
                    $stats['patients'] + $stats['prenatal'] + $stats['checkups']
                    + $stats['children'] + $stats['immunizations']
                ) . '</>'],
            ]
        );
    }

    /**
     * Remove all stress-test generated data.
     */
    private function runCleanup(): int
    {
        $this->displayBanner();
        $this->info('🧹 <fg=red>Cleanup Mode: Removing all load-test data...</>');

        if (!$this->confirm('This will permanently delete ALL data tagged as SYSTEM_LOAD_TEST. Continue?', true)) {
            $this->line('Cleanup cancelled.');
            return self::SUCCESS;
        }

        $this->info('  → Finding load-test patient IDs...');
        $loadTestIds = DB::table('patients')
            ->where('occupation', self::LOAD_TEST_MARKER)
            ->pluck('id')
            ->toArray();

        if (empty($loadTestIds)) {
            $this->warn('No load-test patients found. Nothing to clean up.');
            return self::SUCCESS;
        }

        $this->info('  → Found ' . count($loadTestIds) . ' load-test patients.');

        // Delete in correct dependency order
        $this->line('  → Deleting immunizations...');
        $childIds = DB::table('child_records')
            ->whereIn('mother_id', $loadTestIds)
            ->pluck('id')->toArray();

        if (!empty($childIds)) {
            DB::table('immunizations')->whereIn('child_record_id', $childIds)->delete();
            DB::table('child_records')->whereIn('mother_id', $loadTestIds)->delete();
        }

        $this->line('  → Deleting prenatal checkups...');
        $prenatalIds = DB::table('prenatal_records')
            ->whereIn('patient_id', $loadTestIds)
            ->pluck('id')->toArray();

        if (!empty($prenatalIds)) {
            DB::table('prenatal_checkups')->whereIn('prenatal_record_id', $prenatalIds)->delete();
            DB::table('prenatal_records')->whereIn('patient_id', $loadTestIds)->delete();
        }

        $this->line('  → Deleting patients...');
        DB::table('patients')->whereIn('id', $loadTestIds)->delete();

        $this->info('');
        $this->info('<fg=green>✅ Cleanup complete!</>');
        $this->table(
            ['Table', 'Status'],
            [
                ['immunizations', '<fg=green>Cleaned</>'],
                ['child_records', '<fg=green>Cleaned</>'],
                ['prenatal_checkups', '<fg=green>Cleaned</>'],
                ['prenatal_records', '<fg=green>Cleaned</>'],
                ['patients', '<fg=green>Cleaned</>'],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Display the command banner.
     */
    private function displayBanner(): void
    {
        $this->line('');
        $this->line('<fg=cyan>╔══════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║   Healthcare System - Load & Stress Test Suite  ║</>');
        $this->line('<fg=cyan>║        Barangay Health Station Simulation        ║</>');
        $this->line('<fg=cyan>╚══════════════════════════════════════════════════╝</>');
        $this->line('');
    }
}
