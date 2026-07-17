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
       Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('slug', 60)->unique();
    $table->string('description')->nullable();
    $table->boolean('is_trial')->default(false);
    $table->boolean('is_popular')->default(false);
    $table->integer('sort_order')->default(0);
    $table->decimal('monthly_price', 10, 2)->default(0);
    $table->decimal('yearly_price', 10, 2)->default(0);
    $table->integer('customer_limit')->default(0);
    $table->integer('sms_limit')->default(0);
    $table->integer('whatsapp_limit')->default(0);
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamp('created_at')->useCurrent();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
