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
        $issueDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $expiryDate = (clone $issueDate)->modify('+' . $this->faker->randomElement([6, 12]) . ' months');
        
        $states = ['GJ', 'MH', 'DL', 'KA', 'TN', 'UP'];
        $vehicleNumber = $this->faker->randomElement($states) . $this->faker->numberBetween(10, 99) . $this->faker->lexify('??') . $this->faker->numberBetween(1000, 9999);

        return [
            'user_id' => 1, // Assume user 1 is the main center owner for the seeder
            'customer_name' => $this->faker->name(),
            'customer_mobile' => '9' . $this->faker->numerify('#########'),
            'vehicle_number' => strtoupper($vehicleNumber),
            'vehicle_type' => $this->faker->randomElement(['Bike', 'Car', 'Auto', 'Truck', 'Bus', 'Other']),
            'fuel_type' => $this->faker->randomElement(['Petrol', 'Diesel', 'CNG', 'LPG', 'Electric', 'Hybrid']),
            'puc_certificate_number' => strtoupper($this->faker->bothify('PUC-####-?????')),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiry_date' => $expiryDate->format('Y-m-d'),
            'puc_price' => $this->faker->randomElement([100, 150, 200, 300]),
            'notes' => null,
            'created_at' => $issueDate->format('Y-m-d H:i:s'),
            'updated_at' => clone $issueDate,
        ];
    }
}
