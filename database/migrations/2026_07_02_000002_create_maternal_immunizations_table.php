<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create maternal_immunizations table.
     * Tracks TDaP vaccine doses given to pregnant mothers (not children).
     * Each row = one dose administered (dose 1 or dose 2).
     */
    public function up(): void
    {
        Schema::create('maternal_immunizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('vaccine_name')->default('TDaP');
            $table->unsignedTinyInteger('dose_number');          // 1 or 2
            $table->date('date_administered');
            $table->unsignedSmallInteger('gestational_week_at_dose')->nullable(); // week of pregnancy at time of dose
            $table->date('next_dose_due_date')->nullable();      // auto-computed: date_administered + 3 months (only set on dose 1)
            $table->unsignedBigInteger('vaccine_lot_id')->nullable(); // FK to vaccine_lots
            $table->string('administered_by')->nullable();       // staff name / ID
            $table->boolean('is_external')->default(false);      // true = private clinic, no inventory deduction
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients')
                  ->onDelete('cascade');

            $table->foreign('vaccine_lot_id')
                  ->references('id')
                  ->on('vaccine_lots')
                  ->onDelete('set null');

            $table->index('patient_id');
            $table->index(['patient_id', 'dose_number']);
            $table->index('vaccine_lot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maternal_immunizations');
    }
};
