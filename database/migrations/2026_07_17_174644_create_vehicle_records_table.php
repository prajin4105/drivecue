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
    Schema::create('vehicle_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('customer_name', 150)->nullable();
    $table->string('customer_mobile', 20);
    $table->string('vehicle_number', 30);
    $table->enum('vehicle_type', ['Bike', 'Car', 'Auto', 'Truck', 'Bus', 'Other']);
    $table->enum('fuel_type', ['Petrol', 'Diesel', 'CNG', 'LPG', 'Electric', 'Hybrid'])->nullable();
    $table->string('puc_certificate_number', 100)->nullable();
    $table->date('issue_date');
    $table->date('expiry_date');
    $table->decimal('puc_price', 10, 2)->default(0)->comment('PUC test price / charge paid by the customer');
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'vehicle_number']);
    $table->index(['user_id', 'expiry_date']);
    $table->index('customer_mobile');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_records');
    }
};
