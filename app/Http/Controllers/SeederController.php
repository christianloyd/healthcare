<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Vaccine;

class SeederController extends Controller
{
    /**
     * Run vaccine seeder (protected by secret key)
     */
    public function runVaccineSeeder(Request $request)
    {
        // Security: Check secret key from environment
        $secretKey = env('SEEDER_SECRET_KEY', 'change-this-secret-key');
        
        if ($request->input('key') !== $secretKey) {
            abort(403, 'Unauthorized');
        }

        // Check if vaccines already exist
        $existingCount = Vaccine::count();
        
        if ($existingCount > 0) {
            return response()->json([
                'status' => 'skipped',
                'message' => 'Vaccines already exist',
                'count' => $existingCount,
                'vaccines' => Vaccine::pluck('name')
            ]);
        }

        // Run the seeder
        try {
            Artisan::call('db:seed', [
                '--class' => 'VaccineSeeder',
                '--force' => true
            ]);

            $newCount = Vaccine::count();

            return response()->json([
                'status' => 'success',
                'message' => 'Vaccines seeded successfully',
                'count' => $newCount,
                'vaccines' => Vaccine::pluck('name')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check vaccine status
     */
    public function checkVaccines(Request $request)
    {
        $secretKey = env('SEEDER_SECRET_KEY', 'change-this-secret-key');
        
        if ($request->input('key') !== $secretKey) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'count' => Vaccine::count(),
            'vaccines' => Vaccine::select('id', 'name', 'age_schedule', 'current_stock')->get()
        ]);
    }
}
