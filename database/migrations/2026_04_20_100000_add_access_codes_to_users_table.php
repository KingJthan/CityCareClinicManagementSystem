<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_verification_code')->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_code');
            $table->string('login_otp_code')->nullable()->after('email_verification_expires_at');
            $table->timestamp('login_otp_expires_at')->nullable()->after('login_otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_code',
                'email_verification_expires_at',
                'login_otp_code',
                'login_otp_expires_at',
            ]);
        });
    }
};
