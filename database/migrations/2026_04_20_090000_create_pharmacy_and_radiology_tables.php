<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drug_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('strength', 80);
            $table->string('dosage_form', 80);
            $table->string('unit', 40)->default('tablet');
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('reorder_level')->default(10);
            $table->string('status')->default('active')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['name', 'strength', 'dosage_form']);
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained()->restrictOnDelete();
            $table->foreignId('prescribed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dosage', 120);
            $table->string('frequency', 120)->nullable();
            $table->string('duration', 120)->nullable();
            $table->text('instructions')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('dispensed_at')->nullable();
            $table->text('pharmacist_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('radiology_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ordered_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('study_type', 120);
            $table->string('priority')->default('routine')->index();
            $table->text('clinical_notes');
            $table->string('status')->default('requested')->index();
            $table->text('result_notes')->nullable();
            $table->timestamp('resulted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_orders');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('drugs');
        Schema::dropIfExists('drug_categories');
    }
};
