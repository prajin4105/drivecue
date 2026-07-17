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
       Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('plan_id')->constrained();
    $table->date('start_date');
    $table->date('end_date');
    $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
    $table->enum('payment_status', ['trial', 'paid', 'pending', 'failed'])->default('trial');
    $table->enum('billing_cycle', ['monthly', 'yearly', 'lifetime'])->default('monthly');
    $table->string('notes')->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->index(['user_id', 'status']);
    $table->index('end_date');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
