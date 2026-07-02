<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Patient Repository Implementation
 *
 * Handles all patient data access operations
 */
class PatientRepository implements PatientRepositoryInterface
{
    protected $model;

    /**
     * Constructor
     *
     * @param Patient $model
     */
    public function __construct(Patient $model)
    {
        $this->model = $model;
    }

    /**
     * Get all patients
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get paginated patients, with optional risk status filter.
     *
     * @param int $perPage
     * @param string|null $riskStatus  'high_risk' | 'normal' | null
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 20, ?string $riskStatus = null): LengthAwarePaginator
    {
        $query = $this->model->with('activePrenatalRecord');

        $query = $this->applyRiskStatusFilter($query, $riskStatus);

        return $query->orderBy('created_at', 'desc')
                     ->paginate($perPage);
    }

    /**
     * Find patient by ID
     *
     * @param int $id
     * @return Patient|null
     */
    public function find(int $id): ?Patient
    {
        return $this->model->find($id);
    }

    /**
     * Find patient by formatted ID (e.g., PT-001)
     *
     * @param string $formattedId
     * @return Patient|null
     */
    public function findByFormattedId(string $formattedId): ?Patient
    {
        return $this->model->where('formatted_patient_id', $formattedId)->first();
    }

    /**
     * Create a new patient
     *
     * @param array $data
     * @return Patient
     */
    public function create(array $data): Patient
    {
        $patient = $this->model->create($data);

        // Clear relevant caches
        $this->clearCache();

        return $patient;
    }

