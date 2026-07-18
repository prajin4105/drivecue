<?php

namespace Database\Factories;

use App\Models\VehicleRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;


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
    $faker = FakerFactory::create();

    $issueDate = $faker->dateTimeBetween('-1 year', 'now');
    $expiryDate = (clone $issueDate)->modify('+' . $faker->randomElement([6, 12]) . ' months');

    $states = ['GJ', 'MH', 'DL', 'KA', 'TN', 'UP'];

    $vehicleNumber = $faker->randomElement($states)
        . $faker->numberBetween(10, 99)
        . $faker->lexify('??')
        . $faker->numberBetween(1000, 9999);

    return [
        'user_id' => 1,
        'customer_name' => $faker->name(),
        'customer_mobile' => '9' . $faker->numerify('#########'),
        'vehicle_number' => strtoupper($vehicleNumber),
        'vehicle_type' => $faker->randomElement(['Bike', 'Car', 'Auto', 'Truck', 'Bus', 'Other']),
        'fuel_type' => $faker->randomElement(['Petrol', 'Diesel', 'CNG', 'LPG', 'Electric', 'Hybrid']),
        'puc_certificate_number' => strtoupper($faker->bothify('PUC-####-?????')),
        'issue_date' => $issueDate->format('Y-m-d'),
        'expiry_date' => $expiryDate->format('Y-m-d'),
        'puc_price' => $faker->randomElement([100, 150, 200, 300]),
        'notes' => null,
        'created_at' => $issueDate,
        'updated_at' => $issueDate,
    ];
}
}
