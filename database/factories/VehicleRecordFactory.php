<?php

namespace Database\Factories;

use App\Models\VehicleRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleRecord>
 */
class VehicleRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    $issueDate = fake()->dateTimeBetween('-1 year', 'now');

    $expiryDate = (clone $issueDate)->modify('+' . fake()->randomElement([6, 12]) . ' months');

    $states = ['GJ', 'MH', 'DL', 'KA', 'TN', 'UP'];

    $vehicleNumber = fake()->randomElement($states)
        . fake()->numberBetween(10, 99)
        . fake()->lexify('??')
        . fake()->numberBetween(1000, 9999);

    return [
        'user_id' => 1,
        'customer_name' => fake()->name(),
        'customer_mobile' => '9' . fake()->numerify('#########'),
        'vehicle_number' => strtoupper($vehicleNumber),
        'vehicle_type' => fake()->randomElement(['Bike', 'Car', 'Auto', 'Truck', 'Bus', 'Other']),
        'fuel_type' => fake()->randomElement(['Petrol', 'Diesel', 'CNG', 'LPG', 'Electric', 'Hybrid']),
        'puc_certificate_number' => strtoupper(fake()->bothify('PUC-####-?????')),
        'issue_date' => $issueDate->format('Y-m-d'),
        'expiry_date' => $expiryDate->format('Y-m-d'),
        'puc_price' => fake()->randomElement([100, 150, 200, 300]),
        'notes' => null,
        'created_at' => $issueDate,
        'updated_at' => $issueDate,
    ];
}
}
