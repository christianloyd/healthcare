<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\PrenatalCheckup;
use App\Models\ChildRecord;
use App\Models\Immunization;
use App\Models\Vaccine;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HistoricalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Historical Data Seeding (2023-2026)...');

        $vaccines = Vaccine::all();
        if ($vaccines->isEmpty()) {
            $this->command->error('No vaccines found. Please run VaccineSeeder first.');
            return;
        }

        $addresses = ['Brgy. Mecolong, Dumalinao, Zamboanga del Sur'];
        $firstNames = ['Maria', 'Juana', 'Elena', 'Rose', 'Liza', 'Ana', 'Belen', 'Cora', 'Dina', 'Gina'];
        $lastNames = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Pascual', 'Dela Cruz', 'Villanueva'];

        // Generate data for each year and month
        for ($year = 2023; $year <= 2026; $year++) {
            $this->command->info("Processing year: $year");
            
            // Track vaccines used this year to ensure all are used
            $usedVaccinesThisYear = [];
            
            for ($month = 1; $month <= 12; $month++) {
                // Don't go into future relative to now (May 2026)
                if ($year == 2026 && $month > 5) break;

                $this->command->info("  Month: $month");
                
                // Ensure at least 10-12 patients per month
                $recordsInMonth = rand(10, 12);
                
                for ($i = 0; $i < $recordsInMonth; $i++) {
                    $day = rand(1, 28);
                    $registrationDate = Carbon::create($year, $month, $day);
                    
                    // 1. Create Patient
                    $firstName = $firstNames[array_rand($firstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];
                    $patient = Patient::create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'name' => "$firstName $lastName",
                        'age' => rand(18, 40),
                        'contact' => '09' . rand(100000000, 999999999),
                        'emergency_contact' => '09' . rand(100000000, 999999999),
                        'address' => $addresses[0],
                        'occupation' => 'Housewife',
                        'created_at' => $registrationDate,
                        'updated_at' => $registrationDate,
                    ]);

                    // 2. Create Prenatal Record (80% of patients to ensure high density)
                    if (rand(1, 10) <= 8) {
                        $lmp = $registrationDate->copy()->subWeeks(rand(4, 20));
                        $expectedDueDate = $lmp->copy()->addDays(280);
                        
                        $totalDays = $lmp->diffInDays($registrationDate);
                        $weeks = intval($totalDays / 7);
                        $days = $totalDays % 7;
                        $gestationalAge = "$weeks weeks $days days";
                        $trimester = $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3);

                        $prenatalRecord = PrenatalRecord::create([
                            'patient_id' => $patient->id,
                            'last_menstrual_period' => $lmp->toDateString(),
                            'expected_due_date' => $expectedDueDate->toDateString(),
                            'gestational_age' => $gestationalAge,
                            'trimester' => $trimester,
                            'gravida' => rand(1, 4),
                            'para' => rand(0, 3),
                            'status' => 'normal',
                            'created_at' => $registrationDate,
                            'updated_at' => $registrationDate,
                        ]);

                        // 3. Create Checkups for this record (more checkups = more data points)
                        $checkupCount = rand(2, 6);
                        for ($j = 0; $j < $checkupCount; $j++) {
                            $checkupDate = $registrationDate->copy()->addWeeks($j * 3);
                            if ($checkupDate->gt(now())) break;

                            PrenatalCheckup::create([
                                'prenatal_record_id' => $prenatalRecord->id,
                                'patient_id' => $patient->id,
                                'checkup_date' => $checkupDate->toDateString(),
                                'checkup_time' => '09:00:00',
                                'weight' => rand(50, 75),
                                'bp_high' => rand(110, 130),
                                'bp_low' => rand(70, 90),
                                'belly_size' => 10 + ($j * 3),
                                'baby_heartbeat' => rand(135, 155),
                                'status' => 'completed',
                                'notes' => 'Regular monthly checkup',
                                'conducted_by' => 1,
                                'created_at' => $checkupDate,
                                'updated_at' => $checkupDate,
                            ]);
                        }

                        // 4. Create Child Record (High probability if record is older)
                        if ($year < 2026 || ($year == 2026 && $month < 3)) {
                            $birthDate = $lmp->copy()->addWeeks(rand(38, 41));
                            if ($birthDate->lt(now())) {
                                $childFirstName = rand(0, 1) ? 'Junior' : 'Baby';
                                $child = ChildRecord::create([
                                    'mother_id' => $patient->id,
                                    'first_name' => $childFirstName . ' ' . $lastName,
                                    'last_name' => $lastName,
                                    'birthdate' => $birthDate->toDateString(),
                                    'gender' => rand(0, 1) ? 'Male' : 'Female',
                                    'birth_weight' => rand(2500, 4200) / 1000,
                                    'birth_height' => rand(48, 54),
                                    'created_at' => $birthDate,
                                    'updated_at' => $birthDate,
                                ]);

                                // 5. Create Immunizations (Ensure all vaccines are used)
                                // Pick some random ones but also check what hasn't been used this year
                                $vaccinesToUse = $vaccines->random(rand(3, 6));
                                
                                foreach ($vaccinesToUse as $v) {
                                    $scheduleDate = $birthDate->copy()->addWeeks(rand(0, 24));
                                    if ($scheduleDate->gt(now())) continue;

                                    Immunization::create([
                                        'child_record_id' => $child->id,
                                        'vaccine_id' => $v->id,
                                        'vaccine_name' => $v->name,
                                        'dose' => '1st Dose',
                                        'schedule_date' => $scheduleDate->toDateString(),
                                        'schedule_time' => '10:00:00',
                                        'status' => 'Done',
                                        'notes' => 'Historical record',
                                        'created_at' => $scheduleDate,
                                        'updated_at' => $scheduleDate,
                                    ]);
                                    
                                    $usedVaccinesThisYear[$v->id] = true;
                                }
                            }
                        }
                    }
                }
            }
            
            // Check if any vaccines were missed this year and force some records
            foreach ($vaccines as $v) {
                if (!isset($usedVaccinesThisYear[$v->id])) {
                    // Force at least one record for this vaccine in December (or May 2026)
                    $forceMonth = ($year == 2026) ? 5 : 12;
                    $forceDate = Carbon::create($year, $forceMonth, rand(1, 28));
                    
                    // Create a dummy patient/child for this missing vaccine
                    $patient = Patient::create([
                        'first_name' => 'Legacy', 'last_name' => 'Data', 'name' => 'Legacy Data',
                        'age' => 25, 'contact' => '09000000000', 'emergency_contact' => '09000000000',
                        'address' => $addresses[0], 'occupation' => 'For Seeding',
                        'created_at' => $forceDate, 'updated_at' => $forceDate,
                    ]);
                    
                    $child = ChildRecord::create([
                        'mother_id' => $patient->id, 'first_name' => 'Child ' . $v->name, 'last_name' => 'Test',
                        'birthdate' => $forceDate->copy()->subMonths(6)->toDateString(),
                        'gender' => 'Male', 'birth_weight' => 3.0, 'birth_height' => 50,
                        'created_at' => $forceDate, 'updated_at' => $forceDate,
                    ]);
                    
                    Immunization::create([
                        'child_record_id' => $child->id, 'vaccine_id' => $v->id, 'vaccine_name' => $v->name,
                        'dose' => '1st Dose', 'schedule_date' => $forceDate->toDateString(),
                        'schedule_time' => '08:00:00', 'status' => 'Done',
                        'notes' => 'Ensuring vaccine coverage in seeder',
                        'created_at' => $forceDate, 'updated_at' => $forceDate,
                    ]);
                }
            }
        }

        $this->command->info('✅ Historical Data Seeding completed!');
    }
}
