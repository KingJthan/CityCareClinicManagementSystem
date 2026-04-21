<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('document_type', 80);
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('status')->default('active')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['patient_id', 'document_type']);
            $table->index(['uploaded_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_documents');
    }
};
