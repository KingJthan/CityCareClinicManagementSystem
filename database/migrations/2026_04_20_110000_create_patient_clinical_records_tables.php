<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('provider_name');
            $table->string('policy_number');
            $table->string('member_number')->nullable();
            $table->string('coverage_type')->nullable();
            $table->string('status')->default('active')->index();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ordered_by')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('category')->default('Blood work')->index();
            $table->string('test_name');
            $table->string('result_value')->nullable();
            $table->string('unit', 40)->nullable();
            $table->string('reference_range')->nullable();
            $table->string('status')->default('completed')->index();
            $table->timestamp('resulted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('blood_pressure', 30)->nullable();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->unsignedSmallInteger('oxygen_saturation')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('family_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('condition');
            $table->string('relationship')->nullable();
            $table->string('status')->default('known')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_histories');
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('lab_results');
        Schema::dropIfExists('patient_insurances');
    }
};
