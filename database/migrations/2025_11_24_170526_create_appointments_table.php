<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name');
            $table->string('patient_email');
            $table->string('patient_phone', 20);
            $table->string('consultant_type');
            $table->dateTime('appointment_date');
            $table->decimal('consultation_fee', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->index('patient_email');
            $table->index('status');
            $table->index('appointment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};