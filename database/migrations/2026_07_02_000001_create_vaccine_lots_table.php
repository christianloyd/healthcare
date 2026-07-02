<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create vaccine_lots table for lot-based TDaP inventory tracking.
     * Each row represents one physical lot/batch received by the facility.
     */
    public function up(): void
    {
        Schema::create('vaccine_lots', function (Blueprint $table) {
            $table->id();
            $table->string('vaccine_name')->default('TDaP'); // extensible later
            $table->string('lot_number')->unique();
            $table->date('expiry_date');
            $table->integer('quantity_received')->default(0);   // total received in this lot
            $table->integer('quantity_on_hand')->default(0);    // current available
            $table->integer('quantity_used')->default(0);       // doses administered from this lot
            $table->integer('low_stock_threshold')->default(5); // alert below this level
            $table->date('received_date')->nullable();
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);        // false = depleted or recalled
            $table->timestamps();

            $table->index('lot_number');
            $table->index('expiry_date');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_lots');
    }
};
