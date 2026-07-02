<?php

namespace App\Http\Controllers;

use App\Models\VaccineLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VaccineLotController extends Controller
{
    /**
     * Get all lots for the midwife vaccine section.
     */
    public function index(Request $request)
    {
        $this->authorizeRole();

        $lots = VaccineLot::orderByRaw("
            CASE
                WHEN quantity_on_hand = 0 THEN 2
                WHEN expiry_date < NOW() THEN 3
                ELSE 1
            END
        ")->orderBy('expiry_date')->get();

        $stats = [
            'total_on_hand'   => $lots->sum('quantity_on_hand'),
            'total_used'      => $lots->sum('quantity_used'),
            'low_stock_count' => $lots->filter(fn($l) => $l->is_low_stock)->count(),
            'expiring_count'  => $lots->filter(fn($l) => $l->is_expiring_soon)->count(),
            'active_lots'     => $lots->where('is_active', true)->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json(['lots' => $lots, 'stats' => $stats]);
        }

        return view('midwife.vaccines.lots', compact('lots', 'stats'));
    }

    /**
     * Store a new vaccine lot.
     */
    public function store(Request $request)
    {
        $this->authorizeRole();

        $data = $request->validate([
            'lot_number'          => 'required|string|max:100|unique:vaccine_lots,lot_number',
            'expiry_date'         => 'required|date|after:today',
            'quantity_received'   => 'required|integer|min:1',
            'low_stock_threshold' => 'required|integer|min:1',
            'received_date'       => 'nullable|date|before_or_equal:today',
            'supplier'            => 'nullable|string|max:255',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $data['vaccine_name']    = 'TDaP';
        $data['quantity_on_hand'] = $data['quantity_received'];
        $data['quantity_used']    = 0;

        $lot = VaccineLot::create($data);

        return response()->json([
            'success' => true,
            'message' => "Lot {$lot->lot_number} added successfully.",
            'lot'     => $lot,
        ]);
    }

    /**
     * Update lot (e.g., adjust stock, mark inactive, update threshold).
     */
    public function update(Request $request, VaccineLot $vaccineLot)
    {
        $this->authorizeRole();

        $data = $request->validate([
            'low_stock_threshold' => 'sometimes|integer|min:1',
            'notes'               => 'nullable|string|max:1000',
            'is_active'           => 'sometimes|boolean',
            // Allow manual stock adjustment (e.g. expiry discard)
            'quantity_on_hand'    => 'sometimes|integer|min:0',
        ]);

        $vaccineLot->update($data);

        return response()->json([
            'success' => true,
            'message' => "Lot {$vaccineLot->lot_number} updated.",
            'lot'     => $vaccineLot->fresh(),
        ]);
    }

    /**
     * Return available (non-expired, in-stock) lots for dose-recording dropdown.
     */
    public function available()
    {
        $lots = VaccineLot::active()
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->get(['id', 'lot_number', 'expiry_date', 'quantity_on_hand']);

        return response()->json($lots);
    }

    private function authorizeRole()
    {
        if (!in_array(auth()->user()->role, ['midwife', 'admin'])) {
            abort(403, 'Unauthorized');
        }
    }
}
