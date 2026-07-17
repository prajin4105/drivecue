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
      Schema::create('topup_packages', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->integer('reminders_count')->default(0);
    $table->decimal('price', 10, 2)->default(0);
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->integer('sort_order')->default(0);
    $table->timestamp('created_at')->useCurrent();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_packages');
    }
};
