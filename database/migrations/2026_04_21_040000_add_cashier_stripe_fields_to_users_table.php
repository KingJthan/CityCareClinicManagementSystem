<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'stripe_id')) {
                $table->string('stripe_id')->nullable()->index();
            }

            if (!Schema::hasColumn('users', 'pm_type')) {
                $table->string('pm_type')->nullable();
            }

            if (!Schema::hasColumn('users', 'pm_last_four')) {
                $table->string('pm_last_four', 4)->nullable();
            }

            if (!Schema::hasColumn('users', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('users', 'stripe_id') ? 'stripe_id' : null,
                Schema::hasColumn('users', 'pm_type') ? 'pm_type' : null,
                Schema::hasColumn('users', 'pm_last_four') ? 'pm_last_four' : null,
                Schema::hasColumn('users', 'trial_ends_at') ? 'trial_ends_at' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
