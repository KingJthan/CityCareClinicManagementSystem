<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('category', 80);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image_path')->nullable();
            $table->string('status')->default('active')->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('unit_amount', 10, 2);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('billing_products');
    }
};
