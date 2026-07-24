<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the dummy users we want to create
        $usersToCreate = [
            [
                'name' => 'Admin User',
                'username' => 'admin_super',
                'email' => 'admin@brags.co.uk',
                'role' => 'Admin',
                'details' => [
                    'first_name' => 'Admin',
                    'last_name' => 'Super',
                    'company' => 'Brags Admin Inc',
                    'phone' => '+44 123 456 7890',
                ]
            ],
            [
                'name' => 'John Seller',
                'username' => 'john_seller',
                'email' => 'seller@brags.co.uk',
                'role' => 'Seller',
                'details' => [
                    'first_name' => 'John',
                    'last_name' => 'Seller',
                    'company' => 'Johns Shop',
                    'phone' => '+44 123 456 7891',
                    'billing_city' => 'London',
                    'billing_country' => 'UK'
                ]
            ],
            [
                'name' => 'Jane Customer',
                'username' => 'jane_customer',
                'email' => 'customer@brags.co.uk',
                'role' => 'Customer',
                'details' => [
                    'first_name' => 'Jane',
                    'last_name' => 'Customer',
                    'biographical_info' => 'I love shopping here!',
                    'shipping_city' => 'Manchester',
                    'shipping_country' => 'UK'
                ]
            ]
        ];

        foreach ($usersToCreate as $userData) {
            // Create or update the user
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'username' => $userData['username'],
                    'password' => Hash::make('password123'),
                ]
            );

            // Assign the role to the user
            if (Role::where('name', $userData['role'])->exists()) {
                $user->assignRole($userData['role']);
            }

            // Create the corresponding details record
            $user->detail()->updateOrCreate(
                ['user_id' => $user->id],
                $userData['details']
            );
        }
    }
}