    /**
     * Update patient
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $patient = $this->find($id);

        if (!$patient) {
            return false;
        }

        $updated = $patient->update($data);

        // Clear relevant caches
        $this->clearCache();

        return $updated;
    }

    /**
     * Delete patient (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $patient = $this->find($id);

        if (!$patient) {
            return false;
        }

        $deleted = $patient->delete();

        // Clear relevant caches
        $this->clearCache();

        return $deleted;
    }

    /**
     * Search patients by name or ID
     *
     * @param string $term
     * @return Collection
     */
    public function search(string $term): Collection
    {
        return $this->model->where(function($q) use ($term) {
            $q->where('first_name', 'LIKE', "%{$term}%")
              ->orWhere('last_name', 'LIKE', "%{$term}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
              ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
        })->get();
    }

    /**
     * Search patients with pagination and optional risk status filter.
     *
     * @param string $term
     * @param int $perPage
     * @param string|null $riskStatus  'high_risk' | 'normal' | null
     * @return LengthAwarePaginator
     */
    public function searchPaginated(string $term, int $perPage = 20, ?string $riskStatus = null): LengthAwarePaginator
    {
        $query = $this->model->where(function($q) use ($term) {
            $q->where('first_name', 'LIKE', "%{$term}%")
              ->orWhere('last_name', 'LIKE', "%{$term}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
              ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
        });

        $query = $this->applyRiskStatusFilter($query, $riskStatus);

        return $query->with('activePrenatalRecord')
                     ->orderBy('created_at', 'desc')
                     ->paginate($perPage);
    }

    /**
     * Apply a risk-status WHERE clause to a query builder.
     *
     * High Risk  : age < 18 OR age > 35
     * Normal     : age BETWEEN 18 AND 35
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $riskStatus
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyRiskStatusFilter($query, ?string $riskStatus)
    {
        if ($riskStatus === 'high_risk') {
            $query->where(function ($q) {
                $q->where('age', '<', 18)
                  ->orWhere('age', '>', 35);
            });
        } elseif ($riskStatus === 'normal') {
            $query->whereBetween('age', [18, 35]);
        }

        return $query;
    }

    /**
     * Get patients with active prenatal records
     *
     * @return Collection
     */
    public function withActivePrenatalRecords(): Collection
    {
        return $this->model->whereHas('prenatalRecords', function($query) {
            $query->where('is_active', true)
                  ->where('status', '!=', 'completed');
        })->with(['prenatalRecords' => function($query) {
            $query->where('is_active', true)
                  ->where('status', '!=', 'completed')
                  ->latest();
        }])->get();
    }

    /**
     * Get patients with high risk status
     *
     * @return Collection
     */
    public function getHighRiskPatients(): Collection
    {
        return $this->model->where(function($query) {
            $query->where('age', '<', 18)
                  ->orWhere('age', '>', 35);
        })->orWhereHas('prenatalRecords', function($query) {
            $query->where('status', 'high-risk')
                  ->where('is_active', true);
        })->with('activePrenatalRecord')->get();
    }

    /**
     * Get patient with full profile data (relationships loaded)
     *
     * @param int $id
     * @return Patient|null
     */
    public function getFullProfile(int $id): ?Patient
    {
        return $this->model->with([
            'prenatalRecords' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'prenatalCheckups' => function($query) {
                $query->orderBy('checkup_date', 'desc');
            },
            'childRecords' => function($query) {
                $query->orderBy('birthdate', 'desc');
            },
            'childRecords.immunizations' => function($query) {
                $query->with('vaccine')->orderBy('schedule_date', 'desc');
            },
            'maternalImmunizations' => function($query) {
                $query->with('vaccineLot')->orderBy('dose_number');
            },
            'activePrenatalRecord',
            'latestCheckup'
        ])->find($id);
    }

    /**
     * Count total patients
     *
     * @return int
     */
    public function count(): int
    {
        return Cache::remember('patients_count', 600, function() {
            return $this->model->count();
        });
    }

    /**
     * Count patients with active pregnancies
     *
     * @return int
     */
    public function countActivePregnancies(): int
    {
        return Cache::remember('active_pregnancies_count', 600, function() {
            return $this->model->whereHas('prenatalRecords', function($query) {
                $query->where('is_active', true)
                      ->where('status', '!=', 'completed');
            })->count();
        });
    }

    /**
     * Find duplicate patient by name and age
     *
     * @param string $firstName
     * @param string $lastName
     * @param int $age
     * @return Patient|null
     */
    public function findDuplicate(string $firstName, string $lastName, int $age): ?Patient
    {
        return $this->model->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->where('age', $age)
            ->first();
    }

    /**
     * Find duplicate patient excluding specific ID
     *
     * @param string $firstName
     * @param string $lastName
     * @param int $age
     * @param int $excludeId
     * @return Patient|null
     */
    public function findDuplicateExcept(string $firstName, string $lastName, int $age, int $excludeId): ?Patient
    {
        return $this->model->where('first_name', 'LIKE', $firstName)
            ->where('last_name', 'LIKE', $lastName)
            ->where('age', $age)
            ->where('id', '!=', $excludeId)
            ->first();
    }

    /**
     * Find patient with specified relationships
     *
     * @param int $id
     * @param array $relations
     * @return Patient|null
     */
    public function findWithRelations(int $id, array $relations): ?Patient
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Get patient full profile for printing (ordered for documents)
     *
     * @param int $id
     * @return Patient|null
     */
    public function getFullProfileForPrint(int $id): ?Patient
    {
        return $this->model->with([
            'prenatalRecords' => function($query) {
                $query->orderBy('created_at', 'asc');
            },
            'prenatalCheckups' => function($query) {
                $query->orderBy('checkup_date', 'asc');
            },
            'childRecords' => function($query) {
                $query->orderBy('birthdate', 'asc');
            },
            'childRecords.immunizations' => function($query) {
                $query->with('vaccine')->orderBy('schedule_date', 'asc');
            },
            'activePrenatalRecord',
            'latestCheckup'
        ])->find($id);
    }

    /**
     * Search patients with filters (for AJAX requests)
     *
     * @param string|null $term
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function searchWithFilters(?string $term, array $filters = [], int $limit = 50): Collection
    {
        $query = $this->model->query();

        // Filter patients without active prenatal records if requested
        if (isset($filters['without_prenatal']) && $filters['without_prenatal']) {
            $query->whereDoesntHave('prenatalRecords', function($q) {
                $q->where('is_active', 1)
                  ->where('status', '!=', 'completed');
            });
        }

        // If there's a search term, filter by it
        if (!empty($term)) {
            $query->where(function($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('first_name', 'LIKE', "%{$term}%")
                  ->orWhere('last_name', 'LIKE', "%{$term}%")
                  ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%")
                  ->orWhere('contact', 'LIKE', "%{$term}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if patient has prenatal records
     *
     * @param int $id
     * @return bool
     */
    public function hasPrenatalRecords(int $id): bool
    {
        $patient = $this->find($id);

        if (!$patient) {
            return false;
        }

        return $patient->prenatalRecords()->count() > 0;
    }

    /**
     * Clear patient-related caches
     *
     * @return void
     */
    protected function clearCache(): void
    {
        Cache::forget('patients_count');
        Cache::forget('active_pregnancies_count');
        Cache::forget('dashboard_stats');
    }
}
