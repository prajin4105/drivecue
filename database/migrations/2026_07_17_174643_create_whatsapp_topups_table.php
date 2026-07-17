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
    Schema::create('whatsapp_topups', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('package_id')->nullable()->constrained('topup_packages')->onDelete('set null');
    $table->integer('reminders_added')->default(0);
    $table->decimal('price_paid', 10, 2)->default(0);
    $table->enum('payment_status', ['paid', 'pending', 'free'])->default('paid');
    $table->string('notes')->nullable();
    $table->unsignedBigInteger('granted_by')->nullable()->comment('admin user_id');
    $table->timestamp('created_at')->useCurrent();

    $table->index(['user_id', 'created_at']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_topups');
    }
};
