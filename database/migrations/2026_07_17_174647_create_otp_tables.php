<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 20);
            $table->string('otp_code');
            $table->string('purpose', 50)->default('register'); // register, forgot_password
            $table->dateTime('expires_at');
            $table->boolean('is_used')->default(false);
            $table->dateTime('used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mobile', 'purpose', 'is_used', 'expires_at']);
        });

        Schema::create('otp_rate_limit', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 100);
            $table->enum('type', ['phone', 'ip'])->default('phone');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['identifier', 'type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('otp_rate_limit');
    }
};
