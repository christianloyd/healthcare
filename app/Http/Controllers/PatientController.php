<?php
// app/Http/Controllers/PatientController.php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Repositories\Contracts\PatientRepositoryInterface;
use App\Services\PatientService;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Utils\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Traits\NotifiesHealthcareWorkers;
use App\Http\Resources\PatientSearchResource;

class PatientController extends BaseController
{
    use NotifiesHealthcareWorkers;

    protected $patientRepository;
    protected $patientService;

    /**
     * Constructor - Inject Patient Repository and Service
     *
     * @param PatientRepositoryInterface $patientRepository
     * @param PatientService $patientService
     */
    public function __construct(PatientRepositoryInterface $patientRepository, PatientService $patientService)
    {
        $this->patientRepository = $patientRepository;
        $this->patientService = $patientService;
    }

    /**
     * Display a listing of patients (mothers only)
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        // Clean controller - all query logic in repository
        $perPage = 10;
        $riskStatus = $request->input('risk_status'); // 'high_risk' | 'normal' | null

        $patients = $request->filled('search')
            ? $this->patientRepository->searchPaginated($request->search, $perPage, $riskStatus)
            : $this->patientRepository->paginate($perPage, $riskStatus);

        $patients->withQueryString();

        // Use shared view for both roles
        return view($this->roleView('patients.index'), compact('patients'));
    }

    // Show form to create new patient
    public function create()
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        // Use shared view for both roles
        return view($this->roleView('patients.create'));
    }

    // Store new patient with comprehensive validation
    public function store(StorePatientRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                // Validation is handled automatically by StorePatientRequest
                // Create patient using service (handles duplicate check, phone formatting, notifications)
                $patient = $this->patientService->createPatient($request->validated());

                // Success response
                if ($request->ajax()) {
                    return ResponseHelper::success($patient, 'Patient "' . $patient->name . '" has been registered successfully!');
                }

                $redirectRoute = Auth::user()->role === 'midwife'
                    ? 'midwife.patients.index'
                    : 'bhw.patients.index';

                return redirect()->route($redirectRoute)
                    ->with('success', 'Patient "' . $patient->name . '" has been registered successfully!');

            } catch (\Exception $e) {
                // Log the error
                Log::error('Patient registration failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'input' => $request->validated()
                ]);

                if ($request->ajax()) {
                    return ResponseHelper::error($e->getMessage(), [], 500);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', $e->getMessage());
            }
        });
    }

    // Show a single patient
    public function show($id)
    {
        $patient = $this->patientRepository->findWithRelations($id, ['prenatalRecords']);

        if (!$patient) {
            abort(404, 'Patient not found');
        }

        $view = auth()->user()->role === 'midwife'
            ? 'midwife.patients.show'
            : 'bhw.patients.show';

        return view($view, compact('patient'));
    }

    // Show comprehensive patient profile with all related records
    public function profile($id)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        // Load patient with all related data using repository
        $patient = $this->patientRepository->getFullProfile($id);

        if (!$patient) {
            abort(404, 'Patient not found');
        }

        return view($this->roleView('patients.profile'), compact('patient'));
    }

    // Print patient profile with A4 layout
    public function printProfile($id)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        // Load patient with all related data for printing using repository
        $patient = $this->patientRepository->getFullProfileForPrint($id);

        if (!$patient) {
            abort(404, 'Patient not found');
        }

        return view($this->roleView('patients.print'), compact('patient'));
    }

    // Show form to edit patient
    public function edit($id)
    {
        $patient = $this->patientRepository->find($id);

        if (!$patient) {
            abort(404, 'Patient not found');
        }

        $view = auth()->user()->role === 'midwife'
            ? 'midwife.patients.edit'
            : 'bhw.patients.edit';

        return view($view, compact('patient'));
    }

    // Update patient with comprehensive validation
    public function update(UpdatePatientRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                $patient = $this->patientRepository->find($id);

                if (!$patient) {
                    abort(404, 'Patient not found');
                }

                // Validation is handled automatically by UpdatePatientRequest
                // Update patient using service (handles duplicate check, phone formatting)
                $patient = $this->patientService->updatePatient($patient, $request->validated());

                // Success response
                if ($request->ajax()) {
                    return ResponseHelper::success($patient, 'Patient "' . $patient->name . '" has been updated successfully!');
                }

                $redirectRoute = Auth::user()->role === 'midwife'
                    ? 'midwife.patients.index'
                    : 'bhw.patients.index';

                return redirect()->route($redirectRoute)
                    ->with('success', 'Patient "' . $patient->name . '" has been updated successfully!');

            } catch (\Exception $e) {
                // Log the error
                Log::error('Patient update failed', [
                    'patient_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'input' => $request->validated()
                ]);

                if ($request->ajax()) {
                    return ResponseHelper::error($e->getMessage(), [], 500);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', $e->getMessage());
            }
        });
    }

    // Delete patient (only if no prenatal records)
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            try {
                $patient = $this->patientRepository->find($id);

                if (!$patient) {
                    abort(404, 'Patient not found');
                }

                // Use service to delete (handles prenatal records check)
                $patientName = $this->patientService->deletePatient($patient);

                $redirectRoute = Auth::user()->role === 'midwife'
                    ? 'midwife.patients.index'
                    : 'bhw.patients.index';

                return redirect()->route($redirectRoute)
                    ->with('success', "Patient \"{$patientName}\" has been deleted successfully.");

            } catch (\Exception $e) {
                Log::error('Error deleting patient: ' . $e->getMessage());
                $redirectRoute = Auth::user()->role === 'midwife'
                    ? 'midwife.patients.index'
                    : 'bhw.patients.index';

                return redirect()->route($redirectRoute)
                    ->with('error', $e->getMessage());
            }
        });
    }

    /**
     * Search patients for AJAX requests
     * Used by prenatal record creation and checkup forms
     */
    public function search(Request $request)
    {
        try {
            // Build filters array
            $filters = [];
            if ($request->has('without_prenatal') && $request->without_prenatal == 'true') {
                $filters['without_prenatal'] = true;
            }

            // Get search term
            $searchTerm = $request->has('q') ? $request->q : null;

            // Use repository to search with filters
            $patients = $this->patientRepository->searchWithFilters($searchTerm, $filters, 50);

            // Use PatientSearchResource to return initials for privacy
            return PatientSearchResource::collection($patients);

        } catch (\Exception $e) {
            \Log::error('Error in patient search: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}