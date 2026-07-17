<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Super Admin
        User::updateOrCreate(
            ['mobile' => '9999999999'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'center_name' => 'HQ Administration',
                'password' => Hash::make('password'),
                'role' => 0, // Super Admin
                'mobile_verified' => true,
                'status' => 'active',
            ]
        );

        // 2. Create Test Center Owner
        User::updateOrCreate(
            ['mobile' => '8888888888'],
            [
                'first_name' => 'Test',
                'last_name' => 'Owner',
                'center_name' => 'Drive Cue Demo Center',
                'password' => Hash::make('password'),
                'role' => 1, // Center Owner
                'mobile_verified' => true,
                'status' => 'active',
            ]
        );

        // 3. Create Plans
        Plan::updateOrCreate(
            ['slug' => 'trial'],
            [
                'name' => 'Free Trial',
                'description' => 'Try all premium features for 30 days.',
                'is_trial' => true,
                'is_popular' => false,
                'sort_order' => 1,
                'monthly_price' => 0.00,
                'yearly_price' => 0.00,
                'customer_limit' => 100,
                'sms_limit' => 100,
                'whatsapp_limit' => 100,
                'status' => 'active',
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'silver'],
            [
                'name' => 'Silver Plan',
                'description' => 'Best for small scale local PUC centers.',
                'is_trial' => false,
                'is_popular' => false,
                'sort_order' => 2,
                'monthly_price' => 299.00,
                'yearly_price' => 2999.00,
                'customer_limit' => 500,
                'sms_limit' => 1000,
                'whatsapp_limit' => 1000,
                'status' => 'active',
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'gold'],
            [
                'name' => 'Gold Plan',
                'description' => 'Perfect balance for medium scale centers.',
                'is_trial' => false,
                'is_popular' => true, // popular!
                'sort_order' => 3,
                'monthly_price' => 599.00,
                'yearly_price' => 5999.00,
                'customer_limit' => 1500,
                'sms_limit' => 3500,
                'whatsapp_limit' => 3500,
                'status' => 'active',
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'platinum'],
            [
                'name' => 'Platinum Plan',
                'description' => 'Ultimate plan for large scale vehicle inspection hubs.',
                'is_trial' => false,
                'is_popular' => false,
                'sort_order' => 4,
                'monthly_price' => 1199.00,
                'yearly_price' => 11999.00,
                'customer_limit' => 999999,
                'sms_limit' => 10000,
                'whatsapp_limit' => 10000,
                'status' => 'active',
            ]
        );
    }
}
