<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAndChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create User 1
        $user1 = User::firstOrCreate([
            'email' => 'mak@test',
        ], [
            'name' => 'MakHang',
            'password' => Hash::make('password123'),
        ]);

        // Create User 2 (Authenticated User for example purposes)
        $user2 = User::firstOrCreate([
            'email' => 'PakHang@test',
        ], [
            'name' => 'Pak Hang',
            'password' => Hash::make('password123'),
        ]);

        // Create Channel between the two users
        $channel = $user2->createChannelWith($user1, 'order');

        // Output to confirm the seeding
        $this->command->info("Channel '{$channel->name}' created between {$user1->name} and {$user2->name}.");
    }
}
