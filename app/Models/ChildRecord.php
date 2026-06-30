<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'formatted_child_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'birth_height',
        'birth_weight',
        'birthdate',
        'birthplace',
        'address',
        'father_name',
        'mother_name', // Added this field
        'phone_number',
        'mother_id',
    ];

    // Cast attributes to proper types
    protected $casts = [
        'birthdate' => 'date',
        'birth_height' => 'decimal:2',
        'birth_weight' => 'decimal:3',
    ];

    /* ----------------------------------------------------------
       Boot logic (auto-ID)
    ---------------------------------------------------------- */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($childRecord) {
            if (empty($childRecord->formatted_child_id)) {
                $childRecord->formatted_child_id = static::generateChildId();
            }
        });
    }

    /* ----------------------------------------------------------
       Helper methods
    ---------------------------------------------------------- */
    public static function generateChildId()
    {
        $last = static::orderByDesc('id')->first();
        return 'CH-' . str_pad(($last ? $last->id + 1 : 1), 3, '0', STR_PAD_LEFT);
    }

    // Relationship to Patient (mother)
    public function mother()
    {
        return $this->belongsTo(Patient::class, 'mother_id');
    }

    // Relationship to immunizations
    public function immunizations()
    {
        return $this->hasMany(Immunization::class);
    }

    public function childImmunizations()
    {
        return $this->hasMany(ChildImmunization::class);
    }

    // Accessor for mother's name with fallback
    public function getMotherNameAttribute($value)
    {
        // If mother_name is stored directly, use it
        if ($value) {
            return $value;
        }
        
        // Otherwise, get from relationship
        return $this->mother ? $this->mother->name : null;
    }

    // Mutator to handle mother_name storage
    public function setMotherNameAttribute($value)
    {
        $this->attributes['mother_name'] = $value;
    }

    // Accessor for formatted birth weight
    public function getFormattedBirthWeightAttribute()
    {
        return $this->birth_weight ? number_format($this->birth_weight, 3) . ' kg' : null;
    }

    // Accessor for formatted birth height
    public function getFormattedBirthHeightAttribute()
    {
        return $this->birth_height ? number_format($this->birth_height, 1) . ' cm' : null;
    }

    // Accessor for full name (first + last only for display)
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // Accessor for complete name (including middle name)
    public function getCompleteNameAttribute()
    {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        if ($this->last_name) {
            $name .= ' ' . $this->last_name;
        }
        return trim($name);
    }

    // Accessor for child's age
    public function getAgeAttribute()
    {
        if (!$this->birthdate) return null;

        $birthDate = \Carbon\Carbon::parse($this->birthdate);
        $today = now();

        $years = (int) $birthDate->diffInYears($today);
        $totalMonths = (int) $birthDate->diffInMonths($today);
        $months = $totalMonths % 12;

        if ($years > 0) {
            return $years . ' year' . ($years > 1 ? 's' : '') .
                   ($months > 0 ? ' ' . $months . ' month' . ($months > 1 ? 's' : '') : '');
        }

        return $months . ' month' . ($months > 1 ? 's' : '');
    }

    // Scope for searching
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('middle_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
              ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ["%{$term}%"])
              ->orWhere('formatted_child_id', 'like', "%{$term}%")
              ->orWhere('phone_number', 'like', "%{$term}%")
              ->orWhere('mother_name', 'like', "%{$term}%")
              ->orWhereHas('mother', function($motherQuery) use ($term) {
                  $motherQuery->where('name', 'like', "%{$term}%");
              });
        });
    }

    // Scope for filtering by gender
    public function scopeGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    // Convert to array with additional attributes
    public function toArray()
    {
        $array = parent::toArray();
        
        // Add computed attributes
        $array['age'] = $this->age;
        $array['formatted_birth_weight'] = $this->formatted_birth_weight;
        $array['formatted_birth_height'] = $this->formatted_birth_height;
        
        // Add mother details if relationship is loaded
        if ($this->relationLoaded('mother') && $this->mother) {
            $array['mother_details'] = [
                'id' => $this->mother->id,
                'name' => $this->mother->name,
                'age' => $this->mother->age,
                'contact' => $this->mother->contact,
                'address' => $this->mother->address,
                'formatted_patient_id' => $this->mother->formatted_patient_id
            ];
        }
        
        return $array;
    }
 
}