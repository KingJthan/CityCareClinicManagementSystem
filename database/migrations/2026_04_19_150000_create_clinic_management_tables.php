<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('active')->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('staff_number')->unique();
            $table->string('license_number')->unique();
            $table->string('specialization');
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->time('shift_starts_at')->default('08:00');
            $table->time('shift_ends_at')->default('17:00');
            $table->unsignedSmallInteger('slot_minutes')->default(30);
            $table->json('working_days')->nullable();
            $table->string('room')->nullable();
            $table->string('status')->default('active')->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('allergies')->nullable();
            $table->string('status')->default('active')->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->date('appointment_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('scheduled')->index();
            $table->string('visit_type')->default('Consultation');
            $table->text('reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['doctor_id', 'appointment_date', 'start_time']);
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->text('symptoms')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('prescription')->nullable();
            $table->date('next_visit_date')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('Cash');
            $table->string('status')->default('pending')->index();
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('departments');
    }
};
