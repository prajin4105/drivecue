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
      Schema::create('reminder_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('vehicle_record_id')->nullable()->constrained('vehicle_records')->onDelete('set null');
    $table->string('customer_mobile', 20);
    $table->enum('message_type', ['whatsapp', 'sms']);
    $table->string('reminder_stage', 60);
    $table->text('message_body');
    $table->enum('status', ['pending', 'sent', 'failed', 'opened', 'skipped'])->default('pending');
    $table->text('provider_response')->nullable();
    $table->dateTime('sent_at')->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->index(['user_id', 'created_at']);
  $table->index(
    ['vehicle_record_id', 'reminder_stage', 'message_type'],
    'reminder_logs_idx'
);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
