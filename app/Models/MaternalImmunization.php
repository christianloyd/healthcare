<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MaternalImmunization extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'vaccine_name',
        'dose_number',
        'date_administered',
        'gestational_week_at_dose',
        'next_dose_due_date',
        'vaccine_lot_id',
        'administered_by',
        'is_external',
        'notes',
    ];

    protected $casts = [
        'date_administered'      => 'date',
        'next_dose_due_date'     => 'date',
        'dose_number'            => 'integer',
        'gestational_week_at_dose' => 'integer',
        'is_external'            => 'boolean',
    ];

    /* ----------------------------------------------------------
       Boot — auto-compute next_dose_due_date on Dose 1
    ---------------------------------------------------------- */

    protected static function boot()
    {
        parent::boot();

        static::creating(function (MaternalImmunization $record) {
            if ($record->dose_number == 1 && $record->date_administered) {
                $record->next_dose_due_date = Carbon::parse($record->date_administered)->addMonths(3);
            }
        });
    }

    /* ----------------------------------------------------------
       Relationships
    ---------------------------------------------------------- */

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function vaccineLot()
    {
        return $this->belongsTo(VaccineLot::class, 'vaccine_lot_id');
    }

    /* ----------------------------------------------------------
       Accessors
    ---------------------------------------------------------- */

    public function getDoseLabelAttribute(): string
    {
        return match($this->dose_number) {
            1 => '1st Dose',
            2 => '2nd Dose',
            default => "Dose {$this->dose_number}",
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->is_external ? 'Private (External)' : 'Facility';
    }
}
