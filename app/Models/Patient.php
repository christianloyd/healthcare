<?php
// app/Models/Patient.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'patients';

    /* ----------------------------------------------------------
       Mass-assignable attributes
    ---------------------------------------------------------- */
    protected $fillable = [
        'formatted_patient_id',
        'name',
        'first_name',
        'last_name',
        'age',
        'date_of_birth',
        'contact',
        'emergency_contact',
        'address',
        'occupation',
    ];

    /* ----------------------------------------------------------
       Casting
    ---------------------------------------------------------- */
    protected $casts = [
        'age' => 'integer',
        'date_of_birth' => 'date'
    ];

    /* ----------------------------------------------------------
       Boot logic (auto-ID)
    ---------------------------------------------------------- */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->formatted_patient_id)) {
                $patient->formatted_patient_id = static::generatePatientId();
            }

            // Calculate date_of_birth from age if age is provided and date_of_birth is not
            if ($patient->age && !$patient->date_of_birth) {
                $patient->date_of_birth = Carbon::now()->subYears($patient->age)->startOfYear();
            }
        });

        static::updating(function ($patient) {
            // Calculate date_of_birth from age if age is provided and date_of_birth is not
            if ($patient->age && !$patient->date_of_birth) {
                $patient->date_of_birth = Carbon::now()->subYears($patient->age)->startOfYear();
            }
        });
    }

    /* ----------------------------------------------------------
       Helper methods
    ---------------------------------------------------------- */
    public static function generatePatientId()
    {
        $last = static::withTrashed()->orderByDesc('id')->first();
        return 'PT-' . str_pad(($last ? $last->id + 1 : 1), 3, '0', STR_PAD_LEFT);
    }

    /* ----------------------------------------------------------
       Scopes
    ---------------------------------------------------------- */
    public function scopeSearch($query, $term)
    {
        return $query->where(fn ($q) => $q->where('first_name', 'like', "%{$term}%")
                                          ->orWhere('last_name', 'like', "%{$term}%")
                                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
                                          ->orWhere('name', 'like', "%{$term}%")
                                          ->orWhere('formatted_patient_id', 'like', "%{$term}%"));
    }

    /**
     * Scope for patients with active prenatal records (for checkup dropdown)
     */
    public function scopeWithActivePrenatal($query)
    {
        return $query->whereHas('activePrenatalRecord');
    }

    /* ----------------------------------------------------------
       Relationships
    ---------------------------------------------------------- */
    
    /**
     * Get all prenatal records for this patient
     */
    public function prenatalRecords()
    {
        return $this->hasMany(PrenatalRecord::class);
    }

    /**
     * Get active prenatal record
     */
    public function activePrenatalRecord()
    {
        return $this->hasOne(PrenatalRecord::class)
                    ->whereIn('status', ['normal', 'monitor', 'high-risk', 'due'])
                    ->latest();
    }

    /**
     * Get latest prenatal record
     */
    public function latestPrenatalRecord()
    {
        return $this->hasOne(PrenatalRecord::class)->latest();
    }

    /**
     * Relationship with prenatal checkups
     */
    public function prenatalCheckups()
    {
        return $this->hasMany(PrenatalCheckup::class);
    }

    /**
     * Get latest checkup
     */
    public function latestCheckup()
    {
        return $this->hasOne(PrenatalCheckup::class)->latest('checkup_date');
    }

    /**
     * Relationship with child records (as mother)
     */
    public function childRecords()
    {
        return $this->hasMany(ChildRecord::class, 'mother_id');
    }

    /**
     * Get immunizations through child records
     */
    public function immunizations()
    {
        return $this->hasManyThrough(Immunization::class, ChildRecord::class, 'mother_id', 'child_record_id');
    }

    /**
     * Get maternal TDaP vaccine dose records for this patient (mother)
     */
    public function maternalImmunizations()
    {
        return $this->hasMany(MaternalImmunization::class)->orderBy('dose_number');
    }

    /**
     * Get next scheduled visit from checkups
     */
    public function nextVisitFromCheckups()
    {
        return $this->prenatalCheckups()
                    ->whereNotNull('next_visit_date')
                    ->where('next_visit_date', '>=', Carbon::today())
                    ->orderBy('next_visit_date')
                    ->first();
    }

    /* ----------------------------------------------------------
       Computed attributes
    ---------------------------------------------------------- */
    
    /**
     * Calculate weeks pregnant from prenatal record (WHOLE WEEKS ONLY) - Cached version
     */
    public function getWeeksPregnantFromRecordAttribute()
    {
        // Check if activePrenatalRecord is already loaded to avoid N+1
        if (!$this->relationLoaded('activePrenatalRecord')) {
            $this->load('activePrenatalRecord');
        }

        $prenatalRecord = $this->activePrenatalRecord;

        if (!$prenatalRecord || !$prenatalRecord->last_menstrual_period) {
            return null;
        }

        // Calculate total days since LMP
        $totalDays = Carbon::parse($prenatalRecord->last_menstrual_period)->diffInDays(Carbon::now());

        // Convert to whole weeks only (no decimals)
        $weeks = intval($totalDays / 7);

        // Format properly
        return $weeks == 1 ? "1 week" : "{$weeks} weeks";
    }

    /**
     * Get checkup status based on next visit
     */
    public function getCheckupStatusAttribute()
    {
        $nextVisit = $this->nextVisitFromCheckups();
        
        if (!$nextVisit) {
            // Check if they have any checkups at all
            if ($this->prenatalCheckups()->count() === 0) {
                return 'no_checkups';
            }
            return 'completed';
        }
        
        $nextVisitDate = Carbon::parse($nextVisit->next_visit_date);
        
        if ($nextVisitDate->isPast()) {
            return 'overdue';
        } elseif ($nextVisitDate->isToday() || $nextVisitDate->isTomorrow()) {
            return 'upcoming';
        }
        
        return 'scheduled';
    }

    public function getIsHighRiskPatientAttribute()
    {
        return $this->age < 18 || $this->age > 35;
    }

    public function getHasActivePrenatalRecordAttribute()
    {
        // Check if activePrenatalRecord is already loaded to avoid N+1
        if ($this->relationLoaded('activePrenatalRecord')) {
            return $this->activePrenatalRecord !== null;
        }

        return $this->activePrenatalRecord()->exists();
    }

    public function getTotalPrenatalRecordsAttribute()
    {
        // Use loaded relationship if available, otherwise query
        if ($this->relationLoaded('prenatalRecords')) {
            return $this->prenatalRecords->count();
        }

        return $this->prenatalRecords()->count();
    }

    /**
     * Get the session count (prenatal checkups) for the active/latest prenatal record.
     * Active = ONGOING, otherwise = DONE.
     */
    public function getPrenatalSessionCountAttribute()
    {
        // Prefer the active prenatal record, fall back to the latest
        if ($this->relationLoaded('activePrenatalRecord') && $this->activePrenatalRecord) {
            $record = $this->activePrenatalRecord;
        } else {
            $record = $this->activePrenatalRecord()->first();
        }

        if (!$record) {
            // No active record – count from latest prenatal record
            $record = $this->prenatalRecords()->latest()->first();
        }

        if (!$record) {
            return 0;
        }

        return $record->prenatalCheckups()->count();
    }

    public function getNameAttribute()
    {
        // If name field exists, use it; otherwise combine first_name and last_name
        if (!empty($this->attributes['name'])) {
            return $this->attributes['name'];
        }

        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getFullNameWithIdAttribute()
    {
        return $this->name . ' (' . $this->formatted_patient_id . ')';
    }
 
}