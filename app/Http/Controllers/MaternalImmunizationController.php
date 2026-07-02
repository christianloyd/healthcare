<?php

namespace App\Http\Controllers;

use App\Models\MaternalImmunization;
use App\Models\VaccineLot;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MaternalImmunizationController extends Controller
{
    /**
     * Store a new maternal vaccine dose record.
     * Automatically deducts stock from the selected lot (unless is_external).
     */
    public function store(Request $request, $patientId)
    {
        $request->validate([
            'dose_number'             => 'required|integer|in:1,2',
            'date_administered'       => 'required|date|before_or_equal:today',
            'gestational_week_at_dose' => 'nullable|integer|min:1|max:45',
            'vaccine_lot_id'          => 'nullable|exists:vaccine_lots,id',
            'administered_by'         => 'nullable|string|max:255',
            'is_external'             => 'nullable|boolean',
            'notes'                   => 'nullable|string|max:1000',
        ]);

        $patient = Patient::findOrFail($patientId);

        // Prevent duplicate doses
        $exists = $patient->maternalImmunizations()
            ->where('dose_number', $request->dose_number)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Dose {$request->dose_number} has already been recorded for this patient.",
            ], 422);
        }

        // Prevent recording dose 2 before dose 1
        if ($request->dose_number == 2) {
            $dose1 = $patient->maternalImmunizations()->where('dose_number', 1)->first();
            if (!$dose1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dose 1 must be recorded before Dose 2.',
                ], 422);
            }
        }

        return DB::transaction(function () use ($request, $patient) {
            $isExternal = (bool) $request->input('is_external', false);
            $lotId = $request->vaccine_lot_id;

            // Deduct inventory if facility-administered and a lot is selected
            if (!$isExternal && $lotId) {
                $lot = VaccineLot::find($lotId);
                if (!$lot) {
                    return response()->json(['success' => false, 'message' => 'Selected lot not found.'], 404);
                }
                if ($lot->quantity_on_hand <= 0) {
                    return response()->json(['success' => false, 'message' => "Lot {$lot->lot_number} is out of stock."], 422);
                }
                $lot->deductDose();
            }

            $dose = MaternalImmunization::create([
                'patient_id'               => $patient->id,
                'vaccine_name'             => 'TDaP',
                'dose_number'              => $request->dose_number,
                'date_administered'        => $request->date_administered,
                'gestational_week_at_dose' => $request->gestational_week_at_dose,
                'vaccine_lot_id'           => !$isExternal ? $lotId : null,
                'administered_by'          => $request->administered_by,
                'is_external'              => $isExternal,
                'notes'                    => $request->notes,
            ]);

            // Eager-load the lot for the response
            $dose->load('vaccineLot');

            return response()->json([
                'success' => true,
                'message' => "TDaP Dose {$dose->dose_number} recorded successfully.",
                'dose'    => $dose,
            ]);
        });
    }

    /**
     * Delete a dose record (and restore lot stock if applicable).
     */
    public function destroy($patientId, $doseId)
    {
        $dose = MaternalImmunization::where('patient_id', $patientId)->findOrFail($doseId);

        return DB::transaction(function () use ($dose) {
            // Restore inventory if it was a facility dose linked to a lot
            if (!$dose->is_external && $dose->vaccine_lot_id) {
                $lot = VaccineLot::find($dose->vaccine_lot_id);
                if ($lot) {
                    $lot->increment('quantity_on_hand');
                    $lot->decrement('quantity_used');
                }
            }

            $dose->delete();

            return response()->json([
                'success' => true,
                'message' => "Dose {$dose->dose_number} record deleted successfully.",
            ]);
        });
    }
}
