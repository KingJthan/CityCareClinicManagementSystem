<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->index()->after('reference');
            $table->string('stripe_payment_intent_id')->nullable()->index()->after('stripe_checkout_session_id');
            $table->string('stripe_payment_status', 40)->nullable()->after('stripe_payment_intent_id');
            $table->timestamp('online_payment_started_at')->nullable()->after('stripe_payment_status');
            $table->timestamp('online_payment_completed_at')->nullable()->after('online_payment_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_payment_status',
                'online_payment_started_at',
                'online_payment_completed_at',
            ]);
        });
    }
};
