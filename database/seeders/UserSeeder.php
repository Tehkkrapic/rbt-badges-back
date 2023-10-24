<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        $user = User::factory()->create([
             'name' => 'admin',
             'email' => 'test@example.com',
        ]);

        $user->assignRole(Role::whereName('admin')->first());
        $user->save();
    }
}
